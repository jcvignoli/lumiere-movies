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
 * All methods shoud be static
 */
class Get_Options {

	/**
	 * Get an array of the taxonomy in use in the form of taxonomy
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
		switch ( $imdb_admin_option['imdbseriemovies'] ) {

			case 'movies':
				return 'MOVIE';
			case 'movies+series':
				return 'MOVIE,TV';
			case 'series':
				return 'TV';
			case 'videogames':
				return 'VIDEO_GAME';
			case 'podcasts':
				return 'PODCAST_EPISODE';
			default:
				return 'MOVIE,TV';

		}
	}

	/**
	 * Get Admin options row name as in wp_options
	 *
	 * @return string
	 * @since 4.2.1 method added, returning old row name if exists, new name otherwise
	 * @since 4.2.2 method renamed and returns only the new row name
	 */
	public static function get_admin_tablename(): string {
		return Settings::LUMIERE_ADMIN_OPTIONS;
	}

	/**
	 * Get Data options row name as in wp_options
	 *
	 * @return string
	 * @since 4.2.1 method added, returning old row name if exists, new name otherwise
	 * @since 4.2.2 method renamed and returns only the new row name
	 */
	public static function get_data_tablename(): string {
		return Settings::LUMIERE_DATA_OPTIONS;
	}

	/**
	 * Get Cache options row name as in wp_options
	 *
	 * @return string
	 * @since 4.2.1 method added, returning old row name if exists, new name otherwise
	 * @since 4.2.2 method renamed and returns only the new row name
	 */
	public static function get_cache_tablename(): string {
		return Settings::LUMIERE_CACHE_OPTIONS;
	}

	/**
	 * Get all people's data
	 *
	 * @return array<string, string>
	 */
	public static function get_list_people(): array {
		return Settings::build_people();
	}

	/**
	 * Get all item's data
	 *
	 * @return array<string, string>
	 */
	public static function get_list_items(): array {
		return Settings::build_items();
	}

	/**
	 * Build the URLs for popups
	 *
	 * @param 'movies'|'people'|'movies_search' $column Type of URL we want to get
	 * @param string $domain_url OPTIONAL: Full URL of the domain, usually passed with site_url()
	 * @return string
	 */
	public static function get_popup_url( string $column, $domain_url = '' ): string {
		$imdb_admin_option = get_option( self::get_admin_tablename() );
		$url = [
			'movies'        => $domain_url . $imdb_admin_option['imdburlpopups'] . Settings::URL_BIT_POPUPS_MOVIES,
			'people'        => $domain_url . $imdb_admin_option['imdburlpopups'] . Settings::URL_BIT_POPUPS_PEOPLE,
			'movies_search' => $domain_url . $imdb_admin_option['imdburlpopups'] . Settings::URL_BIT_POPUPS_MOVIES_SEARCH,
		];
		return $url[ $column ];
	}
}

