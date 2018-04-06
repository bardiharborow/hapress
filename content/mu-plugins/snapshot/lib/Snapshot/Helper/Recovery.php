<?php session_start();

if ( ! class_exists( 'Snapshot_Helper_Recovery' ) ) {

	class Snapshot_Helper_Recovery {

		public static $basedir = false;

		public function __construct( $base_file ) {

			self::$basedir = dirname( $base_file );

			// Creates the class autoloader.
			spl_autoload_register( array( $this, 'class_loader' ) );

			Snapshot_View_Form_Recovery::render_recovery_form();
		}

		private function class_loader( $class ) {
			$basedir = self::$basedir;
			$class   = trim( $class );

			if ( preg_match( '/^Snapshot_/', $class ) ) {
				$filename = $basedir . '/lib/' . str_replace( '_', DIRECTORY_SEPARATOR, $class ) . '.php';
				if ( is_readable( $filename ) ) {
					include_once $filename;

					return true;
				}
			}

			return false;
		}


		/* ---- Validation Methods ---- */
		public static function validate_step_1b( $restore_form ) {

			$form_errors                    = array();
			$form_errors['form']            = array();
			$form_errors['message-error']   = array();
			$form_errors['message-success'] = array();

			// Do the form validation first before the heavy processing
			if ( ( ! isset( $restore_form['verify']['file'] ) ) || ( ! strlen( $restore_form['verify']['file'] ) ) ) {
				$form_errors['form']['verify']['file'] = "Snapshot Recover Filename cannot be empty.";
			}

			if ( ( ! isset( $restore_form['verify']['code'] ) ) || ( ! strlen( $restore_form['verify']['code'] ) ) ) {
				$form_errors['form']['verify']['code'] = "Snapshot Recover Code cannot be empty.";
			}

			if ( count( $form_errors['form'] ) ) {
				return $form_errors;
			}

			$snapshot_verify_file = $_SERVER['DOCUMENT_ROOT'] . "/" . stripslashes( $restore_form['verify']['file'] );
			//echo "snapshot_verify_file=[". $snapshot_verify_file ."]<br />";
			if ( ! file_exists( $snapshot_verify_file ) ) {
				$form_errors['message-error'][] = "Unable to find Verify Filename [" . stripslashes( $restore_form['verify']['file'] ) . "] to process. Try again.";

				return $form_errors;
			}

			$snapshot_verify_file_contents = file_get_contents( $snapshot_verify_file );
			$snapshot_verify_code_match    = stristr( $snapshot_verify_file_contents, stripslashes( $restore_form['verify']['code'] ) );
			if ( $snapshot_verify_code_match === false ) {
				$form_errors['message-error'][] = "Verify Code does not match [" . stripslashes( $restore_form['verify']['code'] ) . "]. Try again.";

				return $form_errors;
			}

			$form_errors['message-success'][] = "Verify Code SUCCESS.";

			$_SESSION['restore_form']['verify']['file'] = $restore_form['verify']['file'];
			$_SESSION['restore_form']['verify']['code'] = $restore_form['verify']['code'];

			return $form_errors;
		}

		public static function validate_step_1( $restore_form ) {

			$form_errors                    = array();
			$form_errors['form']            = array();
			$form_errors['message-error']   = array();
			$form_errors['message-success'] = array();

			// Do the form validation first before the heavy processing
			if ( ( ! isset( $restore_form['snapshot']['archive-file'] ) ) || ( ! strlen( $restore_form['snapshot']['archive-file'] ) ) ) {
				$form_errors['form']['snapshot']['archive-file'] = "Snapshot Archive file cannot be empty.";
			} else {
				$_SESSION['restore_form']['snapshot']['archive-file'] = $restore_form['snapshot']['archive-file'];
			}

			if ( ( ! isset( $restore_form['wordpress']['reload'] ) ) || ( ! strlen( $restore_form['wordpress']['reload'] ) ) ) {
				$form_errors['form']['wordpress']['reload'] = "WordPress Reload cannot be empty.";
			} else if ( ( $restore_form['wordpress']['reload'] != "yes" ) && ( $restore_form['wordpress']['reload'] != "no" ) ) {
				$form_errors['form']['wordpress']['reload'] = "WordPress Reload invalid value given.";

				return $form_errors;
			} else {
				$_SESSION['restore_form']['wordpress']['reload'] = $restore_form['wordpress']['reload'];
			}

			if ( ( ! isset( $restore_form['wordpress']['install-path'] ) ) || ( ! strlen( $restore_form['wordpress']['install-path'] ) ) ) {
				$form_errors['form']['wordpress']['install-path'] = "WordPress Install Path file cannot be empty.";
			} else {
				$_SESSION['restore_form']['wordpress']['install-path'] = self::untrailingslashit_snapshot( $restore_form['wordpress']['install-path'] );
				if ( ! file_exists( $_SESSION['restore_form']['wordpress']['install-path'] ) ) {
					mkdir( $_SESSION['restore_form']['wordpress']['install-path'], 0777, true );
				}
				$_SESSION['restore_form']['wordpress']['install-path'] = self::trailingslashit_snapshot( $_SESSION['restore_form']['wordpress']['install-path'] );

			}


			if ( count( $form_errors['form'] ) ) {
				return $form_errors;
			}


			// If here then the form is valid. Now get into the heavy processing
			if ( ! isset( $_SESSION['restore_form']['snapshot']['archive-file'] ) ) {
				$_SESSION['restore_form']['snapshot']['archive-file'] = '';
			}

			unset( $_SESSION['restore_form']['snapshot']['archive-file-local'] );
			unset( $_SESSION['restore_form']['snapshot']['archive-file-remote'] );

			if ( substr( $_SESSION['restore_form']['snapshot']['archive-file'], 0, strlen( 'http' ) ) == "http" ) {
				$_SESSION['restore_form']['snapshot']['archive-file-remote'] = $_SESSION['restore_form']['snapshot']['archive-file'];
				$_SESSION['restore_form']['snapshot']['archive-file-local']  = dirname( __FILE__ ) . "/_snapshot/file/" .
				                                                               basename( $_SESSION['restore_form']['snapshot']['archive-file'] );

				if ( file_exists( $_SESSION['restore_form']['snapshot']['archive-file-local'] ) ) {
					$unlink_ret = unlink( $_SESSION['restore_form']['snapshot']['archive-file-local'] );
					if ( $unlink_ret !== true ) {
						$form_errors['message-error'][] = "Unable to delete previous local file [" . $_SESSION['restore_form']['snapshot']['archive-file-local'] . "]. Manually delete the file. Check parent folder permissions and reload the page.";

						return $form_errors;
					}
				}

				$func_ret = Snapshot_Helper_Utility::remote_url_to_local_file( $_SESSION['restore_form']['snapshot']['archive-file-remote'],
					$_SESSION['restore_form']['snapshot']['archive-file-local'] );
				if ( ( ! file_exists( $_SESSION['restore_form']['snapshot']['archive-file-local'] ) )
				     || ( ! filesize( $_SESSION['restore_form']['snapshot']['archive-file-local'] ) )
				) {
					$form_errors['message-error'][] = "Attempted to download remote Snapshot file to local [" . $_SESSION['restore_form']['snapshot']['archive-file-local'] . "]<br />";
					". File not found or is empty. Check parent folder permissions and reload the page.";

					return $form_errors;
				} else {
					$form_errors['message-success'][] = "Remote Snapshot Archive [" . $_SESSION['restore_form']['snapshot']['archive-file-remote'] . "] downloaded and extracted successfully.";
				}
			} else {
				$local_file = '';
				if ( substr( $_SESSION['restore_form']['snapshot']['archive-file'], 0, 1 ) == "/" ) {
					$local_file = $_SESSION['restore_form']['snapshot']['archive-file'];
				} else {
					$local_file = self::trailingslashit_snapshot( $_SERVER['DOCUMENT_ROOT'] ) . $_SESSION['restore_form']['snapshot']['archive-file'];
				}
				if ( file_exists( $local_file ) ) {
					$_SESSION['restore_form']['snapshot']['archive-file-local'] = $local_file;
					$form_errors['message-success'][]                           = "Local Snapshot Archive located [" . basename( $local_file ) . "] successfully.";
				}
			}

			if ( ( isset( $_SESSION['restore_form']['snapshot']['archive-file-local'] ) ) && ( strlen( $_SESSION['restore_form']['snapshot']['archive-file-local'] ) ) ) {

				/**
				 * THIS IS WHERE THE RECOVERY BEGINS.
				 */
				$_SESSION['restore_form']['snapshot']['extract-path'] = WP_CONTENT_DIR . "/_snapshot_recovery/extract/";
				self::unzip_archive( $_SESSION['restore_form']['snapshot']['archive-file-local'], $_SESSION['restore_form']['snapshot']['extract-path'] );

				// Locate and consume the Snapshot manifest file
				$_SESSION['restore_form']['snapshot']['manifest-file'] = self::trailingslashit_snapshot( $_SESSION['restore_form']['snapshot']['extract-path'] )
				                                                         . "snapshot_manifest.txt";

				if ( ! file_exists( $_SESSION['restore_form']['snapshot']['manifest-file'] ) ) {
					$form_errors['message-error'][] = "Snapshot archive Manifest file missing. Cannot restore/migrate via Snapshot.";

					return $form_errors;
				}

				$manifest_data = Snapshot_Helper_Utility::consume_archive_manifest( $_SESSION['restore_form']['snapshot']['manifest-file'] );
				if ( is_array( $manifest_data ) ) {
					$_SESSION['restore_form']['snapshot']['manifest-data'] = $manifest_data;
					$form_errors['message-success'][]                      = "Snapshot archive Manifest located and loaded successfully.";
				}
			}


			if ( ( $_SESSION['restore_form']['wordpress']['reload'] == "yes" ) && ( isset( $_SESSION['restore_form']['snapshot']['manifest-data']['WP_VERSION'] ) ) ) {

				$_SESSION['restore_form']['wordpress']['archive-file-remote'] = 'http://wordpress.org/wordpress-' .
				                                                                $_SESSION['restore_form']['snapshot']['manifest-data']['WP_VERSION'] . '.zip';

				$_SESSION['restore_form']['wordpress']['archive-file-local'] = dirname( __FILE__ ) . "/_wordpress/file/" .
				                                                               basename( $_SESSION['restore_form']['wordpress']['archive-file-remote'] );

				$func_ret = Snapshot_Helper_Utility::remote_url_to_local_file( $_SESSION['restore_form']['wordpress']['archive-file-remote'],
					$_SESSION['restore_form']['wordpress']['archive-file-local'] );

				if ( ( ! file_exists( $_SESSION['restore_form']['wordpress']['archive-file-local'] ) )
				     || ( ! filesize( $_SESSION['restore_form']['wordpress']['archive-file-local'] ) )
				) {
					$form_errors['message-error'][] = "Attempted to download WordPress file to local [" .
					                                  $_SESSION['restore_form']['wordpress']['archive-file-local'] . "]. File not found or is empty. Check parent folder permissions and reload the page.";

					return $form_errors;

				} else {
					$form_errors['message-success'][] = "Remote WordPress  Archive [" .
					                                    basename( $_SESSION['restore_form']['wordpress']['archive-file-local'] ) . "] downloaded successfully.";

					// Extract WordPress files into place
					$_SESSION['restore_form']['wordpress']['extract-path'] = dirname( __FILE__ ) . "/_wordpress/extract/";

					$unzip_ret = unzip_archive( $_SESSION['restore_form']['wordpress']['archive-file-local'], $_SESSION['restore_form']['wordpress']['extract-path'] );
					if ( file_exists( $_SESSION['restore_form']['wordpress']['extract-path'] . "/wordpress" ) ) {
						$_SESSION['restore_form']['wordpress']['extract-path'] = $_SESSION['restore_form']['wordpress']['extract-path'] . "/wordpress";

						$form_errors['message-success'][] = "WordPress Archive extracted successfully.";
					}

					self::move_tree( $_SESSION['restore_form']['wordpress']['extract-path'], $_SESSION['restore_form']['wordpress']['install-path'] );
				}
			}


			return $form_errors;
		}

		public static function validate_step_2( $restore_form ) {
			$form_errors                    = array();
			$form_errors['form']            = array();
			$form_errors['message-error']   = array();
			$form_errors['message-success'] = array();

			if ( ( ! isset( $restore_form['wordpress']['wp-config'] ) ) || ( ! strlen( $restore_form['wordpress']['wp-config'] ) ) ) {
				$form_errors['form']['wordpress']['wp-config'] = "WordPress wp-config cannot be empty.";

				return $form_errors;
			}
			if ( ( $restore_form['wordpress']['wp-config'] != "existing" ) && ( $restore_form['wordpress']['wp-config'] != "snapshot" ) ) {
				$form_errors['form']['wordpress']['wp-config'] = "WordPress wp-config invalid value given.";

				return $form_errors;
			}
			$_SESSION['restore_form']['wordpress']['wp-config'] = $restore_form['wordpress']['wp-config'];


			if ( ( ! isset( $restore_form['wordpress']['home-url'] ) ) || ( ! strlen( $restore_form['wordpress']['home-url'] ) ) ) {
				$form_errors['form']['wordpress']['home-url'] = "Home URL cannot be empty.";

				return $form_errors;
			}
			$_SESSION['restore_form']['wordpress']['home-url'] = $restore_form['wordpress']['home-url'];

			if ( ( ! isset( $restore_form['wordpress']['site-url'] ) ) || ( ! strlen( $restore_form['wordpress']['site-url'] ) ) ) {
				$form_errors['form']['wordpress']['site-url'] = "Site URL cannot be empty.";

				return $form_errors;
			}
			$_SESSION['restore_form']['wordpress']['site-url'] = $restore_form['wordpress']['site-url'];

			if ( ( ! isset( $restore_form['wordpress']['upload-path'] ) ) || ( ! strlen( $restore_form['wordpress']['upload-path'] ) ) ) {
				$form_errors['form']['wordpress']['upload-path'] = "Upload Path cannot be empty.";

				return $form_errors;
			}
			$_SESSION['restore_form']['wordpress']['upload-path'] = $restore_form['wordpress']['upload-path'];


			// If the user chose to re-use the existing wp-config.php in the root then remove the copy from the snapshot archive
			$snapshot_wp_config  = self::trailingslashit_snapshot( $_SESSION['restore_form']['snapshot']['extract-path'] ) . "www/wp-config.php";
			$wordpress_wp_config = $wp_path = str_replace( strstr( self::$basedir, '/wp-content' ), '', self::$basedir ) . '/wp-config.php';
			if ( $_SESSION['restore_form']['wordpress']['wp-config'] == "existing" ) {
				$_SESSION['restore_form']['wordpress']['wp-config-db'] = self::extract_wp_config_db_info( $wordpress_wp_config );
				if ( file_exists( $snapshot_wp_config ) ) {
					unlink( $snapshot_wp_config );
				}
			} else {
				$_SESSION['restore_form']['wordpress']['wp-config-db'] = self::extract_wp_config_db_info( $snapshot_wp_config );
			}

			if ( file_exists( self::trailingslashit_snapshot( $_SESSION['restore_form']['snapshot']['extract-path'] ) . "www/wp-content" ) ) {
				self::move_tree( self::trailingslashit_snapshot( $_SESSION['restore_form']['snapshot']['extract-path'] ) . "www/wp-content",
					self::trailingslashit_snapshot( $_SESSION['restore_form']['wordpress']['install-path'] ) . "wp-content", true );
			}

			return $form_errors;
		}

		public static function validate_step_3( $restore_form ) {

			$form_errors = array();

			if ( ( ! isset( $restore_form['wordpress']['wp-config-db']['DB_NAME'] ) ) || ( ! strlen( $restore_form['wordpress']['wp-config-db']['DB_NAME'] ) ) ) {
				$form_errors['wordpress']['wp-config-db']['DB_NAME'] = "Database Name cannot be empty.";

				return $form_errors;
			}
			$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_NAME'] = $restore_form['wordpress']['wp-config-db']['DB_NAME'];


			if ( ( ! isset( $restore_form['wordpress']['wp-config-db']['DB_USER'] ) ) || ( ! strlen( $restore_form['wordpress']['wp-config-db']['DB_USER'] ) ) ) {
				$form_errors['wordpress']['wp-config-db']['DB_USER'] = "Database User cannot be empty.";

				return $form_errors;
			}
			$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_USER'] = $restore_form['wordpress']['wp-config-db']['DB_USER'];


			if ( ( ! isset( $restore_form['wordpress']['wp-config-db']['DB_PASSWORD'] ) ) || ( ! strlen( $restore_form['wordpress']['wp-config-db']['DB_PASSWORD'] ) ) ) {
				$form_errors['wordpress']['wp-config-db']['DB_PASSWORD'] = "Database Password cannot be empty.";

				return $form_errors;
			}
			$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_PASSWORD'] = $restore_form['wordpress']['wp-config-db']['DB_PASSWORD'];


			if ( ( ! isset( $restore_form['wordpress']['wp-config-db']['DB_HOST'] ) ) || ( ! strlen( $restore_form['wordpress']['wp-config-db']['DB_HOST'] ) ) ) {
				$form_errors['wordpress']['wp-config-db']['DB_HOST'] = "Database Host cannot be empty.";

				return $form_errors;
			}
			$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_HOST'] = $restore_form['wordpress']['wp-config-db']['DB_HOST'];

			if ( ( ! isset( $restore_form['wordpress']['wp-config-db']['DB_PREFIX'] ) ) || ( ! strlen( $restore_form['wordpress']['wp-config-db']['DB_PREFIX'] ) ) ) {
				$form_errors['wordpress']['wp-config-db']['DB_PREFIX'] = "Database Prefix cannot be empty.";

				return $form_errors;
			}
			$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_PREFIX'] = $restore_form['wordpress']['wp-config-db']['DB_PREFIX'];

			// Use mysqli_ if PHP version above 5.3.0
			if ( version_compare( phpversion(), "5.3.0", ">=" ) ) {
				$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_CONNECTION'] = self::db_connection_test_mysqli(
					$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_NAME'],
					$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_USER'],
					$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_PASSWORD'],
					$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_HOST'] );
			} else {
				$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_CONNECTION'] = self::db_connection_test_mysql(
					$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_NAME'],
					$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_USER'],
					$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_PASSWORD'],
					$_SESSION['restore_form']['wordpress']['wp-config-db']['DB_HOST'] );
			}

			if ( count( $_SESSION['restore_form']['wordpress']['wp-config-db']['DB_CONNECTION'] ) ) {
				$form_errors['wordpress']['wp-config-db']['DB_CONNECTION'] = $_SESSION['restore_form']['wordpress']['wp-config-db']['DB_CONNECTION'];

				return $form_errors;
			}

			return $form_errors;
		}

		/* ---- Utility Methods ---- */

		public static function unzip_archive( $local_archive, $restore_path_base ) {

			// First we clear the directory
			Snapshot_Helper_Utility::recursive_rmdir( $restore_path_base );


			if ( ! file_exists( $local_archive ) ) {
				echo "Archive file [" . $local_archive . "] does not exist<br />";
				die();
			}

			if ( ! class_exists( 'class PclZip' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/class-pclzip.php' );
			}
			$zipArchive   = new PclZip( $local_archive );
			$zip_contents = $zipArchive->listContent();
			if ( $zip_contents ) {
				$extract_files = $zipArchive->extract( PCLZIP_OPT_PATH, $restore_path_base );
				if ( $extract_files ) {
					// Message to say that files have been extracted....
				}
			}

		}

		public static function db_connection_test_mysql( $db_name, $db_user, $db_password, $db_host ) {
			$errors = array();

			$db_link = mysql_connect( $db_host, $db_user, $db_password );
			if ( ! $db_link ) {
				$errors[] = "Could not connect to MySQL: " . mysql_error();
			} else {
				$db_selected = mysql_select_db( $db_name, $db_link );
				if ( ! $db_selected ) {
					$errors[] = "Can't select database [" . $db_name . "]: " . mysql_error();
				}
			}
			mysql_close( $db_link );

			return $errors;
		}

		public static function db_connection_test_mysqli( $db_name, $db_user, $db_password, $db_host ) {
			$errors = array();

			$db_link = mysqli_connect( $db_host, $db_user, $db_password );
			if ( ! $db_link ) {
				$errors[] = "Could not connect to MySQL: " . mysqli_error();
			} else {
				$db_selected = mysqli_select_db( $db_name, $db_link );
				if ( ! $db_selected ) {
					$errors[] = "Can't select database [" . $db_name . "]: " . mysqli_error();
				}
			}
			mysqli_close( $db_link );

			return $errors;
		}


		/**
		 * @param $from_dir - Source Directory
		 * @param $dest_dir Destination Directory
		 * @param bool $move_files (true) will move each file individually. (false) will remove destination sub-directories and move entire source sub-directory
		 */
		public static function move_tree( $from_dir, $dest_dir, $move_files = false ) {

			if ( ! is_dir( $from_dir ) ) {
				echo "Source Directory does not exists [" . $from_dir . "]<br />";
				die();
			}

			if ( ! is_dir( $dest_dir ) ) {
				echo "Destination Directory does not exists [" . $dest_dir . "]<br />";
				die();
			}

			if ( $move_files == true ) {

				$from_files = Snapshot_Helper_Utility::scandir( $from_dir );
				if ( ( is_array( $from_files ) ) && ( count( $from_files ) ) ) {
					foreach ( $from_files as $from_file_full ) {
						$from_file      = str_replace( trailingslashit_snapshot( $from_dir ), '', $from_file_full );
						$dest_file_full = self::trailingslashit_snapshot( $dest_dir ) . $from_file;

						if ( ! file_exists( dirname( $dest_file_full ) ) ) {
							mkdir( dirname( $dest_file_full ), 0777, true );
						}

						if ( file_exists( $dest_file_full ) ) {
							unlink( $dest_file_full );
						}

						$rename_ret = rename( $from_file_full, $dest_file_full );
						if ( $rename_ret === false ) {
							die();
						}
					}
				}
			} else {

				if ( $from_dh = opendir( $from_dir ) ) {
					while ( ( $from_file = readdir( $from_dh ) ) !== false ) {
						if ( ( $from_file == '.' ) || ( $from_file == '..' ) ) {
							continue;
						}

						$from_file_full = self::trailingslashit_snapshot( $from_dir ) . $from_file;
						$dest_file_full = self::trailingslashit_snapshot( $dest_dir ) . $from_file;

						if ( file_exists( $dest_file_full ) ) {
							if ( is_dir( $dest_file_full ) ) {
								Snapshot_Helper_Utility::recursive_rmdir( $dest_file_full );
							} else {
								unlink( $dest_file_full );
							}
						}
						rename( $from_file_full, $dest_file_full );
					}
					closedir( $from_dh );
				}
			}
		}

		public static function extract_wp_config_db_info( $wp_config_file ) {
			//	$wp_config_file = $_SESSION['restore_form']['snapshot']['archive-extract-path'] ."www/wp-config.php";

			$wp_config_db_info      = array();
			$wp_config_file_content = file( $wp_config_file );

			if ( ( $wp_config_file_content ) && ( is_array( $wp_config_file_content ) ) ) {

				foreach ( $wp_config_file_content as $_line => $_line_data ) {
					if ( ( stristr( $_line_data, 'DB_NAME' ) !== false )
					     || ( stristr( $_line_data, 'DB_USER' ) !== false )
					     || ( stristr( $_line_data, 'DB_PASSWORD' ) !== false )
					     || ( stristr( $_line_data, 'DB_HOST' ) !== false )
					) {

						$_line_data = str_replace( "define(", '', $_line_data );
						$_line_data = str_replace( ");", '', $_line_data );

						list( $token, $value ) = explode( ',', $_line_data, 2 );
						$token = trim( $token );
						$value = trim( $value );

						if ( $token[0] == "'" ) {
							$token = str_replace( "'", "", $token );
						} else if ( $token[0] == '"' ) {
							$token = str_replace( '"', "", $token );
						}

						if ( $value[0] == "'" ) {
							$value = str_replace( "'", "", $value );
						} else if ( $value[0] == '"' ) {
							$value = str_replace( '"', "", $value );
						}

						$wp_config_db_info[ $token ] = $value;

					} else if ( stristr( $_line_data, '$table_prefix' ) !== false ) {
						$_line_data = str_replace( '$table_prefix', '', trim( $_line_data ) );
						$_line_data = str_replace( '=', '', trim( $_line_data ) );
						$_line_data = str_replace( ';', '', trim( $_line_data ) );
						if ( $_line_data[0] == "'" ) {
							$_line_data = str_replace( "'", "", $_line_data );
						} else if ( $_line_data[0] == '"' ) {
							$_line_data = str_replace( '"', "", $_line_data );
						}

						//echo "line_data=[". $_line_data ."]<br />";
						//die();
						$wp_config_db_info['DB_BASE_PREFIX'] = $_line_data;
					}
				}
			}

			return $wp_config_db_info;
		}

		public static function trailingslashit_snapshot( $string ) {
			return self::untrailingslashit_snapshot( $string ) . '/';
		}

		public static function untrailingslashit_snapshot( $string ) {
			return rtrim( $string, '/' );
		}


		/***************************************************************************************************/
		/* Search/Replace MySQL data adapted from https://github.com/interconnectit/Search-Replace-DB      */
		/***************************************************************************************************/

		// Note: If this function is edited, make sure to update search_replace_table_data_mysql code too

		public static function search_replace_table_data_mysql( $table, $connection, $search, $replace ) {

			$guid         = isset( $_POST['guid'] ) && $_POST['guid'] == 1 ? 1 : 0;
			$exclude_cols = array( 'guid' );

			$fields = mysql_query( 'DESCRIBE `' . $table . '`', $connection );
			while ( $column = mysql_fetch_array( $fields ) ) {
				$columns[ $column['Field'] ] = $column['Key'] == 'PRI' ? true : false;
			}

			// Count the number of rows we have in the table if large we'll split into blocks, This is a mod from Simon Wheatley
			$row_count   = mysql_query( 'SELECT COUNT(*) FROM `' . $table . '`', $connection );
			$rows_result = mysql_fetch_array( $row_count );
			$row_count   = $rows_result[0];
			if ( $row_count == 0 ) {
				return false;
			}

			$page_size = 50000;
			$pages     = ceil( $row_count / $page_size );

			for ( $page = 0; $page < $pages; $page ++ ) {

				$current_row = 0;
				$start       = $page * $page_size;
				$end         = $start + $page_size;
				// Grab the content of the table
				$data = mysql_query( sprintf( "SELECT * FROM `%s` LIMIT %d, %d", $table, $start, $end ), $connection );

		//		if ( ! $data )
		//			$report[ 'errors' ][] = mysql_error( );

				while ( $row = mysql_fetch_array( $data ) ) {

					//$report[ 'rows' ]++; // Increment the row counter
					$current_row ++;

					$update_sql = array();
					$where_sql  = array();
					$upd        = false;

					foreach ( $columns as $column => $primary_key ) {
						if ( $guid == 1 && in_array( $column, $exclude_cols ) ) {
							continue;
						}

						$edited_data = $data_to_fix = $row[ $column ];

						// Run a search replace on the data that'll respect the serialisation.
						$edited_data = self::recursive_unserialize_replace( $search, $replace, $data_to_fix );

						// Something was changed
						if ( $edited_data != $data_to_fix ) {
							//$report[ 'change' ]++;
							$update_sql[] = $column . ' = "' . mysql_real_escape_string( $edited_data ) . '"';
							$upd          = true;
						}

						if ( $primary_key ) {
							$where_sql[] = $column . ' = "' . mysql_real_escape_string( $data_to_fix ) . '"';
						}
					}

					if ( $upd && ! empty( $where_sql ) ) {
						$sql = 'UPDATE `' . $table . '` SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
						//echo "sql=[". $sql ."]<br />";
						$result = mysql_query( $sql, $connection );
						//if ( ! $result )
						//	$report[ 'errors' ][] = mysql_error( );
						//else
						//	$report[ 'updates' ]++;

					} elseif ( $upd ) {
						//$report[ 'errors' ][] = sprintf( '"%s" has no primary key, manual change needed on row %s.', $table, $current_row );
					}
				}
			}
		}

		public static function search_replace_table_data_mysqli( $table, $connection, $search, $replace ) {

			$guid         = isset( $_POST['guid'] ) && $_POST['guid'] == 1 ? 1 : 0;
			$exclude_cols = array( 'guid' );

			$fields = mysqli_query( 'DESCRIBE `' . $table . '`', $connection );
			while ( $column = mysqli_fetch_array( $fields ) ) {
				$columns[ $column['Field'] ] = $column['Key'] == 'PRI' ? true : false;
			}

			// Count the number of rows we have in the table if large we'll split into blocks, This is a mod from Simon Wheatley
			$row_count   = mysqli_query( 'SELECT COUNT(*) FROM `' . $table . '`', $connection );
			$rows_result = mysqli_fetch_array( $row_count );
			$row_count   = $rows_result[0];
			if ( $row_count == 0 ) {
				return false;
			}

			$page_size = 50000;
			$pages     = ceil( $row_count / $page_size );

			for ( $page = 0; $page < $pages; $page ++ ) {

				$current_row = 0;
				$start       = $page * $page_size;
				$end         = $start + $page_size;
				// Grab the content of the table
				$data = mysqli_query( sprintf( "SELECT * FROM `%s` LIMIT %d, %d", $table, $start, $end ), $connection );

		//		if ( ! $data )
		//			$report[ 'errors' ][] = mysqli_error( );

				while ( $row = mysqli_fetch_array( $data ) ) {

					//$report[ 'rows' ]++; // Increment the row counter
					$current_row ++;

					$update_sql = array();
					$where_sql  = array();
					$upd        = false;

					foreach ( $columns as $column => $primary_key ) {
						if ( $guid == 1 && in_array( $column, $exclude_cols ) ) {
							continue;
						}

						$edited_data = $data_to_fix = $row[ $column ];

						// Run a search replace on the data that'll respect the serialisation.
						$edited_data = self::recursive_unserialize_replace( $search, $replace, $data_to_fix );

						// Something was changed
						if ( $edited_data != $data_to_fix ) {
							//$report[ 'change' ]++;
							$update_sql[] = $column . ' = "' . mysqli_real_escape_string( $edited_data ) . '"';
							$upd          = true;
						}

						if ( $primary_key ) {
							$where_sql[] = $column . ' = "' . mysqli_real_escape_string( $data_to_fix ) . '"';
						}
					}

					if ( $upd && ! empty( $where_sql ) ) {
						$sql = 'UPDATE `' . $table . '` SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
						//echo "sql=[". $sql ."]<br />";
						$result = mysqli_query( $sql, $connection );
						//if ( ! $result )
						//	$report[ 'errors' ][] = mysqli_error( );
						//else
						//	$report[ 'updates' ]++;

					} elseif ( $upd ) {
						//$report[ 'errors' ][] = sprintf( '"%s" has no primary key, manual change needed on row %s.', $table, $current_row );
					}
				}
			}
		}

		/**
		 * Take a serialised array and unserialise it replacing elements as needed and
		 * unserialising any subordinate arrays and performing the replace on those too.
		 *
		 * @param string $from String we're looking to replace.
		 * @param string $to What we want it to be replaced with
		 * @param array $data Used to pass any subordinate arrays back to in.
		 * @param bool $serialised Does the array passed via $data need serialising.
		 *
		 * @return array    The original array with all elements replaced as needed.
		 */
		public static function recursive_unserialize_replace( $from = '', $to = '', $data = '', $serialised = false ) {

			// some unseriliased data cannot be re-serialised eg. SimpleXMLElements
			try {

				if ( is_string( $data ) && ( $unserialized = @unserialize( $data ) ) !== false ) {
					$data = self::recursive_unserialize_replace( $from, $to, $unserialized, true );
				} elseif ( is_array( $data ) ) {
					$_tmp = array();
					foreach ( $data as $key => $value ) {
						$_tmp[ $key ] = self::recursive_unserialize_replace( $from, $to, $value, false );
					}

					$data = $_tmp;
					unset( $_tmp );
				} // Submitted by Tina Matter
				elseif ( is_object( $data ) ) {
					$dataClass = get_class( $data );
					$_tmp      = new $dataClass();
					foreach ( $data as $key => $value ) {
						$_tmp->$key = self::recursive_unserialize_replace( $from, $to, $value, false );
					}

					$data = $_tmp;
					unset( $_tmp );
				} else {
					if ( is_string( $data ) ) {
						$data = str_replace( $from, $to, $data );
					}
				}

				if ( $serialised ) {
					return serialize( $data );
				}

			} catch ( Exception $error ) {

			}

			return $data;
		}

	}

}