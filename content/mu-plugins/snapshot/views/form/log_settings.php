<?php
	$log = Snapshot_Helper_Log::get();
	$enabled = Snapshot_Controller_Full_Log::get()->is_enabled();
	$log_settings = $model->get_config('full_log_setup', array());
	$default_level = !empty($enabled)
		? $log->get_default_level()
		: 0
	;
?>

<div class="snapshot-full-log_settings">
		<?php Snapshot_Model_Request::nonce('snapshot-full_backups-log_setup'); ?>

		<fieldset>
			<legend><?php esc_html_e('Logging', SNAPSHOT_I18N_DOMAIN); ?></legend>
			<p>
				<label for="log-enable">
					<input type="hidden" name="log-enable" value="no" />
					<input
						type="checkbox"
						<?php checked($enabled, true); ?>
						name="log-enable"
						id="log-enable"
						value="yes"
						data-default="<?php echo esc_attr($default_level); ?>"
					/>
					<?php esc_html_e('Enable logging', SNAPSHOT_I18N_DOMAIN); ?>
				</label>
			</p>
		</fieldset>

		<div class="snapshot-settings log-levels">
		<?php foreach ($log->get_known_sections() as $sect => $section) { ?>
			<fieldset class="log_section <?php echo esc_attr($sect); ?>">
				<legend><?php echo esc_html($section); ?></legend>
				<div class="log-options">
					<label for="<?php echo esc_attr($sect); ?>-disable">
						<input type="radio"
							name="log_level[<?php echo esc_attr($sect); ?>]"
							id="<?php echo esc_attr($sect); ?>-disable"
							<?php echo isset($log_settings[$sect])
								? checked((int)$log_settings[$sect], 0, false)
								: checked(0, $default_level, false)
							; ?>
							value="0"
						/>
						<?php echo esc_html_e('Disable', SNAPSHOT_I18N_DOMAIN); ?>
					</label>
				<?php foreach ($log->get_known_levels() as $lvl => $level) { ?>
					<label for="<?php echo esc_attr($sect); ?>-<?php echo esc_attr($lvl); ?>">
						<input type="radio"
							name="log_level[<?php echo esc_attr($sect); ?>]"
							id="<?php echo esc_attr($sect); ?>-<?php echo esc_attr($lvl); ?>"
							value="<?php echo esc_attr($lvl); ?>"
							<?php echo isset($log_settings[$sect])
								? checked((int)$log_settings[$sect], $lvl, false)
								: checked($lvl, $default_level, false)
							; ?>
						/>
						<?php echo esc_html($level); ?>
					</label>
				<?php } ?>
				</div>
			</fieldset>
		<?php } ?>
		<div class="log-actions">
			<a href="#view-log-file" title="<?php esc_attr_e('Managed Backups Log', SNAPSHOT_I18N_DOMAIN); ?>">
				<?php esc_html_e('View your log file', SNAPSHOT_I18N_DOMAIN); ?>
			</a>
		</div>
	</div>
</div>