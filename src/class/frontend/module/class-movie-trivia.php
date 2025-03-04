<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Trivia.
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
 * Method to display trivia for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Trivia {

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
	 * @param 'trivia' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = 0;
		$admin_total_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;

		foreach ( $item_results as $trivia_type => $trivia_content ) {
			$nb_total_items += count( $trivia_content );
		}
		$nb_total_trivia_processed = 1;

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $movie, $item_name, $item_results, $nb_total_items );
		}

		if ( $nb_total_items === 0 ) {
			esc_html_e( 'No trivias found.', 'lumiere-movies' );
		}

		$total_displayed = $admin_total_items > $nb_total_items ? $nb_total_items : $admin_total_items;
		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nb_total_items && $i < $admin_total_items; $i++ ) {
			foreach ( $item_results as $trivia_type => $trivia_content ) {
				$output .= isset( $trivia_content[ $i ]['content'] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $trivia_content[ $i ]['content'] ) : '';
				$nb_total_trivia_processed++;
			}

				// add hr to every trivia but the last.
			if ( $i < ( $nb_total_items - 1 ) && $i < ( $admin_total_items - 1 ) ) {
				$output .= "\n\t\t\t\t<hr>";
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 * Array of results is sorted by column
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'trivia' $item_name The name of the item
	 * @param array<string, array<array-key, array<string, string>>> $item_results
	 * @param int<0, max> $nb_total_items
	 */
	public function get_module_popup( Title $movie, string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->subtitle_item(
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		if ( $nb_total_items === 0 ) {
			esc_html_e( 'No trivias found.', 'lumiere-movies' );
		}

		$nb_total_trivia_processed = 1;

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			foreach ( $item_results as $trivia_type => $trivia_content ) {
				$text = isset( $trivia_content[ $i ]['content'] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $trivia_content[ $i ]['content'] ) : '';

				// It may be empty, continue to the next result.
				if ( strlen( $text ) === 0 ) {
					continue;
				}

				$output .= "\n\t\t\t\t<div>\n\t\t\t\t\t" . '[#' . esc_html( strval( $nb_total_trivia_processed ) ) . '] <i>' . esc_html( $trivia_type ) . '</i> ' . $text . "\n\t\t\t\t</div>";

				if ( $nb_total_trivia_processed === 5 ) {
					$isset_next = isset( $trivia_content[ $i + 1 ] ) ? true : false;
					$output .= $isset_next === true ? $this->output_class->click_more_start() : '';

				}

				if ( $nb_total_trivia_processed > 2 && $nb_total_trivia_processed === $nb_total_items ) {
					$output .= $this->output_class->click_more_end();
				}
				$nb_total_trivia_processed++;
			}
		}
		return $output;
	}
}
