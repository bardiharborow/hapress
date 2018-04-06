<?php

/**
 * Deals with managed (full) backups restoration
 */
class Snapshot_Helper_Restore {

	private $_archive;
	private $_manifest;
	private $_queues;
	private $_seed;
	private $_destination;
	private $_session;

	private function __construct () {}

	public static function from ($archive) {
		$me = new self;
		$me->set_archive_path($archive);
		$me->_spawn_queues();
		return $me;
	}

	public function to ($path) {
		$this->_destination = untrailingslashit(wp_normalize_path(realpath($path)));
	}


	public function get_archive_path () {
		return $this->_archive;
	}

	public function set_archive_path ($archive) {
		$fullpath = realpath($archive);
		if (empty($fullpath) || !is_readable($fullpath)) return false;

		$status = !!($this->_archive = wp_normalize_path($fullpath));
		if ($status) {
			$this->_seed = sha1_file($this->_archive);
		}

		return $status;
	}

	public function get_manifest () {
		if (!empty($this->_manifest)) return $this->_manifest;

		if (empty($this->_archive)) {
			Snapshot_Helper_Log::warn("Unable to fetch manifest from unknown archive.");
			return false;
		}
		$zip = Snapshot_Helper_Zip::get($this->_archive);

		$root = $this->_get_root();
		$manifest_file = Snapshot_Model_Manifest::get_file_name();
		$manifest_path = wp_normalize_path($root . '/' . $manifest_file);

		if (file_exists($manifest_path)) @unlink($manifest_path);

		$status = $zip->extract_specific($root, array($manifest_file));
		if (empty($status)) {
			Snapshot_Helper_Log::warn("Unable to extract manifest.");
			return false;
		}

		$this->_manifest = Snapshot_Model_Manifest::consume($manifest_path);

		unlink($manifest_path);

		return $this->_manifest;
	}

	public function get_intermediate_destination () {
		if (empty($this->_seed)) {
			Snapshot_Helper_Log::info("Unable determine intermediate location from unknown seed.");
			return false;
		}
		return $this->_get_path($this->_seed);
	}

	public function get_queues () {
		if (empty($this->_queues)) $this->_spawn_queues();
		return $this->_queues;
	}

	/**
	 * Check if the queues restore is done
	 *
	 * @return bool
	 */
	public function is_done () {
		$queues = is_array($this->_queues) ? $this->_queues : array();
		foreach ($queues as $idx => $queue) {
			if (!$this->_queue_done($idx)) return false;
		}
		return true;
	}

	public function clear () {
		Snapshot_Helper_Log::info("Starting post-restoration cleanup");

		$this->_session->data = array();
		$this->_session->save_session();

		Snapshot_Helper_Utility::recursive_rmdir($this->get_intermediate_destination());

		Snapshot_Helper_Log::info("Post-restoration cleanup complete");

		return true;
	}

	/**
	 * Process the entire files queue, working directly with archive
	 *
	 * @return bool
	 */
	public function process_files () {
		$zip = Snapshot_Helper_Zip::get($this->_archive);
		$queues = is_array($this->_queues) ? $this->_queues : array();
		$files = array();
		$status = false;

		foreach ($queues as $type => $queue) {
			if ($this->_queue_done($type)) continue;
			$method = '_process_' . $type . '_queue';
			if (!is_callable(array($this, $method))) continue;

			$status = call_user_func_array(array($this, $method), array($queue));
			break;
		}

		if ($this->is_done()) Snapshot_Helper_Log::info("Restoration from queues complete");

		return $status;
	}


	private function _process_fileset_queue ($q) {
		$chunk_size = $q->get_chunk_size();
		$chunk = (int)$this->_get_session_value('fileset', 'chunk', 0);
		$start = $chunk * $chunk_size;

		$status = true;

		$prefix = $q->get_prefix();
		$source = untrailingslashit($this->get_intermediate_destination() . $prefix);
		$destination = trailingslashit(wp_normalize_path($this->_destination));

		$all_files = Snapshot_Helper_Utility::scandir($source);
		if (empty($all_files)) return false;

		$files = array_slice($all_files, $start, $chunk_size);
		foreach ($files as $file) {
			$filepath = preg_replace('/^' . preg_quote($source, '/') . '/i', '', $file);
			$path = trim(wp_normalize_path(dirname($filepath)), '/');
			$fullpath = trailingslashit(wp_normalize_path("{$destination}{$path}"));

			if (!is_dir($fullpath)) wp_mkdir_p($fullpath);

			// Attempt regular copy first
			if (!copy($file, $fullpath . basename($file))) {
				$status = false;
				global $wp_filesystem;
				// Fall back to WP stuff
				if (is_callable(array($wp_filesystem, 'copy'))) {
					$res = $wp_filesystem->copy($file, $fullpath . basename($file));
					if ($res) $status = true;
				}
			}
		}

		if ($status) {
			Snapshot_Helper_Log::info("Restored fileset chunk {$chunk}");

			$chunk += 1;
			$done = !!($start + $chunk_size >= count($all_files));

			if ($done) Snapshot_Helper_Log::info("Fileset restoration complete");

			$this->_set_session_value('fileset', 'chunk', $chunk);
			$this->_set_session_value('fileset', 'done', $done);
		} else Snapshot_Helper_Log::warn("There has been an issue restoring fileset chunk {$chunk}");

		return $status;
	}

