<?php $this->load('_head-start'); ?>

<div id="snapshot-widgets" class="metabox-holder">
	<div class="postbox-container">

		<div class="postbox activate_backup">
			<h3 class="hndle ui-sortable-handle"><span><?php esc_html_e('Get Started', SNAPSHOT_I18N_DOMAIN); ?></span></h3>
			<div class="inside">
				<div class="main">
					<p>
						<?php echo wp_kses(
							__('Great, you already have the <b>WPMU DEV Dashboard plugin</b> installed which will act as the API connection to our cloud storage servers.', SNAPSHOT_I18N_DOMAIN),
							array('b' => array())
						);
						?>
						<?php echo wp_kses(
							__('Next, click <b>Activate Full Backups</b> to enable this service.', SNAPSHOT_I18N_DOMAIN),
							array('b' => array())
						); ?>
						<?php esc_html_e('You will be able to select the frequency and time of day to run the automated full backups.', SNAPSHOT_I18N_DOMAIN); ?>
					</p>
					<p>
						<?php Snapshot_View_Template::get('form')->load('activate_backups', array('model' => $model)); ?>
					</p>
				</div>
			</div>
		</div>

	</div>
</div>

<?php $this->load('_foot', array('model' => $model)); ?>