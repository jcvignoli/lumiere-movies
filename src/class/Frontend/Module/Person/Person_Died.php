<?php
/**
 * Class for displaying person module date of death.
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

use Lumiere\Frontend\Module\Parent_Module;

/**
 * Method to display date of death for person
 *
 * @since 4.5 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'died', array<'death', array{ day: int|null, month: string|null, mon: int|null, year: int|null, place: string|null, cause: string|null, status: 'ALIVE'|'DEAD'|'PRESUMED_DEAD'|null }>|array{}, \Lumiere\Vendor\Imdb\Name>
 * @phan-suppress PhanGenericMissingParameters
 */
final class Person_Died extends Parent_Module {

	/**
	 * Display the main module version
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$death = $imdb_class->$item_name();

		if ( $death === [] ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, [ 'death' => $death ], 0 );
		}

		if ( ! isset( $death['status'] ) || $death['status'] !== 'DEAD' ) {
			return '';
		}

		$death_day = isset( $death['day'] ) ? (string) $death['day'] . ' ' : '(' . __( '(day unknown)', 'lumiere-movies' ) . ') ';
		$month_tmp = strtotime( $death['month'] ?? '' );
		$death_month = $month_tmp !== false && $month_tmp > 0 ? date_i18n( 'F', intval( wp_date( 'm', $month_tmp ) ) ) . ' ' : '(' . __( '(month unknown)', 'lumiere-movies' ) . ') ';
		$death_year = isset( $death['year'] ) ? (string) $death['year'] : '(' . __( '(year unknown)', 'lumiere-movies' ) . ')';

		$output = $this->output_class->misc_layout( 'date_inside', '&#8224;&nbsp;' . esc_html__( 'Died on', 'lumiere-movies' ), esc_html( $death_day . $death_month . $death_year ) );

		if ( ( isset( $death['place'] ) ) && ( strlen( $death['place'] ) !== 0 ) ) {
			/** translators: 'in' like 'Died in' */
			$output .= ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $death['place'] );
		}

		if ( ( isset( $death['cause'] ) ) && ( strlen( $death['cause'] ) !== 0 ) ) {
			/** translators: 'cause' like 'Cause of death' */
			$output .= ', ' . esc_html__( 'cause', 'lumiere-movies' ) . ' ' . esc_html( $death['cause'] );
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {
		$output = '';
		$death = $item_results['death'] ?? null;

		if ( isset( $death['status'] ) && $death['status'] === 'DEAD' ) {

			$output .= "\n\t\t\t\t" . '<div id="death" class="lumiere_align_center lum_minus10">';

			$death_day = isset( $death['day'] ) ? (string) $death['day'] . ' ' : __( '(day unknown)', 'lumiere-movies' ) . ' ';
			$death_month = isset( $death['month'] ) ? date_i18n( 'F', (int) $death['month'] ) . ' ' : __( '(month unknown)', 'lumiere-movies' ) . ' ';
			$death_year = isset( $death['year'] ) ? (string) $death['year'] : __( '(year unknown)', 'lumiere-movies' );

			$output .= "\n\t\t\t\t\t" . '<span class="lum_results_section_subtitle">'
				. esc_html__( 'Died on', 'lumiere-movies' ) . '</span>'
				. esc_html( $death_day . $death_month . $death_year );

			if ( ( isset( $death['place'] ) ) && ( strlen( $death['place'] ) !== 0 ) ) {
				/** translators: 'in' like 'Died in' */
				$output .= ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $death['place'] );
			}

			if ( ( isset( $death['cause'] ) ) && ( strlen( $death['cause'] ) !== 0 ) ) {
				/** translators: 'cause' like 'Cause of death' */
				$output .= ' (' . esc_html( $death['cause'] . ')' );
			}

			$output .= "\n\t\t\t\t" . '</div>';
		}
		return $output;
	}
}
