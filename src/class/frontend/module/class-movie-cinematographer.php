<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Cinematographer.
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
 * Method to display Cinematographer for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Cinematographer {

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
	 * Display the main module version
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'cinematographer' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $movie, $item_name, $item_results, $nb_total_items );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= $this->link_maker->lumiere_link_popup_people( $item_results, $i );
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}

		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'cinematographer' $item_name The name of the item
	 * @param array<array-key, array<string, string>> $item_results
	 * @param int<0, max> $nb_total_items
	 */
	public function get_module_popup( Title $movie, string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		if ( $nb_total_items === 0 ) {
			esc_html_e( 'No cinematographers found.', 'lumiere-movies' );
		}

		for ( $i = 0; $i < $nb_total_items; $i++  ) {

			$output .= $this->link_maker->lumiere_movies_officialsites_details(
				$item_results[ $i ]['url'],
				$item_results[ $i ]['label'],
			);

			if ( $i < ( $nb_total_items - 1 ) ) {
				$output .= ', ';
			}

		}
		return $output;
	}

	/**
	 * Display the Taxonomy module version
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'cinematographer' $item_name The name of the item
	 */
	public function get_module_taxo( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$get_taxo_options = $this->movie_taxo->create_taxonomy_options( 'cinematographer', esc_html( $item_results[ $i ]['name'] ), $this->imdb_admin_values );
			$output .= $this->output_class->get_layout_items( esc_html( $movie->title() ), $get_taxo_options );
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}

		return $output;
	}
}
