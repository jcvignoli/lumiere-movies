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

namespace Lumiere\Frontend\Module;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Name;
use Lumiere\Frontend\Main;
use Lumiere\Frontend\Layout\Output;
use Lumiere\Config\Get_Options_Person;
use Lumiere\Config\Get_Options;

/**
 * Method to display Credit for person
 * Retrieves all movies that are available in \Lumiere\Config\Settings_Person::credits_role_all()
 *
 * @since 4.4.3 new class
 */
class Person_Credit {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output $output_class = new Output(),
	) {
		// Construct Frontend Main trait with options and links.
		$this->start_main_trait();
	}

	/**
	 * Display the main module version
	 *
	 * @param Name $person_class IMDbPHP title class
	 * @param string $sub_cat The name of the item
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

		$i = 0;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Person::get_all_credit_role( $nb_total_items )[ $sub_cat ] )
		);

		foreach ( $item_results[ $sub_cat ] as $credit_role ) {

			$output .= $this->output_class->get_link(
				'internal_with_spinner',
				wp_nonce_url( Get_Options::get_popup_url( 'film', site_url() ) . '?mid=' . $credit_role['titleId'] ),
				$credit_role['titleName'],
			);

			if ( isset( $credit_role['year'] ) ) {
				$output .= ' (' . strval( $credit_role['year'] ) . ')';
			}

			if ( isset( $credit_role['characters'] ) && count( $credit_role['characters'] ) > 0 ) {
				$output .= ' as <i>' . esc_html( $credit_role['characters'][0] ) . '</i>';
			}

			// Display a "show more" after XX results, only if a next result exists
			if ( $i === $max_results ) {
				$isset_next = isset( $item_results[ $sub_cat ][ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'see_all_start' ) : '';
			}

			if ( $i > $max_results && $i === $nb_total_items ) {
				$output .= $this->output_class->misc_layout( 'see_all_end' );
			}
			$i++;
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

		$nb_rows_click_more = $max_results;
		$i = 0;

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Person::get_all_credit_role( $nb_total_items )[ $sub_cat ] )
		);
		$output .= '(' . strval( $nb_total_items ) . ')';

		foreach ( $item_results[ $sub_cat ] as $credit_role ) {

			$output .= "\n\t\t\t\t " . $this->output_class->get_link(
				'internal_with_spinner',
				wp_nonce_url( Get_Options::get_popup_url( 'film', site_url() ) . '?mid=' . $credit_role['titleId'] ),
				$credit_role['titleName'],
			);

			if ( isset( $credit_role['year'] ) ) {
				$output .= ' (' . strval( $credit_role['year'] ) . ')';
			}

			if ( isset( $credit_role['characters'] ) && count( $credit_role['characters'] ) > 0 ) {
				$output .= ' as <i>' . $credit_role['characters'][0] . '</i>';

			}

			// Display a "show more" after XX results, only if a next result exists
			if ( $i === $max_results ) {
				$isset_next = isset( $item_results[ $sub_cat ][ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? "\t\t\t" . $this->output_class->misc_layout( 'see_all_start' ) : '';
			}

			if ( $i > $max_results && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'see_all_end' );
			}
			$i++;
		}

		return $output;
	}
}
