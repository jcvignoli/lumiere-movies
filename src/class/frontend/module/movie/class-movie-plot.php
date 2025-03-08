<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Plot.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;
use Lumiere\Config\Get_Options;

/**
 * Method to display Plot for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Plot extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'plot' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$admin_total_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;
		$nb_total_items = count( $item_results );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$total_displayed = $admin_total_items > $nb_total_items ? $nb_total_items : $admin_total_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] )
		);

		for ( $i = 0; ( $i < $nb_total_items ) && ( $i < $admin_total_items ); $i++ ) {

			$output .= $item_results[ $i ]['plot'] !== null ? $this->link_maker->lumiere_movies_plot_details( $item_results[ $i ]['plot'] ) : __( 'No plot found', 'lumiere-movies' );

			// add hr to every plot but the last.
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
	 * @param 'plot' $item_name The name of the item
	 * @param array<int<0, max>, array<string, string>> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = "\n" . '<div id="lumiere_popup_pluts_group">';

		$output .= $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= "\n\t" . '<div>';
			$output .= ' [#' . strval( $i + 1 ) . '] ' . $item_results[ $i ]['plot'];
			if ( $i < $nb_total_items - 1 ) {
				$output .= "\n<br>";
			}
			$output .= "\n\t</div>";
		}

		$output .= "\n</div>";

		return $output;
	}

	/**
	 * Display the Popup version of the module, displaying all results on two columns
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'plot' $item_name The name of the item
	 */
	public function get_module_popup_two_columns( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$output .= $this->output_class->misc_layout(
				'two_columns_first',
				'<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="' . esc_url( wp_nonce_url( Get_Options::get_popup_url( 'person', site_url() ) . $item_results[ $i ]['imdb'] . '/?mid=' . $item_results[ $i ]['imdb'] ) ) . '" title="' . __( 'internal link', 'lumiere-movies' ) . ' ' . $item_results[ $i ]['name'] . '">' . "\n\t\t\t\t" . $item_results[ $i ]['name'] . '</a>'
			);

			$output .= $this->output_class->misc_layout(
				'two_columns_second',
				$item_results[ $i ]['jobs'][0] ?? ''
			);

		}
		return $output;
	}

}
