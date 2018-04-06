<form method="post">
	<?php Snapshot_Model_Request::nonce('snapshot-full_backups-activate'); ?>
	<p>
		<label for="secret-key">
			<?php esc_html_e('Please, enter your secret key:', SNAPSHOT_I18N_DOMAIN); ?>
			<input
				type="text"
				name="secret-key"
				class="widefat"
				id="secret-key"
				value=""
				placeholder="<?php esc_attr_e('Your secret key here', SNAPSHOT_I18N_DOMAIN); ?>"
			/>
			<?php
				printf (
					__('You can get your secret key <a href="%s" target="_blank">here</a>.', SNAPSHOT_I18N_DOMAIN),
					esc_attr($model->get_current_secret_key_link())
				);
			?>
		</label>
	</p>
	<p>
		<button class="button button-primary" name="activate" value="yes">
			<?php esc_html_e('Activate Managed Backups', SNAPSHOT_I18N_DOMAIN); ?>
		</button>
	</p>
</form>