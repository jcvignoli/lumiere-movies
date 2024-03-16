<?php declare( strict_types = 1 );
/**
 * Admin Trait
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

/**
 * Trait for general function
 * @since 4.0.3
 */
trait Admin_General {

	/**
	 * Function lumiere_notice
	 * Display a confirmation notice, such as "options saved"
	 *
	 * @param int $code type of message
	 * @param string $msg text to display
	 */
	public function lumiere_notice( int $code, string $msg ): string {

		switch ( $code ) {
			default:
			case 1: // success notice, green
				return '<div class="notice notice-success"><p>' . $msg . '</p></div>';
			case 2: // info notice, blue
				return '<div class="notice notice-info"><p>' . $msg . '</p></div>';
			case 3: // simple error, red
				return '<div class="notice notice-error"><p>' . $msg . '</p></div>';
			case 4: // warning, yellow
				return '<div class="notice notice-warning"><p>' . $msg . '</p></div>';
			case 5: // success notice, green, dismissible
				return '<div class="notice notice-success is-dismissible"><p>' . $msg . '</p></div>';
			case 6: // info notice, blue, dismissible
				return '<div class="notice notice-info is-dismissible"><p>' . $msg . '</p></div>';
			case 7: // simple error, red, dismissible
				return '<div class="notice notice-error is-dismissible"><p>' . $msg . '</p></div>';
			case 8: // warning, yellow, dismissible
				return '<div class="notice notice-warning is-dismissible"><p>' . $msg . '</p></div>';
		}
	}

}

