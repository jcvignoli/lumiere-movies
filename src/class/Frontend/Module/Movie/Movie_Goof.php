<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Goof.
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
 * Method to display goof for movies
 *
 * @since 4.5 new class
 */
final class Movie_Goof extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'goof' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$filter_nbtotal_items = array_filter( $item_results, fn( array $item_results ) => ( count( array_values( $item_results ) ) > 0 ) ); // counts the actual goofs, not their categories

		$nb_total_items = count( $filter_nbtotal_items );
		$admin_total_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $filter_nbtotal_items, $nb_total_items );
		}

		$total_displayed = $admin_total_items > $nb_total_items ? $nb_total_items : $admin_total_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $total_displayed )[ $item_name ] )
		);

		foreach ( Get_Options_Movie::get_list_goof_cat() as $category => $data_explain ) {
			if ( ! isset( $item_results[ $category ] ) ) {
				continue;
			}
			// Loop conditions: less than the total number of goofs available AND less than the goof limit setting, using a loop counter.
			for ( $i = 0; $i < $total_displayed; $i++ ) {
				if ( ! isset( $item_results[ $category ][ $i ]['content'] ) || strlen( $item_results[ $category ][ $i ]['content'] ) === 0 ) {
					continue;
				}
				$output .= $this->output_class->misc_layout( 'frontend_items_sub_cat_parent', $data_explain );
				$output .= $this->output_class->misc_layout( 'frontend_items_sub_cat_content', $item_results[ $category ][ $i ]['content'] );
			}
		}

		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'goof' $item_name The name of the item
	 * @param array<string, array<array-key, array<string, string>>> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$translated_item = Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ];
		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( $translated_item )
		);

		$overall_loop = 1;

		foreach ( Get_Options_Movie::get_list_goof_cat() as $category => $data_explain ) {
			if ( ! isset( $item_results[ $category ] ) ) {
				continue;
			}
			// Loop conditions: less than the total number of goofs available AND less than the goof limit setting, using a loop counter.
			for ( $i = 0; $i < $nb_total_items; $i++ ) {

				if ( ! isset( $item_results[ $category ][ $i ]['content'] ) || strlen( $item_results[ $category ][ $i ]['content'] ) === 0 ) {
					continue;
				}

				$output .= $this->output_class->misc_layout( 'numbered_list', strval( $overall_loop ), $data_explain, $item_results[ $category ][ $i ]['content'] );

				if ( $overall_loop === 5 ) {
					$isset_next = isset( $item_results[ $category ][ $i + 1 ] ) ? true : false;
					$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $translated_item ) : '';
				}
				$overall_loop ++;
			}

			if ( $category === array_key_last( Get_Options_Movie::get_list_goof_cat() ) ) {
				$output .= $this->output_class->misc_layout( 'click_more_end' );
			}

		}
		return $output;
	}
}
