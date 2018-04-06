<?php

/**
 * Full backup creation helper
 */
class Snapshot_Helper_Backup {

	const FINAL_PREFIX = 'full_backup';

	/**
	 * Internal errors reference
	 *
	 * @var array
	 */
	private $_errors;

	/**
	 * Internal blog ID reference
	 *
	 * @var number
	 */
	private $_blog_id;

	/**
	 * Internal queues reference
	 *
	 * @var array
	 */
	private $_queues = array();

	/**
	 * Internal backup index reference
	 *
	 * Used for backup resolution
	 *
	 * @var string
	 */
	private $_idx;

	/**
	 * Internal backup timestamp reference
	 *
	 * @var int
	 */
	private $_timestamp;

	/**
	 * Creates the backup helper instance
	 */
	public function __construct () {}

	/**
	 * Gets the current blog ID
	 *
	 * @return int
	 */
	public function get_blog_id () {
		return (int)$this->_blog_id;
	}

	/**
	 * Sets the current blog ID
	 *
	 * @param int $blog_id Blog ID
	 */
	public function set_blog_id ($blog_id=null) {
		return $this->_blog_id = $blog_id;
	}

	/**
	 * Create a backup location
	 * Can be called multiple times, won't re-create the location
	 *
	 * @param string $idx End location
	 *
	 * @return bool
	 */
	public function create ($idx) {
		$status = false; // Start with assumed failure

		if (!preg_match('/^[-_a-z0-9]+$/', $idx)) return $this->_set_error(sprintf(__('Invalid destination: %s', SNAPSHOT_I18N_DOMAIN), $idx));

		$path = $this->resolve_backup($idx);
		if (empty($path)) return $status;

		$this->_idx = $idx;
		$this->_timestamp = time();

		$status = true;
		return $status;
	}

	/**
	 * Add a queue to current backup
	 *
	 * @param Snapshot_Model_Queue $queue Queue instance
	 *
	 * @return bool
	 */
	public function add_queue ($queue) {
		if (!($queue instanceof Snapshot_Model_Queue)) return false;
		$this->_queues[] = $queue;
		return true;
	}

	/**
	 * Loads the session instance
	 *
	 * @param string $idx Index of the session to load
	 *
	 * @return object Snapshot_Helper_Session instance
	 */
	public static function get_session ($idx) {
		$loc = trailingslashit(WPMUDEVSnapshot::instance()->get_setting('backupSessionFolderFull'));
		$session = new Snapshot_Helper_Session($loc, Snapshot_Helper_String::conceal("backup_{$idx}"));
		return $session;
	}

	/**
	 * Check if a given archive name is a full backup
	 *
	 * @param string $filename Filename to check
	 * @param int $timestamp Optional timestamp
	 *
	 * @return bool
	 */
	public static function is_full_backup ($filename, $timestamp=false) {
		$timestamp = (int)$timestamp;
		if (empty($timestamp)) {
			$timestamp = '[0-9]+';
		}
		return (bool)preg_match(
			'/' .
				preg_quote(Snapshot_Helper_Backup::FINAL_PREFIX, '/') . '-' . $timestamp . '-full-[A-Za-z0-9]+\.zip$' .
			'/',
			$filename
		);
	}

	/**
	 * Saves backup progress indicators
	 *
	 * This is achieved by saving current state in the session
	 *
	 * @return bool
	 */
	public function save () {
		$session = self::get_session($this->_idx);
		$session->data = array(
			'queues' => $this->get_queues(),
			'timestamp' => $this->_timestamp,
		);

		return $session->save_session();
	}

