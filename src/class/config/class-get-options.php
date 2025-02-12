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

use Lumiere\Tools\Data;
use Lumiere\Config\Settings;

/**
 * Getting Settings and database options
 * Helper class meant to be called anywhere, so all methods should be static
 */
class Get_Options extends Settings {

	/**
	 * Define all the pages of Lumiere
	 *
	 * @see \Lumiere\Admin\Admin:lumiere_execute_admin_assets()
	 *
	 * @return array<string>
	 */
	public static function get_admin_lum_pages(): array {
		$imdb_admin_option = get_option( parent::LUM_ADMIN_OPTIONS );
		return [
			$imdb_admin_option !== false ? $imdb_admin_option['imdburlstringtaxo'] : parent::URL_STRING_TAXO,
			parent::FILE_COPY_THEME_TAXONOMY,
			parent::GUTENBERG_SEARCH_FILE,          // For accessing the search in clicking a link (ie gutenberg)
			parent::SEARCH_URL_ADMIN,               // For accessing the search in URL lumiere/search
			parent::POPUP_SEARCH_PATH,
			parent::POPUP_MOVIE_PATH,
			parent::POPUP_PERSON_PATH,
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
		$imdb_data_values = get_option( self::get_data_tablename() );
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
	 * Get Data options row name as in wp_options
	 *
	 * @return string
	 */
	public static function get_data_tablename(): string {
		return parent::LUM_DATA_OPTIONS;
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
	 * Get the type of people elements that are used for taxonomy
	 *
	 * @return array<string, string>
	 */
	public static function get_list_people_taxo(): array {
		return parent::define_list_taxo_people();
	}

	/**
	 * Get the type items elements that are used for taxonomy
	 *
	 * @return array<string, string>
	 */
	public static function get_list_items_taxo(): array {
		return parent::define_list_taxo_items();
	}

	/**
	 * Get All taxonomy types: all people and items elements that are used for taxonomy
	 *
	 * @return array<string, string>
	 */
	public static function get_list_all_elements_taxo(): array {
		return [
			...parent::define_list_taxo_people(),
			...parent::define_list_taxo_items(),
		];
	}

	/**
	 * Get all categories of connected movies
	 *
	 * @since 4.4 method added
	 * @return array<string, string>
	 */
	public static function get_list_connect_cat(): array {
		return parent::define_list_connect_cat();
	}

	/**
	 * Get all categories of goofs
	 *
	 * @since 4.4 method added
	 * @return array<string, string>
	 */
	public static function get_list_goofs_cat(): array {
		return parent::define_list_goofs_cat();
	}

	/**
	 * Get all type items (taxo+non taxo)
	 *
	 * @since 4.4 method added
	 * @return array<string, string>
	 */
	public static function get_all_items(): array {
		return [
			...parent::define_list_non_taxo_items(),
			...parent::define_list_taxo_people(), // Taxo_people is all people options, since there are no people options that are not taxonomy.
			...parent::define_list_taxo_items(),
		];
	}

	/**
	 * Get all type items that are used for taxonomy
	 *
	 * @return array<string, string>
	 */
	public static function get_items_with_numbers(): array {
		return parent::define_list_items_with_numbers();
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
		return $domain_url . $imdb_admin_option['imdburlpopups'] . parent::URL_BIT_POPUPS[ $type_url ] . '/';
	}
}

