<?php
//define('DONOTCACHEPAGE', '1');

$wp_path = str_replace( strstr( dirname( __FILE__ ), '/wp-content' ), '', dirname( __FILE__ ) ) . '/';

// To let out own plugin know we are in SHORTINIT mode
define('WPMUDEV_CHAT_SHORTINIT', true);

define( 'SHORTINIT', 1 );
require $wp_path . 'wp-load.php'; // adjust according to your paths
require ABSPATH . WPINC . '/session.php';
require ABSPATH . WPINC . '/formatting.php';
require ABSPATH . WPINC . '/capabilities.php';
require ABSPATH . WPINC . '/user.php';
require ABSPATH . WPINC . '/meta.php';
require ABSPATH . WPINC . '/post.php';
require ABSPATH . WPINC . '/pluggable.php';
wp_plugin_directory_constants();
wp_cookie_constants();

//if( current_user_can( 'manage_options' ) ) { // check capability

$GLOBALS = array(); // free some memory

// require your file here
include_once dirname( __FILE__ ) . '/lib/Snapshot/Helper/Recovery.php';
$recovery = new Snapshot_Helper_Recovery( __FILE__ );

//} else {
//	header("HTTP/1.1 401 Unauthorized");
//	exit;
//}