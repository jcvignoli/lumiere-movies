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

use Lumiere\Config\Get_Options;
use Lumiere\Config\Settings_Movie;

/**
 * Getting Settings and database options
 * Helper class meant to be called anywhere, so all methods should be static and public
 */
class Get_Options_Movie extends Settings_Movie {

	/**
	 * Get Data options row name as in wp_options
	 *
	 * @return string
	 */
	public static function get_data_tablename(): string {
		return parent::LUM_DATA_OPTIONS;
	}

	/**
	 * Get the type items elements that are used for taxonomy
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 */
	public static function get_list_items_taxo( int $number = 1 ): array {
		return parent::define_list_taxo_items( $number );
	}

	/**
	 * Get the type items elements that are used for taxonomy
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 */
	public static function get_list_all_items( int $number = 1 ): array {
		return [
			...parent::define_list_non_taxo_items( $number ),
			...parent::define_list_taxo_items( $number ),
		];
	}

	/**
	 * Get All taxonomy types: all people and items elements that are used for taxonomy
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 */
	public static function get_list_fields_taxo( int $number = 1 ): array {
		return [
			...parent::define_list_taxo_people( $number ),
			...parent::define_list_taxo_items( $number ),
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
	public static function get_list_goof_cat(): array {
		return parent::define_list_goof_cat();
	}

	/**
	 * Get all categories of trivias
	 *
	 * @since 4.5 method added
	 * @return array<string, string>
	 */
	public static function get_list_trivia_cat(): array {
		return parent::define_list_trivia_cat();
	}

	/**
	 * Get all items non-taxonomy
	 *
	 * @since 4.5.1 method added
	 * @return array<string, string>
	 */
	public static function get_list_non_taxo_items( int $number = 1 ): array {
		return parent::define_list_non_taxo_items( $number );
	}

	/**
	 * Get all elements (people and items) that can take numbers as options
	 * Find all Settings_Movie::LUM_DATA_DEFAULT_WITHNUMBER available in define_list_taxo_people(), define_list_taxo_items() or define_list_non_taxo_items()
	 *
	 * @see Settings::get_default_data_option()

	 * @return array<string, string>
	 * @phpstan-return array{actor?: string, alsoknow?: string, connection?: string, goof?: string, plot?: string, producer?: string, quote?: string, soundtrack?: string, tagline?: string, trailer?: string, trivia?: string, writer?: string}
	 */
	public static function get_items_with_numbers( int $number = 1 ): array {
		$list_all = Get_Options::get_all_fields( $number );
		$list_elements_with_numbers = [];
		$list_keys_with_numbers = array_keys( parent::LUM_DATA_DEFAULT_WITHNUMBER );
		foreach ( $list_all as $element => $translation ) {
			if ( in_array( $element, $list_keys_with_numbers, true ) ) {
				$list_elements_with_numbers[ $element ] = $translation;
			}
		}
		return $list_elements_with_numbers;
	}

	/**
	 * Get the type of people elements that are used for taxonomy
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 */
	public static function get_list_people_taxo( int $number = 1 ): array {
		return parent::define_list_taxo_people( $number );
	}
}

