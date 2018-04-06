<?php
$time_key = time();
$item = array();

while ( true ) {
	if ( ! isset( WPMUDEVSnapshot::instance()->config_data['items'][ $time_key ] ) ) {
		break;
	}
	$time_key = time();
}

$requirements_test = Snapshot_Helper_Utility::check_system_requirements();
$checks = $requirements_test['checks'];
$all_good = $requirements_test['all_good'];
$warning = $requirements_test['warning'];

$disabled = $model->has_api_error() ? 'disabled="disabled"' : '';
$cron_disabled = $model->get_config( 'disable_cron', false );

?>

<section id="header">
	<h1><?php esc_html_e( 'Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<?php $this->render( "managed-backups/partials/create-backup-progress", false, array( 'item' => $item, 'time_key' => $time_key ), false, false ); ?>

<div id="snapshot-ajax-out">
	<div class="out"></div>
</div>

<form id="managed-backup-update" method="post" action="<?php echo add_query_arg( 'tab', 'settings', WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) ); ?>">

	<input type="hidden" id="snapshot-action" name="snapshot-action" value="update-managed-backup-setting"/>

	<input type="hidden" id="snapshot-backup-action" name="snapshot-schedule" value="yes"/>

	<div id="container" class="snapshot-three wps-page-wizard">

		<section class="box new-snapshot-main-box">

			<div class="box-title has-button">

				<h3><?php _e( 'Backups Wizard', SNAPSHOT_I18N_DOMAIN ); ?></h3>

				<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) ); ?>" class="button button-small button-gray button-outline"><?php _e( 'Back', SNAPSHOT_I18N_DOMAIN ); ?></a>

			</div>

			<div class="box-content">

				<?php $this->render( "common/requirements-test", false, $requirements_test, false, false ); ?>

				<div class="box-tab configuration-box<?php if ( $all_good ) {
					echo ' open';
				} ?>">

					<div class="box-tab-title can-toggle">
						<h3><?php _e( 'Configuration', SNAPSHOT_I18N_DOMAIN ); ?></h3>
						<?php if ( $all_good ): ?>
							<i class="wps-icon i-arrow-right"></i>
						<?php endif; ?>
					</div>

					<?php if ( $all_good ): ?>

						<div class="box-tab-content">

							<div id="wps-check-notice" class="row">

								<div class="col-xs-12">

									<div class="wps-auth-message <?php if ( ! $all_good ) {
										echo 'error';
									} else if ( $warning ) {
										echo 'warning';
									} else {
										echo 'success';
									} ?>">
										<?php if ( ! $all_good ) { ?>
											<p><?php _e( 'You must meet the server requirements before proceeding.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } else if ( $warning ) { ?>
											<p><?php _e( 'You have 1 or more requirements warnings. You can proceed, however Snapshot may run into issues due to the warnings.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } else { ?>
											<p><?php _e( 'You meet the server requirements. You can proceed now.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } ?>
									</div>

								</div>

							</div>

							<div id="wps-new-destination" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Destination', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="box-mask">

										<label class="label-title"><?php

											$storage = Snapshot_Model_Full_Remote_Storage::get();

											printf(
												__( "Managed backups can only be stored on WPMU DEV's cloud servers. You have <strong>%s</strong> of your %s storage remaining.", SNAPSHOT_I18N_DOMAIN ),
												size_format( $storage->get_free_remote_space() ),
												size_format( $storage->get_total_remote_space() )
											); ?></label>

										<div class="box-gray">

											<div class="radio-destination">


												<div class="wps-input--item">

													<div class="wps-input--radio">

														<input checked="checked" type="radio">

														<label for="snap-cloud"></label>

													</div>

													<label for="snap-cloud"><span><?php _e( 'WPMU DEV Cloud', SNAPSHOT_I18N_DOMAIN ); ?></span><i class="wps-typecon cloud"></i></label>

												</div>

											</div>
										</div>

									</div>

								</div>

							</div>

							<div id="wps-new-frequency" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php _e( 'Frequency', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="box-mask">

										<label class="label-title">
											<?php _e( 'Would you like to schedule managed backups to run regularly or once-off?', SNAPSHOT_I18N_DOMAIN ); ?>
										</label>

										<div class="wps-input--group">

											<div class="wps-input--item">

												<div class="wps-input--radio">
													<input id="frequency-once" type="radio"<?php checked( $model->get_config( 'disable_cron', false ) ); ?> name="frequency" value="once">

													<label for="frequency-once"></label>

												</div>
												<label for="frequency-once"><?php _e( 'Once-off', SNAPSHOT_I18N_DOMAIN ); ?></label>
											</div>

											<div class="wps-input--item">
												<div class="wps-input--radio">
													<input id="frequency-daily" type="radio" name="frequency" value="schedule"<?php
														checked( ! $model->get_config( 'disable_cron', false ) ); ?>>

													<label for="frequency-daily"></label>
												</div>

												<label for="frequency-daily"><?php _e( 'Run daily, weekly or monthly', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

										<div id="snapshot-schedule-options-container" class="box-gray">

											<h3><?php _e( 'Schedule', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<?php Snapshot_Model_Request::nonce( 'snapshot-full_backups-schedule' ); ?>

											<div class="wps-new-backups-schedule schedule-inline-form">

												<select id="frequency" name="frequency" <?php echo $disabled; ?> >
													<?php foreach ( $model->get_frequencies() as $key => $label ) { ?>
														<option
																value="<?php echo esc_attr( $key ); ?>"
															<?php selected( $key, $model->get_frequency() ); ?>
														><?php echo esc_html( $label ); ?></option>
													<?php } ?>
												</select>

												<select id="schedule_time" name="schedule_time" <?php echo $disabled; ?> >
													<?php foreach ( $model->get_schedule_times() as $key => $label ) { ?>
														<option
																value="<?php echo esc_attr( $key ); ?>"
															<?php selected( $key, $model->get_schedule_time() ); ?>
														><?php echo esc_html( $label ); ?></option>
													<?php } ?>
												</select>

											</div>

											<h3><?php _e( 'Storage Limit', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<div class="storage-inline-form">

												<span class="inbetween">Keep</span>

												<?php
												if ( ! isset( $item['archive-count'] ) ) {
													$item['archive-count'] = Snapshot_Model_Full_Remote_Storage::get()->get_max_backups_limit();
												}

												?>
												<input type="number" name="backups-limit" id="snapshot-archive-count"
												       value="<?php echo esc_attr( $item['archive-count'] ); ?>">

												<span class="inbetween"><?php _e( 'backups before removing older archives.', SNAPSHOT_I18N_DOMAIN ); ?></span>

											</div>

											<p>
												<small><?php _e( "By default, Snapshot will run as many scheduled backups as you need. We recommend that you remove older backups to avoid filling your remote storage limit. If you would like to keep all of your backup archives, just set your storage limit to 0.", SNAPSHOT_I18N_DOMAIN ); ?></small>
											</p>

											<h3><?php _e( 'Optional', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<div class="wps-input--item">

												<div class="wps-input--checkbox">

													<input type="checkbox" id="checkbox-run-backup-now" class="" value="1" checked/>

													<label for="checkbox-run-backup-now"></label>

												</div>

												<label for="checkbox-run-backup-now"><?php _e( 'Also run a backup now', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

									</div>

								</div>

							</div>

							<div class="row">

								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

									<div class="form-button-container form-button-single">

										<button type="submit" class="button button-blue"
										        data-update-settings-text="<?php esc_attr_e( 'Update Settings', SNAPSHOT_I18N_DOMAIN ); ?>"
										        data-run-backup-text="<?php esc_attr_e( 'Run Backup', SNAPSHOT_I18N_DOMAIN ); ?>">
											<?php _e( 'Run Backup', SNAPSHOT_I18N_DOMAIN ); ?>
										</button>

									</div>

								</div>

							</div>

						</div>

					<?php endif; ?>

				</div>

			</div>

		</section>

	</div>
</form>