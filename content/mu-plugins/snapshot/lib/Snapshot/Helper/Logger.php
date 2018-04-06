<?php
/*
Snapshots Logger Class
Dexcription: This logger class is used from various parts of the Snapshots plugin to write messages to the log or archive items.
*/

if ( ! class_exists( 'Snapshot_Helper_Logger' ) ) {
	class Snapshot_Helper_Logger {

		var $DEBUG;
		var $logFolder;
		var $logFileFull;
		var $item_key;
		var $data_item_key;
		var $log_fp;

		function __construct( $backupLogFolderFull, $item_key, $data_item_key ) {
			$this->logFolder     = trailingslashit( $backupLogFolderFull );
			$this->item_key      = $item_key;
			$this->data_item_key = $data_item_key;

			$this->start_logger();
		}

		function Snapshot_Helper_Logger( $backupLogFolderFull, $item_key, $data_item_key ) {
			$this->__construct( $backupLogFolderFull, $item_key, $data_item_key );
		}

		function __destruct() {
			if ( $this->log_fp ) {
				fclose( $this->log_fp );
			}
		}

		function start_logger() {
			$this->logFileFull = $this->logFolder . "/" . $this->item_key . "_" . $this->data_item_key . ".log";
			$this->log_fp      = fopen( $this->logFileFull, 'a' );
		}

		function get_log_filename() {
			return $this->logFileFull;
		}

		function log_message( $message ) {
			if ( $this->log_fp ) {
				fwrite( $this->log_fp, Snapshot_Helper_Utility::show_date_time( time(), 'Y-m-d H:i:s' ) . ": " . $message . "\r\n" );
				fflush( $this->log_fp );
			}
		}

	}
}