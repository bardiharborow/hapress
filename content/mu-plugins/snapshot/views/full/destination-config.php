<div class="snapshot-config">

	<div class="configure">
		<img src="<?php echo esc_url( WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_PLUGIN_URL' ) ); ?>/assets/img/destinations-config.png"/>
		<p>
			<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' ) ); ?>snapshots_full_backup_panel" class="button button-primary">
				<?php esc_html_e( 'Configure Full Backups', SNAPSHOT_I18N_DOMAIN ); ?>
			</a>
		</p>
	</div>

	<div class="benefits">
		<h3><?php esc_html_e( 'WPMU DEV Full Cloud Backup Service', SNAPSHOT_I18N_DOMAIN ); ?></h3>
		<ul>
			<li>
				<b><?php esc_html_e( 'Automatically backup your entire WordPress installation', SNAPSHOT_I18N_DOMAIN ); ?></b>
				<?php esc_html_e( 'including theme files, uploads, database tables and core WordPress files to our hosted cloud servers.', SNAPSHOT_I18N_DOMAIN ); ?>
			</li>
			<li>
				<?php esc_html_e( 'One-click restore an entire WordPress installation.', SNAPSHOT_I18N_DOMAIN ); ?>
			</li>
			<li>
				<?php echo wp_kses( __( '<b>Free</b> service for WPMU DEV members.', SNAPSHOT_I18N_DOMAIN ), array( 'b' => array() ) ); ?>
			</li>
		</ul>
	</div>

</div>