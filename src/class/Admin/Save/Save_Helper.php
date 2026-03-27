<?php declare( strict_types = 1 );
/**
 * Helper methods for admin options save.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Admin\Save;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Admin_General;

/**
 * Helper methods for Save_Options
 *
 * @since 4.6
 */
class Save_Helper {

	/**
	 * Traits
	 */
	use Admin_General;

	/**
	 * Build the current URL for referer
	 * Use all the values data in $_GET automatically, except those in $forbidden_url_strings
	 * @return false|string The URL string if it's ok, false if both the $_GET is non-existant and wp_get_referer() can't get anything
	 */
	protected function get_referer(): bool|string {

		if ( count( $_GET ) > 0 ) {
			$forbidden_url_strings = [ 'dothis', 'where', 'type', '_nonce_cache_deleteindividual', '_nonce_cache_refreshindividual' ];
			$args = [];
			foreach ( $_GET as $key => $value ) {
				if ( ! in_array( $key, $forbidden_url_strings, true ) ) {
					$args[ $key ] = $value;
				}
			}
			return add_query_arg( $args, admin_url( 'admin.php' ) );
		}
		return wp_get_referer();
	}
}

