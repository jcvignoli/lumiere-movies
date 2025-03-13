<?php declare( strict_types = 1 );
/**
 * Class for displaying person module Pubprints.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Module\Person;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Name;
use Lumiere\Config\Get_Options_Person;

/**
 * Method to display Pubprints for persons
 *
 * @since 4.5 new class
 */
class Person_Pubprints extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param Name $person_class IMDbPHP title class
	 * @param 'pubprints' $item_name The name of the item
	 */
	public function get_module( Name $person_class, string $item_name ): string {

		$item_results = $person_class->$item_name();
		$nb_total_items = count( $item_results );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			if ( isset( $item_results[ $i ]['author'][0] ) && strlen( $item_results[ $i ]['author'][0] ) > 0 ) {
				$output .= "\n\t\t" . $item_results[ $i ]['author'][0];
			}

			if ( isset( $item_results[ $i ]['title'] ) && strlen( $item_results[ $i ]['title'] ) > 0 ) {
				$output .= ' <i>' . $item_results[ $i ]['title'] . '</i> ';
			}

			if ( isset( $item_results[ $i ]['year'] ) && strlen( $item_results[ $i ]['year'] ) > 0 ) {
				$output .= '(' . $item_results[ $i ]['year'] . ')';
			}

			if ( isset( $item_results[ $i ]['details'] ) && strlen( $item_results[ $i ]['details'] ) !== 0 ) {
				$output .= $item_results[ $i ]['details'] . ' ';
			}

			if ( $i < ( $nb_total_items - 1 ) ) {
				$output .= ', ';
			}

			if ( $i === ( $nb_total_items - 1 ) ) {
				$output .= "\n\t" . '</span>';
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'pubprints' $item_name The name of the item
	 * @param array<array-key, array<string, string>> $item_results
	 * @phpstan-param array<array-key, array{author?: array<string>, title?: string, year?: string, details?: string }> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$nb_rows_display_clickmore = 5;

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			// Display a "click to show more" after XX results
			if ( $i === $nb_rows_display_clickmore ) {
				$isset_next = isset( $item_results[ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'see_all_start', $item_name ) : '';
			}

			if ( isset( $item_results[ $i ]['author'][0] ) && strlen( $item_results[ $i ]['author'][0] ) > 0 ) {
				$output .= "\n\t\t" . $item_results[ $i ]['author'][0];
			}

			if ( isset( $item_results[ $i ]['title'] ) && strlen( $item_results[ $i ]['title'] ) > 0 ) {
				$output .= ' <i>' . $item_results[ $i ]['title'] . '</i> ';
			}

			if ( isset( $item_results[ $i ]['year'] ) && strlen( $item_results[ $i ]['year'] ) > 0 ) {
				$output .= '(' . $item_results[ $i ]['year'] . ')';
			}

			if ( isset( $item_results[ $i ]['details'] ) && strlen( $item_results[ $i ]['details'] ) !== 0 ) {
				$output .= $item_results[ $i ]['details'] . ' ';
			}

			if ( $i < ( $nb_total_items - 1 ) ) {
				$output .= ', ';
			}

			// End of "click to show more"
			if ( $i > $nb_rows_display_clickmore && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'see_all_end' );
			}
		}
		return $output;
	}
}
