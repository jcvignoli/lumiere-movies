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
 *
 * @since 4.0.3
 */
trait Admin_General {

	/**
	 * Get the current URL
	 *
	 * @return string
	 */
	public function lumiere_get_current_admin_url() {
		$current = admin_url( str_replace( site_url( '', 'relative' ) . '/wp-admin', '', $_SERVER['REQUEST_URI'] ?? '' ) );
		return $current;
	}

	/**
	 * Request WP_Filesystem credentials if file doesn't have it.
	 * @param string $file The file with full path to ask the credentials form
	 *
	 * @since 3.9.7 Added extra require_once() if $wp_filesystem is null
	 */
	public function lumiere_wp_filesystem_cred( string $file ): void {

		global $wp_filesystem;

		// On some environnements, $wp_filesystem is sometimes not correctly initialised through globals.
		if ( $wp_filesystem === null ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		/** WP: request_filesystem_credentials($form_post, $type, $error, $context, $extra_fields, $allow_relaxed_file_ownership); */
		$creds = request_filesystem_credentials( $file, '', false );

		if ( $creds === false ) {
			echo esc_html__( 'Credentials are required to edit this file: ', 'lumiere-movies' ) . esc_html( $file );
			return;
		}

		$credit_open = is_array( $creds ) === true ? WP_Filesystem( $creds ) : false;

		// our credentials were no good, ask for them again.
		if ( $credit_open === false || $credit_open === null ) {

			$creds_two = request_filesystem_credentials( $file, '', true, '' );

			// If credentials succeeded or failed, don't pass them to WP_Filesystem.
			if ( is_bool( $creds_two ) === true ) {
				WP_Filesystem();
				return;
			}

			WP_Filesystem( $creds_two );
		}
	}
}

