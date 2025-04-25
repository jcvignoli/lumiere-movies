<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Quote.
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
 * Method to display Quote for movies
 *
 * @since 4.5 new class
 */
class Movie_Quote extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the module
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'quote' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );
		$admin_max_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$total_displayed = $admin_max_items > $nb_total_items ? $nb_total_items : $admin_max_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $total_displayed )[ $item_name ] )
		);

		for ( $i = 0; $i < $admin_max_items && ( $i < $nb_total_items ); $i++ ) {
			if ( is_array( $item_results[ $i ] ) ) {
				foreach ( $item_results[ $i ] as $sub_quote ) {
					$output .= str_starts_with( $sub_quote, '[' ) ? "\n\t\t\t" : "\n\t\t\t&laquo; ";
					$output .= $sub_quote;
					$output .= str_ends_with( $sub_quote, ']' ) ? "\n\t\t\t" : "\n\t\t\t&raquo; ";
				}
				$output .= "\n\t\t\t\t<br>";
				continue;
			}
			$output .= "\n\t\t\t&laquo; " . $item_results[ $i ] . ' &raquo; ';
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'quote' $item_name The name of the item
	 * @param array<array-key, string|array<string, string>> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			if ( is_array( $item_results[ $i ] ) ) {
				foreach ( $item_results[ $i ] as $sub_quote ) {
					$output .= str_starts_with( $sub_quote, '[' ) ? "\n\t\t\t" : "\n\t\t\t&laquo; ";
					$output .= $sub_quote;
					$output .= str_ends_with( $sub_quote, ']' ) ? "\n\t\t\t" : "\n\t\t\t&raquo; ";
				}
				$output .= "\n\t\t\t\t<br>";
				continue;
			}
			$output .= "\n\t\t\t&laquo; " . $item_results[ $i ] . ' &raquo; ';
		}
		return $output;
	}

}
