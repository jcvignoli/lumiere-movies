<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Trailer.
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
use Lumiere\Config\Get_Options;

/**
 * Method to display Trailer for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Trailer {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output $output_class = new Output(),
	) {
		// Construct Frontend Main trait with options and links.
		$this->start_main_trait();
	}

	/**
	 * Display the module
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'trailer' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->video(); // Title::video() works faster than Title::trailer()
		$item_results = $item_results['Trailer'] ?? null; // Two rows available: Clip and Trailer
		$admin_max_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;
		$nb_total_items = isset( $item_results ) ? count( $item_results ) : 0;

		// if no results, exit.
		if ( $nb_total_items === 0 || $item_results === null ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $movie, $item_name, $item_results, $nb_total_items );
		}

		$total_displayed = $admin_max_items > $nb_total_items ? $nb_total_items : $admin_max_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $admin_max_items && $i < $nb_total_items; $i++ ) {

			if ( ! isset( $item_results[ $i ]['playbackUrl'] ) ) {
				continue;
			}

			$output .= $this->link_maker->lumiere_movies_trailer_details( $item_results[ $i ]['playbackUrl'], $item_results[ $i ]['name'] );

			if ( $i < ( $admin_max_items - 1 ) && $i < ( $nb_total_items - 1 ) ) {
				$output .= ', '; // add comma to every trailer but the last.
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'trailer' $item_name The name of the item
	 * @param array<array-key, array<string, string>> $item_results
	 * @param int<0, max> $nb_total_items
	 */
	public function get_module_popup( Title $movie, string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		if ( $nb_total_items === 0 ) {
			esc_html_e( 'No quotes found.', 'lumiere-movies' );
		}

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			if ( ! isset( $item_results[ $i ]['playbackUrl'] ) ) {
				continue;
			}
			$output .= $this->link_maker->lumiere_movies_trailer_details( $item_results[ $i ]['playbackUrl'], $item_results[ $i ]['name'] );
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', '; // add comma to every trailer but the last.
			}
		}
		return $output;
	}

}