	/**
	 * Loads backup state from session
	 *
	 * @param string $idx Backup index to load
	 *
	 * @return mixed Snapshot_Helper_Backup instance on success, (bool)false on failure
	 */
	public static function load ($idx) {
		$session = self::get_session($idx);
		$session->load_session();
		if (empty($session->data) || !is_array($session->data)) return false;

		$me = new self;
		$me->create($idx);

		$queues = !empty($session->data['queues']) && is_array($session->data['queues'])
			? $session->data['queues']
			: array()
		;
		if (empty($queues)) return false;

		foreach ($queues as $item) {
			if (empty($item['type']) || empty($item['sources'])) continue;
			$class = 'Snapshot_Model_Queue_' . ucfirst($item['type']);
			$queue = new $class($idx);
			foreach ($item['sources'] as $source) {
				$queue->add_source($source);
			}
			$me->add_queue($queue);
		}

		$me->_timestamp = !empty($session->data['timestamp'])
			? $session->data['timestamp']
			: time()
		;

		return $me;
	}

	/**
	 * Get queues list
	 *
	 * @return array
	 */
	public function get_queues () {
		if (empty($this->_queues)) return array();

		$result = array();
		foreach ($this->_queues as $queue) {
			if (!($queue instanceof Snapshot_Model_Queue)) continue;
			$result[] = array(
				'type' => strtolower(preg_replace('/Snapshot_Model_Queue_/', '', get_class($queue))),
				'sources' => $queue->get_sources(),
			);
		}
		return $result;
	}

	/**
	 * Estimates the total steps the backup will take to run
	 *
	 * @return int
	 */
	public function get_total_steps_estimate () {
		$size = 0;
		if (empty($this->_queues)) return $size;

		foreach ($this->_queues as $queue) {
			$size += $queue->get_total_steps();
		}

		return $size;
	}

	/**
	 * Check if the queues backup is done
	 *
	 * @return bool
	 */
	public function is_done () {
		$queues = is_array($this->_queues) ? $this->_queues : array();
		foreach ($queues as $queue) {
			if (!$queue->is_done()) return false;
		}
		return true;
	}

	/**
	 * Call clear on all queues
	 *
	 * @return bool
	 */
	public function clear () {
		$this->_create_manifest();
		$queues = is_array($this->_queues) ? $this->_queues : array();
		foreach ($queues as $queue) {
			$queue->clear();
		}
		// and now! clean up ourselves
		$this->_queues = array();
		$this->save();
		return true;
	}


	/**
	 * Stops and removes currently processing backup
	 *
	 * @return bool
	 */
	public function stop_and_remove () {
		$this->clear(); // Clear all queues

		// Now drop all intermediate backup files
		$intermediate_path = $this->get_archive_path($this->_idx);
		if (!file_exists($intermediate_path)) return false;

		return @unlink($intermediate_path);
	}

	/**
	 * Last step in a backup - postprocess the created archive
	 *
	 * @return bool
	 */
	public function postprocess () {
		// Lastly, move archive - first, get the intermediate archive path
		$intermediate_path = $this->get_archive_path($this->_idx);
		if (!file_exists($intermediate_path)) return false;

		// Next up, create the new filename
		$destination = $this->get_destination_path();
		if (file_exists($destination)) return false;

		// Lastly, move it
		return rename($intermediate_path, $destination);
	}

	/**
	 * Fetch backup timestamp
	 *
	 * @return int UNIX timestamp
	 */
	public function get_timestamp () {
		return !empty($this->_timestamp)
			? (int)$this->_timestamp
			: 0
		;
	}

	/**
	 * Gets the final destination filename.
	 *
	 * @return string
	 */
	public function get_destination_filename () {
		static $filename;

		if (empty($filename)) {
			$intermediate_path = $this->get_archive_path($this->_idx);
			$filename = self::FINAL_PREFIX . '-' . $this->get_timestamp() . '-' . $this->_idx . '-' . Snapshot_Helper_Utility::get_file_checksum($intermediate_path) . '.zip';
		}

		return $filename;
	}

	/**
	 * Get full destination path.
	 *
	 * @return string
	 */
	public function get_destination_path () {
		$filename = $this->get_destination_filename();
		$destination = trailingslashit(WPMUDEVSnapshot::instance()->get_setting('backupBaseFolderFull'));
		return "{$destination}{$filename}";
	}

