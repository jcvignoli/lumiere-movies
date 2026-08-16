<?php
/**
 * Class for displaying persons module trademark.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Frontend\Module\Person;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options_Person;
use Lumiere\Frontend\Module\Parent_Module;

/**
 * Method to display trademark for person
 *
 * @since 4.5 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'trademark', array<array-key, string>, \Lumiere\Vendor\Imdb\Name>
 */
final class Person_Trademark extends Parent_Module {

	/**
	 * Display the main module version
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$item_results = $imdb_class->$item_name();
		$nb_total_items = count( $item_results );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$nb_rows_display_clickmore = $this->settings->get_person_option( 'number' )[ $item_name . '_number' ] !== null ? intval( $this->settings->get_person_option( 'number' )[ $item_name . '_number' ] ) : 10; /** max number of movies before breaking with "see all" */

		$item_may_plural = Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ];
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( $item_may_plural )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$text = $item_results[ $i ] ?? '';

			// Display a "show more" after XX results
			if ( $i === $nb_rows_display_clickmore ) {
				$isset_next = isset( $item_results[ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $item_may_plural ) : '';
			}

			$output .= strlen( $text ) > 0 ? $this->output_class->misc_layout( 'numbered_list', strval( $i + 1 ), '', $text ) : '';

			if ( $i > $nb_rows_display_clickmore && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'click_more_end' );
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

		$nb_rows_display_clickmore = 3;

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ] )
		);

		$output .= '(' . strval( $nb_total_items ) . ')';

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$text = $item_results[ $i ] ?? '';

			// It may be empty, continue to the next result.
			if ( strlen( $text ) === 0 ) {
				continue;
			}

			// Display a "show more" after XX results
			if ( $i === $nb_rows_display_clickmore ) {
				$isset_next = isset( $item_results[ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $item_name ) : '';
			}

			$output .= $this->output_class->misc_layout( 'numbered_list', strval( $i + 1 ), '', $text );

			if ( $i > $nb_rows_display_clickmore && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'click_more_end' );
			}
		}
		return $output;
	}
}
