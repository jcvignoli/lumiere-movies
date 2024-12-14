<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress global vars
 *
 * @package           lumiere-movies
 * @author            jcvignoli
 * @copyright         2005 https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 */

// Get the path of Lumière dir
if ( ! defined( 'LUMIERE_WP_PATH' ) ) {
	define( 'LUMIERE_WP_PATH', plugin_dir_path( __FILE__ ) );
}

// Get the URL of Lumière dir
if ( ! defined( 'LUMIERE_WP_URL' ) ) {
	define( 'LUMIERE_WP_URL', plugin_dir_url( __FILE__ ) );
}
