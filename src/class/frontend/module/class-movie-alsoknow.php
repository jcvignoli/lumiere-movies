<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Alsoknow.
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
 * Method to display Alsoknow for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Alsoknow {

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
	 * Display the main module version
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'alsoknow' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );
		$admin_max_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) + 1 : 0; // Adding 1 since first array line is the title

		if ( $nb_total_items < 2 ) { // Since the first result is the original title, must be greater than 1
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nb_total_items && $i < $admin_max_items; $i++ ) {

			// Original title, already using it in the box.
			if ( $i === 0 ) {
				continue;
			}
			$output .= "\n\t\t\t<i>" . esc_html( $item_results[ $i ]['title'] ) . '</i>';

			if ( isset( $item_results[ $i ]['countryId'] ) ) {
				$output .= ' (';
				$output .= esc_html( $item_results[ $i ]['country'] );
				if ( isset( $item_results[ $i ]['comment'][0] ) ) {
					$output .= ' - ';
					$output .= esc_html( $item_results[ $i ]['comment'][0] );
				}
				$output .= ')';
			}

			if ( $i < ( $nb_total_items - 1 ) && $i < ( $admin_max_items - 1 ) ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'alsoknow' $item_name The name of the item
	 * @param array<array-key, array<string, string>> $item_results
	 * @param int<0, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			// Original title, already using it in the box.
			if ( $i === 0 ) {
				continue;
			}
			$output .= "\n\t\t\t<i>" . esc_html( $item_results[ $i ]['title'] ) . '</i>';

			if ( isset( $item_results[ $i ]['countryId'] ) ) {
				$output .= ' (';
				$output .= esc_html( $item_results[ $i ]['country'] );
				if ( isset( $item_results[ $i ]['comment'][0] ) ) {
					$output .= ' - ';
					$output .= esc_html( $item_results[ $i ]['comment'][0] );
				}
				$output .= ')';
			}

			if ( $i < ( $nb_total_items - 1 ) ) {
				$output .= ', ';
			}
		}
		return $output;
	}
}
