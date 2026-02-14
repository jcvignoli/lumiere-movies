<?php declare( strict_types = 1 );
/**
 * Child class for displaying person data option selection
 * Child of Admin_Menu
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */

namespace Lumiere\Admin\Submenu;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Admin_Menu;
use Lumiere\Tools\Debug;

/**
 * Display data person options for data order and data selection
 *
 * @since 4.6 new class
 */
final class Dataperson extends Admin_Menu {

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
			Debug::display_lum_vars( $this->imdb_data_person_values, 'no_var_dump', null );
		}

		// Display submenu
		$this->include_with_vars(
			'data/admin-data-submenu',
			[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
		);

		if (
			wp_verify_nonce( $nonce, 'check_display_page' ) > 0
			&& isset( $_GET['page'] ) && str_contains( $this->page_data_person, sanitize_key( $_GET['page'] ) ) === true
			&& ! isset( $_GET['subsection'] )
		) {
			/**
			 * Data person template
			 * The template will retrieve the args. In parent class.
			 */
			$this->include_with_vars(
				'data/admin-data-person-display',
				[ 'lum_calling_class' => $this ], /** Add an array with vars to send in the template */
			);
		} elseif (
			wp_verify_nonce( $nonce, 'check_display_page' ) > 0
			&& isset( $_GET['page'] ) && str_contains( $this->page_data_person, sanitize_key( $_GET['page'] ) ) === true
			&& isset( $_GET['subsection'] ) && str_contains( $this->page_data_person_order, sanitize_key( $_GET['subsection'] ) )
		) {

			/**
			 * Data person order template
			 * The template will retrieve the args. In parent class.
			 */
			$this->include_with_vars(
				'data/admin-data-person-order',
				[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
			);
		}
	}
}

