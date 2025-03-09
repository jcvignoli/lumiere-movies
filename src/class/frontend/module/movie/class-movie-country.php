<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Title.
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
 * Method to display title for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Country extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the Country
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'country' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= sanitize_text_field( $item_results[ $i ] );
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}

		return $output;
	}

	/**
	 * Display the Popup version of the module, comma separated results
	 *
	 * @param 'country' $item_name The name of the item
	 * @param array<array-key, string> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= sanitize_text_field( $item_results[ $i ] );
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Country for taxonomy
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'country' $item_name The name of the item
	 */
	public function get_module_taxo( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, $item_results[ $i ], $this->imdb_admin_values );
			$output .= $this->output_class->get_layout_items( $movie->title(), $get_taxo_options );

			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}

		}
		return $output;
	}
}
