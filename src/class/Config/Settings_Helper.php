<?php declare( strict_types = 1 );
/**
 * Settings build
 * Helper class for Settings class
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
namespace Lumiere\Config;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { // Don't check for Settings class since it's Settings class.
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Config\Get_Options_Person;
use Lumiere\Tools\Data;

/**
 * Settings Helper class
 * Includes functions needed by Settings to build its values
 *
 * @since 4.4 Created
 *
 * @phpstan-type ARRAY_WITHNUMBERS array{imdbwidgetactornumber?: string, imdbwidgetalsoknownumber?: string, imdbwidgetconnectionnumber?: string, imdbwidgetgoofnumber?: string, imdbwidgetplotnumber?: string, imdbwidgetproducernumber?: string, imdbwidgetquotenumber?: string, imdbwidgetsoundtracknumber?: string, imdbwidgettaglinenumber?: string, imdbwidgettrailernumber?: string, imdbwidgettrivianumber?: string, imdbwidgetwriternumber?: string}
 *
 * @phpstan-import-type OPTIONS_DATA_ORDER from \Lumiere\Config\Settings_Movie
 * @phpstan-import-type OPTIONS_DATA_TAXO from \Lumiere\Config\Settings_Movie
 * @phpstan-import-type OPTIONS_DATA_WIDGET from \Lumiere\Config\Settings_Movie
 * @phpstan-import-type OPTIONS_DATA_PERSON_ORDER from \Lumiere\Config\Settings_Person
 * @phpstan-import-type OPTIONS_DATA_PERSON_ACTIVATED from \Lumiere\Config\Settings_Person
 * @phpstan-import-type OPTIONS_DATA_PERSON_NUMBER from \Lumiere\Config\Settings_Person
 */
class Settings_Helper {

	/**
	 * Define the number of updates on first install
	 * Find the number of the last file number in updates folder, ie Lumiere_Update_File_24.php => 24
	 * Adding +1 so only the next update is executed
	 *
	 * @return string The last number in file number found plus one
	 */
	public static function get_nb_updates(): string {
		$update_files = glob( LUM_WP_PATH . Get_Options::LUM_UPDATES_PATH . '/Lumiere_Update_File_*.php' );
		// Extract the Lumiere_Update_File_"XX" number
		$last_nb = is_array( $update_files ) && count( $update_files ) > 0 ? preg_replace( '/[^0-9]/', '', $update_files ) : [];
		// Convert the number to three digits if it is lower than 100 => compatibility
		/** @psalm-suppress PossiblyNullArgument (PHPStan says otherwise) */
		$three_digits = array_map(
			function( string $last_nb ) {
				return sprintf( '%03s', $last_nb );
			},
			$last_nb
		);
		// Get the highest number, remove heading 0
		$highest_number = count( $three_digits ) > 0 ? (int) ltrim( max( $three_digits ), '0' ) : 0;
		return strval( $highest_number + 1 );
	}

	/**
	 * Create rows for 'imdbtaxonomy' using internal methods in data_movie
	 *
	 * @see Settings::get_default_data_movie_option() Meant to be used there
	 *
	 * @param list<string>|null $activated List of taxonomy to activate by default
	 * @return array<string, string>
	 * @phpstan-return OPTIONS_DATA_WIDGET
	 */
	protected function get_data_rows_taxo( ?array $activated ): array {
		$taxonomy_keys = [
			...array_keys( Get_Options_Movie::get_list_people_taxo() ),
			...array_keys( Get_Options_Movie::get_list_items_taxo() ),
		];
		$array_taxonomy = [];
		foreach ( $taxonomy_keys as $row_number => $taxonomy_key ) {
			if ( isset( $activated ) && in_array( $taxonomy_key, $activated, true ) ) {
				$array_taxonomy[ 'imdbtaxonomy' . $taxonomy_key ] = '1';
				continue;
			}
			$array_taxonomy[ 'imdbtaxonomy' . $taxonomy_key ] = '0';
		}
		/**
		 * @psalm-var OPTIONS_DATA_WIDGET $array_taxonomy Dunno why psalm needs this
		 * @phpstan-ignore varTag.nativeType (is not subtype of native type array<non-falsy-string, '0'|'1'>)
		 */
		return $array_taxonomy;
	}

