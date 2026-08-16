<?php
/**
 * Class for displaying movies module Extsites.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options_Movie;
use Lumiere\Frontend\Module\Parent_Module;

/**
 * Method to display Extsites for movies
 *
 * @since 4.5 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'extSites', array<array{label: string, url: string, language: list<string>}>, \Lumiere\Vendor\Imdb\Title>
 */
final class Movie_Extsites extends Parent_Module {

	/**
	 * Display the main module version
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$item_results = $imdb_class->$item_name();
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
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++  ) {

			$output .= $this->link_maker->get_officialsites(
				$item_results[ $i ]['url'],
				$item_results[ $i ]['label'],
			);

			if ( $i < ( $nb_total_items - 1 ) ) {
				$output .= ', ';
			}

		}
		return $output;
	}
}
