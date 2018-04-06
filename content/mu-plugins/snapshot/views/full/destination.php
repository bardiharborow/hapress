<div class="snapshot-destination full">
	<?php
		$template = $model->has_dashboard() && $model->is_active()
			? 'destination-backups'
			: 'destination-config'
		;
		$this->load($template, array('model' => $model));
	?>
</div>