	/**
	 * Create rows for 'imdbwidget' using internal methods in data_movie
	 *
	 * @see Settings::get_default_data_movie_option() Meant to be used there
	 *
	 * @param list<string>|null $activated List of taxonomy to activate by default
	 * @return array<string, string>
	 * @phpstan-return OPTIONS_DATA_TAXO
	 */
	protected function get_data_rows_widget( ?array $activated ): array {
		$widget_keys = [
			...array_keys( Get_Options_Movie::get_list_non_taxo_items() ),
			...array_keys( Get_Options_Movie::get_list_items_taxo() ),
			...array_keys( Get_Options_Movie::get_list_people_taxo() ),
		];
		$array_widget = [];
		foreach ( $widget_keys as $row_number => $widget_key ) {
			if ( isset( $activated ) && in_array( $widget_key, $activated, true ) ) {
				$array_widget[ 'imdbwidget' . $widget_key ] = '1';
				continue;
			}
			$array_widget[ 'imdbwidget' . $widget_key ] = '0';
		}
		/** @psalm-var OPTIONS_DATA_TAXO $array_widget Dunno why psalm needs this */
		return $array_widget;
	}

	/**
	 * Create rows for 'imdbwidgetorder' array in data_movie
	 * Get all elements items/people, then reorder them since it will be the order by default when installing the plugin
	 *
	 * @see Settings::get_default_data_option() Meant to be used there
	 *
	 * @return array{imdbwidgetorder:array<string,string>}
	 * @phpstan-return OPTIONS_DATA_ORDER
	 */
	protected function get_data_rows_imdbwidgetorder(): array {
		$widget_keys = [
			...array_keys( Get_Options_Movie::get_list_non_taxo_items() ),
			...array_keys( Get_Options_Movie::get_list_people_taxo() ),
			...array_keys( Get_Options_Movie::get_list_items_taxo() ),
		];

		$array_imdbwidgetorder = [];
		$i = 0;

		// Build an associative array added to 'imdbwidgetorder' column.
		foreach ( $widget_keys as $row_number => $imdbwidgetorder_key ) {
			$array_imdbwidgetorder['imdbwidgetorder'][ $imdbwidgetorder_key ] = strval( $i );
			$i++;
		}

		// Reorder by swapping two columns.
		if ( isset( $array_imdbwidgetorder['imdbwidgetorder'] ) ) {
			$array_imdbwidgetorder['imdbwidgetorder'] = Data::array_multiassoc_swap_values( $array_imdbwidgetorder['imdbwidgetorder'], 'runtime', 'director' );
			$array_imdbwidgetorder['imdbwidgetorder'] = Data::array_multiassoc_swap_values( $array_imdbwidgetorder['imdbwidgetorder'], 'alsoknow', 'tagline' );
			$array_imdbwidgetorder['imdbwidgetorder'] = Data::array_multiassoc_swap_values( $array_imdbwidgetorder['imdbwidgetorder'], 'rating', 'actor' );
			$array_imdbwidgetorder['imdbwidgetorder'] = Data::array_multiassoc_swap_values( $array_imdbwidgetorder['imdbwidgetorder'], 'connection', 'genre' );
			$array_imdbwidgetorder['imdbwidgetorder'] = Data::array_multiassoc_swap_values( $array_imdbwidgetorder['imdbwidgetorder'], 'prodCompany', 'alsoknow' );
			$array_imdbwidgetorder['imdbwidgetorder'] = Data::array_multiassoc_swap_values( $array_imdbwidgetorder['imdbwidgetorder'], 'goof', 'rating' );
			$array_imdbwidgetorder['imdbwidgetorder'] = Data::array_multiassoc_swap_values( $array_imdbwidgetorder['imdbwidgetorder'], 'plot', 'writer' );
			$array_imdbwidgetorder['imdbwidgetorder'] = Data::array_multiassoc_swap_values( $array_imdbwidgetorder['imdbwidgetorder'], 'extSites', 'keyword' );
			$array_imdbwidgetorder['imdbwidgetorder'] = Data::array_multiassoc_swap_values( $array_imdbwidgetorder['imdbwidgetorder'], 'source', 'country' );
			$array_imdbwidgetorder['imdbwidgetorder'] = Data::array_multiassoc_swap_values( $array_imdbwidgetorder['imdbwidgetorder'], 'color', 'source' );
		}
		/** @psalm-var OPTIONS_DATA_ORDER $array_imdbwidgetorder Dunno why psalm needs this */
		return $array_imdbwidgetorder;
	}

