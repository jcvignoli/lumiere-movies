<?php declare( strict_types = 1 );
/**
 * Class for displaying persons module Pubportrayal.
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
use Lumiere\Config\Get_Options;

/**
 * Method to display Pubportrayal for person
 *
 * @since 4.4.3 new class
 */
class Person_Pubportrayal extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param Name $person_class IMDbPHP title class
	 * @param 'pubportrayal' $item_name The name of the item
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

		for ( $i = 0; $i < $nb_total_items; ++$i ) {
			$output .= "<a rel=\"nofollow\" class='lum_popup_internal_link lum_add_spinner' href='" . esc_url( wp_nonce_url( Get_Options::get_popup_url( 'film', site_url() ) . '?mid=' . $item_results[ $i ]['id'] ) ) . "'>" . $item_results[ $i ]['title'] . '</a>';
			if ( isset( $item_results[ $i ]['year'] ) && strlen( strval( $item_results[ $i ]['year'] ) ) > 0 ) {
				$output .= ' (' . $item_results[ $i ]['year'] . ') ';
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 * Array of results is sorted by column
	 *
	 * @param 'pubportrayal' $item_name The name of the item
	 * @param array<array-key, array<string, string>> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; ++$i ) {
			$output .= "\n\t\t\t\t\t" . $this->output_class->get_link(
				'internal_with_spinner',
				parent::get_film_url( strval( $item_results[ $i ]['id'] ) ),
				$item_results[ $i ]['title'],
			);
			if ( isset( $item_results[ $i ]['year'] ) && strlen( strval( $item_results[ $i ]['year'] ) ) > 0 ) {
				$output .= ' (' . $item_results[ $i ]['year'] . ') ';
			}
		}
		return $output;
	}
}
