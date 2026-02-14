<?php declare( strict_types = 1 );
/**
 * Cache options class
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

use Lumiere\Admin\Cache\Cache_Files_Management;
use Lumiere\Admin\Admin_Menu;
use Lumiere\Tools\Debug;

/**
 * Display cache admin menu
 *
 * @since 4.0 Methods moved from this class into Cache_Files_Management, using templates instead of having templates here
 */
final class Cache extends Admin_Menu {

	/**
	 * Display the body
	 *
	 * @param Cache_Files_Management $cache_mngmt_class To create cache folder if it doesn't exists
	 * @param string $nonce nonce from Admin_Menu to be checked when doing $_GET checks
	 * @see \Lumiere\Admin\Admin_Menu::call_admin_subclass() Calls this method
	 */
	protected function lum_submenu_start( Cache_Files_Management $cache_mngmt_class, string $nonce ): void {

		// First part of the menu
		$this->include_with_vars(
			'admin-menu-first-part',
			[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
		);

		// Make sure cache folder exists and is writable
		$cache_mngmt_class->lumiere_create_cache( true );

		// Show the vars if debug is activated.
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {
			Debug::display_lum_vars( $this->imdb_cache_values, 'var_dump', null );
		}

		// Cache submenu.
		$this->include_with_vars(
			'cache/admin-cache-submenu',
			[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
		);

		if (
			wp_verify_nonce( $nonce, 'check_display_page' ) > 0
			&& isset( $_GET['page'] ) && str_contains( $this->page_cache_option, sanitize_text_field( wp_unslash( strval( $_GET['page'] ) ) ) ) === true
			&& ! isset( $_GET['subsection'] )
		) {

			// Cache options menu.
			$size = $this->lumiere_format_bytes( $cache_mngmt_class->cache_getfoldersize( $this->imdb_cache_values['imdbcachedir'] ) );
			$this->include_with_vars(
				'cache/admin-cache-options',
				[ 'size' => $size ], /** Add an array with vars to send in the template */
			);

		} elseif (
			isset( $_GET['page'] ) && str_contains( $this->page_cache_option, sanitize_text_field( wp_unslash( strval( $_GET['page'] ) ) ) ) === true
			&& isset( $_GET['subsection'] ) && $_GET['subsection'] === 'manage'
			&& wp_verify_nonce( $nonce, 'check_display_page' ) > 0
		) {
			// Cache managment menu.
			$this->include_with_vars(
				'cache/admin-cache-manage',
				[
					'cache_file_count'          => $cache_mngmt_class->cache_countfolderfiles( $this->imdb_cache_values['imdbcachedir'] ),
					'size_cache_total'          => $cache_mngmt_class->cache_getfoldersize( $this->imdb_cache_values['imdbcachedir'] ),
					'list_movie_cache'          => $cache_mngmt_class->get_imdb_object_per_cat( 'movie' ),
					'list_people_cached'        => $cache_mngmt_class->get_imdb_object_per_cat( 'people' ),
					'size_cache_pics'           => $cache_mngmt_class->cache_getfoldersize( $this->imdb_cache_values['imdbphotoroot'] ),
					'lum_that'                  => $this,
					'this_cache_manage_page'    => $this->page_cache_manage,
					'query_cache_info'          => $cache_mngmt_class->get_cache_query_info( $this->imdb_cache_values['imdbcachedir'] ),
				], /** Add an array with vars to send in the template */
			);
		}
	}
}

