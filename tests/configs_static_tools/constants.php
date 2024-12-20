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

if ( !defined( 'LUMIERE_WP_PATH' ) ) {
	// Extra check for phpstan on github.
	$abs_path = dirname( dirname( dirname( __DIR__ ) ) ) . '/blogpourext/';
	if ( is_dir( $abs_path ) ) {
		// Local dev value.
		define( 'LUMIERE_WP_PATH', $abs_path . 'wp-content/plugins/lumiere-movies/' );
	} elseif ( defined( 'ABSPATH' ) ) {
		// Value that PHPStan github gets.
		define( 'LUMIERE_WP_PATH', ABSPATH );
	}
}

if ( !defined( 'LUMIERE_WP_URL' ) ) {
	define( 'LUMIERE_WP_URL', 'https://whatver.com/wp-content/plugins/lumiere-movies/' );
}

/**
 * The WP_DEBUG_LOG is either bool or string, but is defined as only bool in stubs
 * Using random function to have bool or string defines
 * Can't override PHPStan behaviour, but with phan and psalm ok
 */
if ( ! defined( 'WP_DEBUG_LOG' ) ) {
	$rand = rand( 1, 10 );
	if ( $rand > 5 ) {
		$final = ABSPATH . 'whateverthepath';
	} else {
		$final = false;
	}
	define( 'WP_DEBUG_LOG', $final );
}

/* Dynamic constant, declaration for Psalm -> but then brings issue with PHPStan!
if ( ! defined( 'XMLRPC_REQUEST' ) ) {
	define('XMLRPC_REQUEST',
	// @var mixed $x
	$x = false
	);
}
if ( ! defined( 'DOING_CRON' ) ) {
	define('DOING_CRON',
	// @var mixed $x
	$x = false
	);
}
if ( ! defined( 'REST_REQUEST' ) ) {
	define('REST_REQUEST',
	// @var mixed $x
	$x = false
	);
}
if ( ! defined( 'DOING_AUTOSAVE' ) ) {
	define('DOING_AUTOSAVE',
	// @var mixed $x
	$x = false
	);
}
}*/
