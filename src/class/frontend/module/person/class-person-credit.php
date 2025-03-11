<?php declare( strict_types = 1 );
/**
 * Class for displaying persons module Credit.
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
use Lumiere\Tools\Data;

/**
 * Method to display Credit for person
 * Retrieves all movies that are available in \Lumiere\Config\Settings_Person::credits_role_all()
 *
 * @since 4.5 new class
 */
class Person_Credit extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param Name $person_class IMDbPHP title class
	 * @param string $sub_cat The name of the subcategory
	 * @param int<0, max> $max_results Limit the number of results
	 */
	public function get_module( Name $person_class, string $sub_cat, int $max_results ): string {

		$item_results = $person_class->credit();
		$nb_total_items = isset( $item_results[ $sub_cat ] ) ? count( $item_results[ $sub_cat ] ) : 0;

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $sub_cat, $item_results, $nb_total_items, $max_results );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			Data::mb_ucfirst( Get_Options_Person::get_all_credit_role( $nb_total_items )[ $sub_cat ] ) // Can start with special charas, so use homemade ucfirst that behaves like mb_ucfirst().
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= "\n\t\t\t\t " . parent::get_popup_person( $item_results[ $sub_cat ][ $i ]['imdb'], $item_results[ $sub_cat ][ $i ]['name'] );

			if ( isset( $item_results[ $sub_cat ][ $i ]['year'] ) ) {
				$output .= ' (' . strval( $item_results[ $sub_cat ][ $i ]['year'] ) . ')';
			}

			if ( isset( $item_results[ $sub_cat ][ $i ]['characters'] ) && count( $item_results[ $sub_cat ][ $i ]['characters'] ) > 0 ) {
				/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable (I don't get the error) */
				$output .= ' as <i>' . $item_results[ $sub_cat ][ $i ]['characters'][0] . '</i>';
			}

			// Display a "show more" after XX results, only if a next result exists.
			if ( $i === $max_results ) {
				$isset_next = isset( $item_results[ $sub_cat ][ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? "\t\t\t" . $this->output_class->misc_layout( 'see_all_start' ) : '';
			}

			if ( $i > $max_results && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'see_all_end' );
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 * Array of results is sorted by column
	 *
	 * @param string $sub_cat The name of the subcategory
	 * @param array<array-key, array<array-key, array<string, string|array<array-key, string>>>> $item_results
	 * @phpstan-param array<array-key, array<array-key, array{titleId: string, titleName: string, year?: string, characters?: list<string>}>> $item_results
	 * @param int<1, max> $nb_total_items
	 * @param int<0, max> $max_results Limit the number of results
	 */
	public function get_module_popup( string $sub_cat, array $item_results, int $nb_total_items, int $max_results ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			Data::mb_ucfirst( Get_Options_Person::get_all_credit_role( $nb_total_items )[ $sub_cat ] ) // Can start with special charas, so use homemade ucfirst that behaves like mb_ucfirst().
		);

		if ( $nb_total_items > $max_results ) {
			$output .= '(' . strval( $nb_total_items ) . ')'; // Show the total number found right after the title.
		}

		for ( $i = 0; $i < $nb_total_items; $i ++ ) {
			$output .= parent::get_person_url( $item_results[ $sub_cat ][ $i ]['titleId'], $item_results[ $sub_cat ][ $i ]['titleName'] );

			if ( isset( $item_results[ $sub_cat ][ $i ]['year'] ) ) {
				$output .= ' (' . strval( $item_results[ $sub_cat ][ $i ]['year'] ) . ')';
			}

			if ( isset( $item_results[ $sub_cat ][ $i ]['characters'] ) && count( $item_results[ $sub_cat ][ $i ]['characters'] ) > 0 ) {
				$output .= ' as <i>' . $item_results[ $sub_cat ][ $i ]['characters'][0] . '</i>';
			}

			// Display a "show more" after XX results, only if a next result exists.
			if ( $i === $max_results ) {
				$isset_next = isset( $item_results[ $sub_cat ][ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? "\t\t\t" . $this->output_class->misc_layout( 'see_all_start' ) : '';
			}

			if ( $i > $max_results && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'see_all_end' );
			}
		}
		return $output;
	}
}
