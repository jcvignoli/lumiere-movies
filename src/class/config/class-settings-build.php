<?php declare( strict_types = 1 );
/**
 * Settings build
 * Helper class for Settings class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */
namespace Lumiere\Config;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { // Don't check for Settings class since it's Settings class.
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use FilesystemIterator;
use Lumiere\Config\Get_Options;
use Lumiere\Config\Settings;

/**
 * Settings Build class
 * Helper class for Settings class
 * @since 4.4 Created
 */
class Settings_Build {

	/**
	 * Define the number of updates on first install
	 * Find the number of files in updates folder
	 *
	 * @return string The number of files found
	 */
	protected function get_nb_updates(): string {
		$files = new FilesystemIterator( LUMIERE_WP_PATH . Settings::UPDATES_PATH, \FilesystemIterator::SKIP_DOTS );
		return strval( iterator_count( $files ) + 1 );
	}

	/**
	 * Define all the pages of Lumiere
	 *
	 * @see \Lumiere\Admin\Admin:lumiere_execute_admin_assets()
	 *
	 * @return array<string>
	 */
	public static function get_all_lumiere_pages(): array {
		$imdb_admin_option = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		return [
			$imdb_admin_option !== false ? $imdb_admin_option['imdburlstringtaxo'] : Settings::URL_STRING_TAXO, // dunno if Settings is really needed
			Get_Options::get_popup_url( 'movies' ),
			Get_Options::get_popup_url( 'people' ),
			Get_Options::get_popup_url( 'movies_search' ),
			Settings::FILE_COPY_THEME_TAXONOMY,
			Settings::GUTENBERG_SEARCH_FILE, // For access to search in clicking a link (ie gutenberg)
			Settings::SEARCH_URL_ADMIN, // For access to search in URL lumiere/search
			Settings::POPUP_SEARCH_PATH,
			Settings::POPUP_MOVIE_PATH,
			Settings::POPUP_PERSON_PATH,
		];
	}

	/**
	 * Define all types of items
	 * This lists merge taxonomy items with those that are not meant for taxo
	 *
	 * @return array<string, string>
	 */
	protected static function define_list_all_items(): array {
		return [
			...Settings::define_list_taxo_people(), // Taxo_people is all people options, since there are no people options that are not taxonomy.
			...Settings::define_list_taxo_items(),
			...Settings::define_list_non_taxo_items(),
		];
	}

	/**
	 * Create rows for 'imdbtaxonomy' using internal methods
	 *
	 * @see Settings::get_default_data_option() Meant to be used there
	 *
	 * @param list<string>|null $activated List of taxonomy to activate by default
	 * @return array<string, string>
	 * @phpstan-return array{imdbtaxonomyactor?: '0'|'1', imdbtaxonomycolor?: '0'|'1', imdbtaxonomycomposer?: '0'|'1', imdbtaxonomycountry?: '0'|'1', imdbtaxonomycreator?: '0'|'1', imdbtaxonomydirector?: '0'|'1', imdbtaxonomygenre?: '0'|'1', imdbtaxonomykeyword?: '0'|'1', imdbtaxonomylanguage?: '0'|'1', imdbtaxonomyproducer?: '0'|'1', imdbtaxonomywriter?: '0'|'1'}
	 */
	protected function get_data_rows_taxo( ?array $activated ): array {
		$taxonomy_keys = [ ...array_keys( Settings::define_list_taxo_people() ), ...array_keys( Settings::define_list_taxo_items() ) ];
		$array_taxonomy = [];
		foreach ( $taxonomy_keys as $row_number => $taxonomy_key ) {
			if ( isset( $activated ) && in_array( $taxonomy_key, $activated, true ) ) {
				$array_taxonomy[ 'imdbtaxonomy' . $taxonomy_key ] = '1';
				continue;
			}
			$array_taxonomy[ 'imdbtaxonomy' . $taxonomy_key ] = '0';
		}
		return $array_taxonomy;
	}

