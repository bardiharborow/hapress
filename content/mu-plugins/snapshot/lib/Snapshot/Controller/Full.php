<?php

/**
 * Full backups controller abstraction
 */
class Snapshot_Controller_Full {

	/**
	 * Singleton instance
	 *
	 * @var object Snapshot_Controller_Full
	 */
	private static $_instance;

	/**
	 * Model reference
	 *
	 * @var object Snapshot_Model_Full_Backup
	 */
	protected $_model;

	/**
	 * Constructs an instance, never to the outside world.
	 *
	 * Also sets up a model reference to be used by it and
	 * implementing classes.
	 */
	protected function __construct () {
		$this->_model = new Snapshot_Model_Full_Backup;
	}

	/**
	 * No public cloning kthxbai
	 */
	protected function __clone () {}

	/**
	 * Singleton instance getter
	 *
	 * @return object Snapshot_Controller_Full
	 */
	public static function get () {
		if (empty(self::$_instance)) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * Gets the backup type of this controller
	 *
	 * For future use
	 *
	 * @return string Backup type
	 */
	protected function _get_backup_type () {
		return 'full';
	}

	/**
	 * Gets prefixed filter/action name
	 *
	 * @param string $action Action/filter name to prefix
	 *
	 * @return string
	 */
	public function get_filter ($action) {
		return 'snapshot-controller-' . $this->_get_backup_type() . '-' . $action;
	}

	/**
	 * Dispatch admin view, AJAX and cron controllers.
	 */
	public function run () {
		Snapshot_Controller_Full_Log::get()->run();

		if (is_admin() && current_user_can('manage_snapshots_items')) {
			Snapshot_Controller_Full_Admin::get()->run();
			Snapshot_Controller_Full_Ajax::get()->run();
		}

		Snapshot_Controller_Full_Cron::get()->run();
	}

	/**
	 * Deactivates the full backups
	 */
	public function deactivate () {
		Snapshot_Controller_Full_Admin::get()->deactivate();
		Snapshot_Controller_Full_Ajax::get()->deactivate();
		Snapshot_Controller_Full_Cron::get()->deactivate();
	}

	/**
	 * Check if we're all set to actually do backups
	 *
	 * @return bool
	 */
	protected function _is_backup_processing_ready () {
		if (!$this->_model->has_dashboard()) return false; // No dashboard
		if (!$this->_model->is_active()) return false; // Not activated

		return true;
	}

	/**
	 * Actually start the backup.
	 *
	 * @param string $idx Backup index to start
	 *
	 * @return bool
	 */
	protected function _start_backup ($idx) {
		$backup = new Snapshot_Helper_Backup;

		Snapshot_Helper_Log::info("Starting backup");

		$files = new Snapshot_Model_Queue_Fileset($idx);
		$files->clear();
		$files->add_source($idx);

		// Use the factory method to access all tables
		$tables = Snapshot_Model_Queue_Tableset::all($idx);

		$status = $backup->create($idx);
		$status = $backup->add_queue($files);
		$status = $backup->add_queue($tables);

		Snapshot_Helper_Log::info("Created the backup and added the queues");

		$status = $backup->save();

		if (empty($status)) Snapshot_Helper_Log::warn("There was an error in initial backup saving");
		else Snapshot_Model_Full_Error::get()->clear();

		return $status;
	}

	/**
	 * Actually process the backup
	 *
	 * @throws Snapshot_Exception On error limit reached.
	 *
	 * @param string $idx Backup index to process
	 *
	 * @return bool Is backup done?
	 */
	protected function _process_backup ($idx) {
		$status = false;
		$errors = Snapshot_Model_Full_Error::get();

		// Start by expecting this to fail
		$errors->add(Snapshot_Model_Full_Error::ERROR_GENERAL);

		$backup = Snapshot_Helper_Backup::load($idx);

		$error = $errors->get_offending();
		if (!empty($error)) {
			Snapshot_Helper_Log::info("We have an offending error: {$error}");

			/**
			 * Automatic backup processing encountered too many errors
			 *
			 * @since 3.0-beta-12
			 *
			 * @param string Action type indicator (process or finish)
			 * @param string $error Offending error message key
			 * @param object|bool $backup A Snapshot_Helper_Backup instance, or (bool)false
			 */
			do_action($this->get_filter('error'), 'process', $error, $backup); // Notify anyone interested

			if (!empty($backup)) $backup->stop_and_remove(); // Delete everything, we're not going further
			throw new Snapshot_Exception($error);
		}

		if (!empty($backup)) {
			$error_key = $errors->get_current_error_key($backup);
			$errors->remove(Snapshot_Model_Full_Error::ERROR_GENERAL); // We will have a more specific error to store
			$errors->add($error_key); // We now store the more specific error

			$status = $backup->process_files();

			if (empty($status)) {
				Snapshot_Helper_Log::warn("There was an error processing the files");
			} else {
				$errors->remove($error_key); // So we're good, clear the backup error
				Snapshot_Helper_Log::info("Successfully processed backup files chunk");
			}

			$backup->save();

			$status = $backup->is_done();
		}

		return $status;
	}

	/**
	 * Wrapping up and clearing backup.
	 *
	 * @throws Snapshot_Exception On error limit reached.
	 *
	 * @param string $idx Backup index to process
	 *
	 * @return bool
	 */
	protected function _finish_backup ($idx) {
		$errors = Snapshot_Model_Full_Error::get();

		// Start by expecting this to fail
		$errors->add(Snapshot_Model_Full_Error::ERROR_POSTPROCESS);

		$backup = Snapshot_Helper_Backup::load($idx);

		$error = $errors->get_offending();
		if (!empty($error)) {
			Snapshot_Helper_Log::info("We have an offending error! {$error}");

			/**
			 * Automatic backup processing encountered too many errors
			 *
			 * @since 3.0-beta-12
			 *
			 * @param string Action type indicator (process or finish)
			 * @param string $error Offending error message key
			 * @param object|bool $backup A Snapshot_Helper_Backup instance, or (bool)false
			 */
			do_action($this->get_filter('error'), 'finish', $error, $backup); // Notify anyone interested

			if (!empty($backup)) $backup->stop_and_remove(); // Delete everything, we're not going further
			throw new Snapshot_Exception($error);
		}

		if (!$backup) {
			// Scenario - unable to load backup directly
			// This means that we have already wrapped up backup creation and
			// attempted backup post-processing, which also moves resulting file
			// to its final destination locally.
			// Now, to continue with the upload.

			$session = Snapshot_Helper_Backup::get_session($idx);
			if (empty($session->data['timestamp'])) {
				Snapshot_Helper_Log::error("There was an error continuing backup finalization");
				return false;
			}
			$timestamp = $session->data['timestamp'];

			$errors->remove(Snapshot_Model_Full_Error::ERROR_POSTPROCESS); // We're good here
			Snapshot_Helper_Log::info("Continuing backup finalization");

			// Record status, we will be using this
			$status = $this->_model->continue_item_upload($timestamp);

			if ($status) {
				// Alright, so the continued upload completed.
				// Let's notify the service we're done here
				return $this->_notify_service_about_upload($timestamp);
			}

			return $status; // Not done yet, so carry on
		}

		// Default scenario - backup directly loaded
		// Meaning, we still need to postprocess. So let's get on with it.
		$backup->clear();
		$status = $backup->postprocess();


		// And now, push it. Aah. Push it good.
		if ($status) {
			$errors->remove(Snapshot_Model_Full_Error::ERROR_POSTPROCESS);
			$status = $this->_model->send_backup($backup);
		} else {
			Snapshot_Helper_Log::error("There was an error postprocessing the backup");
		}

		if (!empty($status)) {
			$errors->clear();
			Snapshot_Helper_Log::info("Backup successfully finalized");
			$timestamp = $backup->get_timestamp();
			$status = $this->_notify_service_about_upload($timestamp); // Carry on with backup notification
		} else {
			Snapshot_Helper_Log::info("Postpone backup finalization and service notification");
		}

		return $status;
	}

	/**
	 * Notifies the service about our backup being uploaded
	 *
	 * @param int $timestamp UNIX timestamp
	 *
	 * @return bool
	 */
	protected function _notify_service_about_upload ($timestamp) {
		Snapshot_Helper_Log::info("Pinging service");

		if (empty($timestamp) || !is_numeric($timestamp)) {
			Snapshot_Helper_Log::warn("Invalid timestamp, giving up");
			return false;
		}

		// Also update remote schedule
		// This is because we rely on this call to cache the icon status timestamp
		// https://app.asana.com/0/11140230629075/167863403840660
		$status = $this->_model->update_remote_schedule($timestamp);

		if ($status) Snapshot_Helper_Log::info("Service received our last backup info");
		else Snapshot_Helper_Log::warn("We encountered an issue commmunicating last backup info to service");

		return !!$status;
	}

}