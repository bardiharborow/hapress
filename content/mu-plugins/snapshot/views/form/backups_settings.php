<?php
	$secret_key = $model->get_config('secret-key', '');
?>
<form method="post">
	<?php Snapshot_Model_Request::nonce('snapshot-full_backups-settings'); ?>
	<p class="api-setup">
		<label for="secret-key">
			<span><?php esc_html_e('Snapshot Key', SNAPSHOT_I18N_DOMAIN); ?></span>
			<input type="text" id="secret-key" name="secret-key" class="widefat" value="<?php echo esc_attr($secret_key); ?>" />
			<?php if (empty($secret_key)) { ?>
				<?php
					printf(
						__('You can get your secret key <a href="%s" target="_blank">here</a>.', SNAPSHOT_I18N_DOMAIN),
						esc_attr($model->get_current_secret_key_link())
					);
				?>
			<?php } ?>
		</label>
	</p>

	<?php Snapshot_View_Template::get('form')->load('limit_settings', array('model' => $model)); ?>
	<?php Snapshot_View_Template::get('form')->load('log_settings', array('model' => $model)); ?>

	<p class="snapshot-settings actions">
		<button class="button">
			<?php esc_html_e('Cancel', SNAPSHOT_I18N_DOMAIN); ?>
		</button>
		<button class="button button-primary" name="snapshot-settings" value="yes">
			<?php esc_html_e('Update Settings', SNAPSHOT_I18N_DOMAIN); ?>
		</button>
	</p>
</form>