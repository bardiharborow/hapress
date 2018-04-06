<div class="backup-actions">
	<?php if (Snapshot_Controller_Full_Cron::is_running()) { ?>
		<div class="cron-warn"><?php esc_html_e('Automatic backup currently running', SNAPSHOT_I18N_DOMAIN); ?></div>
	<?php } else {
	/**
	 * No cron backup running, safe to do this here and now
	 */
	?>
		<button name="backup" value="yes" class="button button-primary button-backup-test">
			<?php esc_html_e('Backup now', SNAPSHOT_I18N_DOMAIN); ?>
		</button>

	<script type="text/javascript">
	;(function ($, undefined) {
		$(function () {
			Sfb.ManualBackup.run();
		});
	})(jQuery);
	</script>
	<style type="text/css">
	button.button-backup-test span.out {
		margin-left: 1em;
		font-size: .8em;
	}
	</style>
<?php } ?>
</div>