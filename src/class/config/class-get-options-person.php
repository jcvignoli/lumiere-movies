<?php declare( strict_types = 1 );
/**
 * Getting Settings and database options
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Config;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Settings_Person;

/**
 * Get settings related to Persons
 * Helper class meant to be called anywhere, so all methods should be static and public
 */
class Get_Options_Person extends Settings_Person {

	/**
	 * Get People fields
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 */
	public static function get_all_person_fields( int $number = 1 ): array {
		return parent::define_list_items_person( $number );
	}

	/**
	 * Get activated credit roles
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 */
	public static function get_all_credit_role( int $number = 1 ): array {
		return parent::credits_role_all( $number );
	}
}

