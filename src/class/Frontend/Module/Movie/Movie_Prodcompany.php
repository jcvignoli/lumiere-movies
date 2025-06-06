<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Prodcompany.
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
 * Method to display prodcompany for movies
 *
 * @since 4.5 new class
 */
final class Movie_Prodcompany extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'prodCompany' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

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
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$comment = isset( $item_results[ $i ]['attribute'][0] ) ? '"' . $item_results[ $i ]['attribute'][0] . '"' : '';
			$output .= $this->link_maker->get_prodcompany(
				$item_results[ $i ]['name'],
				$item_results[ $i ]['id'],
				$comment,
			);
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'prodCompany' $item_name The name of the item
	 * @param array{name:string,id:string,country:string,attribute:string,year:int}[] $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$comment = isset( $item_results[ $i ]['attribute'][0] ) ? '"' . $item_results[ $i ]['attribute'][0] . '"' : '';
			$output .= $this->link_maker->get_prodcompany(
				$item_results[ $i ]['name'],
				$item_results[ $i ]['id'],
				$comment,
			);
		}
		return $output;
	}
}
