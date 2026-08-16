<?php
/**
 * Class for displaying person module award.
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
 * Method to display award for person
 *
 * @since 4.5 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'award', array<array-key, mixed>, \Lumiere\Vendor\Imdb\Name>
 */
final class Person_Award extends Parent_Module {

	/**
	 * Display the main module version
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$item_results = $imdb_class->$item_name();
		$nb_total_items = 0;
		$minus_one = 0;
		$minus_two = 0;
		foreach ( $item_results as $item_title => $array ) { // erase that number if relevant.
			$nb_total_items += count( $array );
			if ( isset( $array['win'] ) ) {
				$minus_one = 1;
			}
			if ( isset( $array['nom'] ) ) {
				$minus_two = 1;
			}
		}
		$nb_total_items = $nb_total_items - $minus_one - $minus_two;

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$nb_rows_display_clickmore = $this->settings->get_person_option( 'number' )[ $item_name . '_number' ] !== null ? intval( $this->settings->get_person_option( 'number' )[ $item_name . '_number' ] ) : 5; /** max number of movies before breaking with "see all" */

		$item_may_plural = Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ];
		$title = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( $item_may_plural )
		);

		$overall_loop = 0;
		$total_awards = '';
		$output = '';
		foreach ( $item_results as $item_title => $array ) {
			$count_sub = count( $array );

			for ( $i = 0; $i < $count_sub; $i++ ) {

				// Display a "show more" after XX results
				if ( $overall_loop === $nb_rows_display_clickmore ) {
					$isset_next = isset( $array[ $i + 1 ] ) ? true : false;
					$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $item_may_plural ) : '';
				}

				$award = $array[ $i ]['awardName'] ?? '';
				$award .= isset( $array[ $i ]['awardYear'] ) ? ' (' . strval( $array[ $i ]['awardYear'] ) . ')' : '';
				$award .= isset( $array[ $i ]['awardCategory'] ) ? ' &ldquo;' . $array[ $i ]['awardCategory'] . '&rdquo;' : '';
				$award .= isset( $array[ $i ]['awardNote'] ) ? ' &ldquo;' . $array[ $i ]['awardNote'] . '&rdquo;' : '';
				$award .= isset( $array[ $i ]['awardOutcome'] ) ? ' <i>' . $array[ $i ]['awardOutcome'] . '</i>' : '';
				if ( isset( $array[ $i ]['awardTitles'], $array[ $i ]['awardTitles'][0], $array[ $i ]['awardTitles'][0]['titleName'] ) ) {
					$award .= ' <i>' . __( 'for', 'lumiere-movies' ) . ' ' . parent::get_popup_film_byid( $array[ $i ]['awardTitles'][0]['titleName'], $array[ $i ]['awardTitles'][0]['titleId'] ) . '</i>';
				}

				$output .= $overall_loop < ( $nb_total_items - 1 ) ? $this->output_class->misc_layout( 'numbered_list', strval( $overall_loop + 1 ), '', $award ) : '';

				if ( $overall_loop > $nb_rows_display_clickmore && $nb_total_items > 0 && $overall_loop === $nb_total_items ) {
					/* Translators: %1s and %2s are numbers */
					$total_awards .= isset( $array['win'], $array['nom'] ) ? '<i>' . wp_sprintf( __( 'Won %1$1s awards and was nominated %2$2s times.', 'lumiere-movies' ), $array['win'], $array['nom'] ) . '</i>' : '';
					$output .= $this->output_class->misc_layout( 'click_more_end' );
				}
				$overall_loop++;
			}
		}
		return $title . $total_awards . $output;
	}

	/**
	 * Display the Popup version of the module
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$nb_rows_display_clickmore = 5;
		$item_may_plural = Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ];
		$title = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( $item_may_plural )
		);

		$overall_loop = 0;
		$total_awards = '';
		$output = '';
		foreach ( $item_results as $item_title => $array ) {
			$count_sub = count( $array );

			for ( $i = 0; $i < $count_sub; $i++ ) {

				// Display a "show more" after XX results
				if ( $overall_loop === $nb_rows_display_clickmore ) {
					$isset_next = isset( $array[ $i + 1 ] ) ? true : false;
					$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $item_may_plural ) : '';
				}

				$award = $array[ $i ]['awardName'] ?? '';
				$award .= isset( $array[ $i ]['awardYear'] ) ? ' (' . strval( $array[ $i ]['awardYear'] ) . ')' : '';
				$award .= isset( $array[ $i ]['awardCategory'] ) ? ' &ldquo;' . $array[ $i ]['awardCategory'] . '&rdquo;' : '';
				$award .= isset( $array[ $i ]['awardNote'] ) ? ' &ldquo;' . $array[ $i ]['awardNote'] . '&rdquo;' : '';
				$award .= isset( $array[ $i ]['awardOutcome'] ) ? ' <i>' . $array[ $i ]['awardOutcome'] . '</i>' : '';
				if ( isset( $array[ $i ]['awardTitles'], $array[ $i ]['awardTitles'][0], $array[ $i ]['awardTitles'][0]['titleName'] ) ) {
					$award .= ' <i>' . __( 'for', 'lumiere-movies' ) . ' ' . parent::get_popup_film_byid( $array[ $i ]['awardTitles'][0]['titleName'], $array[ $i ]['awardTitles'][0]['titleId'] ) . '</i>';
				}

				$output .= $overall_loop < ( $nb_total_items - 1 ) ? $this->output_class->misc_layout( 'numbered_list', strval( $overall_loop + 1 ), '', $award ) : '';

				if ( $overall_loop > $nb_rows_display_clickmore && $nb_total_items > 0 && $overall_loop === $nb_total_items ) {
					/* Translators: %1s and %2s are numbers */
					$total_awards .= isset( $array['win'], $array['nom'] ) ? '<i>' . wp_sprintf( __( 'Won %1$1s awards and was nominated %2$2s times.', 'lumiere-movies' ), $array['win'], $array['nom'] ) . '</i>' : '';
					$output .= $this->output_class->misc_layout( 'click_more_end' );
				}
				$overall_loop++;
			}
		}
		return $title . $total_awards . $output;
	}
}
