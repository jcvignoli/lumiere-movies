<?php declare( strict_types = 1 );
/**
 * Admin Trait
 *
 * @author Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Tools\Files;

/**
 * Trait for general function
 *
 * @since 4.1
 */
trait Admin_General {

	/**
	 * Traits
	 * Include trait Files, since most of the classes need to access to those functions
	 */
	use Files;

	/**
	 * Get the current URL
	 *
	 * @return string
	 */
	public function get_current_admin_url(): string {
		$current_url = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
		return admin_url( str_replace( site_url( '', 'relative' ) . '/wp-admin', '', $current_url ) );
	}
}

