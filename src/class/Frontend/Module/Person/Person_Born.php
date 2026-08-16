<?php
/**
 * Class for displaying person module date of birth.
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
 * Method to display date of birth for person
 *
 * @since 4.5 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'born', array{day: int|null, month: string|null, mon: int|null, year: int|null, place: string|null }|array{}, \Lumiere\Vendor\Imdb\Name>
 * @phan-suppress PhanGenericMissingParameters
 */
final class Person_Born extends Parent_Module {

	/**
	 * Display the main module version
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$birthday = $imdb_class->$item_name();

		if ( ! isset( $birthday ) || $birthday === [] ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $birthday, 0 );
		}

		$birthday_day = isset( $birthday['day'] ) ? (string) $birthday['day'] . ' ' : '(' . __( 'day unknown', 'lumiere-movies' ) . ') ';
		$month_tmp = strtotime( $birthday['month'] ?? '' );
		$birthday_month = $month_tmp !== false && $month_tmp > 0 ? date_i18n( 'F', intval( wp_date( 'm', $month_tmp ) ) ) . ' ' : '(' . __( 'month unknown', 'lumiere-movies' ) . ') ';
		$birthday_year = isset( $birthday['year'] ) ? (string) $birthday['year'] : '(' . __( 'year unknown', 'lumiere-movies' ) . ')';

		$output = $this->output_class->misc_layout( 'date_inside', '&#9788;&nbsp;' . esc_html__( 'Born on', 'lumiere-movies' ), esc_html( $birthday_day . $birthday_month . $birthday_year ) );

		if ( ( isset( $birthday['place'] ) ) && ( strlen( $birthday['place'] ) !== 0 ) ) {
			$output .= ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $birthday['place'] );
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
		$get_birthday = array_filter( $item_results, fn( $item_results ) => ( $item_results !== '' ) );

		if ( count( $get_birthday ) > 0 ) {
			$output .= "\n\t\t\t\t" . '<div id="birth" class="lumiere_align_center lum_minus10">';

			$birthday_day = isset( $get_birthday['day'] ) ? strval( $get_birthday['day'] ) . ' ' : '(' . __( 'day unknown', 'lumiere-movies' ) . ') ';
			$birthday_month = isset( $get_birthday['month'] ) ? date_i18n( 'F', intval( $get_birthday['month'] ) ) . ' ' : '(' . __( 'month unknown', 'lumiere-movies' ) . ') ';
			$birthday_year = isset( $get_birthday['year'] ) ? strval( $get_birthday['year'] ) : '(' . __( 'year unknown', 'lumiere-movies' ) . ')';

			$output .= "\n\t\t\t\t\t" . '<span class="lum_results_section_subtitle">'
				. esc_html__( 'Born on', 'lumiere-movies' ) . '</span>'
				. esc_html( $birthday_day . $birthday_month . $birthday_year );

			if ( isset( $get_birthday['place'] ) ) {
				/** translators: 'in' like 'Born in' */
				$output .= ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $get_birthday['place'] );
			}

			$output .= "\n\t\t\t\t" . '</div>';
		}
		return $output;
	}
}
