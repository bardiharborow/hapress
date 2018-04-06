<?php
	$this->load('_head-backups', array('model' => $model));
?>

<div id="snapshot-widgets" class="metabox-holder">
	<div class="postbox-container">

		<div class="backup-list-wrapper">
		<?php
			if (!$model->has_backups()) {
				$this->load('_backups-message', array('model' => $model));
			} else {
				$this->load('_backups-list', array('model' => $model));
			}
		?>
		</div>

		<div class="postbox backup-settings schedule">
			<h3 class="hndle ui-sortable-handle"><span><?php esc_html_e('Backups Schedule', SNAPSHOT_I18N_DOMAIN); ?></span></h3>
			<div class="inside">
				<div class="main">
					<?php Snapshot_View_Template::get('form')->load('frequency_settings', array('model' => $model)); ?>
				</div>
			</div>
		</div>

		<div class="postbox backup-settings settings">
			<h3 class="hndle ui-sortable-handle"><span><?php esc_html_e('Settings', SNAPSHOT_I18N_DOMAIN); ?></span></h3>
			<div class="inside">
				<div class="main">
					<?php Snapshot_View_Template::get('form')->load('backups_settings', array('model' => $model)); ?>
				</div>
			</div>
		</div>

	</div>
</div>

<?php $this->load('_foot', array('model' => $model)); ?>