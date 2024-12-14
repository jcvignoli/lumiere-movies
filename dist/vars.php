<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress global vars
 *
 * @package           lumiere-movies
 * @author            jcvignoli
 * @copyright         2005 https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 */
 

if ( !defined( 'LUMIERE_WP_PATH' ) ) {
	define( 'LUMIERE_WP_PATH', plugin_dir_path( __FILE__ ) );
}

if ( !defined( 'LUMIERE_WP_URL' ) ) {
	define( 'LUMIERE_WP_URL', plugin_dir_url( __FILE__ ) );
}