	private function _process_tableset_queue ($q) {
		$tables = $this->_get_session_value('tableset', 'tables', array());
		$source = untrailingslashit($this->get_intermediate_destination());
		$all_tables = array();

		$all_files = Snapshot_Helper_Utility::scandir($source);
		if (empty($all_files)) return false;

		foreach ($all_files as $file) {
			if (!preg_match('/\.sql$/i', $file)) continue;
			$filepath = preg_replace('/^' . preg_quote($source, '/') . '/i', '', $file);
			$filepath = trim(wp_normalize_path(dirname($filepath)), '/');
			if (0 !== strlen($filepath)) continue; // Not top level... not interested

			$all_tables[] = $file;
		}

		if (empty($all_tables)) return true; // No sqls found

		$status = true;
		$db = new Snapshot_Model_Database_Backup;

		do_action('snapshot-full_backups-restore-tables', $all_tables, $tables);

		foreach ($all_tables as $table_file) {
			$table = basename($table_file);
			if (in_array($table, $tables)) continue;

			Snapshot_Helper_Log::info("Begin restoring table: {$table}");

			$sql = file_get_contents($table_file);
			$db->restore_databases($sql);

			if (count($db->errors)) {
				$status = false;
				Snapshot_Helper_Log::error("There has been an error restoring {$table}");
			} else {
				if ($this->_postprocess_table($table)) {
					$tables[] = $table;
					$this->_set_session_value('tableset', 'tables', $tables);
					if (count($tables) === count($all_tables)) {
						if ($this->_postprocess_global_tables()) {
							$this->_set_session_value('tableset', 'done', true);
						} else Snapshot_Helper_Log::warn("There has been an issue prostprocessing global tables");
					}
				} else Snapshot_Helper_Log::warn("There has been an issue prostprocessing table {$table}");
			}
			break; // Do one table at the time
		}

		return $status;

	}

	/**
	 * Post-process tables.
	 *
	 * Eventually will replace Snapshot::snapshot_ajax_restore_convert_db_content()
	 * In current iteration, full backups just restore to whatever the stored point was.
	 * No post-processing
	 *
	 * @param string $table_name Table name to post-process
	 *
	 * @return bool
	 */
	private function _postprocess_table ($table_name) {
		return true; // Full backups don't do table post-processing
	}

	/**
	 * Post-process global tables
	 *
	 * Eventually will replace Snapshot::_postprocess_global_tables()
	 * In current iteration, full backups just restore to whatever the stored point was.
	 * No post-processing
	 *
	 * @return bool
	 */
	private function _postprocess_global_tables () {
		return true; // Full backups don't do global post-processing.
	}

	private function _get_root () {
		return trailingslashit(wp_normalize_path(
			trailingslashit(wp_normalize_path(WPMUDEVSnapshot::instance()->get_setting('backupRestoreFolderFull'))) . '_imports'
		));
	}

	private function _get_path ($frag) {
		$frag = preg_replace('/[^-_a-z0-9]/', '', $frag);
		if (empty($frag)) return false;

		return trailingslashit(wp_normalize_path($this->_get_root() . $frag));
	}

	private function _extract () {
		$fullpath = $this->get_intermediate_destination();

		if (empty($fullpath)) {
			Snapshot_Helper_Log::warn("Unable to determine the intermediate location for restoring.");
			return false; // Something went wrong
		}
		if (is_dir($fullpath)) return false; // Already extracted

		wp_mkdir_p($fullpath);
		if (!is_dir($fullpath)) {
			Snapshot_Helper_Log::warn("Unable to create intermediate location: {$fullpath}");
			return false; // Couldn't create
		}

		$zip = Snapshot_Helper_Zip::get($this->_archive);
		return $zip->extract($fullpath);
	}

	private function _spawn_queues () {
		$fullpath = $this->get_intermediate_destination();
		if (empty($fullpath)) return false;

		$manifest = $this->get_manifest();
		if( !$manifest ) return false;
		$queues = $manifest->get('QUEUES');

		// Boot session
		$loc = trailingslashit(WPMUDEVSnapshot::instance()->get_setting('backupSessionFolderFull'));
		$this->_session = new Snapshot_Helper_Session($loc, $this->_seed);
		$this->_session->load_session();

		$this->_extract();

		foreach ($queues as $raw) {
			if (empty($raw['type']) || empty($raw['sources'])) continue;
			$class_name = 'Snapshot_Model_Queue_' . ucfirst($raw['type']);
			if (!class_exists($class_name)) continue;

			$queue = new $class_name('restore');

			$this->_queues[$raw['type']] = $queue;
		}
		if (!empty($this->_queues)) ksort($this->_queues); // Sort queues, fileset coming before tableset alphabetically

		return $this->_queues;
	}

	private function _queue_done ($type) {
		return $this->_get_session_value($type, 'done');
	}

	private function _get_session_value ($section, $key, $fallback=false) {
		if (!isset($this->_session->data[$section])) return $fallback;
		if (!isset($this->_session->data[$section][$key])) return $fallback;

		return $this->_session->data[$section][$key];
	}

	private function _set_session_value ($section, $key, $value) {
		if (empty($this->_session->data[$section])) $this->_session->data[$section] = array();

		$this->_session->data[$section][$key] = $value;
		return $this->_session->save_session();
	}
}