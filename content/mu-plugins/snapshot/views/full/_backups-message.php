<div class="postbox backup-list">
	<h3 class="hndle ui-sortable-handle"><span><?php esc_html_e('Backups', SNAPSHOT_I18N_DOMAIN); ?></span></h3>
	<div class="inside">
		<div class="main">
			<p>
				<?php esc_html_e('Your cloud backups will appear here when the first backup has run, as well as on your WPMU DEV Hub.', SNAPSHOT_I18N_DOMAIN); ?>
				<?php Snapshot_View_Template::get('full')->load('_backups-state', array('model' => $model)); ?>
			</p>
		</div>
	</div>
</div>