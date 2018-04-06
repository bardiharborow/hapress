<?php $this->load('_head-start'); ?>

<?php
	$is_dashboard_active = $model->is_dashboard_active();
	$is_dashboard_installed = $is_dashboard_active
		? true
		: $model->is_dashboard_installed()
	;
	$has_dashboard_key = $model->has_dashboard_key();
?>

<div id="snapshot-widgets" class="metabox-holder">
	<div class="postbox-container">

		<div class="postbox get_started">
			<h3 class="hndle ui-sortable-handle"><span><?php esc_html_e('Get Started', SNAPSHOT_I18N_DOMAIN); ?></span></h3>
			<div class="inside">
				<div class="main">
					<p>
						<?php
						echo wp_kses(
							__('First, you\'ll need to install and activate the <b>WPMU DEV Dashboard plugin</b> which will act as the API connection to our cloud storage servers.', SNAPSHOT_I18N_DOMAIN),
							array('b' => array())
						);
						?>
						<?php
						esc_html_e(
							'Once installed, you\'ll be able to access your backups from the WPMU DEV onsite dashboard anytime, anywhere - nifty!',
							SNAPSHOT_I18N_DOMAIN
						);
						?>
					</p>
					<p>
					<?php if (empty($is_dashboard_active) && empty($is_dashboard_installed)) { ?>
						<a href="https://premium.wpmudev.org/project/wpmu-dev-dashboard/" target="_blank" class="button button-primary">
							<?php esc_html_e('Install WPMU DEV Dashboard', SNAPSHOT_I18N_DOMAIN); ?>
						</a>
					<?php } else if (empty($is_dashboard_active) && !empty($is_dashboard_installed)) { ?>
						<a href="<?php echo esc_url(network_admin_url('plugins.php')); ?>" class="button button-primary">
							<?php esc_html_e('Activate WPMU DEV Dashboard', SNAPSHOT_I18N_DOMAIN); ?>
						</a>
					<?php } else if (!empty($has_dashboard_key)) { ?>
						<a href="<?php echo esc_url(admin_url('admin.php?page=wpmudev')); ?>" class="button button-primary">
							<?php esc_html_e('Login to the WPMU DEV Dashboard plugin', SNAPSHOT_I18N_DOMAIN); ?>
						</a>
					<?php } ?>
					</p>
				</div>
			</div>
		</div>

	</div>
</div>

<?php $this->load('_foot', array('model' => $model)); ?>