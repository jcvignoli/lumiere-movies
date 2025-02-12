<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress global vars
 *
 * @package           lumiere-movies
 * @author            jcvignoli
 * @copyright         2005 https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
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
