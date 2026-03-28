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
		$current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( (string) $_SERVER['REQUEST_URI'] ) ) : '';
		return admin_url( str_replace( site_url( '', 'relative' ) . '/wp-admin', '', $current_url ) );
	}

	/**
	 * Validate nonce for Admin.
	 * Can work with Nonce Name or Nonce Token, but one is mandatory.
	 *
	 * @param string $nonce_name Optional: because by default '_wpnonce'
	 * @param int|string $nonce_action Optional: by default -1 that means no action name
	 * @param string $nonce_token Optional: token nonce, ie 'a244uv223', by default ''
	 * @return bool True if nonce is valid (either 1 or 2 on success)
	 */
	public function is_valid_nonce( string $nonce_name = '_wpnonce', int|string $nonce_action = -1, string $nonce_token = '' ): bool {

		// a $nonce_token was provided, use it as it is
		if ( strlen( $nonce_token ) > 0 && wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce_token ) ), $nonce_action ) !== false ) {
			return true;
		}

		$nonce_token = isset( $_REQUEST[ $nonce_name ] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST[ $nonce_name ] ) ) : '';
		$nonce_token_valid = strlen( $nonce_token ) > 0 && wp_verify_nonce( $nonce_token, $nonce_action ) !== false;

		if ( $nonce_token_valid === false ) {
			return false;
		}
		return true;
	}

	/**
	 * Validate the nonce for Admin. If invalid, die()
	 * Works only with Nonce Name (no token)
	 *
	 * @param string $nonce_name Optional: because by default '_wpnonce'
	 * @param int|string $nonce_action Optional: by default -1, which means no action name at all
	 * @return void Die if invalid nonce
	 */
	public function is_valid_nonce_die( string $nonce_name = '_wpnonce', int|string $nonce_action = -1 ): void {
		check_admin_referer( $nonce_action, $nonce_name );
	}
}

