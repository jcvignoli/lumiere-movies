<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Keyword.
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
use Lumiere\Frontend\Movie\Movie_Taxonomy;
use Lumiere\Config\Get_Options;

/**
 * Method to display Keyword for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Keyword {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output $output_class = new Output(),
		protected Movie_Taxonomy $movie_taxo = new Movie_Taxonomy()
	) {
		// Construct Frontend Main trait with options and links.
		$this->start_main_trait();
	}

	/**
	 * Display the module
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'keyword' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );
		$hard_limit_items = 10;

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$total_displayed = $hard_limit_items > $nb_total_items ? $nb_total_items : $hard_limit_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items && $i < $hard_limit_items; $i++ ) {
			$output .= esc_attr( $item_results[ $i ] );
			if ( $i < $nb_total_items - 1 && $i < $hard_limit_items - 1 ) {
				$output .= ', ';
			}
		}

		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'keyword' $item_name The name of the item
	 * @param array<array-key, string> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= esc_attr( $item_results[ $i ] );
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the module for taxonomy
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'keyword' $item_name The name of the item
	 */
	public function get_module_taxo( Title $movie, string $item_name ): string {

		$items_result = $movie->$item_name();
		$nb_total_items = count( $items_result );
		$hard_limit_items = 10;

		if ( $nb_total_items === 0 ) {
			return '';
		}

		$total_displayed = $hard_limit_items > $nb_total_items ? $nb_total_items : $hard_limit_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items && $i < $hard_limit_items; $i++ ) {

			$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, sanitize_text_field( $items_result[ $i ] ), $this->imdb_admin_values );
			$output .= $this->output_class->get_layout_items( $movie->title(), $get_taxo_options );

			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}
}
