<?php

if ( ! class_exists( 'Snapshot_View_Destination_Listing' ) ) {

	class Snapshot_View_Destination_Listing {

		public static function render_destination_listing_panel() {

			if ( ! Snapshot_Helper_Utility::is_pro() ) {
				self::render_premium_plugin_prompt();

				return false;
			}

			if ( ( isset( $_REQUEST['snapshot-action'] ) )
			     && ( ( sanitize_text_field( $_REQUEST['snapshot-action'] ) == 'add' )
			          || ( sanitize_text_field( $_REQUEST['snapshot-action'] ) == 'edit' )
			          || ( sanitize_text_field( $_REQUEST['snapshot-action'] ) == 'update' ) )
			) {
				self::render_destination_edit_panel();
			} else {
				?>
				<div id="snapshot-edit-destinations-panel" class="wrap snapshot-wrap">
					<h2><?php _ex( "All Snapshot Destinations", "Snapshot Destination Page Title", SNAPSHOT_I18N_DOMAIN ); ?> </h2>

					<p><?php _ex( "This page show all the destinations available for the Snapshot plugin. A destination is a remote system like Amazon S3, Dropbox or SFTP. Simply select the destination type from the drop down then will in the details. When you add or edit a Snapshot you will be able to assign it a destination. When the snapshot backup runs the archive file will be sent to the destination and local archive stored or deleted depending on <strong>Keep local archives?</strong> option.", 'Snapshot page description', SNAPSHOT_I18N_DOMAIN ); ?></p>
					<?php
					if ( session_id() == "" ) {
						@session_start();
					}

					$destinations = array();
					foreach ( WPMUDEVSnapshot::instance()->config_data['destinations'] as $key => $item ) {

						$type = $item['type'];
						if ( ! isset( $destinations[ $type ] ) ) {
							$destinations[ $type ] = array();
						}

						$destinations[ $type ][ $key ] = $item;
					}

					do_action('snapshot-destinations-render_list-before');

					$destinationClasses = WPMUDEVSnapshot::instance()->get_setting( 'destinationClasses' );
					if ( ( $destinationClasses ) && ( count( $destinationClasses ) ) ) {
						ksort( $destinationClasses );

						$idx = 0;
						foreach ( $destinationClasses as $classObject ) {
							//echo "classObject<pre>"; print_r($classObject); echo "</pre>";
							if (!($idx % 2)) echo '<div class="snapshot-destination-row">';
							?>
						<div class="snapshot-destination">
							<h3 style="float:left;">
								<?php echo $classObject->name_display; ?>
								<?php if ( current_user_can( 'manage_snapshots_destinations' ) ) { ?>
									<a class="add-new-h2"
									    href="<?php echo WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' );
									    ?>snapshots_destinations_panel&amp;snapshot-action=add&amp;type=<?php echo $classObject->name_slug; ?>">
										<?php esc_html_e('Add New', SNAPSHOT_I18N_DOMAIN); ?>
									</a>
								<?php } ?>
							</h3>
							<?php if ( ( isset( $classObject->name_logo ) ) && ( strlen( $classObject->name_logo ) ) ) {
								?><img style="float: right; height: 40px;" src="<?php echo $classObject->name_logo; ?>"
								       alt="<?php $classObject->name_display; ?>" /><?php
							} ?>
							<form id="snapshot-edit-destination-<?php echo $classObject->name_slug; ?>" action="<?php
							echo WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' ); ?>snapshots_destinations_panel"
							      method="post">
								<input type="hidden" name="snapshot-action" value="delete-bulk"/>
								<input type="hidden" name="snapshot-destination-type"
								       value="<?php echo esc_attr( $classObject->name_slug ); ?>"/>
								<?php wp_nonce_field( 'snapshot-delete-destination-bulk-' . $classObject->name_slug,
									'snapshot-noonce-field-' . $classObject->name_slug ); ?>
								<?php
								$edit_url   = WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' )
								              . 'snapshots_destinations_panel&amp;snapshot-action=edit&amp;type=' . $classObject->name_slug . '&amp;';
								$delete_url = WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' )
								              . 'snapshots_destinations_panel&amp;snapshot-action=delete&amp;';

								if ( isset( $destinations[ $classObject->name_slug ] ) ) {
									$destination_items = $destinations[ $classObject->name_slug ];
								} else {
									$destination_items = array();
								}

								$classObject->display_listing_table( $destination_items, $edit_url, $delete_url );
								?>
							</form>
						</div>
						<?php
							if ($idx % 2) echo '</div>'; // destination row div end
							$idx++;
						}
					}

					?>
				</div>
			<?php
			}

		}

		/**
		 *
		 */
		public static function render_destination_edit_panel() {
			?>
			<div id="snapshot-metaboxes-destination_add" class="wrap snapshot-wrap">
					<?php
			$item = 0;
			if ( isset( $_REQUEST['snapshot-action'] ) ) {

				if ( sanitize_text_field( $_REQUEST['snapshot-action'] ) == "edit" ) {

					?>
					<h2><?php _ex( "Edit Snapshot Destination", "Snapshot Plugin Page Title", SNAPSHOT_I18N_DOMAIN ); ?></h2>
					<p><?php _ex( "", 'Snapshot page description', SNAPSHOT_I18N_DOMAIN ); ?></p>
					<?php
					if ( isset( $_REQUEST['item'] ) ) {
						$item_key = sanitize_text_field( $_REQUEST['item'] );
						if ( isset( WPMUDEVSnapshot::instance()->config_data['destinations'][ $item_key ] ) ) {
							$item = WPMUDEVSnapshot::instance()->config_data['destinations'][ $item_key ];
						}
					}
				} else if ( sanitize_text_field( $_REQUEST['snapshot-action'] ) == "add" ) {
					?>
					<h2><?php _ex( "Add Snapshot Destination", "Snapshot Plugin Page Title", SNAPSHOT_I18N_DOMAIN ); ?></h2>
					<p><?php _ex( "", 'Snapshot page description', SNAPSHOT_I18N_DOMAIN ); ?></p>
					<?php
					unset( $item );
					$item = array();

					if ( isset( $_REQUEST['type'] ) ) {
						$item['type'] = sanitize_text_field( $_REQUEST['type'] );
					}
				} else if ( sanitize_text_field( $_REQUEST['snapshot-action'] ) == "update" ) {

					?>
					<h2><?php _ex( "Edit Snapshot Destination", "Snapshot Plugin Page Title", SNAPSHOT_I18N_DOMAIN ); ?></h2>
					<p><?php _ex( "", 'Snapshot page description', SNAPSHOT_I18N_DOMAIN ); ?></p>
					<?php
					if ( isset( $_POST['snapshot-destination'] ) ) {
						$item = $_POST['snapshot-destination'];
					}
				}

			}
			if ( $item ) {
				Snapshot_Helper_UI::form_ajax_panels();
				?>
						<form action="<?php echo WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' ); ?>snapshots_destinations_panel&amp;snapshot-action=<?php echo urlencode( sanitize_text_field( $_GET['snapshot-action'] ) ); ?>&amp;type=<?php echo urlencode( $item['type'] ); ?>" method="post">
							<?php
				if ( ( sanitize_text_field( $_GET['snapshot-action'] ) == "edit" ) || ( sanitize_text_field( $_GET['snapshot-action'] ) == "update" ) ) {
					?>
					<input type="hidden" name="snapshot-action" value="update"/>
					<input type="hidden" name="item" value="<?php echo sanitize_text_field( $_GET['item'] ); ?>"/>
					<?php wp_nonce_field( 'snapshot-update-destination', 'snapshot-noonce-field' ); ?>
				<?php
				} else if ( sanitize_text_field( $_GET['snapshot-action'] ) == "add" ) {
					?>
					<input type="hidden" name="snapshot-action" value="add"/>
					<?php wp_nonce_field( 'snapshot-add-destination', 'snapshot-noonce-field' ); ?>
				<?php
				}

				$item_object = Snapshot_Model_Destination::get_object_from_type( $item['type'] );
				if ( ( $item_object ) && ( is_object( $item_object ) ) ) {
					$item_object->display_details_form( $item );
				}
				?>
								<input class="button-primary" type="submit" value="<?php _e( 'Save Destination', SNAPSHOT_I18N_DOMAIN ); ?>" />
								<a class="button-secondary" href="<?php echo WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' );
				?>snapshots_destinations_panel"><?php _e( 'Cancel', SNAPSHOT_I18N_DOMAIN ); ?></a>

							</div>
						</form>
						<?php
			}
			?>
			</div>
			<?php
		}

		public static function render_premium_plugin_prompt() {

			?>
			<div id="snapshot-edit-destinations-panel" class="wrap snapshot-wrap">
				<h2><?php _e( "Snapshot Destinations", SNAPSHOT_I18N_DOMAIN ); ?> </h2>
				<?php

				$message = '<p>A Snapshot destination is a great way to make sure you store your "Snapshots" somewhere else other than your website host. ';
				$message .= 'This makes sure your backups are safely available from somewhere else ready when you need them.</p>';

				$message .= '<p>Snapshot currently integrates with the following reliable services: Amazon AWS (S3 Buckets), Dropbox, Google Drive, Green Qloud and standard FTP.</p>';

				$message .= '<p>Destinations are available to you in Snapshot Pro from WPMU Dev: <a href="%s">Upgrade Now</a></p>';

				$message = sprintf( __( $message, SNAPSHOT_I18N_DOMAIN ), esc_url( 'https://premium.wpmudev.org/project/snapshot' ) );

				echo $message;

				?>

			</div>
		<?php
		}

	}

}