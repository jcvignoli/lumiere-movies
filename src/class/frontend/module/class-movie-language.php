<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Language.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Module;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;
use Lumiere\Frontend\Main;
use Lumiere\Frontend\Layout\Output;
use Lumiere\Frontend\Movie\Movie_Taxonomy;
use Lumiere\Config\Get_Options;

/**
 * Method to display language for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Language {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output $output_class = new Output(),
		protected Movie_Taxonomy $movie_taxo = new Movie_Taxonomy()
	) {
		// Construct Frontend Main trait with options and links.
		$this->start_main_trait();
	}

	/**
	 * Display the title and possibly the year
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$languages = $movie->$item_name();
		$nbtotallanguages = count( $languages );

		if ( $nbtotallanguages === 0 ) {
			return '';
		}

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotallanguages )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nbtotallanguages; $i++ ) {

			$output .= esc_html( $languages[ $i ] );

			if ( $i < $nbtotallanguages - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the title and possibly the year
	 * @see Movie_Display::factory_items_methods() that builds this method
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param string $item_name The name of the item, ie 'director', 'writer'
	 */
	public function get_module_taxo( Title $movie, string $item_name ): string {

		$languages = $movie->$item_name();
		$nbtotallanguages = count( $languages );

		if ( $nbtotallanguages === 0 ) {
			return '';
		}

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotallanguages )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nbtotallanguages; $i++ ) {

			$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, esc_html( $languages[ $i ] ), $this->imdb_admin_values );
			$output .= $this->output_class->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

			if ( $i < $nbtotallanguages - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}
}
