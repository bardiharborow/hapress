<?php

class Snapshot_Model_Full_Local extends Snapshot_Model_Full_Abstract {

	/**
	 * Gets model type
	 *
	 * Used in filtering implementation
	 *
	 * @return string Model type tag
	 */
	public function get_model_type () {
		return 'local';
	}

	/**
	 * Gets a list of backups
	 *
	 * @return array A list of full backup items
	 */
	public function get_backups () {
		return apply_filters(
			$this->get_filter('get_backups'),
			$this->_get_raw_backup_items()
		);
	}

	/**
	 * Gets a local backup file instance path
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return mixed Path to backup if local file exists, (bool)false otherwise
	 */
	public function get_backup ($timestamp) {
		$backups = $this->_get_raw_backup_files();
		$pattern = preg_quote(Snapshot_Helper_Backup::FINAL_PREFIX . '-' . $timestamp . '-', '/') . '.*\.zip$';
		$result = false;

		if (empty($backups)) return $result;

		foreach ($backups as $path) {
			if (!preg_match("/{$pattern}/", $path)) continue;
			$result = $path;
			break;
		}

		return $result;
	}

	/**
	 * Deletes a local backup instance
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return bool
	 */
	public function delete_backup ($timestamp) {
		$path = $this->get_backup($timestamp);
		if (empty($path)) return false;

		return @unlink($path);
	}

	/**
	 * Check if the timestamp backup exists locally
	 *
	 * @param int $timestamp Timestamp for backup to resolve
	 *
	 * @return bool
	 */
	public function has_backup ($timestamp) {
		$path = $this->get_backup($timestamp);
		return !empty($path);
	}

	/**
	 * Rotates local backups
	 *
	 * @return bool
	 */
	public function rotate_backups () {
		$to_remove = array();
		$raw_list = $this->_get_raw_backup_items();
		$count = count($raw_list);

		$max_items = 0;

		if (empty($max_items) || $count <= $max_items) return true; // Already there

		$oldest = false;
		for ($i=0; $i<50; $i++) {
			$item = $this->_get_oldest_file_item($raw_list, $oldest);
			if (empty($item)) break; // No more oldest files

			$oldest = $item['name'];

			$to_remove[] = $item['timestamp'];
			if ($count - count($to_remove) < $max_items) break; // We're good to go
		}

		if (empty($to_remove)) return true; // Done already

		$status = true;
		foreach ($to_remove as $rmv) {
			if (!$this->delete_backup($rmv)) $status = false;
		}

		return $status;
	}

	/**
	 * Get a list of raw local full backup filepaths
	 *
	 * @return array
	 */
	private function _get_raw_backup_files () {
		$root = trailingslashit(WPMUDEVSnapshot::instance()->get_setting('backupBaseFolderFull'));
		$pattern = Snapshot_Helper_Backup::FINAL_PREFIX . '*.zip';
		$list = glob($root . $pattern);

		return !empty($list)
			? $list
			: array()
		;

	}

	/**
	 * Gets a list of backup items from local files
	 *
	 * @return array
	 */
	private function _get_raw_backup_items () {
		$list = $this->_get_raw_backup_files();

		$result = array();
		foreach ($list as $raw) {
			$timestamp = $this->_get_file_timestamp_from_name(basename($raw));
			if (empty($timestamp)) continue;

			$result[] = array(
				'name' => basename($raw),
				'size' => filesize($raw),
				'timestamp' => $timestamp,
				'local' => true,
			);
		}

		return $result;
	}

}