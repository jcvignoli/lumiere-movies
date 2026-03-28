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
	 * @param string $nonce_token nonce created in Admin_Menu::get_admin_submenu()
	 * @see \Lumiere\Admin\Admin_Menu::call_admin_subclass() Calls this method
	 */
	protected function lum_submenu_start( \Lumiere\Admin\Cache\Cache_Files_Management $cache_mngmt_class, string $nonce_token ): void {

		// Check the nonce, die() otherwise. In Admin_General trait.
		if ( $this->is_valid_nonce( nonce_token: $nonce_token, nonce_action: 'check_display_page' ) === false ) {
			wp_die( esc_html__( 'Invalid or missing nonce.', 'lumiere-movies' ), 'Lumière Movies', [ 'response' => 403 ] );
		}

		// First part of the menu
		$this->include_with_vars(
			'admin/admin-menu-first-part',
			[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
		);

		// Show the vars if debug is activated.
		if (
			$this->settings->get_admin_option( 'imdbdebug' ) !== null && $this->settings->get_admin_option( 'imdbdebug' ) === '1'
		) {
			Debug::display_lum_vars( $this->settings->get_person_options(), 'no_var_dump', null );
		}

		// Display submenu
		$this->include_with_vars(
			'data/admin-data-allmenu',
			[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
		);

		$current_page = isset( $_GET['page'] ) ? sanitize_key( strval( $_GET['page'] ) ) : '';
		$subsection = isset( $_GET['subsection'] ) ? sanitize_key( strval( $_GET['subsection'] ) ) : '';

		if (
			strlen( $current_page ) > 0 && str_contains( $this->page_data_person, $current_page ) === true
			&& strlen( $subsection ) === 0
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
			strlen( $current_page ) > 0 && str_contains( $this->page_data_person, $current_page ) === true
			&& strlen( $subsection ) > 0 && str_contains( $this->page_data_person_order, $subsection )
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