	/**
	 * Get full path for a certain backup
	 *
	 * @param string $idx End location
	 *
	 * @return string Full backup path
	 */
	public function get_path ($idx) {
		$destination = preg_replace('/[^-_a-z0-9]/', '', Snapshot_Helper_String::conceal(basename($idx)));
		if (empty($destination)) return $this->_set_error(sprintf(__('Invalid destination: %s', SNAPSHOT_I18N_DOMAIN), $idx));

		return trailingslashit(WPMUDEVSnapshot::instance()->get_setting('backupBackupFolderFull')) . $destination;
	}

	/**
	 * Get archive file name (basename)
	 *
	 * @return string Archive file basename
	 */
	public function get_archive_name () {
		return Snapshot_Helper_String::conceal('archive.zip');
	}

	/**
	 * Archive path getter
	 *
	 * @param string $idx End location
	 *
	 * @return string Full archive path
	 */
	public function get_archive_path ($idx) {
		return trailingslashit($this->get_path($idx)) . $this->get_archive_name();
	}

	/**
	 * Resolve the backup full path
	 * If destination doesn't exist, it will be created
	 *
	 * @param string $idx End location
	 *
	 * @return mixed (bool)false on failure, (string)full path on success
	 */
	public function resolve_backup ($idx) {
		$path = $this->get_path($idx);
		if (empty($path)) return false;

		if (file_exists($path)) return true; // Already done, moving on

		wp_mkdir_p($path);

		if (!file_exists($path)) return $this->_set_error(sprintf(__('Backup path creation failed: %s', SNAPSHOT_I18N_DOMAIN), $path));
		if (!is_writable($path)) return $this->_set_error(sprintf(__('Backup path not writable: %s', SNAPSHOT_I18N_DOMAIN), $path));

		return $path;
	}

	/**
	 * Process the entire files queue, working directly with archive
	 *
	 * @return bool
	 */
	public function process_files () {
		$path = $this->get_archive_path($this->_idx);

		$zip = Snapshot_Helper_Zip::get($path);
		$queues = is_array($this->_queues) ? $this->_queues : array();
		$files = array();
		$status = false;

		foreach ($queues as $queue) {
			if ($queue->is_done()) continue;

			$current_source = $queue->get_current_source();

			$status = true; // We still have a queue to process
			$files = $queue->get_files();

			$zip->set_root($queue->get_root());
			$prefix = $queue->get_prefix();

			$next_source = $queue->get_current_source();

			if (!empty($files)) $status = $zip->add($files, $prefix);
			else if (!empty($current_source['chunk']) && !empty($next_source['chunk'])) {
				$status = $current_source['chunk'] !== $next_source['chunk'];
			} else $status = false;

			break;
		}

		return $status;
	}

	/**
	 * Gets the first undone queue
	 *
	 * @return object|false Snapshot_Model_Queue instance, or false
	 */
	public function get_current_queue () {
		$queues = is_array($this->_queues) ? $this->_queues : array();
		foreach ($queues as $queue) {
			if ($queue->is_done()) continue;

			return $queue;
		}
		return false;
	}

	/**
	 * Get all recorded errors.
	 *
	 * @return array All errors encountered this far
	 */
	public function errors () {
		return $this->_errors;
	}

	/**
	 * Get manifest object for this backup
	 *
	 * @return object Snapshot_Model_Manifest instance
	 */
	public function get_manifest () {
		static $manifest;
		if (empty($manifest)) $manifest = Snapshot_Model_Manifest::create($this);

		return $manifest;
	}

	/**
	 * Create a manifest file for this backup
	 *
	 * @return bool
	 */
	private function _create_manifest () {
		$archive = $this->get_archive_path($this->_idx);
		$zip = Snapshot_Helper_Zip::get($archive);

		$manifest = $this->get_manifest();
		$path = trailingslashit($this->get_path($this->_idx));
		$file = $path . Snapshot_Model_Manifest::get_file_name();

		file_put_contents($file, $manifest->get_flat());

		$zip->set_root($path);
		$status = $zip->add(array($file));

		if (file_exists($file)) @unlink($file);

		return $status;
	}

	/**
	 * Add error to the queue
	 *
	 * @param string $string Error message
	 */
	private function _set_error ($string) {
		$this->_errors[] = $string;
		return false;
	}

}