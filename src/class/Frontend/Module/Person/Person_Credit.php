<?php
/**
 * Class for displaying persons module Credit.
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
use Lumiere\Config\Settings_Popup;
use Lumiere\Frontend\Module\Parent_Module;
use Lumiere\Tools\Data;

/**
 * Method to display Credit for person
 * Retrieves all movies that are available in \Lumiere\Config\Settings_Person::credits_role_all()
 * Uses {@see \Lumiere\Tools\Data::mb_ucfirst()} method for a translated first character which could be accentuated in other languages
 *
 * @since 4.5 new class
 * @since 4.8.2 Using interface
 *
 * @phpstan-type SubCatList 'actor'|'actress'|'archiveFootage'|'artDepartment'|'assistantDirector'|'bio'|'born'|'cinematographer'| 'costume_department'|'costume_supervisor'|'died'|'director'|'editor'|'miscellaneous'|'pic'|'producer'|'self'|'showrunner'| 'soundtrack'|'stunts'|'thanks'|'title'|'writer'
 * @extends Parent_Module<SubCatList, array<string, list<array{ titleId: string, titleName: string, titleType: string, year: int|null, endYear: int|null, characters: list<string>|null, jobs: list<string>, titleFullImageUrl: string|null, titleThumbImageUrl: string|null }>>, \Lumiere\Vendor\Imdb\Name>
 * @phan-suppress PhanGenericMissingParameters
 */
final class Person_Credit extends Parent_Module {

	/**
	 * Display the main module version
	 * $item_name is the subcategory
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {

		$item_results = $imdb_class->credit();
		$nb_rows_click_more = $this->settings->get_person_option( 'number' . $item_name . '_number' ) !== null ? intval( $this->settings->get_person_option( 'number' . $item_name . '_number' ) ) : 9; /** max number of movies before breaking with "see all" */

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, 1 /* not used in get_module_popup() method */ );
		}

		$output = '';
		$loops = 0;

		foreach ( Settings_Popup::PERSON_ALL_ROLES as $module ) {

			$nb_total_items = isset( $item_results[ $module ] ) ? count( $item_results[ $module ] ) : 0;

			if ( $nb_total_items === 0 ) {
				continue;
			}

			if ( $loops > 0 ) {
				$output .= '<br>';
			}

			$output .= $this->output_class->misc_layout(
				'frontend_subtitle_item',
				Data::mb_ucfirst( Get_Options_Person::get_all_credit_role( $nb_total_items )[ $module ] ) // Can start with special charas, so use homemade ucfirst that behaves like mb_ucfirst().
			);

			for ( $i = 0; $i < $nb_total_items; $i++ ) {

				if ( ! isset( $item_results[ $module ][ $i ] ) ) {
					continue;
				}

				$output .= parent::get_popup_film_byid( $item_results[ $module ][ $i ]['titleName'], $item_results[ $module ][ $i ]['titleId'] );

				if ( isset( $item_results[ $module ][ $i ]['year'] ) ) {
					$output .= ' (' . strval( $item_results[ $module ][ $i ]['year'] ) . ') ';
				}

				if ( isset( $item_results[ $module ][ $i ]['characters'] ) && count( $item_results[ $module ][ $i ]['characters'] ) > 0 ) {
					/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable (I don't get the error) */
					$output .= 'as <i>' . $item_results[ $module ][ $i ]['characters'][0] . '</i> ';
				}

				// Display a "show more" after XX results, only if a next result exists.
				if ( $i === $nb_rows_click_more ) {
					$isset_next = isset( $item_results[ $module ][ $i + 1 ] ) ? true : false;
					$output .= $isset_next === true ? "\t\t\t" . $this->output_class->misc_layout( 'see_all_start' ) : '';
				}

				if ( $i > $nb_rows_click_more && $i === ( $nb_total_items - 1 ) ) {
					$output .= $this->output_class->misc_layout( 'see_all_end' );
				}
			}
			$loops++;
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 * Array of results is sorted by column
	 * $item_name is the subcategory
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$nb_rows_click_more = 9; /** max number of movies before breaking with "see all" */
		$nb_total_items = count( $item_results[ $item_name ] ?? [] );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			Data::mb_ucfirst( Get_Options_Person::get_all_credit_role( $nb_total_items )[ $item_name ] ) // Can start with special charas, so use homemade ucfirst that behaves like mb_ucfirst().
		);

		if ( $nb_total_items > $nb_rows_click_more ) {
			$output .= '(' . strval( $nb_total_items ) . ')'; // Show the total number found right after the title.
		}

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= parent::get_film_url( $item_results[ $item_name ][ $i ]['titleId'], $item_results[ $item_name ][ $i ]['titleName'] );

			if ( isset( $item_results[ $item_name ][ $i ]['year'] ) ) {
				$output .= ' (' . strval( $item_results[ $item_name ][ $i ]['year'] ) . ')';
			}

			if ( isset( $item_results[ $item_name ][ $i ]['characters'][0] ) ) {
				$output .= ' as <i>' . $item_results[ $item_name ][ $i ]['characters'][0] . '</i>';
			}

			// Display a "show more" after XX results, only if a next result exists.
			if ( $i === $nb_rows_click_more ) {
				$isset_next = isset( $item_results[ $item_name ][ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? "\t\t\t" . $this->output_class->misc_layout( 'see_all_start' ) : '';
			}

			if ( $i > $nb_rows_click_more && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'see_all_end' );
			}
		}
		return $output;
	}
}
