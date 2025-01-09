<?php declare( strict_types = 1 );
/**
 * Trait for getting database options
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Tools\Data;
use Lumiere\Settings;

/**
 * Trait for getting database options
 */
trait Get_Options {

	/**
	 * Traits
	 */
	use Data;

	/**
	 * Get an array of the taxonomy in use in the form of taxonomy
	 * @return array<int, string>
	 * @phpstan-return array<array-key, string>
	 */
	public function get_taxonomy_activated(): array {
		$imdb_data_values = get_option( Settings::get_data_tablename() );
		$imdb_admin_values = get_option( Settings::get_admin_tablename() );
		$all_tax_array = $this->lumiere_array_key_exists_wildcard( $imdb_data_values, 'imdbtaxonomy*', 'key-value' ); // Method in trait Data
		$taxonomy_full_name = [];
		foreach ( $all_tax_array as $option => $activated ) {
			// Check if a specific taxonomy (such as actor, genre) is activated.
			if ( $activated !== '1' ) {
				continue;
			}
			$taxonomy_item = is_string( $option ) ? str_replace( 'imdbtaxonomy', '', $option ) : ''; // Such as "director"
			$taxonomy_full_name[] = $imdb_admin_values['imdburlstringtaxo'] . $taxonomy_item; // Such as "lumiere-director"

		}
		return $taxonomy_full_name;
	}

	/**
	 * Get the current Lumière version
	 * @return string
	 */
	public static function get_lumiere_version(): string {
		return ( new Settings() )->lumiere_version;
	}
}

