<?php if (!defined('WPINC')) die; ?>
<div id="snapshot-full_backups-panel" class="wrap snapshot-wrap">
	<header>
		<h2><span><?php _ex( "Managed Backups", "Snapshot Full Backups Title", SNAPSHOT_I18N_DOMAIN ); ?></span></h2>
		<p>
			<?php esc_html_e('Backup your entire WordPress installation to our WPMU DEV secure cloud automatically.', SNAPSHOT_I18N_DOMAIN); ?>
			<?php esc_html_e('Yep - files, databases, WordPress core, the lot.', SNAPSHOT_I18N_DOMAIN); ?>
		</p>
	</header>