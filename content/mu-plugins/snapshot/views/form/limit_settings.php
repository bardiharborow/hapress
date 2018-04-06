<?php
	if (!$model->is_active()) return false;

	$secret_key = $model->get_config('secret-key', false);
	if (empty($secret_key)) return false;

	$limit = Snapshot_Model_Full_Remote_Storage::get()->get_max_backups_limit();
	$all_limits = array_merge(
		range(1, 4),
		range(5, 20, 5),
		range(30, 100, 10)
	);
?>
<div class="snapshot-full-limit_settings">
	<fieldset>
		<p>
			<label for="backups-limit">
				<?php esc_html_e('Keep this many remote backups:', SNAPSHOT_I18N_DOMAIN); ?>
				<select name="backups-limit" id="backups-limit">
				<?php foreach ($all_limits as $lmt) { ?>
					<option
						value="<?php echo (int)$lmt; ?>"
						<?php selected($lmt, $limit); ?>
					><?php echo (int)$lmt; ?></option>
				<?php } ?>
				</select>
			</label>
		</p>
	</fieldset>
</div>