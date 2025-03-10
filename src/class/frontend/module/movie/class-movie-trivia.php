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

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;
use Lumiere\Config\Get_Options;

/**
 * Method to display trivia for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Trivia extends \Lumiere\Frontend\Module\Parent_Module {

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

		foreach ( Get_Options::get_list_trivia_cat() as $trivia_cat ) {
			for ( $i = 0; $i < $total_displayed; $i++ ) {
				if ( ! isset( $item_results[ $trivia_cat ][ $i ]['content'] ) ) {
					continue;
				}
				$output .= $this->output_class->misc_layout( 'frontend_items_sub_cat_parent', Get_Options::get_list_trivia_cat() [ $trivia_cat ] );
				$output .= $this->output_class->misc_layout( 'frontend_items_sub_cat_content', $item_results[ $trivia_cat ][ $i ]['content'] );

			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 * Array of results is sorted by column
	 *
	 * @param 'trivia' $item_name The name of the item
	 * @param array<string, array<array-key, array<string, string>>> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$translated_item = Get_Options::get_all_fields( $nb_total_items )[ $item_name ];
		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( $translated_item )
		);

		$overall_loop = 1;

		foreach ( $item_results as $trivia_type => $trivia_content ) {

			// Process only categories in settings.
			if ( in_array( $trivia_type, Get_Options::get_list_trivia_cat(), true ) === false ) {
				continue;
			}

			for ( $i = 0; $i < $nb_total_items; $i++ ) {

				$text = $item_results[ $trivia_type ][ $i ]['content'] ?? '';

				// It may be empty, continue to the next result.
				if ( strlen( $text ) === 0 ) {
					continue;
				}

				$output .= $this->output_class->misc_layout( 'numbered_list', strval( $overall_loop ), Get_Options::get_list_trivia_cat() [ $trivia_type ], $text );

				if ( $overall_loop === 5 ) {
					$isset_next = isset( $item_results[ $trivia_type ][ $overall_loop + 1 ] ) ? true : false;
					$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $translated_item ) : '';
				}

				if ( $overall_loop > 5 && $overall_loop === $nb_total_items ) {
					$output .= $this->output_class->misc_layout( 'click_more_end' );
				}
				$overall_loop++;
			}
		}
		return $output;
	}
}
