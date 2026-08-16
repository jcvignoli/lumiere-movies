<?php
/**
 * Class for displaying person module News.
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
 * Method to display news for persons
 *
 * @since 4.5 new class
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'news', list<array{ id: string, title: string|null, author: string|null, date: string, extUrl: string|null, extHomepageUrl: string|null, extHomepageLabel: string|null, textHtml: string|null, textText: string|null, thumbnailUrl: string|null }>, \Lumiere\Vendor\Imdb\Name>
 * @phan-suppress PhanGenericMissingParameters
 */
final class Person_News extends Parent_Module {

	/**
	 * Display the main module version
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$item_results = $imdb_class->$item_name();
		$nb_total_items = count( $item_results );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$nb_rows_click_more = $this->settings->get_person_option( 'number' )[ $item_name . '_number' ] !== null ? intval( $this->settings->get_person_option( 'number' )[ $item_name . '_number' ] ) : 5; /** max number of movies before breaking with "see all" */

		$item_may_plural = Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ];
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( $item_may_plural )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			// Display a "show more" after XX results.
			if ( $i === $nb_rows_click_more ) {
				$isset_next = isset( $item_results[ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $item_may_plural ) : '';
			}

			// URL.
			$output .= isset( $item_results[ $i ] ) && isset( $item_results[ $i ]['title'] ) && isset( $item_results[ $i ]['extUrl'] ) ? parent::get_external_url( $item_results[ $i ]['title'], $item_results[ $i ]['extUrl'] ) : $item_results[ $i ]['title'] ?? '';

			// Date.
			$date_time = strtotime( $item_results[ $i ]['date'] );
			if ( isset( $item_results[ $i ]['date'] ) && strlen( $item_results[ $i ]['date'] ) > 0 && $date_time !== false ) {
				$output .= ' (' . (string) wp_date( get_option( 'date_format' ), $date_time ) . ')';
			}

			// Text, limited in words.
			if ( isset( $item_results[ $i ]['textText'] ) && strlen( $item_results[ $i ]['textText'] ) > 0 ) {
				$output .= ' ' . wp_trim_words( $item_results[ $i ]['textText'], 50, ' [...]' );
			}

			// End of "click to show more".
			if ( $i > $nb_rows_click_more && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'click_more_end' );
			}

			// Breaking line.
			if ( $i < $nb_total_items - 1 ) {
				$output .= '<br>';
			}

		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$nb_rows_click_more = $this->settings->get_person_option( 'number' )[ $item_name . '_number' ] !== null ? intval( $this->settings->get_person_option( 'number' )[ $item_name . '_number' ] ) : 5; /** max number of movies before breaking with "see all" */

		$item_may_plural = Get_Options_Person::get_all_person_fields( $nb_total_items )[ $item_name ];
		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( $item_may_plural )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			// Display a "show more" after XX results.
			if ( $i === $nb_rows_click_more ) {
				$isset_next = isset( $item_results[ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $item_may_plural ) : '';
			}

			// URL.
			$output .= isset( $item_results[ $i ] ) && isset( $item_results[ $i ]['title'] ) && isset( $item_results[ $i ]['extUrl'] ) ? parent::get_external_url( $item_results[ $i ]['title'], $item_results[ $i ]['extUrl'] ) : $item_results[ $i ]['title'] ?? '';

			// Date.
			if ( isset( $item_results[ $i ]['date'] ) && strlen( $item_results[ $i ]['date'] ) > 0 ) {
				$date = strtotime( $item_results[ $i ]['date'] );
				$output .= $date !== false ? ' (' . (string) wp_date( get_option( 'date_format' ), $date ) . ')' : '';
			}

			// Text, limited in words.
			if ( isset( $item_results[ $i ]['textText'] ) && strlen( $item_results[ $i ]['textText'] ) > 0 ) {
				$output .= ' ' . wp_trim_words( $item_results[ $i ]['textText'], 50, ' [...]' );
			}

			// Display a "show more" after XX results.
			if ( $i === $nb_rows_click_more ) {
				$isset_next = isset( $item_results[ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $item_name ) : '';
			}

			// Breaking line.
			if ( $i < $nb_total_items - 1 ) {
				$output .= '<br>';
			}

			// End of "click to show more".
			if ( $i > $nb_rows_click_more && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'click_more_end' );
			}
		}
		return $output;
	}
}
