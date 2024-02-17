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

/* Dynamic constant, declaration for Psalm but it brings a new error about always false in class logger...
if ( ! defined( 'DOING_CRON' ) ) {
	define('DOING_CRON',
	// @var mixed $x
	$x = false
	);
}
*/