	/**
	 * Create rows for 'imdbtaxonomy' in data_movie
	 *
	 * @see Settings::get_default_data_movie_option() Meant to be used there
	 *
	 * @param array<string, string>|null $activated List of taxonomy to activate by default
	 * @return array<string, string>
	 * @phpstan-return ARRAY_WITHNUMBERS
	 */
	protected function get_data_rows_withnumbers( ?array $activated ): array {
		$array_with_numbers = [];
		$count = isset( $activated ) ? count( $activated ) - 1 : 0; // Remove 1 to total count since arrays start at 0.
		$reversed = isset( $activated ) ? array_reverse( $activated, true ) : [];
		$reversed_array = [];
		foreach ( $reversed as $k => $v ) {
			$reversed_array[] = [ $k => $v ];
		}
		$loop = array_keys( Get_Options_Movie::LUM_DATA_DEFAULT_WITHNUMBER );
		foreach ( $loop as $key => $withnumber_key ) {
			if ( in_array( $withnumber_key, array_keys( $reversed ), true ) && $count > -1 ) {
				$array_with_numbers[ 'imdbwidget' . $withnumber_key . 'number' ] = $reversed_array[ $count ][ $withnumber_key ];
				$count--;
				continue;
			}
			$array_with_numbers[ 'imdbwidget' . $withnumber_key . 'number' ] = '0';
		}
		return $array_with_numbers;
	}

	/**
	 * Create rows for 'order' array in data_person
	 *
	 * @see Settings::get_default_data_person_option() Meant to be used there
	 *
	 * @return array<'order', array<string, string>>
	 * @phpstan-return OPTIONS_DATA_PERSON_ORDER
	 */
	protected function get_data_person_order(): array {
		$data = [];
		$values = array_keys( Get_Options_Person::get_all_person_fields() );
		$i = 1;
		foreach ( $values as $value ) {
			$data['order'][ $value ] = (string) $i;
			$i++;
		}
		/**
		 * @psalm-var OPTIONS_DATA_PERSON_ORDER $data (says return is less specific otherwise)
		 * @phpstan-ignore varTag.nativeType (PHPDoc tag @var with type array{order: array{title: numeric-string...} is not subtype of native type array{order:non-empty-array<lowercase-string&non-falsy-string&numeric-string&uppercase-string>})
		 */
		return $data;
	}

	/**
	 * Create rows for 'activated' array in data_person
	 *
	 * @see Settings::get_default_data_person_option() Meant to be used there
	 *
	 * @return array<'activated', array<string, string>>
	 * @phpstan-return OPTIONS_DATA_PERSON_ACTIVATED
	 */
	protected function get_data_person_activated(): array {
		$data = [];
		$values = array_keys( Get_Options_Person::get_all_person_fields() );
		foreach ( $values as $value ) {
			if ( in_array( $value, Get_Options_Person::LUM_DATA_PERSON_DEFAULT_ACTIVE, true ) ) { // Always activated.
				$data['activated'][ $value . '_active' ] = '1';
				continue;
			}
			$data['activated'][ $value . '_active' ] = '0';
		}
		/** @psalm-var OPTIONS_DATA_PERSON_ACTIVATED $data */
		return $data;
	}

	/**
	 * Create rows for 'number' array in data_person
	 *
	 * @see Settings::get_default_data_person_option() Meant to be used there
	 *
	 * @return array<'number', array<string, string>>
	 * @phpstan-return OPTIONS_DATA_PERSON_NUMBER
	 */
	protected function get_data_person_number(): array {
		$data = [];
		foreach ( Get_Options_Person::LUM_DATA_PERSON_DEFAULT_WITHNUMBER as $key => $number ) {
			$data['number'][ $key . '_number' ] = $number;
		}
		/**
		 * @psalm-var OPTIONS_DATA_PERSON_NUMBER $data
		 * @phpstan-ignore varTag.nativeType
		 */
		return $data;
	}
}

