<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Genre.
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
 * Method to display genre for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Genre {

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
	 * Display the Genre
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'genre' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$genre = $movie->$item_name();
		$nbtotalgenre = count( $genre ) > 0 ? count( $genre ) : 0;

		if ( $nbtotalgenre === 0 ) {
			return '';
		}

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotalgenre )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nbtotalgenre; $i++ ) {
			$output .= isset( $genre[ $i ]['mainGenre'] ) ? esc_html( $genre[ $i ]['mainGenre'] ) : '';
			if ( $i < $nbtotalgenre - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Genre for taxonomy
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'genre' $item_name The name of the item, ie 'director', 'writer'
	 */
	public function get_module_taxo( Title $movie, string $item_name ): string {

		$genre = $movie->$item_name();
		$nbtotalgenre = count( $genre ) > 0 ? count( $genre ) : 0;

		if ( $nbtotalgenre === 0 ) {
			return '';
		}

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotalgenre )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nbtotalgenre; $i++ ) {

			$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, esc_html( $genre[ $i ]['mainGenre'] ), $this->imdb_admin_values );
			$output .= isset( $genre[ $i ]['mainGenre'] ) ? $this->output_class->get_layout_items( esc_html( $movie->title() ), $get_taxo_options ) : '';

			if ( $i < $nbtotalgenre - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}
}
