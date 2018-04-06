
<section id="header">
	<h1><?php esc_html_e( 'Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-backups">

	<section class="box wps-widget-getkey">

		<div class="box-title">
			<h3><?php _e('Get Started', SNAPSHOT_I18N_DOMAIN); ?></h3>
		</div>

		<div class="box-content">

			<div class="row">

				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

					<div class="wps-image img-snappie-four"></div>

					<div class="wps-getkey-box">

						<p><?php printf( __( '%s, as a WPMU DEV member you get 10GB free cloud storage included in your membership. Create and store full backups of your website, including WordPress core files. And if disaster strikes, you can quickly and easily restore your website any time. <br/>Add your Snapshot Key to enable this service.', SNAPSHOT_I18N_DOMAIN ), wp_get_current_user()->display_name ); ?></p>

					</div>

					<p>
						<a id="view-snapshot-key" class="button button-blue"><?php _e( 'Activate Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?></a>
					</p>

				</div>

			</div>

		</div>

	</section>

</div>

<?php
$model = new Snapshot_Model_Full_Backup;
$apiKey = $model->get_config('secret-key', '');
$data = array(
	"hasApikey" => !empty($apiKey),
	"apiKey" => $apiKey,
	"apiKeyUrl" => $model->get_current_secret_key_link()
);
?>

<?php $this->render("boxes/modals/popup-snapshot", false, $data, false, false); ?>