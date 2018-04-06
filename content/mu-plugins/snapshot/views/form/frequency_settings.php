<?php
	$disabled = $model->has_api_error()
		? 'disabled="disabled"'
		: ''
	;
	$cron_disabled = $model->get_config('disable_cron', false);
	$secret_key = $model->get_config('secret-key', '');
?>
<form method="post" <?php echo $disabled; ?> >
	<?php Snapshot_Model_Request::nonce('snapshot-full_backups-schedule'); ?>
<?php if (!$cron_disabled && !empty($secret_key)) { ?>
	<p>
		<?php esc_html_e('Managed backups run automatically on the frequency you choose below.', SNAPSHOT_I18N_DOMAIN); ?>
		<?php esc_html_e('We recommend choosing a time when your visitor traffic is at its lowest point and a frequency that suits how often your site content changes.', SNAPSHOT_I18N_DOMAIN); ?>
	</p>
	<p class="time-settings">
		<label for="frequency">
			<span><?php esc_html_e('Frequency', SNAPSHOT_I18N_DOMAIN); ?></span>
			<br />
			<select id="frequency" name="frequency" <?php echo $disabled; ?> >
			<?php foreach ($model->get_frequencies() as $key => $label) { ?>
				<option
					value="<?php echo esc_attr($key); ?>"
					<?php selected($key, $model->get_frequency()); ?>
				><?php echo esc_html($label); ?></option>
			<?php } ?>
			</select>
		</label>
		<label for="schedule_time">
			<span><?php esc_html_e('Time of day', SNAPSHOT_I18N_DOMAIN); ?></span>
			<br />
			<select id="schedule_time" name="schedule_time" <?php echo $disabled; ?> >
			<?php foreach ($model->get_schedule_times() as $key => $label) { ?>
				<option
					value="<?php echo esc_attr($key); ?>"
					<?php selected($key, $model->get_schedule_time()); ?>
				><?php echo esc_html($label); ?></option>
			<?php } ?>
			</select>
		</label>
	</p>
	<p><?php Snapshot_View_Template::get('full')->load('backup-start-time', array('model' => $model)); ?></p>
<?php } else { ?>
	<p>
		<em>
			<?php esc_html_e('Your automatic backups are currently disabled.', SNAPSHOT_I18N_DOMAIN); ?>
			<?php if (!empty($secret_key)) esc_html_e('You can still create manual backups.', SNAPSHOT_I18N_DOMAIN); ?>
		</em>
	</p>
<?php } ?>
	<p class="snapshot-settings actions">
	<?php if (!$cron_disabled && !empty($secret_key)) { ?>
		<button class="button deactivate" name="snapshot-disable-cron" value="yes">
			<?php esc_html_e('Deactivate', SNAPSHOT_I18N_DOMAIN); ?>
		</button>
		<button class="button button-primary" name="snapshot-schedule" value="yes">
			<?php esc_html_e('Update Schedule', SNAPSHOT_I18N_DOMAIN); ?>
		</button>
	<?php } else if (!empty($secret_key)) { ?>
		<button class="button" name="snapshot-enable-cron" value="yes">
			<?php esc_html_e('Activate', SNAPSHOT_I18N_DOMAIN); ?>
		</button>
	<?php } ?>
	</p>
</form>