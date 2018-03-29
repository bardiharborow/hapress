<?php
/**
 * The base configuration for WordPress
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', $_SERVER['DB_NAME'] );

/** MySQL database username */
define( 'DB_USER', $_SERVER['DB_USER'] );

/** MySQL database password */
define( 'DB_PASSWORD', $_SERVER['DB_PASSWORD'] );

/** MySQL hostname */
define( 'DB_HOST', $_SERVER['DB_HOST'] );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', isset($_SERVER['DB_CHARSET']) ? $_SERVER['DB_CHARSET'] : 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', isset($_SERVER['DB_COLLATE']) ? $_SERVER['DB_COLLATE'] : '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         $_SERVER['AUTH_KEY'] );
define( 'SECURE_AUTH_KEY',  $_SERVER['SECURE_AUTH_KEY'] );
define( 'LOGGED_IN_KEY',    $_SERVER['LOGGED_IN_KEY'] );
define( 'NONCE_KEY',        $_SERVER['NONCE_KEY'] );
define( 'AUTH_SALT',        $_SERVER['AUTH_SALT'] );
define( 'SECURE_AUTH_SALT', $_SERVER['SECURE_AUTH_SALT'] );
define( 'LOGGED_IN_SALT',   $_SERVER['LOGGED_IN_SALT'] );
define( 'NONCE_SALT',       $_SERVER['NONCE_SALT'] );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = isset($_SERVER['TABLE_PREFIX']) ? $_SERVER['TABLE_PREFIX'] : 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', isset($_SERVER['WP_DEBUG']) ? $_SERVER['WP_DEBUG'] === 'true' : false );

/**
 * The Google App Engine file system is read-only.
 */
define( 'DISALLOW_FILE_MODS', true );

/**
 * Replace built-in cron with cron.yaml.
 */
define( 'DISABLE_WP_CRON', true );

/**
 * Move the WordPress content directory up one level.
 */
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/content' );

/**
 * Force site location.
 */
define( 'WP_HOME', 'https://' . $_SERVER['HOST'] );
define( 'WP_SITEURL', 'https://' . $_SERVER['HOST'] );

/**
 * Enable and configure multisite.
 */
define( 'WP_ALLOW_MULTISITE', true );
if (isset($_SERVER['MULTISITE']) ? $_SERVER['MULTISITE'] === 'true' : false) {
	define( 'MULTISITE', true );
	define( 'SUBDOMAIN_INSTALL', true );
	define( 'DOMAIN_CURRENT_SITE', $_SERVER['HOST'] );
	define( 'PATH_CURRENT_SITE', '/' );
	define( 'SITE_ID_CURRENT_SITE', 1 );
	define( 'BLOG_ID_CURRENT_SITE', 1 );
}

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
