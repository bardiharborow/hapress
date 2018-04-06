<?php
if (!defined('WPINC')) die;
$secret_key = $model->get_config('secret-key', '');
?>
<div id="snapshot-full_backups-panel" class="wrap snapshot-wrap">
	<header class="<?php echo !empty($secret_key) && $model->is_active() ? 'is-' : 'not-'; ?>active">
		<h2>
			<span><?php echo esc_html(_x( "Managed Backups", "Snapshot Managed Backups Title", SNAPSHOT_I18N_DOMAIN )); ?></span>
			<?php if (!empty($secret_key) && $model->is_active()) { ?>
				<?php Snapshot_View_Template::get('full')->load('_start_backup'); ?>
			<?php } ?>
		</h2>
		<p>
			<?php Snapshot_View_Template::get('full')->load('_backups-state', array('model' => $model)); ?>
			<?php
				printf(
					wp_kses(__('View and manage your managed backups in your <a href="%s">WPMU DEV Hub</a>.', SNAPSHOT_I18N_DOMAIN), array('a' => array('href' => array()))),
					esc_url($model->get_current_site_management_link())
				);
			?>
		</p>
	</header>

	<div class="snapshot-errors">
	<?php

	// Main errors area
	if (!empty($secret_key)) {
		// We have secret key, *and* some errors. Let's show everything
		if ($model->has_errors()) {
			$errors = array_unique($model->get_errors());
			foreach ($errors as $error) {
				?>
					<div class="notice notice-error below-h2">
						<p><?php echo $this->to_message_html($error); ?></p>
					</div>
				<?php
			}
		}
	} else {
		// No secret key - of course we have errors.
		// First thing's first, let's just focus on secret key error
		?>
			<div class="notice notice-error below-h2">
				<p><?php printf(Snapshot_View_Full_Backup::get_message('missing_secret_key'), esc_url($model->get_current_secret_key_link())); ?></p>
			</div>
		<?php
	}


	if (!$model->has_dashboard_key()) {
		// So apparently, we don't have a dash key available.
		// We do, however, have the dashboard available, so let's show an error.
		?>
			<div class="notice notice-error">
				<p><?php esc_html_e('Please log into your WPMU DEV Dashboard before continuing.', SNAPSHOT_I18N_DOMAIN); ?></p>
			</div>
		<?php
	}

	// Local backups warning
	if (count($model->local()->get_backups())) {
		?>
		<div class="notice-warning notice below-h2">
			<p>
				<?php esc_html_e('It seems there are some local managed backups around. Having your backups off-site is a much better idea.', SNAPSHOT_I18N_DOMAIN); ?>
				<?php esc_html_e('You may wish to remove those permanently.', SNAPSHOT_I18N_DOMAIN); ?>
				<button class="button button-secondary snapshot show-local hide-if-no-js" type="button">
					<?php esc_html_e('Show me', SNAPSHOT_I18N_DOMAIN); ?>
				</button>
			</p>
		</div>
		<?php
	}

	// Out of space error
	if (Snapshot_Model_Full_Remote_Storage::get()->is_out_of_space()) {
		?>
		<div class="notice notice-error">
			<h3>
				<?php esc_html_e('You ran out of space for your backups.', SNAPSHOT_I18N_DOMAIN); ?>
			</h3>
			<?php if (Snapshot_Model_Full_Remote_Storage::get()->has_previous_backups()) { ?>
				<p>
					<?php esc_html_e('Please, clear up some of your older backups.', SNAPSHOT_I18N_DOMAIN); ?>
				</p>
			<?php } else { ?>
				<p>
					<?php esc_html_e('Please, check your membership plan.', SNAPSHOT_I18N_DOMAIN); ?>
				</p>
			<?php } ?>
		</div>
		<?php
	}
	?>
	</div>