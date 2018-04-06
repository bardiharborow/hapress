<?php

/**
 * Hub actions controller
 *
 * @since v3.0.5-BETA-4
 */
class Snapshot_Controller_Full_Hub extends Snapshot_Controller_Full {

	const ACTION_CLEAR_API_CACHE = 'clear_cache';
	const ACTION_SET_KEY = 'set_key';
	const ACTION_SCHEDULE_BACKUPS = 'schedule_backups';
	const ACTION_START_BACKUP = 'start_backup';

	private $_running = false;

	/**
	 * Internal instance reference
	 *
	 * @var object Snapshot_Controller_Full_Ajax instance
	 */
	private static $_instance;

	/**
	 * Singleton instance getter
	 *
	 * @return object Snapshot_Controller_Full_Ajax instance
	 */
	public static function get () {
		if (empty(self::$_instance)) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * Dispatch Hub actions handling.
	 */
	public function run () {
		if ($this->is_running()) return false;

		add_filter( 'wdp_register_hub_action', array($this, 'register_endpoints') );
		$this->_running = true;
	}

	/**
	 * Runs on deactivation
	 */
	public function deactivate () {}

	/**
	 * Checks to see if we're running already
	 *
	 * @return bool
	 */
	public function is_running () {
		return $this->_running;
	}

	/**
	 * Gets the list of known Hub actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions () {
		$known = array(
			self::ACTION_CLEAR_API_CACHE,
			self::ACTION_SET_KEY,
			self::ACTION_SCHEDULE_BACKUPS,
			self::ACTION_START_BACKUP,
		);
		return $known;
	}

	/**
	 * Registers handlers for actions pushed from the Hub
	 *
	 * @param array Known actions
	 *
	 * @return array Augmented actions
	 */
	public function register_endpoints ($actions) {
		if (!is_array($actions)) return $actions;

		$known = $this->get_known_actions();
		if (!is_array($known)) return $actions;

		foreach ($known as $action_raw_name) {
			$method = "json_{$action_raw_name}";
			if (!is_callable(array($this, $method))) continue; // We don't know how to handle this action

			$action_name = "snapshot_{$action_raw_name}";
			$actions[$action_name] = array($this, $method);
		}

		return $actions;
	}

	/**
	 * Cache clearing implementation helper
	 *
	 * Clears API creds cache.
	 * Called by the JSON request handler.
	 *
	 * @return array|WP_Error Status array on success, error object on failure
	 */
	public function clear_api_cache() {
		$status = false;

		$api = Snapshot_Model_Full_Remote_Api::get();
		$api->clean_up_api();
		Snapshot_Helper_Log::info('API cache cleaned up, attempting to re-connect now', 'Remote');

		$status = $api->connect();

		return empty($status)
			? new WP_Error(Snapshot_Helper_Log::LEVEL_WARNING, 'Error re-connecting to refresh API cache')
			: array('code' => 0,)
		;
	}

	/**
	 * Cache clearing Hub request handler
	 *
	 * Clears API creds cache.
	 * Fires on membership upgrade, if the user was out of space.
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function json_clear_cache ($params, $action) {
		Snapshot_Helper_Log::info('Cache cleanup request received, attempting to process', 'Remote');
		$status = $this->clear_api_cache();

		if (is_wp_error($status)) {
			Snapshot_Helper_Log::info('Issue encountered with cache cleanup/refresh', 'Remote');
			wp_send_json_error($status);
		} else {
			Snapshot_Helper_Log::info('Cache successfully refreshed', 'Remote');
			wp_send_json_success($status);
		}
	}

	/**
	 * Trigger new key exchange
	 *
	 * Provides a OTP token, then snapshot should fetch the real key using
	 * that, responding with success or error message.
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function json_set_key ($params, $action) {
		Snapshot_Helper_Log::error('OTP set key request received', 'Remote');

		$status = false;
		$token = is_object($params) && isset($params->token)
			? $params->token
			: (is_array($params) && isset($params['token'])
				? $params['token']
				: false
			)
		;

		if (!empty($token)) {
			// Use token to fetch and set key
			$rmt = Snapshot_Model_Full_Remote_Key::get();
			$key = $rmt->get_remote_key($token);
			if (!empty($key)) $status = $rmt->set_key($key);
		}

		if ($status && !is_wp_error($status)) {
			Snapshot_Helper_Log::info('Key set', 'Remote');
			wp_send_json_success($status);
		} else {
			Snapshot_Helper_Log::info('Problem fetching key with OTP token', 'Remote');
			wp_send_json_error($status);
		}
	}

	/**
	 * Validates the params passed to schedule backups action
	 *
	 * @param object $params API-passed params
	 *
	 * @return bool Valid or not
	 */
	public function validate_schedule_params ($params) {
		$status = true;
		if (!isset($params->active)) $status = false;

		$frequencies = array_keys($this->_model->get_frequencies());
		if (!isset($params->frequency) || !in_array($params->frequency, $frequencies)) $status = false;
		if (!isset($params->time) || !is_numeric($params->time)) $status = false;
		if (!isset($params->limit) || !is_numeric($params->limit)) $status = false;

		if (!empty($status)) {
			Snapshot_Helper_Log::info("Reschedule params are all valid", "Remote");
		} else {
			Snapshot_Helper_Log::warn("Invalid reschedule parameters passed from service", "Remote");
		}

		return $status;
	}

	/**
	 * Applies valid schedule changes
	 *
	 * @param object $params API-passed params
	 *
	 * @return bool Status
	 */
	public function apply_schedule_change ($params) {
		if (!$params->active) {
			Snapshot_Helper_Log::info("Automated rescheduling, cron disabled", "Remote");
			$this->_model->set_config('frequency', false);
			$this->_model->set_config('schedule_time', false);
			$this->_model->set_config('disable_cron', true);
			Snapshot_Controller_Full_Cron::get()->stop();
		} else {
			Snapshot_Helper_Log::info("Automated rescheduling, cron enabled, with settings", "Remote");
			$this->_model->set_config('frequency', $params->frequency);
			$this->_model->set_config('schedule_time', $params->time);
			$this->_model->set_config('disable_cron', false);
			Snapshot_Controller_Full_Cron::get()->reschedule();
		}

		Snapshot_Model_Full_Remote_Storage::get()->set_max_backups_limit($params->limit);

		return $this->_model->update_remote_schedule();
	}

	/**
	 * Constructs the schedule change response
	 *
	 * @param object $params API-passed params
	 *
	 * @return array Response params
	 */
	public function construct_schedule_response ($params) {
		Snapshot_Helper_Log::info("Automated rescheduling, response creation", "Remote");
		$new_settings = array();
		$domain = Snapshot_Model_Full_Remote_Api::get()->get_domain();
		if (!empty($domain)) {
			$lmodel = new Snapshot_Model_Full_Local;
			$frequency = $params->frequency;
			$time = $params->time;

			// If there's no cron jobs allowed, send nothing
			if ($this->_model->get_config('disable_cron', false)) {
				$frequency = '';
				$time = 0;
			}

			// Build our arguments
			$new_settings = array(
				'domain' => $domain,
				'backup_freq' => $frequency,
				'backup_time' => $time,
				'backup_limit' => Snapshot_Model_Full_Remote_Storage::get()->get_max_backups_limit(),
				'local_full_backups' => json_encode($lmodel->get_backups()),
			);
			Snapshot_Helper_Log::info("Automated rescheduling, created response array", "Remote");
		} else {
			Snapshot_Helper_Log::warn("Unable to create response array", "Remote");
		}

		return $new_settings;
	}

	/**
	 * Update Snapshot backup schedule settings
	 *
	 * @param object $params Parameters passed in json body
	 *      $active bool Whether schedule is active or unactive
	 *      $frequency string|bool daily/weekly/monthly (defaults to not changing)
	 *      $time integer|bool Offset in seconds from UTC midnight (1-82800) (defaults to not changing)
	 *      $limit integer How many backups to keep before rotating (default 3)
	 * @param string $action The action name that was called
	 */
	public function json_schedule_backups ($params, $action) {
		//save settings, and return the same object as normally gets sent
		//to REST api (so we can skip that callback eventually when triggered
		//remotely)
		Snapshot_Helper_Log::info("Attempting automated reschedule", "Remote");

		// Step 1: validate stuff
		$status = $this->validate_schedule_params($params);

		if (empty($status)) {
			// Bye!
			return wp_send_json_error();
		}

		// Valid stuff, let's go
		$this->apply_schedule_change($params);

		// Now, construct the response
		$new_settings = $this->construct_schedule_response($params);

		return empty($new_settings)
			? wp_send_json_error()
			: wp_send_json_success($new_settings)
		;
	}

	/**
	 * Actually performs a new full backup start
	 *
	 * @return bool Status
	 */
	public function start_backup () {
		$cron = Snapshot_Controller_Full_Cron::get();

		Snapshot_Helper_Log::info("Booting backup", "Remote");
		if ($cron->is_running()) {
			Snapshot_Helper_Log::info("Scheduled backup already running", "Remote");
			// Already running. Bye!
			return false;
		}

		if ($this->_model->get_config('disable_cron', false)) {
			Snapshot_Helper_Log::info("Scheduled backups disabled, re-enabling", "Remote");
			$this->_model->set_config('disable_cron', false);
		}

		$cron->start_backup(); // Now, let's go
		$cron->force_actual_start();
		$status = $cron->is_running();
		Snapshot_Helper_Log::info(sprintf("Remotely triggered backup started: %s", var_export($status,1)), "Remote");

		return $status;
	}

	/**
	 * Handles a new full backup start request
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function json_start_backup ($params, $action) {
		Snapshot_Helper_Log::info("Remote backup initiating request received", "Remote");

		$status = $this->start_backup();

		return !empty($status)
			? wp_send_json_success()
			: wp_send_json_error()
		;
	}
}