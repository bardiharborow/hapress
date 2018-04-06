<div class="box-tab requirements-check-box<?php if ( !$all_good || $warning ) { echo ' open'; } ?>">
	<div class="box-tab-title can-toggle">
		<h3><?php _e( 'Requirements Check', SNAPSHOT_I18N_DOMAIN ); ?>
		<span class="wps-tag wps-tag--<?php if ( !$all_good ) { echo 'red'; } else if ( $warning ) { echo 'yellow'; } else { echo 'green'; } ?>">
		<?php
			if ( !$all_good ) {
			_e( 'FAIL', SNAPSHOT_I18N_DOMAIN );
			} else if ( $warning ) {
			_e( 'WARNING', SNAPSHOT_I18N_DOMAIN );
			} else {
			_e( 'PASS', SNAPSHOT_I18N_DOMAIN );
			} ?>
		</span></h3>
		<i class="wps-icon i-arrow-right"></i>
	</div>
	<div class="box-tab-content">
		<div class="wps-requirements-list">
			<div class="box-gray">
				<table class="wps-table" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<th>
								<?php _e( 'PHP Version', SNAPSHOT_I18N_DOMAIN ); ?>
								<?php if( !$checks['PhpVersion']['test'] ) : ?>
								<span class="wps-tag wps-tag--red"><?php _e( 'FAIL', SNAPSHOT_I18N_DOMAIN ); ?></span>
								<?php else : ?>
								<span class="wps-tag wps-tag--green"><?php _e( 'PASS', SNAPSHOT_I18N_DOMAIN ); ?></span>
								<?php endif; ?>
							</th>
							<?php if( !$checks['PhpVersion']['test'] ) : ?>
							<td>
								<?php printf( __( 'Your PHP version is out of date.
									Your current version is %s and we require 5.2 or newer.
									You\'ll need to update your PHP version to proceed.
									If you use a managed host, contact them directly to have it updated.', SNAPSHOT_I18N_DOMAIN ) ,$checks['PhpVersion']['value'] ); ?>
							</td>
							<?php endif; ?>
						</tr>
						<tr>
							<th <?php if( $checks['MaxExecTime']['test'] ) : ?> colspan="2" <?php endif; ?> >
								<?php _e( 'Max Execution Time', SNAPSHOT_I18N_DOMAIN ); ?>
								<?php if( !$checks['MaxExecTime']['test'] ) : ?>
								<span class="wps-tag wps-tag--yellow"><?php _e( 'WARNING', SNAPSHOT_I18N_DOMAIN ); ?></span>
								<?php else : ?>
								<span class="wps-tag wps-tag--green"><?php _e( 'PASS', SNAPSHOT_I18N_DOMAIN ); ?></span>
								<?php endif; ?>
							</th>
							<?php if( !$checks['MaxExecTime']['test'] ) : ?>
							<td>
								<?php printf( __( '<b><code>max_execution_time</code> is set to %s which is too low</b>.
									A minimum execution time of 150 seconds is recommended to give the migration process the
									best chance of succeeding. If you use a managed host, contact them directly to have it updated.', SNAPSHOT_I18N_DOMAIN ) ,$checks['MaxExecTime']['value'] ); ?>
							</td>
							<?php endif; ?>
						</tr>
						<tr>
							<th <?php if( $checks['Mysqli']['test'] ) : ?> colspan="2" <?php endif; ?> >
								<?php _e( 'MySQLi', SNAPSHOT_I18N_DOMAIN ); ?>
								<?php if( !$checks['Mysqli']['test'] ) : ?>
								<span class="wps-tag wps-tag--red"><?php _e( 'FAIL', SNAPSHOT_I18N_DOMAIN ); ?></span>
								<?php else : ?>
								<span class="wps-tag wps-tag--green"><?php _e( 'PASS', SNAPSHOT_I18N_DOMAIN ); ?></span>
								<?php endif; ?>
							</th>
							<?php if( !$checks['Mysqli']['test'] ) : ?>
							<td>
								<?php _e( '<b>PHP MySQLi module not found</b>.
									Snapshot needs the MySQLi module to be installed and enabled
									on the target server. If you use a managed host, contact them
									directly to have this module installed and enabled.', SNAPSHOT_I18N_DOMAIN );
									?>
							</td>
							<?php endif; ?>
						</tr>
						<tr>
							<th <?php if( $checks['Zip']['test'] ) : ?> colspan="2" <?php endif; ?> >
								<?php _e( 'GZip', SNAPSHOT_I18N_DOMAIN ); ?>
								<?php if( !$checks['Zip']['test'] ) : ?>
								<span class="wps-tag wps-tag--red"><?php _e( 'FAIL', SNAPSHOT_I18N_DOMAIN ); ?></span>
								<?php else : ?>
								<span class="wps-tag wps-tag--green"><?php _e( 'PASS', SNAPSHOT_I18N_DOMAIN ); ?></span>
								<?php endif; ?>
							</th>
							<?php if( !$checks['Zip']['test'] ) : ?>
							<td>
								<?php _e( '<b>PHP Zip module not found</b>.
									To unpack the zip file, Snapshot needs the Zip module to be installed and enabled.
									If you use a managed host, contact them directly to have it updated.', SNAPSHOT_I18N_DOMAIN );
									?>
							</td>
							<?php endif; ?>
						</tr>
					</tbody>
				</table>
			</div>
			<p><a href="" class="button button-outline button-gray"><?php _e('Re-Check', SNAPSHOT_I18N_DOMAIN); ?></a></p>
		</div>
	</div>
</div>