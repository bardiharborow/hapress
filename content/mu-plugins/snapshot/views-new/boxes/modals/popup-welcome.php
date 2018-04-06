<?php

$plugin = WPMUDEVSnapshot::instance();

/* Don't display this notice if it has already been seen */
if ( isset( $plugin->config_data['seen_welcome'] ) && $plugin->config_data['seen_welcome'] ) {
	return;
}

$plugin->config_data['seen_welcome'] = true;
$plugin->save_config();

?>
<div id="wps-welcome-message" class="snapshot-three wps-popup-modal show">

	<div class="wps-popup-mask"></div>

	<div class="wps-popup-content">
		<div class="box">
			<div class="box-title has-button can-close">
				<h3><?php _e('Welcome to Snapshot', SNAPSHOT_I18N_DOMAIN); ?></h3>
				<a href="#" class="button button-small button-outline button-gray wps-popup-close wps-dismiss-welcome">
					<?php _e('Skip', SNAPSHOT_I18N_DOMAIN); ?>
				</a>
			</div>

			<div class="box-content">
				<div class="row">

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

						<?php if ( $is_client && ! $has_snapshot_key) : ?>

							<p><?php _e('Welcome to Snapshot Pro, the hottest backups plugin for WordPress! Let’s start by choosing what type of backup you’d like to make - there are two types…', SNAPSHOT_I18N_DOMAIN); ?></p>

						<?php else : ?>

							<p><?php _e('Welcome to Snapshot, the hottest backups plugin for WordPress! With this plugin you can backup and migrate bits and pieces of your website to third party destinations like Dropbox, Google Drive, Amazon S3 & more.', SNAPSHOT_I18N_DOMAIN); ?></p>

						<?php endif; ?>

						<?php if ( $is_client && ! $has_snapshot_key) : ?>

							<div class="wps-welcome-message-pro">
								<h3><?php _e('WPMU DEV Managed Backups', SNAPSHOT_I18N_DOMAIN); ?></h3>
								<p><small><?php _e('As part of your WPMU DEV membership you get 10GB free cloud storage to back up and store your entire WordPress website - including WordPress itself. You can schedule these backups to run daily, monthly or weekly and should you ever need it you can restore an entire website in just a few clicks.', SNAPSHOT_I18N_DOMAIN); ?></small></p>
								<p><a class="button button-blue button-small wps-dismiss-welcome"
									  href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-managed-backups') ); ?>">
										<?php _e( 'Activate Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?>
									</a>
								</p>
							</div>

							<div class="wps-welcome-message-pro">
								<h3><?php _e('Snapshots', SNAPSHOT_I18N_DOMAIN); ?></h3>
								<p><small><?php _e('With Snapshots you can backup and migrate bits and pieces of your website. You can choose what files, plugins/themes and database tables to backup and then store them on third party destinations. To get started, let’s add your first destination.', SNAPSHOT_I18N_DOMAIN); ?></small></p>
							</div>

						<?php endif; ?>

							<p><?php _e("<strong>Let’s start by adding a new destination</strong>; where would you like to store your first snapshot?", SNAPSHOT_I18N_DOMAIN); ?></p>

						<table cellpadding="0" cellspacing="0">
							<tbody>
								<tr><?php // Dropbox ?>
									<td class="start-icon"><i class="wps-typecon dropbox"></i></td>
									<td class="start-name"><?php _e('Dropbox', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<a class="button button-blue button-small wps-dismiss-welcome"
										   href="<?php echo esc_url( add_query_arg( array( 'snapshot-action' => 'add' , 'type' => 'dropbox' ), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ) ); ?>">
											<?php _e('Add Destination', SNAPSHOT_I18N_DOMAIN); ?>
										</a>
									</td>
								</tr>

								<tr><?php // Google Drive ?>
									<td class="start-icon"><i class="wps-typecon google"></i></td>
									<td class="start-name"><?php _e('Google', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<a class="button button-blue button-small wps-dismiss-welcome"
										   href="<?php echo esc_url( add_query_arg( array( 'snapshot-action' => 'add' , 'type' => 'google-drive' ), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ) ); ?>">
											<?php _e('Add Destination', SNAPSHOT_I18N_DOMAIN); ?>
											</a>
									</td>
								</tr>

								<tr><?php // Amazon S3 ?>
									<td class="start-icon"><i class="wps-typecon aws"></i></td>
									<td class="start-name"><?php _e('Amazon S3', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<a class="button button-blue button-small wps-dismiss-welcome"
										   href="<?php echo esc_url( add_query_arg( array( 'snapshot-action' => 'add' , 'type' => 'aws' ), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ) ); ?>">
											<?php _e('Add Destination', SNAPSHOT_I18N_DOMAIN); ?>
										</a>
									</td>
								</tr>

								<tr><?php // sFTP ?>
									<td class="start-icon"><i class="wps-typecon sftp"></i></td>
									<td class="start-name"><?php _e('FTP / sFTP', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<a class="button button-blue button-small wps-dismiss-welcome"
										   href="<?php echo esc_url( add_query_arg( array( 'snapshot-action' => 'add' , 'type' => 'ftp' ), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ) ); ?>">
											<?php _e('Add Destination', SNAPSHOT_I18N_DOMAIN); ?>
										</a>
									</td>
								</tr>

								<tr><?php // Local ?>
									<td class="start-icon"><i class="wps-typecon local"></i></td>
									<td class="start-name"><?php _e('Local', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<a class="button button-gray button-small button-outline wps-dismiss-welcome"
										   href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-new-snapshot') ); ?>" >
											<?php _e('Use Destination', SNAPSHOT_I18N_DOMAIN); ?></a>
									</td>
								</tr>

							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>