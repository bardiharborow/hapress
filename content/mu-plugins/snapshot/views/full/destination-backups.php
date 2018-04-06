<div class="snapshot-backups">
	<h3>
		<?php esc_html_e('Full Cloud Backups', SNAPSHOT_I18N_DOMAIN); ?>
		<?php if ( current_user_can( 'manage_snapshots_destinations' ) ) { ?>
			<a class="add-new-h2" href="<?php echo esc_url(WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' )); ?>snapshots_full_backup_panel">
				<?php esc_html_e('Add New', SNAPSHOT_I18N_DOMAIN); ?>
			</a>
		<?php } ?>
	</h3>

	<table class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e('WPMU DEV Account', SNAPSHOT_I18N_DOMAIN); ?></th>
				<th><?php esc_html_e('Time of Day', SNAPSHOT_I18N_DOMAIN); ?></th>
				<th><?php esc_html_e('Last Scan', SNAPSHOT_I18N_DOMAIN); ?></th>
				<th><?php esc_html_e('Storage Used', SNAPSHOT_I18N_DOMAIN); ?></th>
				<th><?php esc_html_e('Manage', SNAPSHOT_I18N_DOMAIN); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="email">
					<?php
					$account = class_exists( 'WPMUDEV_Dashboard' ) ? WPMUDEV_Dashboard::$site->get_option( 'auth_user' ) : __('Install Dashboard', SNAPSHOT_I18N_DOMAIN);
					echo esc_html( $account ); ?>
				</td>

				<td class="schedule">
					<?php
					$frequencies = $model->get_frequencies();
					$freq = $model->get_frequency();

					$schedules = $model->get_schedule_times();
					$sched = $model->get_schedule_time();

					if (!empty($freq) && !empty($frequencies[$freq]) && !empty($schedules) && !empty($schedules[$sched])) {
						printf(
							__('%1$s &middot; %2$s', SNAPSHOT_I18N_DOMAIN),
							esc_html($schedules[$sched]),
							esc_html($frequencies[$freq])
						);
					}
					?>
				</td>

				<td class="timestamp">
					<?php
					$last = $model->remote()->get_freshest_backup();
					if (is_array($last) && !empty($last['timestamp'])) {
						printf(
							'%s &middot; %s',
							esc_html(date('H:i', $last['timestamp'])),
							esc_html(date('M j Y', $last['timestamp']))
						);
					} else esc_html_e('N/A', SNAPSHOT_I18N_DOMAIN);
					?>
				</td>

				<td class="storage">
					<?php
					$total = (float)$model->remote()->get_total_remote_space();
					$used = (float)$model->remote()->get_used_remote_space();
					if ($total) {
						printf(
							'%1$s / %2$s',
							esc_html(Snapshot_Helper_Utility::size_format($used)),
							esc_html(Snapshot_Helper_Utility::size_format($total))
						);
					} else esc_html_e('N/A', SNAPSHOT_I18N_DOMAIN);
					?>
				</td>

				<td>
					<a href="<?php echo esc_url(WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' )); ?>snapshots_full_backup_panel" class="button">
						<?php esc_html_e('Configure', SNAPSHOT_I18N_DOMAIN); ?>
					</a>
				</td>
			</tr>
		</tbody>
	</table>

</div>