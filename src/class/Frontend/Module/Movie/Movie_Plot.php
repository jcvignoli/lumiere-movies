<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Plot.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options_Movie;

/**
 * Method to display Plot for movies
 *
 * @since 4.5 new class
 */
final class Movie_Plot extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'plot' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

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
			ucfirst( Get_Options_Movie::get_all_fields( $total_displayed )[ $item_name ] )
		);

		for ( $i = 0; ( $i < $nb_total_items ) && ( $i < $admin_total_items ); $i++ ) {
			if ( ! isset( $item_results[ $i ]['plot'] ) ) {
				continue;
			}
			$output .= $this->link_maker->get_plot( $item_results[ $i ]['plot'] );
			// add hr to every plot but the last.
			$output .= $i < ( $nb_total_items - 1 ) && $i < ( $admin_total_items - 1 ) ? "\n\t\t\t\t<hr>" : '';
		}

		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'plot' $item_name The name of the item
	 * @param array<int<0, max>, array<string, string>> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$nb_max_clickmore = 20;

		$output = "\n" . '<div id="lumiere_popup_pluts_group">';

		$output .= $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= $this->output_class->misc_layout( 'numbered_list', strval( $i + 1 ), '', $item_results[ $i ]['plot'] );
			if ( $i === $nb_max_clickmore ) {
				$isset_next = isset( $item_results[ $i + 1 ]['plot'] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $item_name ) : '';
			}
			if ( $i > $nb_max_clickmore && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'click_more_end' );
			}
		}
		return $output;
	}
}
