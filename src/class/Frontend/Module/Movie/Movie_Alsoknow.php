<?php
/**
 * Class for displaying movies module Alsoknow.
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
 * Method to display Alsoknow for movies
 *
 * @since 4.5 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'alsoknow', array<array{title: string, country: string, countryId: string, language: string|null, languageId: string, comment: array<string>|null}>, \Lumiere\Vendor\Imdb\Title>
 */
final class Movie_Alsoknow extends Parent_Module {

	/**
	 * Display the module version
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$item_results = $imdb_class->$item_name();
		$nb_total_items = count( $item_results );
		$admin_max_items = $this->settings->get_movie_option( 'imdbwidget' . $item_name . 'number' ) !== null ? intval( $this->settings->get_movie_option( 'imdbwidget' . $item_name . 'number' ) ) + 1 : 0; // Adding 1 since first array line is the title

		if ( $nb_total_items < 2 ) { // Since the first result is the original title, must be greater than 1
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items && $i < $admin_max_items; $i++ ) {

			// Original title, already using it in the box.
			if ( $i === 0 ) {
				continue;
			}
			$output .= "\n\t\t\t<i>" . $item_results[ $i ]['title'] . '</i>';

			if ( isset( $item_results[ $i ]['countryId'] ) ) {
				$output .= ' (';
				$output .= $item_results[ $i ]['country'];
				if ( isset( $item_results[ $i ]['comment'][0] ) ) {
					$output .= ' - ';
					$output .= $item_results[ $i ]['comment'][0];
				}
				$output .= ')';
			}

			if ( $i < ( $nb_total_items - 1 ) && $i < ( $admin_max_items - 1 ) ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			// Original title, already using it in the box.
			if ( $i === 0 ) {
				continue;
			}
			$output .= "\n\t\t\t<i>" . $item_results[ $i ]['title'] . '</i>';

			if ( isset( $item_results[ $i ]['countryId'] ) ) {
				$output .= ' (';
				$output .= $item_results[ $i ]['country'];
				if ( isset( $item_results[ $i ]['comment'][0] ) ) {
					// @phan-suppress-next-line PhanTypeArraySuspiciousNullable
					$output .= ' - ' . $item_results[ $i ]['comment'][0];
				}
				$output .= ')';
			}

			if ( $i < ( $nb_total_items - 1 ) ) {
				$output .= ', ';
			}
		}
		return $output;
	}
}
