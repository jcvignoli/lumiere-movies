<?php
// Missing wordpress constants in phpstan
if ( ! defined( 'ABSPATH' ) ) {
	$local_file = dirname( __DIR__ ) . '/../../blogpourext/';
	if ( is_dir( $local_file ) ) {
		define( 'ABSPATH', $local_file );
	} else {
		define( 'ABSPATH', 'src/' );
	}
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define('WP_CONTENT_DIR', 'wp-content' );
}
if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes');
}

if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', ABSPATH . 'wp-includes/plugins/lumiere-movies/');
}

