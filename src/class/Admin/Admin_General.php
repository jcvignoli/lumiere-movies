<?php declare( strict_types = 1 );
/**
 * Admin Trait
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
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
		$current_url = esc_url_raw( wp_unslash( strval( $_SERVER['REQUEST_URI'] ?? '' ) ) );
		return admin_url( str_replace( site_url( '', 'relative' ) . '/wp-admin', '', $current_url ) );
	}

	/**
	 * Validate nonce for Admin.
	 *
	 * @param string $nonce_name by default '_wpnonce'
	 * @param int|string $nonce_action by default -1, which means no action name at all
	 * @return bool True if nonce is valid (either 1 or 2 on success)
	 */
	public function is_valid_nonce( string $nonce_name = '_wpnonce', int|string $nonce_action = -1 ): bool {
		return check_admin_referer( $nonce_action, $nonce_name ) > 0;
	}

	/**
	 * Validate the nonce for Admin. If invalid, die()
	 *
	 * @param string $nonce_name by default '_wpnonce'
	 * @param int|string $nonce_action by default -1, which means no action name at all
	 * @return void Die if invalid nonce
	 */
	public function is_valid_nonce_die( string $nonce_name = '_wpnonce', int|string $nonce_action = -1 ): void {
		if ( $this->is_valid_nonce( $nonce_name, $nonce_action ) === false ) {
			wp_die( esc_html__( 'Invalid or missing nonce.', 'lumiere-movies' ), 'Lumière Movies', [ 'response' => 403 ] );
		}
	}
}

