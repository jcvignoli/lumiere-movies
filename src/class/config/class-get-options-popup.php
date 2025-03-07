<?php declare( strict_types = 1 );
/**
 * Getting Settings and database options
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Config;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Settings_Popup;

/**
 * Get settings related to popups
 * Helper class meant to be called anywhere, so all methods should be static
 */
class Get_Options_Popup extends Settings_Popup {

	/**
	 * Get People fields
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 */
	public static function get_all_person_fields( int $number = 1 ): array {
		return parent::define_list_items_person( $number );
	}
}

