<?php $destinations = array();

foreach ( WPMUDEVSnapshot::instance()->config_data['destinations'] as $key => $item ){
	$type = $item['type'];

	if ( ! isset( $destinations[ $type ] ) ){
		$destinations[ $type ] = array();
	}

	$destinations[ $type ][ $key ] = $item;
} ?>

<section class="box wpsd-widget-dropbox">

	<div class="box-title has-typecon has-button">

		<i class="wps-typecon dropbox"></i>

		<h3><?php _e( 'Dropbox', SNAPSHOT_I18N_DOMAIN ); ?></h3>

		<a class="button button-small button-outline" href="<?php echo add_query_arg( array( 'snapshot-action' => 'add' , 'type' => 'dropbox' ), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ); ?>" class="button button-outline"><?php _e( 'Add Destination', SNAPSHOT_I18N_DOMAIN ); ?></a>

	</div>

	<div class="box-content">

		<div class="row">

			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

				<?php $this->render( "destinations/partials/dropbox-destination-list", false, array( 'item' => $item,'destinations' => ( isset( $destinations[ 'dropbox' ] ) ? $destinations[ 'dropbox' ] : array() ) ), false, false ); ?>

			</div>

		</div>

	</div>

</section>