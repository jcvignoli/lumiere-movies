<?php declare( strict_types = 1 );
/**
 * Class for displaying movies data.
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Post;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Frontend\Post\Front_Parser;

/**
 * Those methods are utilised by class Movie to display the sections
 * The class uses \Lumiere\Link_Maker\Link_Factory to automatically select the appropriate Link maker class to display data ( i.e. Classic links, Highslide/Bootstrap, No Links, AMP)
 * It uses ImdbPHP Classes to display movies/people data
 * It uses Layout defined in Output
 * It uses taxonomy functions in Add_Taxonomy
 * It extends Front_Parser
 *
 * @since 4.0 new class, methods were extracted from Front_Parser class
 * @since 4.5 using now modules through a factory design
 */
class Movie_Factory extends Front_Parser {

	/**
	 * Build the methods to be called in class Movie_Factory
	 * Use imdbphp class to get the Title class
	 *
	 * @param string $mid_premier_resultat IMDb ID, not as int since it loses its heading 0s
	 */
	public function factory_movie_items_methods( string $mid_premier_resultat ): string {

		$outputfinal = '';

		// Find the Title based on $mid_premier_resultat.
		$movie_object = $this->plugins_classes_active['imdbphp']->get_title_class(
			esc_html( $mid_premier_resultat ),
			$this->logger->log,
		);

		foreach ( $this->imdb_data_values['imdbwidgetorder'] as $data_detail => $order ) {

			// Key for $this->imdb_data_values
			$key_data_values = 'imdbwidget' . $data_detail;

			if (
				// Use order to select the position of the data detail.
				isset( $this->imdb_data_values['imdbwidgetorder'][ $data_detail ] )
				&& $this->imdb_data_values['imdbwidgetorder'][ $data_detail ] === $order
				// Is the data detail activated?
				&& isset( $this->imdb_data_values[ $key_data_values ] )
				&& $this->imdb_data_values[ $key_data_values ] === '1'
			) {

				// Get module.
				$text = $this->get_module_movie( $movie_object, $data_detail );
				if ( strlen( $text ) === 0 ) {
					continue;
				}
				// If the module exists and returned text, display and wrap it.
				$outputfinal .= $this->output_class->front_item_wrapper(
					$text,
					$data_detail,
					$this->imdb_admin_values
				);
			}
		}
		return $outputfinal;
	}

	/**
	 * Get movies modules in module folder
	 *
	 * @param Title $movie_object IMDbPHP title class
	 * @param string $item_name The name of the item
	 */
	private function get_module_movie( Title $movie_object, string $item_name ): string {

		/** @psalm-suppress RedundantFunctionCallGivenDocblockType */
		$class_name = Get_Options_Movie::LUM_FILM_MODULE_CLASS . ucfirst( strtolower( $item_name ) ); // strtolower to avoid camelCase names.

		// Return if class doesn't exist
		if ( class_exists( $class_name ) === false ) { // Class Movie_Year is therefore skipped.
			return '';
		}

		$module = new $class_name();

		// Taxonomy is active.
		if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' && isset( $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] ) && $this->imdb_data_values[ 'imdbtaxonomy' . $item_name ] === '1' ) {
			/** @phpstan-ignore method.notFound */
			return $module->get_module_taxo( $movie_object, $item_name );
		}

		/** @phpstan-ignore method.notFound */
		return $module->get_module( $movie_object, $item_name );
	}

}
