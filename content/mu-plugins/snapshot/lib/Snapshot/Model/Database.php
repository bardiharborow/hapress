<?php
/**
 * This file extends `wpdb`
 *
 * Needed to patch some database functions to allow SQL `RENAME` to work properly.
 *
 * During a restore Snapshot load the database tables to temporary tables.
 * Then once all tables have been loaded it DROPs the original table then RENAMEs the restored to replace the original table. Seems the
 * WordPress wpdb class via the $wpdb->query() function does not treat the sql RENAME keyword like DROP, CREATE, etc. It expects a
 * query result. Which a RENAME does not produce. So this class extends wpdb then replaces the query() function to include RENAME as one
 * of the 'special' keywords.
 *
 * @since 2.5
 *
 * @package Snapshot
 * @subpackage Model
 */

if ( ! class_exists( 'Snapshot_Model_Database' ) ) {
	class Snapshot_Model_Database extends wpdb {

		function query( $query ) {
			if ( ! $this->ready ) {
				return false;
			}

			// some queries are made before the plugins have been loaded, and thus cannot be filtered with this method
			$query = apply_filters( 'query', $query );

			$return_val = 0;
			$this->flush();

			// Log how the function was called
			$this->func_call = "\$db->query(\"$query\")";

			// Keep track of the last query for debug..
			$this->last_query = $query;

			if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
				$this->timer_start();
			}

			if ( version_compare( phpversion(), "5.3.0", ">=" ) ) {
				$this->result = @mysqli_query( $query, $this->dbh );
			} else {
				$this->result = @mysql_query( $query, $this->dbh );
			}
			$this->num_queries ++;

			if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
				$this->queries[] = array( $query, $this->timer_stop(), $this->get_caller() );
			}

			// If there is an error then take note of it..
			if ( version_compare( phpversion(), "5.3.0", ">=" ) ) {
				if ( $this->last_error = mysqli_error( $this->dbh ) ) {
					$this->print_error();

					return false;
				}
			} else {
				if ( $this->last_error = mysql_error( $this->dbh ) ) {
					$this->print_error();

					return false;
				}
			}

			if ( preg_match( '/^\s*(create|alter|truncate|drop|rename)\s/i', $query ) ) {
				$return_val = $this->result;
			} elseif ( preg_match( '/^\s*(insert|delete|update|replace)\s/i', $query ) ) {
				if ( version_compare( phpversion(), "5.3.0", ">=" ) ) {
					$this->rows_affected = mysqli_affected_rows( $this->dbh );
				} else {
					$this->rows_affected = mysql_affected_rows( $this->dbh );
				}
				// Take note of the insert_id
				if ( preg_match( '/^\s*(insert|replace)\s/i', $query ) ) {
					if ( version_compare( phpversion(), "5.3.0", ">=" ) ) {
						$this->insert_id = mysqli_insert_id( $this->dbh );
					} else {
						$this->insert_id = mysql_insert_id( $this->dbh );
					}
				}
				// Return number of rows affected
				$return_val = $this->rows_affected;
			} else {
				$num_rows = 0;

				if ( version_compare( phpversion(), "5.3.0", ">=" ) ) {
					while ( $row = @mysqli_fetch_object( $this->result ) ) {
						$this->last_result[ $num_rows ] = $row;
						$num_rows ++;
					}
				} else {
					while ( $row = @mysql_fetch_object( $this->result ) ) {
						$this->last_result[ $num_rows ] = $row;
						$num_rows ++;
					}
				}

				// Log number of rows the query returned
				// and return number of rows selected
				$this->num_rows = $num_rows;
				$return_val     = $num_rows;
			}

			return $return_val;
		}

	}
}
