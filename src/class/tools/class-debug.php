<?php declare( strict_types = 1 );
/**
 * Debug static class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Trait for debuging operations
 */
class Debug {

	/**
	 * Return the hooks currently used
	 */
	public static function get_hooks(): string {
		global $wp_filter;
		return '<pre>' . print_r( array_keys( $wp_filter ), true ) . '</pre>';
	}
}

