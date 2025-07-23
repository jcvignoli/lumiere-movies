<?php declare( strict_types = 1 );
/**
 * Class for displaying person module date of death.
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

/**
 * Method to display date of death for person
 *
 * @since 4.5 new class
 */
final class Person_Died extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param \Lumiere\Vendor\Imdb\Name $person_class IMDbPHP title class
	 * @param 'died' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Name $person_class, string $item_name ): string {

		$death = $person_class->$item_name();

		if ( count( $death ) === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $death, $item_name );
		}

		if ( ! isset( $death['status'] ) || $death['status'] !== 'DEAD' ) {
			return '';
		}

		$death_day = isset( $death['day'] ) && strlen( strval( $death['day'] ) ) > 0 ? strval( $death['day'] ) . ' ' : '(' . __( '(day unknown)', 'lumiere-movies' ) . ') ';
		$month_tmp = strtotime( $death['month'] ?? '' );
		$death_month = $month_tmp !== false && $month_tmp > 0 ? date_i18n( 'F', intval( wp_date( 'm', $month_tmp ) ) ) . ' ' : '(' . __( '(month unknown)', 'lumiere-movies' ) . ') ';
		$death_year = isset( $death['year'] ) && strlen( strval( $death['year'] ) ) > 0 ? strval( $death['year'] ) : '(' . __( '(year unknown)', 'lumiere-movies' ) . ')';

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
	 *
	 * @param array<string, string|int> $death The array of death
	 * @phpstan-param array{ day?: int, month?: int, year?: int, place?: string, cause?: string, status?: string|'DEAD' } $death
	 * @param string $item_name
	 */
	public function get_module_popup( array $death, string $item_name ): string {

		$output = '';

		if ( isset( $death['status'] ) && $death['status'] === 'DEAD' ) {

			$output .= "\n\t\t\t\t" . '<div id="death" class="lumiere_align_center"><font size="-1">';

			$death_day = isset( $death['day'] ) ? (string) $death['day'] . ' ' : __( '(day unknown)', 'lumiere-movies' ) . ' ';
			$death_month = isset( $death['month'] ) ? date_i18n( 'F', $death['month'] ) . ' ' : __( '(month unknown)', 'lumiere-movies' ) . ' ';
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

			$output .= "\n\t\t\t\t" . '</font></div>';
		}
		return $output;
	}
}
