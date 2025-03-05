<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Title.
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
 * Method to display title for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Country {

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
	 * Display the Country
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'country' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$country = $movie->$item_name();
		$nbtotalcountry = count( $country );

		// if no result, exit.
		if ( $nbtotalcountry === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotalcountry )[ $item_name ] ) )
		);

		// Taxonomy is unactive.
		for ( $i = 0; $i < $nbtotalcountry; $i++ ) {
			$output .= sanitize_text_field( $country[ $i ] );
			if ( $i < $nbtotalcountry - 1 ) {
				$output .= ', ';
			}
		}

		return $output;
	}

	/**
	 * Display the Country for taxonomy
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'country' $item_name The name of the item
	 */
	public function get_module_taxo( Title $movie, string $item_name ): string {

		$country = $movie->$item_name();
		$nbtotalcountry = count( $country );

		// if no result, exit.
		if ( $nbtotalcountry === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nbtotalcountry )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nbtotalcountry; $i++ ) {

			$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, esc_html( $country[ $i ] ), $this->imdb_admin_values );
			$output .= $this->output_class->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );

			if ( $i < $nbtotalcountry - 1 ) {
				$output .= ', ';
			}

		}
		return $output;
	}
}