	/**
	 * Create rows for 'imdbwidget' using internal methods
	 *
	 * @see Settings::get_default_data_option() Meant to be used there
	 *
	 * @param list<string>|null $activated List of taxonomy to activate by default
	 * @return array<string, string>
	 * @phpstan-return array{imdbwidgettitle?: '0'|'1', imdbwidgetpic?: '0'|'1', imdbwidgetruntime?: '0'|'1', imdbwidgetdirector?: '0'|'1', imdbwidgetconnection?: '0'|'1', imdbwidgetcountry?: '0'|'1', imdbwidgetactor?: '0'|'1', imdbwidgetcreator?: '0'|'1', imdbwidgetrating?: '0'|'1', imdbwidgetlanguage?: '0'|'1', imdbwidgetgenre?: '0'|'1', imdbwidgetwriter?: '0'|'1', imdbwidgetproducer?: '0'|'1', imdbwidgetkeyword?: '0'|'1', imdbwidgetprodcompany?: '0'|'1', imdbwidgetplot?: '0'|'1', imdbwidgetgoof?: '0'|'1', imdbwidgetcomment?: '0'|'1', imdbwidgetquote?: '0'|'1', imdbwidgettagline?: '0'|'1', imdbwidgettrailer?: '0'|'1', imdbwidgetcolor?: '0'|'1', imdbwidgetalsoknow?: '0'|'1', imdbwidgetcomposer?: '0'|'1', imdbwidgetsoundtrack?: '0'|'1', imdbwidgetofficialsites?: '0'|'1', imdbwidgetsource?: '0'|'1', imdbwidgetyear?: '0'|'1'}
	 */
	protected function get_data_rows_widget( ?array $activated ): array {
		$widget_keys = [
			...array_keys( Settings::define_list_non_taxo_items() ),
			...array_keys( Settings::define_list_taxo_items() ),
			...array_keys( Settings::define_list_taxo_people() ),
		];
		$array_widget = [];
		foreach ( $widget_keys as $row_number => $widget_key ) {
			if ( isset( $activated ) && in_array( $widget_key, $activated, true ) ) {
				$array_widget[ 'imdbwidget' . $widget_key ] = '1';
				continue;
			}
			$array_widget[ 'imdbwidget' . $widget_key ] = '0';
		}
		return $array_widget;
	}

	/**
	 * Create rows for 'imdbwidgetorder' using internal methods
	 *
	 * @see Settings::get_default_data_option() Meant to be used there
	 *
	 * @return array<string, string>
	 * @phpstan-return array{ imdbwidgetorder: array{title?: string, pic?: string, runtime?: string, director?: string, connection?: string, country?: string, actor?: string, creator?: string, rating?: string, language?: string, genre?: string, writer?: string, producer?: string, keyword?: string, prodcompany?: string, plot?: string, goof?: string, comment?: string, quote?: string, tagline?: string, trailer?: string, color?: string, alsoknow?: string, composer?: string, soundtrack?: string, officialsites?: string, source?: string, year?: string} }
	 */
	protected function get_data_rows_imdbwidgetorder(): array {
		$widget_keys = [
			...array_keys( Settings::define_list_non_taxo_items() ),
			...array_keys( Settings::define_list_taxo_items() ),
			...array_keys( Settings::define_list_taxo_people() ),
		];
		$array_imdbwidgetorder = [];
		$i = 0;
		foreach ( $widget_keys as $row_number => $imdbwidgetorder_key ) {
			$array_imdbwidgetorder['imdbwidgetorder'][ $imdbwidgetorder_key ] = strval( $i );
			$i++;
		}
		return $array_imdbwidgetorder;
	}

	/**
	 * Create rows for 'imdbtaxonomy' using internal methods
	 * @see Settings::get_default_data_option() Meant to be used there
	 *
	 * @param array<string, string>|null $activated List of taxonomy to activate by default
	 * @return non-empty-array<string, string>
	 * @phpstan-return array{imdbwidgetactornumber?: string, imdbwidgetalsoknownumber?: string, imdbwidgetconnectionnumber?: string, imdbwidgetgoofnumber?: string, imdbwidgetplotnumber?: string, imdbwidgetproducernumber?: string, imdbwidgetquotenumber?: string, imdbwidgetsoundtracknumber?: string, imdbwidgettaglinenumber?: string, imdbwidgettrailernumber?: string}
	 */
	protected function get_data_rows_withnumbers( ?array $activated ): array {
		$array_with_numbers = [];
		$count = isset( $activated ) ? count( $activated ) - 1 : 0; // Remove 1 to total count since arrays start at 0.
		$loop = array_keys( Settings::define_list_items_with_numbers() );
		$reversed = isset( $activated ) ? array_reverse( $activated, true ) : [];
		$reversed_array = [];
		foreach ( $reversed as $k => $v ) {
			$reversed_array[] = [ $k => $v ];
		}
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
}

