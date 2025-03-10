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

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;
use Lumiere\Config\Get_Options;

/**
 * Method to display soundtrack for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Soundtrack extends \Lumiere\Frontend\Module\Parent_Module {

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

		for ( $i = 0; $i < $admin_total_items && ( $i < $nb_total_items ); $i++ ) {

			$output .= ucfirst( strtolower( $item_results[ $i ]['soundtrack'] ) );
			$output .= isset( $item_results[ $i ]['credits'][0] ) ? ' <i>' . $item_results[ $i ]['credits'][0] . '</i>' : '';
			if ( isset( $item_results[ $i ]['creditSplit']['creditors'][0]['name'] ) && isset( $item_results[ $i ]['creditSplit']['creditors'][0]['nameId'] ) ) {
				$output .= ' <i>' . $item_results[ $i ]['creditSplit']['creditors'][0]['creditType'] . ' ';
				$output .= $this->link_maker->lumiere_imdburl_of_soundtrack(
					$item_results[ $i ]['creditSplit']['creditors'][0]['nameId'],
					$item_results[ $i ]['creditSplit']['creditors'][0]['name'],
				);
				$output .= '</i>';
			}
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
	 * @param 'soundtrack' $item_name The name of the item
	 * @param array<mixed> $item_results Complex array of results with several possibilies
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$nb_rows_display_clickmore = 5;

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$output .= "\n\t\t\t\t\t" . ucfirst( strtolower( $item_results[ $i ]['soundtrack'] ) );

			if ( isset( $item_results[ $i ]['creditSplit']['creditors'][0]['name'] ) && isset( $item_results[ $i ]['creditSplit']['creditors'][0]['nameId'] ) ) {
				$output .= isset( $item_results[ $i ]['creditSplit']['creditors'][0]['nameId'] ) ? ' <i>' . $item_results[ $i ]['creditSplit']['creditors'][0]['creditType'] . ' ' . parent::get_film_url( $item_results[ $i ]['creditSplit']['creditors'][0]['nameId'], $item_results[ $i ]['creditSplit']['creditors'][0]['name'] ) . '</i>' : ' <i>' . $item_results[ $i ]['creditSplit']['creditors'][0]['name'] . '</i>';
			}
			$output .= ( $i < $nb_total_items - 1 ) ? ', ' : '';

			if ( $i === $nb_rows_display_clickmore ) {
				$isset_next = isset( $item_results[ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'see_all_start', Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) : '';
			}

			if ( $i > $nb_rows_display_clickmore && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'see_all_end' );
			}

		}
		return $output;
	}
}
