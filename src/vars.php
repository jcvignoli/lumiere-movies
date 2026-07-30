<?php
/**
 * Lumière Movies WordPress global vars
 * These vars are available before the Plugin activation
 * They are available anywhere in the Plugin or for any plugin
 *
 * @package       lumieremovies
 */
declare( strict_types = 1 );

// Prevent any direct call.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'You are not allowed to call this page directly.' );
}

// Get the path of Lumière dir
if ( ! defined( 'LUMIERE_WP_PATH' ) ) {
	define( 'LUMIERE_WP_PATH', plugin_dir_path( __FILE__ ) );
}

// Get the URL of Lumière dir
if ( ! defined( 'LUMIERE_WP_URL' ) ) {
	define( 'LUMIERE_WP_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'LUMIERE_INCOMPATIBLE_PLUGINS' ) ) {
	/**
	 * If those plugins are installed, Lumière will be deactivated and could not be activated again
	 * Those plugins are crap and Lumière will not support them
	 */
	define( 'LUMIERE_INCOMPATIBLE_PLUGINS', [ 'rss-feed-post-generator-echo/rss-feed-post-generator-echo.php' ] );
}

// Composer folder
if ( ! defined( 'LUMIERE_VENDOR_FOLDER' ) ) {
	define( 'LUMIERE_VENDOR_FOLDER', 'vendor/' );
}
