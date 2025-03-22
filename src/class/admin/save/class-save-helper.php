<?php declare( strict_types = 1 );
/**
 * Helper methods for admin options save.
 *
 * @copyright (c) 2024, Lost Highway
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
use Lumiere\Config\Open_Options;

/**
 * Helper methods for Save_Options
 *
 * @since 4.6 new
 */
class Save_Helper {

	/**
	 * Traits
	 */
	use Open_Options, Admin_General;

	/**
	 * Allows to limit the calls to rewrite rules refresh
	 * @var string|null $page_data_taxo Full URL to data page taxonomy subpage
	 * @see Save_Options::save_data_options()
	 * @since 4.1
	 */
	protected null|string $page_data_taxo;

	/**
	 * Constructor
	 * @param string|null $page_data_taxo Full URL to data page taxonomy subpage
	 */
	public function __construct( ?string $page_data_taxo = null ) {

		// Store page
		$this->page_data_taxo = $page_data_taxo;

		// Get options from database.
		$this->get_db_options(); // In Open_Options trait.
	}

	/**
	 * Build the current URL for referer
	 * Use all the values data in $_GET automatically, except those in $forbidden_url_strings
	 * @return false|string The URL string if it's ok, false if both the $_GET is non-existant and wp_get_referer() can't get anything
	 */
	protected function get_referer(): bool|string {

		/** @psalm-suppress PossiblyNullArgument -- Argument 1 of esc_html cannot be null, possibly null value provided - I don't even understand*/
		$gets_array = array_map( 'esc_html', $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- no escape ok!
		// These forbidden strings are generated in Cache class in $_GET
		$forbidden_url_strings = [ 'dothis', 'where', 'type', '_nonce_cache_deleteindividual', '_nonce_cache_refreshindividual' ];
		$first_url_string = '';
		$next_url_strings = '';
		foreach ( $gets_array as $var => $value ) {

			// Don't add to the URL those forbidden strings
			if ( in_array( $var, $forbidden_url_strings, true ) ) {
				continue;
			}
			// Build the beginning of the URL on the first occurence
			if ( $var === array_key_first( $gets_array ) ) {
				$first_url_string = 'admin.php?' . $var . '=' . $value;
				continue;
			}

			// Add the strings on the next lines after the first one
			$next_url_strings .= '&' . $var . '=' . $value;
		}
		return count( $gets_array ) > 0 ? admin_url( $first_url_string . $next_url_strings ) : wp_get_referer();
	}

	/**
	 * Validate nonce
	 * Can be $_GET or $_POST
	 *
	 * @param string $nonce_action Action for nonce
	 * @param string $nonce_field Field name in $_POST or $_GET
	 * @param string $get_or_post using $_POST by default
	 * @return bool True if nonce is valid
	 */
	protected function is_valid_nonce( string $nonce_action, string $nonce_field, string $get_or_post = 'post' ): bool {
		if ( $get_or_post === 'get' ) {
			return isset( $_GET[ $nonce_field ] ) && is_string( $_GET[ $nonce_field ] ) && wp_verify_nonce( sanitize_key( $_GET[ $nonce_field ] ), $nonce_action ) > 0;
		}
		return isset( $_POST[ $nonce_field ] ) && is_string( $_POST[ $nonce_field ] ) && wp_verify_nonce( sanitize_key( $_POST[ $nonce_field ] ), $nonce_action ) > 0;
	}
}

