<?php declare( strict_types = 1 );
/**
 * Class for displaying person module date of birth.
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
 * Method to display date of birth for person
 *
 * @since 4.5 new class
 */
class Person_Born extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param \Imdb\Name $person_class IMDbPHP title class
	 * @param 'born' $item_name The name of the item
	 */
	public function get_module( \Imdb\Name $person_class, string $item_name ): string {

		$birthday = $person_class->$item_name();

		if ( ! isset( $birthday ) || count( $birthday ) === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $birthday, $item_name );
		}

		$birthday_day = isset( $birthday['day'] ) && strlen( strval( $birthday['day'] ) ) > 0 ? strval( $birthday['day'] ) . ' ' : '(' . __( 'day unknown', 'lumiere-movies' ) . ') ';
		$month_tmp = strtotime( $birthday['month'] ?? '' );
		$birthday_month = $month_tmp !== false && $month_tmp > 0 ? date_i18n( 'F', intval( wp_date( 'm', $month_tmp ) ) ) . ' ' : '(' . __( 'month unknown', 'lumiere-movies' ) . ') ';
		$birthday_year = isset( $birthday['year'] ) && strlen( strval( $birthday['year'] ) ) > 0 ? strval( $birthday['year'] ) : '(' . __( 'year unknown', 'lumiere-movies' ) . ')';

		$output = $this->output_class->misc_layout( 'date_inside', '&#9788;&nbsp;' . esc_html__( 'Born on', 'lumiere-movies' ), esc_html( $birthday_day . $birthday_month . $birthday_year ) );

		if ( ( isset( $birthday['place'] ) ) && ( strlen( $birthday['place'] ) !== 0 ) ) {
			$output .= ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $birthday['place'] );
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param array<string, string> $birthday The array of birthday
	 * @phpstan-param array{ day?: int, month?: string, year?: int, place?: string } $birthday
	 * @param string $item_name
	 */
	public function get_module_popup( array $birthday, string $item_name ): string {

		$birthday_day = isset( $birthday['day'] ) && $birthday['day'] > 0 ? strval( $birthday['day'] ) . ' ' : '(' . __( 'day unknown', 'lumiere-movies' ) . ') ';
		$month_tmp = strtotime( $birthday['month'] ?? '' );
		$birthday_month = $month_tmp !== false && $month_tmp > 0 ? date_i18n( 'F', intval( wp_date( 'm', $month_tmp ) ) ) . ' ' : '(' . __( 'month unknown', 'lumiere-movies' ) . ') ';
		$birthday_year = isset( $birthday['year'] ) && $birthday['year'] > 0 ? strval( $birthday['year'] ) : '(' . __( 'year unknown', 'lumiere-movies' ) . ')';

		$output = $this->output_class->misc_layout( 'date_inside', '&#9788;&nbsp;' . esc_html__( 'Born on', 'lumiere-movies' ), esc_html( $birthday_day . $birthday_month . $birthday_year ) );

		if ( ( isset( $birthday['place'] ) ) && ( strlen( $birthday['place'] ) !== 0 ) ) {
			$output .= ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $birthday['place'] );
		}
		return $output;
	}
}
