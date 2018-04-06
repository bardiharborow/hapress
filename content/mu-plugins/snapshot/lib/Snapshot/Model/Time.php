<?php

class Snapshot_Model_Time {

	private static $_instance;

	public static function get () {
		if (empty(self::$_instance)) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * Centralized local timestamp fetching
	 *
	 * @return int UNIX timestamp
	 */
	public function get_local_time () {
		return (int)apply_filters(
			$this->get_filter('local_timestamp'),
			current_time('timestamp', 0)
		);
	}

	/**
	 * Centralized UTC timestamp fetching
	 *
	 * @return int UNIX timestamp
	 */
	public function get_utc_time () {
		return (int)apply_filters(
			$this->get_filter('utc_timestamp'),
			current_time('timestamp', 1)
		);
	}

	/**
	 * Gets time diff from UTC
	 *
	 * @return float Hours
	 */
	public function get_utc_offset () {
		$tz = get_option( 'timezone_string' );
		if ( $tz ) {
			// This actually returns seconds. Convert to hours.
			return timezone_offset_get( timezone_open( $tz ), new DateTime() ) / HOUR_IN_SECONDS;
		} else {
			return floatval( get_option( 'gmt_offset' ) );
		}
	}

	/**
	 * Gets time diff from UTC
	 *
	 * @return int Seconds
	 */
	public function get_utc_diff () {
		return $this->get_utc_offset() * HOUR_IN_SECONDS;
	}

	/**
	 * Convert a local timestamp to the UTC timestamp
	 *
	 * @param int $local_time Local timestamp to convert
	 *
	 * @return int
	 */
	public function to_utc_time ($local_time) {
		if (!is_numeric($local_time)) return $local_time;
		$local_time = (int)$local_time;

		return $local_time - $this->get_utc_diff();
	}

	/**
	 * Convert an UTC timestamp to the local timestamp
	 *
	 * @param int $utc_time UTC timestamp to convert
	 *
	 * @return int
	 */
	public function to_local_time ($utc_time) {
		if (!is_numeric($utc_time)) return $utc_time;
		$utc_time = (int)$utc_time;

		return $utc_time + $this->get_utc_diff();
	}

	/**
	 * Filter/action name getter
	 *
	 * @param string $filter Filter name to convert
	 *
	 * @return string Full filter name
	 */
	public function get_filter ($filter=false) {
		if (empty($filter)) return false;
		if (!is_string($filter)) return false;
		return 'snapshot-model-time-' . $filter;
	}
}