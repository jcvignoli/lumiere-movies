<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Quote.
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
 * Method to display Quote for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Quote {

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
	 * @param 'quote' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );
		$admin_max_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;

		if ( $nb_total_items === 0 ) {
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

		for ( $i = 0; $i < $admin_max_items && ( $i < $nb_total_items ); $i++ ) {
			if ( is_array( $item_results[ $i ] ) ) {
				foreach ( $item_results[ $i ] as $sub_quote ) {
					$output .= str_starts_with( $sub_quote, '[' ) ? "\n\t\t\t" : "\n\t\t\t&laquo; ";
					$output .= esc_html( $sub_quote );
					$output .= str_ends_with( $sub_quote, ']' ) ? "\n\t\t\t" : "\n\t\t\t&raquo; ";
				}
				$output .= "\n\t\t\t\t<br>";
				continue;
			}
			$output .= "\n\t\t\t&laquo; " . esc_html( $item_results[ $i ] ) . ' &raquo; ';
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'quote' $item_name The name of the item
	 * @param array<array-key, string|array<string, string>> $item_results
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
			if ( is_array( $item_results[ $i ] ) ) {
				foreach ( $item_results[ $i ] as $sub_quote ) {
					$output .= str_starts_with( $sub_quote, '[' ) ? "\n\t\t\t" : "\n\t\t\t&laquo; ";
					$output .= esc_html( $sub_quote );
					$output .= str_ends_with( $sub_quote, ']' ) ? "\n\t\t\t" : "\n\t\t\t&raquo; ";
				}
				$output .= "\n\t\t\t\t<br>";
				continue;
			}
			$output .= "\n\t\t\t&laquo; " . esc_html( $item_results[ $i ] ) . ' &raquo; ';
		}
		return $output;
	}

}
