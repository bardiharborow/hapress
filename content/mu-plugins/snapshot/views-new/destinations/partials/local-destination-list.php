<?php if( !isset( $destinations ) || empty( $destinations ) ) : ?>

	<div class="wps-notice">

		<p><?php _e('You haven\'t added a Local destination yet.', SNAPSHOT_I18N_DOMAIN); ?></p>

	</div>

<?php else: ?>

	<table cellpadding="0" cellspacing="0">

		<thead>

			<tr>

				<th class="wps-destination-name"><?php _e('Name', SNAPSHOT_I18N_DOMAIN); ?></th>

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

					<td class="wps-destination-dir" data-text="Dir:"><?php echo WPMUDEVSnapshot::instance()->config_data['config']['backupFolder']; ?></td>

					<td class="wps-destination-shots"><?php Snapshot_Model_Destination::show_destination_item_count( $id ); ?></td>

					<td class="wps-destination-config">

						<a class="button button-small button-outline button-gray" href="<?php echo add_query_arg( array( 'snapshot-action' => 'edit' , 'type' => urlencode( $destination['type'] ) , 'item' => urlencode( $id ) ), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations') ); ?>">
							<span class="dashicons dashicons-admin-generic"></span>
							<span class="wps-destination-config-text"><?php _e('Configure', SNAPSHOT_I18N_DOMAIN); ?></span>
						</a>

					</td>

				</tr>

			<?php endforeach; ?>

		</tbody>

	</table>

<?php endif; ?>