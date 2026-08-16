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
 * @since 4.8.2 Using interface, refactored
 *
 * @phan-type AwardsTitles = array{ titleId: string, titleName: string, titleNote: string|null, titleFullImageUrl: string|null, titleThumbImageUrl: string|null }
 * @phan-type AwardsShort = array{ awardYear: int|null, awardWinner: bool, awardCategory: string|null, awardName: string|null, awardTitles: list<AwardsTitles>, awardNotes: string|null, awardOutcome: string|null }
 * @phan-type AwardsAll = array<string, list<AwardsShort>|array{win?: int, nom?: int}>
 * @extends Parent_Module<'award', AwardsAll, \Lumiere\Vendor\Imdb\Name>
 */
final class Person_Award extends Parent_Module {

	/**
	 * Display the main module version
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		/** @var AwardsAll $item_results */
		$item_results   = $imdb_class->$item_name();
		$nb_total_items = $this->calculate_total_items( $item_results );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$person_number_option      = $this->settings->get_person_option( 'number' )[ $item_name . '_number' ] ?? null;
		$nb_rows_display_clickmore = $person_number_option !== null ? intval( $person_number_option ) : 5; /** max number of movies before breaking with "see all" */

		return $this->render_award_list(
			$item_name,
			$item_results,
			$nb_total_items,
			$nb_rows_display_clickmore,
			// The function will display the awards films with clickable links.
			fn( array $title ): string => parent::get_popup_film_byid( $title['titleName'], $title['titleId'] )
		);
	}

	/**
	 * Display the Popup version of the module
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {
		$nb_rows_display_clickmore = 5;

		return $this->render_award_list(
			$item_name,
			$item_results,
			$nb_total_items,
			$nb_rows_display_clickmore,
			// The function will display the awards films with no links.
			fn( array $title ): string => $title['titleName'] . $title['titleId']
		);
	}

	/**
	 * Calculate total valid items from results array.
	 *
	 * @param AwardsAll $item_results
	 * @return int
	 */
	private function calculate_total_items( array $item_results ): int {
		$nb_total_items = 0;
		$minus_one      = 0;
		$minus_two      = 0;

		foreach ( $item_results as $array ) {
			$nb_total_items += count( $array );
			if ( isset( $array['win'] ) ) {
				$minus_one = 1;
			}
			if ( isset( $array['nom'] ) ) {
				$minus_two = 1;
			}
		}

		return $nb_total_items - $minus_one - $minus_two;
	}

	/**
	 * Shared rendering pipeline for both standard and popup views.
	 *
	 * @param string $item_name
	 * @param AwardsAll $item_results
	 * @param int $nb_total_items
	 * @param int $nb_rows_display_clickmore
	 * @param callable(AwardsTitles): string $awards_title_call
	 * @return string
	 */
	private function render_award_list(
		string $item_name,
		array $item_results,
		int $nb_total_items,
		int $nb_rows_display_clickmore,
		callable $awards_title_call
	): string {
		$item_may_plural = Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ];
		$title           = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( $item_may_plural )
		);

		$overall_loop = 0;
		$total_awards = '';
		$output       = '';

		foreach ( $item_results as $array ) {
			$count_sub = count( $array );

			for ( $i = 0; $i < $count_sub; $i++ ) {

				// Display a "show more" after XX results
				if ( $overall_loop === $nb_rows_display_clickmore ) {
					$isset_next = isset( $array[ $i + 1 ] );
					$output    .= $isset_next ? $this->output_class->misc_layout( 'click_more_start', $item_may_plural ) : '';
				}

				// if $array[ $i ]['awardName'], means it's AwardsShort
				$award = isset( $array[ $i ]['awardName'] )
					? $this->build_award_item_markup( $array[ $i ], $awards_title_call )
					: '';

				$output .= $overall_loop < ( $nb_total_items - 1 )
					? $this->output_class->misc_layout( 'numbered_list', (string) ( $overall_loop + 1 ), '', $award )
					: '';

				if ( $overall_loop > $nb_rows_display_clickmore && $nb_total_items > 0 && $overall_loop === $nb_total_items ) {
					$total_awards .= isset( $array['win'], $array['nom'] )
						/* Translators: %1s and %2s are numbers */
						? '<i>' . wp_sprintf( __( 'Won %1$1s awards and was nominated %2$2s times.', 'lumiere-movies' ), $array['win'], $array['nom'] ) . '</i>'
						: '';
					$output       .= $this->output_class->misc_layout( 'click_more_end' );
				}

				$overall_loop++;
			}
		}

		return $title . $total_awards . $output;
	}

	/**
	 * Construct string formatting for individual award entries.
	 *
	 * @param AwardsShort $award_data
	 * @param callable(AwardsTitles): string $awards_title_call
	 * @return string
	 */
	private function build_award_item_markup( array $award_data, callable $awards_title_call ): string {
		$output = $award_data['awardName'];

		if ( isset( $award_data['awardYear'] ) ) {
			$output .= ' (' . (string) $award_data['awardYear'] . ')';
		}
		if ( isset( $award_data['awardCategory'] ) ) {
			$output .= ' &ldquo;' . $award_data['awardCategory'] . '&rdquo;';
		}
		if ( isset( $award_data['awardNotes'] ) ) {
			$output .= ' &ldquo;' . $award_data['awardNotes'] . '&rdquo;';
		}
		if ( isset( $award_data['awardOutcome'] ) ) {
			$output .= ' <i>' . $award_data['awardOutcome'] . '</i>';
		}
		if ( isset( $award_data['awardTitles'][0]['titleName'] ) ) {
			$film_link = $awards_title_call( $award_data['awardTitles'][0] );
			$output   .= ' <i>' . __( 'for', 'lumiere-movies' ) . ' ' . $film_link . '</i>';
		}

		return $output ?? '';
	}
}
