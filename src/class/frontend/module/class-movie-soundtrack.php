<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Soundtrack.
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
 * Method to display soundtrack for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Soundtrack {

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
	 * @param 'soundtrack' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );
		$admin_total_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $movie, $item_name, $item_results, $nb_total_items );
		}

		if ( $nb_total_items === 0 ) {
			return '';
		}

		$total_displayed = $admin_total_items > $nb_total_items ? $nb_total_items : $admin_total_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $admin_total_items && ( $i < $nb_total_items ); $i++ ) {

			$soundtrack_name = "\n\t\t\t" . ucfirst( strtolower( $item_results[ $i ]['soundtrack'] ) );

			$output .= "\n\t\t\t" . $this->link_maker->lumiere_imdburl_of_soundtrack( sanitize_text_field( $soundtrack_name ) ) . ' ';

			$output .= isset( $item_results[ $i ]['credits'][0] ) ? ' <i>' . $item_results[ $i ]['credits'][0] . '</i>' : '';
			$output .= isset( $item_results[ $i ]['credits'][1] ) ? ' <i>' . $item_results[ $i ]['credits'][1] . '</i>' : '';

			if ( $i < ( $admin_total_items - 1 ) && $i < ( $nb_total_items - 1 ) ) {
				$output .= ', ';
			}
		}

		return $output;
	}

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 * Array of results is sorted by column
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'soundtrack' $item_name The name of the item
	 * @param array<array-key, array<string, string>> $item_results
	 * @param int<0, max> $nb_total_items
	 */
	public function get_module_popup( Title $movie, string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		if ( $nb_total_items === 0 ) {
			esc_html_e( 'No soundtracks found.', 'lumiere-movies' );
		}

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$soundtrack_name = "\n\t\t\t" . esc_html( ucfirst( strtolower( $item_results[ $i ]['soundtrack'] ) ) );
			$output .= "\n\t\t\t" . $this->link_maker->lumiere_imdburl_to_internalurl( $soundtrack_name );

			$output .= isset( $item_results[ $i ]['credits'][0] ) ? ' <i>' . esc_html( $item_results[ $i ]['credits'][0] ) . '</i>' : '';
			$output .= isset( $item_results[ $i ]['credits'][1] ) ? ' <i>' . esc_html( $item_results[ $i ]['credits'][1] ) . '</i>' : '';

			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}

			if ( $i === 5 ) {
				$isset_next = isset( $item_results[ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) : '';

			}

			if ( $i > 2 && $i === $nb_total_items ) {
				$output .= $this->output_class->misc_layout( 'click_more_end' );
			}

		}
		return $output;
	}
}
