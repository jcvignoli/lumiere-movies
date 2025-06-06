<?php declare( strict_types = 1 );
/**
 * Main options class
 * Child of Admin_Menu
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */

namespace Lumiere\Admin\Submenu;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Config\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Cache\Cache_Files_Management;
use Lumiere\Admin\Admin_Menu;
use Lumiere\Tools\Debug;
use Lumiere\Config\Get_Options;

/**
 * Display Main options menu
 * @since 4.0 Using templates instead of having templates here
 */
final class Main extends Admin_Menu {

	/**
	 * Pages name
	 */
	private const PAGES_NAMES = [
		'menu_first'        => 'admin-menu-first-part',
		'menu_submenu'      => 'main/admin-main-submenu',
		'main_options'      => 'main/admin-main-layout',
		'advanced_options'  => 'main/admin-main-advanced',
	];

	/**
	 * Display the options
	 *
	 * @param Cache_Files_Management $cache_mngmt_class To create cache folder if it doesn't exists
	 * @param string $nonce nonce from Admin_Menu to be checked when doing $_GET checks
	 * @see \Lumiere\Admin\Admin_Menu::call_admin_subclass() Calls this method
	 */
	protected function lum_submenu_start( Cache_Files_Management $cache_mngmt_class, string $nonce ): void {

		// First part of the menu.
		$this->include_with_vars(
			self::PAGES_NAMES['menu_first'],
			[ $this ], /** Add an array with vars to send in the template */
			self::TRANSIENT_ADMIN,
		);

		// Create the cache if it doesn't exists.
		$cache_mngmt_class->lumiere_create_cache( true );

		// Show the vars if debug is activated.
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {
			Debug::display_lum_vars( $this->imdb_admin_values, 'no_var_dump', null );
		}

		// Submenu.
		$this->include_with_vars(
			self::PAGES_NAMES['menu_submenu'],
			[ Get_Options::LUM_PICS_URL, $this->page_main_base, $this->page_main_advanced ], /** Add an array with vars to send in the template */
			self::TRANSIENT_ADMIN,
		);

		// The body.
		if (
			// Main options.
			wp_verify_nonce( $nonce, 'check_display_page' ) > 0
			&& isset( $_GET['page'] ) && str_contains( $this->page_main_base, sanitize_text_field( wp_unslash( strval( $_GET['page'] ) ) ) ) === true
			&& ! isset( $_GET['subsection'] )
		) {
			$this->include_with_vars(
				self::PAGES_NAMES['main_options'],
				[ Get_Options::LUM_PICS_URL ], /** Add an array with vars to send in the template */
				self::TRANSIENT_ADMIN,
			);

		} elseif (
			// Advanced options.
			isset( $_GET['page'] ) && str_contains( $this->page_main_advanced, sanitize_text_field( wp_unslash( strval( $_GET['page'] ) ) ) ) === true
			&& isset( $_GET['subsection'] ) && $_GET['subsection'] === 'advanced'
			&& wp_verify_nonce( $nonce, 'check_display_page' ) > 0
		) {
			$this->include_with_vars(
				self::PAGES_NAMES['advanced_options'],
				[], /** Add an array with vars to send in the template */
				self::TRANSIENT_ADMIN,
			);
		}
	}
}

