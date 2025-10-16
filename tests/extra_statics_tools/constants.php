<?php
/**
 * Missing wordpress constants in static checks.
 */
if ( ! defined( 'ABSPATH' ) ) {
	$local_file = dirname( dirname( dirname( __DIR__ ) ) ) . '/blogpourext/';
	if ( is_dir( $local_file ) ) {
		// Local dev value.
		define( 'ABSPATH', $local_file );
	} else {
		// Value that PHPStan github gets.
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

if ( !defined( 'LUM_WP_PATH' ) ) {
	// Extra check for phpstan on github.
	$abs_path = dirname( dirname( dirname( __DIR__ ) ) ) . '/blogpourext/';
	if ( is_dir( $abs_path ) ) {
		// Local dev value.
		define( 'LUM_WP_PATH', $abs_path . 'wp-content/plugins/lumiere-movies/' );
	} elseif ( defined( 'ABSPATH' ) ) {
		// Value that PHPStan github gets.
		define( 'LUM_WP_PATH', './' );
	}
}

if ( !defined( 'LUM_WP_URL' ) ) {
	define( 'LUM_WP_URL', 'https://whatver.com/wp-content/plugins/lumiere-movies/' );
}

/**
 * Returns randomly a string or false
 */
function randomizer_string_or_false() {
	$rand = rand( 1, 10 );
	if ( $rand > 5 ) {
		return 'random_string';
	} else {
		return false;
	}
}

/**
 * The WP_DEBUG_LOG is either bool or string, but is defined as only bool in stubs
 * Using random function to have two different types of outputs defined
 */
if ( ! defined( 'WP_DEBUG_LOG' ) ) { /** for phan */
	define( 'WP_DEBUG_LOG', randomizer_string_or_false() );
}

if ( ! defined( 'LUMIERE_INCOMPATIBLE_PLUGINS' ) ) {
	define( 'LUMIERE_INCOMPATIBLE_PLUGINS', [ 'my_crapy_plugin' ] );
}

if ( ! defined( 'LUM_VENDOR_FOLDER' ) ) {
	define( 'LUM_VENDOR_FOLDER', 'vendor/' );
}
