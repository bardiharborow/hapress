<?php
	$sorts = array();

	if (!empty($items)) foreach ($items as $item) {
		if (empty($item['timestamp'])) continue;

		$key = date('Y-m', (int)$item['timestamp']);
		if (!empty($sorts[$key])) continue;

		$sorts[$key] = date_i18n('M Y', $item['timestamp']);
	}
?>
<?php if (!empty($sorts)) { ?>
	<div class="alignleft actions">
		<select name="snapshot-full-date_selection">
			<option><?php esc_html_e('All dates', SNAPSHOT_I18N_DOMAIN); ?></option>
		<?php if (count($sorts) > 1) foreach ($sorts as $key => $date) { ?>
			<option value="<?php echo esc_attr($key); ?>">
				<?php echo esc_html($date); ?>
			</option>
		<?php } ?>
		</select>
		<button class="button button-secondary">
			<?php esc_html_e('Filter', SNAPSHOT_I18N_DOMAIN); ?>
		</button>
	</div>
<?php } ?>

<?php if ($model->has_dashboard() && $model->is_active()) { ?>
	<div class="alignright actions refresh">
		<a href="#refresh" class="button">
			<span class="label"><?php esc_html_e('Refresh List', SNAPSHOT_I18N_DOMAIN); ?></span>
		</a>
	</div>
	<div class="alignright actions reset-api">
		<a href="#reset-api" class="button">
			<span class="label"><?php esc_html_e('Reset API', SNAPSHOT_I18N_DOMAIN); ?></span>
		</a>
	</div>
<?php } ?>

<script>
;(function ($) {

	$(function () {
		$(".backup-list .tablenav .alignright.actions.refresh a").click(function (e) {
			if (e && e.preventDefault) e.preventDefault();
			if (e && e.stopPropagation) e.stopPropagation();

			$.post(ajaxurl, {
				action: 'snapshot-full_backup-reload'
			}).always(function () {
				window.location.reload();
			});

			return false;
		});
	});

	$(function () {
		$(".backup-list .tablenav .alignright.actions.reset-api a").click(function (e) {
			if (e && e.preventDefault) e.preventDefault();
			if (e && e.stopPropagation) e.stopPropagation();

			$.post(ajaxurl, {
				action: 'snapshot-full_backup-reset_api'
			}).always(function () {
				window.location.reload();
			});

			return false;
		});
	});


})(jQuery);
</script>