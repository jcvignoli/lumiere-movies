<?php declare( strict_types = 1 );
/**
 * Class for displaying persons module quotes.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Module\Person;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Name;
use Lumiere\Config\Get_Options_Person;

/**
 * Method to display quotes for person
 *
 * @since 4.4.3 new class
 */
class Person_Quotes extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param Name $person_class IMDbPHP title class
	 * @param 'quotes' $item_name The name of the item
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
			$text = isset( $item_results[ $i ] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $item_results[ $i ] ) : '';

			// It may be empty, continue to the next result.
			if ( strlen( $text ) === 0 ) {
				continue;
			}
			$output .= "\n\t\t\t" . '<div>';
			$output .= ' [#' . strval( $i + 1 ) . '] ' . $text;
			$output .= '</div>';
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 * Array of results is sorted by column
	 *
	 * @param 'quotes' $item_name The name of the item
	 * @param array<array-key, string> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$nb_rows_click_more = 3;

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ] )
		);
		$output .= '(' . strval( $nb_total_items ) . ')';

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$text = isset( $item_results[ $i ] ) ? $this->link_maker->lumiere_imdburl_to_internalurl( $item_results[ $i ] ) : '';

			// It may be empty, continue to the next result.
			if ( strlen( $text ) === 0 ) {
				continue;
			}

			// Display a "show more" after XX results
			if ( $i === $nb_rows_click_more ) {
				$isset_next = isset( $item_results[ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $item_name ) : '';
			}

			$output .= "\n\t\t\t" . '<div>';
			$output .= ' [#' . strval( $i + 1 ) . '] ' . $text;
			$output .= '</div>';

			if ( $i > $nb_rows_click_more && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'click_more_end' );
			}
		}
		return $output;
	}
}
