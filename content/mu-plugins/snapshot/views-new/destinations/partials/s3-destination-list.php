<?php if( !isset( $destinations ) || empty( $destinations ) ) : ?>

	<div class="wps-notice">

		<p><?php _e('You haven\'t added an Amazon S3 destination yet.', SNAPSHOT_I18N_DOMAIN); ?></p>

	</div>

<?php else: ?>

	<table cellpadding="0" cellspacing="0">

		<thead>

			<tr>

				<th class="wps-destination-name"><?php _e('Name', SNAPSHOT_I18N_DOMAIN); ?></th>

				<th class="wps-destination-bucket"><?php _e('Bucket', SNAPSHOT_I18N_DOMAIN); ?></th>

				<th class="wps-destination-dir"><?php _e('Directory', SNAPSHOT_I18N_DOMAIN); ?></th>

				<th class="wps-destination-shots"><?php _e('Snapshots', SNAPSHOT_I18N_DOMAIN); ?></th>

				<th class="wps-destination-config"></th>

			</tr>

		</thead>

		<tbody>

			<?php foreach($destinations as $id => $destination) : ?>

				<tr>

					<td class="wps-destination-name">

						<a href="<?php echo add_query_arg( array( 'snapshot-action' => 'edit' , 'type' => urlencode( $destination['type'] ) , 'item' => urlencode( $id ) ), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ); ?>"><?php echo $destination['name'] ?></a>

					</td>

					<td class="wps-destination-bucket" data-text="Bucket:"><?php echo $destination['bucket'] ?></td>

					<td class="wps-destination-dir" data-text="Dir:"><?php echo $destination['directory'] ?></td>

					<td class="wps-destination-shots"><?php Snapshot_Model_Destination::show_destination_item_count( $id ); ?></td>

					<td class="wps-destination-config">

						<a class="button button-small button-outline button-gray" href="<?php echo add_query_arg( array( 'snapshot-action' => 'edit' , 'type' => urlencode( $destination['type'] ) , 'item' => urlencode( $id ) ), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ); ?>"><?php _e('Configure', SNAPSHOT_I18N_DOMAIN); ?></a>

					</td>

				</tr>

			<?php endforeach; ?>

		</tbody>

	</table>

<?php endif; ?>