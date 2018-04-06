<?php
if ($model->get_config('disable_cron', false)) return false;

$start_timestamp = $model->get_next_automatic_backup_start_time();
if (!empty($start_timestamp) && $start_timestamp > time()) {
	$time = Snapshot_Model_Time::get()->to_local_time($start_timestamp);
	$date = '<time datetime="' . esc_attr(date('Y-m-d\TH:i:s\ZP', $start_timestamp)) . '">' .
		sprintf(
			_x('%1$s, %2$s at %3$s', 'Next automatic backup: [day date], [year] at [time]', SNAPSHOT_I18N_DOMAIN),
			date_i18n('l F j', $time),
			date_i18n('Y', $time),
			date_i18n('g\:ia', $time)
		) .
	'</time>';
}
?>

<?php
	if (!empty($date)) echo wp_kses(
		sprintf(__('Your next automatic backup is to happen on %s.', SNAPSHOT_I18N_DOMAIN), $date),
		array(
			'time' => array(
				'datetime' => array(),
			),
		)
	);
?><!-- next backup -->