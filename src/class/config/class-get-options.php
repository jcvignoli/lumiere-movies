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

use Lumiere\Tools\Data;
use Lumiere\Config\Settings;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Config\Get_Options_Person;

/**
 * Getting Settings and database options
 * Helper class meant to be called anywhere, so all methods should be static
 */
class Get_Options extends Settings {

	/**
	 * Define all the pages of Lumiere
	 *
	 * @see \Lumiere\Admin\Admin::lumiere_execute_admin_assets()
	 *
	 * @return array<string>
	 */
	public static function get_admin_lum_pages(): array {
		$imdb_admin_option = get_option( parent::LUM_ADMIN_OPTIONS );
		return [
			$imdb_admin_option !== false ? $imdb_admin_option['imdburlstringtaxo'] : parent::URL_STRING_TAXO,
			parent::LUM_FILE_COPY_THEME_TAXONOMY,
			parent::LUM_SEARCH_ITEMS_FILE, // For accessing the search in clicking a link (ie gutenberg)
			parent::LUM_SEARCH_ITEMS_URL_ADMIN,  // For accessing the search in URL lumiere/search
			Get_Options_Movie::LUM_POPUP_SEARCH_PATH, // to be removed?
			Get_Options_Movie::LUM_POPUP_MOVIE_PATH, // to be removed?
			Get_Options_Person::LUM_POPUP_PERSON_PATH, // to be removed?
		];
	}

	/**
	 * Get an array of the taxonomy in use in the form of taxonomy
	 *
	 * @see \Lumiere\Tools\Data::lumiere_array_key_exists_wildcard() Check if a string exists in array array using a wildcard
	 *
	 * @return array<int, string>
	 * @phpstan-return array<array-key, string>
	 */
	public static function get_taxonomy_activated(): array {
		$imdb_data_values = get_option( Get_Options_Movie::get_data_tablename() );
		$imdb_admin_values = get_option( self::get_admin_tablename() );
		$all_tax_array = Data::lumiere_array_key_exists_wildcard( $imdb_data_values, 'imdbtaxonomy*', 'key-value' ); // Method in trait Data
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
		return lum_get_version();
	}

	/**
	 * Retrieve selected type of search in admin
	 *
	 * @return string
	 * @see \Imdb\TitleSearch For the options
	 */
	public static function get_type_search(): string {
		$imdb_admin_option = get_option( self::get_admin_tablename() );
		return parent::LUM_IMDB_SEARCH_CATEGORY[ $imdb_admin_option['imdbseriemovies'] ];
	}

	/**
	 * Get Admin options row name as in wp_options
	 *
	 * @return string
	 */
	public static function get_admin_tablename(): string {
		return parent::LUM_ADMIN_OPTIONS;
	}

	/**
	 * Get Cache options row name as in wp_options
	 *
	 * @return string
	 */
	public static function get_cache_tablename(): string {
		return parent::LUM_CACHE_OPTIONS;
	}

	/**
	 * Build the URLs for popups
	 *
	 * @since 4.4 method added
	 *
	 * @param string $type_url
	 * @phpstan-param string $type_url Type of URL we want to get
	 * @param string $domain_url OPTIONAL: Full URL of the domain, usually passed with site_url()
	 * @return string
	 */
	public static function get_popup_url( string $type_url, string $domain_url = '' ): string {
		$imdb_admin_option = get_option( self::get_admin_tablename() );
		return $domain_url . $imdb_admin_option['imdburlpopups'] . parent::LUM_URL_BIT_POPUPS[ $type_url ] . '/';
	}
}

