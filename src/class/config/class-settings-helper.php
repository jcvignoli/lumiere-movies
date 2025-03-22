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

use FilesystemIterator;
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
 * @phpstan-type ARRAY_IMDBWIDGETORDER array{ 'imdbwidgetorder': array{title?: string, pic?: string, runtime?: string, director?: string, connection?: string, country?: string, actor?: string, cinematographer?: string, rating?: string, language?: string, genre?: string, writer?: string, producer?: string, keyword?: string, prodCompany?: string, plot?: string, goof?: string, quote?: string, tagline?: string, trailer?: string, color?: string, alsoknow?: string, composer?: string, soundtrack?: string, extSites?: string, source?: string, trivia?: string, year?: string} }
 * @phpstan-type ARRAY_WITHNUMBERS array{imdbwidgetactornumber?: string, imdbwidgetalsoknownumber?: string, imdbwidgetconnectionnumber?: string, imdbwidgetgoofnumber?: string, imdbwidgetplotnumber?: string, imdbwidgetproducernumber?: string, imdbwidgetquotenumber?: string, imdbwidgetsoundtracknumber?: string, imdbwidgettaglinenumber?: string, imdbwidgettrailernumber?: string, imdbwidgettrivianumber?: string, imdbwidgetwriternumber?: string}
 * @phpstan-type ARRAY_TAXO_ITEMS array{imdbtaxonomyactor?: '0'|'1', imdbtaxonomycolor?: '0'|'1', imdbtaxonomycomposer?: '0'|'1', imdbtaxonomycountry?: '0'|'1', imdbtaxonomycinematographer?: '0'|'1', imdbtaxonomydirector?: '0'|'1', imdbtaxonomygenre?: '0'|'1', imdbtaxonomykeyword?: '0'|'1', imdbtaxonomylanguage?: '0'|'1', imdbtaxonomyproducer?: '0'|'1', imdbtaxonomywriter?: '0'|'1'}
 * @phpstan-type ARRAY_WIDGET array{imdbwidgettitle?: '0'|'1', imdbwidgetpic?: '0'|'1', imdbwidgetruntime?: '0'|'1', imdbwidgetdirector?: '0'|'1', imdbwidgetconnection?: '0'|'1', imdbwidgetcountry?: '0'|'1', imdbwidgetactor?: '0'|'1', imdbwidgetcinematographer?: '0'|'1', imdbwidgetrating?: '0'|'1', imdbwidgetlanguage?: '0'|'1', imdbwidgetgenre?: '0'|'1', imdbwidgetwriter?: '0'|'1', imdbwidgetproducer?: '0'|'1', imdbwidgetkeyword?: '0'|'1', imdbwidgetprodCompany?: '0'|'1', imdbwidgetplot?: '0'|'1', imdbwidgetgoof?: '0'|'1', imdbwidgetquote?: '0'|'1', imdbwidgettagline?: '0'|'1', imdbwidgettrailer?: '0'|'1', imdbwidgetcolor?: '0'|'1', imdbwidgetalsoknow?: '0'|'1', imdbwidgetcomposer?: '0'|'1', imdbwidgetsoundtrack?: '0'|'1', imdbwidgetextSites?: '0'|'1', imdbwidgetsource?: '0'|'1', imdbwidgettrivia?: '0'|'1', imdbwidgetyear?: '0'|'1'}
 * @phpstan-import-type OPTIONS_DATA_PERSON_ORDER from \Lumiere\Config\Settings_Person
 * @phpstan-import-type OPTIONS_DATA_PERSON_ACTIVATED from \Lumiere\Config\Settings_Person
 * @phpstan-import-type OPTIONS_DATA_PERSON_NUMBER from \Lumiere\Config\Settings_Person
 */
class Settings_Helper {

	/**
	 * Define the number of updates on first install
	 * Find the number of files in updates folder
	 *
	 * @return string The number of files found
	 */
	protected function get_nb_updates(): string {
		$files = new FilesystemIterator( LUM_WP_PATH . Get_Options::LUM_UPDATES_PATH, \FilesystemIterator::SKIP_DOTS );
		return strval( iterator_count( $files ) + 1 );
	}

	/**
	 * Create rows for 'imdbtaxonomy' using internal methods in data_movie
	 *
	 * @see Settings::get_default_data_movie_option() Meant to be used there
	 *
	 * @param list<string>|null $activated List of taxonomy to activate by default
	 * @return array<string, string>
	 * @phpstan-return ARRAY_TAXO_ITEMS
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
		/** @psalm-var ARRAY_TAXO_ITEMS $array_taxonomy Dunno why psalm needs this */
		return $array_taxonomy;
	}

	/**
	 * Create rows for 'imdbwidget' using internal methods in data_movie
	 *
	 * @see Settings::get_default_data_movie_option() Meant to be used there
	 *
	 * @param list<string>|null $activated List of taxonomy to activate by default
	 * @return array<string, string>
	 * @phpstan-return ARRAY_WIDGET
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
		/** @psalm-var ARRAY_WIDGET $array_widget Dunno why psalm needs this */
		return $array_widget;
	}

	/**
	 * Create rows for 'imdbwidgetorder' array in data_movie
	 * Get all elements items/people, then reorder them since it will be the order by default when installing the plugin
	 *
	 * @see Settings::get_default_data_option() Meant to be used there
	 *
	 * @return array{imdbwidgetorder:array<string,string>}
	 * @phpstan-return ARRAY_IMDBWIDGETORDER
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
		/** @psalm-var ARRAY_IMDBWIDGETORDER $array_imdbwidgetorder Dunno why psalm needs this */
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
	 * @return array<array-key, array<string, string>>
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
		if ( ! isset( $data['order'] ) || count( $data['order'] ) < 2 ) {
			throw new \Exception( 'Could not create data person order' );
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
			if ( $value === 'title' || $value === 'pic' ) { // Always activated.
				$data['activated'][ $value . '_active' ] = '1';
				continue;
			}
			$data['activated'][ $value . '_active' ] = '0';
		}
		if ( ! isset( $data['activated'] ) || count( $data['activated'] ) < 2 ) {
			throw new \Exception( 'Could not create data person activated' );
		}
		/** @psalm-var OPTIONS_DATA_PERSON_ACTIVATED $data */
		return $data;
	}

	/**
	 * Create rows for 'number' array in data_person
	 * Do not include data that will always be unactivated in Settings_Person::LUM_DATA_PERSON_UNACTIVE
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

