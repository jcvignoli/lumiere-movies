<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Extsites.
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
 * Method to display Extsites for movies
 *
 * @since 4.5 new class
 */
final class Movie_Extsites extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'extSites' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$external_sites = $item_results['official'] ?? $item_results['misc'] ?? [];
		$nb_total_items = count( $external_sites );
		$hardcoded_max_sites = 8;                                   /* max sites 8, so 7 displayed */

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $external_sites, $nb_total_items );
		}

		$total_displayed = $hardcoded_max_sites > $nb_total_items ? $nb_total_items : $hardcoded_max_sites;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $total_displayed )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items && $i < $hardcoded_max_sites; $i++  ) {

			$output .= $this->link_maker->get_officialsites(
				$external_sites[ $i ]['url'],
				$external_sites[ $i ]['label'],
			);

			if ( $i < ( $nb_total_items - 1 ) && $i < ( $hardcoded_max_sites - 1 ) ) {
				$output .= ', ';
			}

		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'extSites' $item_name The name of the item
	 * @param array<array-key, array<string, string>> $external_sites
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $external_sites, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++  ) {

			$output .= $this->link_maker->get_officialsites(
				$external_sites[ $i ]['url'],
				$external_sites[ $i ]['label'],
			);

			if ( $i < ( $nb_total_items - 1 ) ) {
				$output .= ', ';
			}

		}
		return $output;
	}
}
