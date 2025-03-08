<?php declare( strict_types = 1 );
/**
 * Class for displaying person module Pubinterview.
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

use Imdb\Name;
use Lumiere\Frontend\Main;
use Lumiere\Frontend\Layout\Output_Popup;
use Lumiere\Config\Get_Options_Person;

/**
 * Method to display pubinterview for persons
 *
 * @since 4.4.3 new class
 */
class Person_Pubinterview {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output_Popup $output_popup_class = new Output_Popup(),
	) {
		// Construct Frontend Main trait with options and links.
		$this->start_main_trait();
	}

	/**
	 * Display the main module version
	 *
	 * @param Name $person_class IMDbPHP title class
	 * @param 'pubinterview' $item_name The name of the item
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

		$output = $this->output_popup_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= isset( $item_results[ $i ] ) && isset( $item_results[ $i ]['title'] ) ? '<i>' . $item_results[ $i ]['title'] . '</i> ' : '';
			if ( isset( $item_results[ $i ]['date']['year'] ) && strlen( strval( $item_results[ $i ]['date']['year'] ) ) !== 0 ) {
				$output .= ' (' . strval( $item_results[ $i ]['date']['year'] ) . ')';
			}
			if ( isset( $item_results[ $i ]['reference'] ) && strlen( $item_results[ $i ]['reference'] ) !== 0 ) {
				$output .= ' ' . $item_results[ $i ]['reference'];
			}
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'pubinterview' $item_name The name of the item
	 * @param array<array-key, array<string, string|array<array-key, string>>> $item_results
	 * @phpstan-param array<array-key, array{date?: array{year?: string }, title?: string, reference?: string}> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_popup_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= isset( $item_results[ $i ] ) && isset( $item_results[ $i ]['title'] ) ? '<i>' . $item_results[ $i ]['title'] . '</i> ' : '';
			if ( isset( $item_results[ $i ]['date']['year'] ) && strlen( strval( $item_results[ $i ]['date']['year'] ) ) !== 0 ) {
				$output .= ' (' . strval( $item_results[ $i ]['date']['year'] ) . ')';
			}
			if ( isset( $item_results[ $i ]['reference'] ) && strlen( $item_results[ $i ]['reference'] ) !== 0 ) {
				$output .= ' ' . $item_results[ $i ]['reference'];
			}
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}
}
