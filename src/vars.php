<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress global vars
 * These vars are available before the Plugin activation
 * They are available anywhere in the Plugin or for any plugin
 *
 * @package       lumieremovies
 */

// Get the path of Lumière dir
if ( ! defined( 'LUM_WP_PATH' ) ) {
	define( 'LUM_WP_PATH', plugin_dir_path( __FILE__ ) );
}

// Get the URL of Lumière dir
if ( ! defined( 'LUM_WP_URL' ) ) {
	define( 'LUM_WP_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'LUMIERE_INCOMPATIBLE_PLUGINS' ) ) {
	/**
	 * If those plugins are installed, Lumière will be deactivated and could not be activated again
	 * Those plugins are crap and Lumière will not support them
	 */
	define( 'LUMIERE_INCOMPATIBLE_PLUGINS', [ 'rss-feed-post-generator-echo/rss-feed-post-generator-echo.php' ] );
}
