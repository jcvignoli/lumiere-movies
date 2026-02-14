<?php declare( strict_types = 1 );
/**
 * Child class for displaying movie data option selection
 * Child of Admin_Menu
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.1
 * @package       lumieremovies
 */

namespace Lumiere\Admin\Submenu;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Admin_Menu;
use Lumiere\Admin\Copy_Templates\Detect_New_Theme;
use Lumiere\Tools\Debug;
use Lumiere\Config\Get_Options_Movie;

/**
 * Display movie data options for taxonomy, data order and data selection
 *
 * @since 4.0 Using templates file instead of the HTML code here
 * @since 4.6 Renamed to Datamovie class and splitted with new Dataperson class
 * @see \Lumiere\Admin\Admin_Menu for templates copy, if put it here the transiant is not passed to { @link \Lumiere\Admin\Copy_Templates\Copy_Theme }
 */
final class Datamovie extends Admin_Menu {

	/**
	 * Display the body
	 *
	 * @param \Lumiere\Admin\Cache\Cache_Files_Management $cache_mngmt_class Not utilised in this class, but needed in some other Submenu classes
	 * @param string $nonce nonce from Admin_Menu to be checked when doing $_GET checks
	 * @see \Lumiere\Admin\Admin_Menu::call_admin_subclass() Calls this method
	 */
	protected function lum_submenu_start( \Lumiere\Admin\Cache\Cache_Files_Management $cache_mngmt_class, string $nonce ): void {

		// First part of the menu
		$this->include_with_vars(
			'admin/admin-menu-first-part',
			[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
		);

		// Show the vars if debug is activated.
		if (
			isset( $this->imdb_admin_values['imdbdebug'] ) && $this->imdb_admin_values['imdbdebug'] === '1'
		) {
			Debug::display_lum_vars( $this->imdb_data_values, 'no_var_dump', null );
		}

		// Display submenu
		$this->include_with_vars(
			'data/admin-data-submenu',
			[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
		);

		if (
			wp_verify_nonce( $nonce, 'check_display_page' ) > 0
			&& isset( $_GET['page'] ) && str_contains( $this->page_data_movie, sanitize_key( $_GET['page'] ) ) === true
			&& ! isset( $_GET['subsection'] )
		) {

			/**
			 * Display data template
			 * The template will retrieve the args. In parent class.
			 */
			$this->include_with_vars(
				'data/admin-data-movie-display',
				[
					'lum_calling_class' => $this,
					'lum_items_people'  => $this->get_display_select_options()[0],
					'lum_comments_fields' => $this->get_display_select_options()[1],
				], /** Add an array with vars to send in the template */
			);

		} elseif (
			isset( $_GET['page'] ) && str_contains( $this->page_data_movie_order, sanitize_key( $_GET['page'] ) ) === true
			&& isset( $_GET['subsection'] ) && str_contains( $this->page_data_movie_order, sanitize_key( $_GET['subsection'] ) )
			&& wp_verify_nonce( $nonce, 'check_display_page' ) > 0
		) {

			/**
			 * Display data order
			 * The template will retrieve the args. In parent class.
			 */
			$this->include_with_vars(
				'data/admin-data-movie-order',
				[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
			);

		} elseif (
			isset( $_GET['page'] ) && str_contains( $this->page_data_movie_taxo, sanitize_key( $_GET['page'] ) ) === true
			&& isset( $_GET['subsection'] ) && str_contains( $this->page_data_movie_taxo, sanitize_key( $_GET['subsection'] ) )
			&& wp_verify_nonce( $nonce, 'check_display_page' ) > 0
		) {

			/**
			 * Taxonomy data template
			 * The template will retrieve the args. In parent class.
			 */
			$this->include_with_vars(
				'data/admin-data-movie-taxonomy',
				[
					'lum_that'               => $this,
					'lum_all_taxo_elements'  => $this->get_taxo_fields(),
					'lum_fields_updated'     => ( new Detect_New_Theme() )->search_new_update(),
					'lum_current_admin_page' => $this->page_data_movie_taxo . '&taxotype=',
				], /** Add an array with vars to send in the template */
			);
		}
	}

	/**
	 * Get the fields for taxonomy selection
	 *
	 * @return array<string, string>
	 */
	private function get_taxo_fields(): array {
		$all_taxo_elements = Get_Options_Movie::get_list_fields_taxo();
		asort( $all_taxo_elements );
		return $all_taxo_elements;
	}

	/**
	 * Build the options for selection display
	 *
	 * @return array<int, array<string>>
	 */
	private function get_display_select_options(): array {

		// Merge the list of items and people with two extra lists
		$array_full = Get_Options_Movie::get_all_fields();

		// Sort the array to display in alphabetical order
		asort( $array_full );

		// Add the comments to the arrays of items and people
		return [ $array_full, Get_Options_Movie::get_items_details_comments() ];
	}
}

