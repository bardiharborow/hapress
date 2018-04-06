<?php

if (!$model->get_config('disable_cron', false)) {
	$frequencies = $model->get_frequencies();
	$freq = $model->get_frequency();

	$schedules = $model->get_schedule_times();
	$sched = $model->get_schedule_time();

	if (!empty($freq) && !empty($frequencies[$freq]) && !empty($schedules) && !empty($schedules[$sched])) {
		printf(
			wp_kses(__('Your automatic backups are set to run <b>%1$s</b>, at <b>%2$s</b>.', SNAPSHOT_I18N_DOMAIN), array('b' => array())),
			esc_html($frequencies[$freq]),
			esc_html($schedules[$sched])
		);
	}

	Snapshot_View_Template::get('full')->load('backup-start-time', array('model' => $model));

} else {
	esc_html_e('Your automatic managed backups are disabled.', SNAPSHOT_I18N_DOMAIN);

	$secret_key = $model->get_config('secret-key', '');
	if (!empty($secret_key)) {
		echo ' ';
		esc_html_e('You can still run manual managed backups.', SNAPSHOT_I18N_DOMAIN);
	}
}