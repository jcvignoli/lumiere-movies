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
	 * @param string $nonce_token nonce created in Admin_Menu::get_admin_submenu()
	 * @see \Lumiere\Admin\Admin_Menu::call_admin_subclass() Calls this method
	 */
	protected function lum_submenu_start( Cache_Files_Management $cache_mngmt_class, string $nonce_token ): void {

		// Check the nonce, die() otherwise. In Admin_General trait.
		if ( $this->is_valid_nonce( nonce_token: $nonce_token, nonce_action: 'check_display_page' ) === false ) {
			wp_die( esc_html__( 'Invalid or missing nonce.', 'lumiere-movies' ), 'Lumière Movies', [ 'response' => 403 ] );
		}

		// First part of the menu
		$this->include_with_vars(
			'admin-menu-first-part',
			[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
		);

		// Make sure cache folder exists and is writable
		$cache_mngmt_class->lumiere_create_cache( true );

		// Show the vars if debug is activated.
		if ( $this->settings->get_admin_option( 'imdbdebug' ) !== null && $this->settings->get_admin_option( 'imdbdebug' ) === '1' ) {
			Debug::display_lum_vars( $this->settings->get_cache_options(), 'var_dump', null );
		}

		// Cache submenu.
		$this->include_with_vars(
			'cache/admin-cache-submenu',
			[ 'lum_that' => $this ], /** Add an array with vars to send in the template */
		);

		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';
		$subsection = isset( $_GET['subsection'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['subsection'] ) ) : '';

		if (
			strlen( $current_page ) > 0 && str_contains( $this->page_cache_option, $current_page ) === true
			&& strlen( $subsection ) === 0
		) {
			// Cache options menu.
			$size = size_format( $cache_mngmt_class->cache_getfoldersize( $this->settings->get_cache_option( 'imdbcachedir' ) ), 2 );
			$this->include_with_vars(
				'cache/admin-cache-options',
				[ 'size' => $size ], /** Add an array with vars to send in the template */
			);

		} elseif (
			strlen( $current_page ) > 0 && str_contains( $this->page_cache_option, $current_page ) === true
			&& $subsection === 'manage'
		) {
			// Cache managment menu.
			$this->include_with_vars(
				'cache/admin-cache-manage',
				[
					'cache_file_count'          => $cache_mngmt_class->cache_countfolderfiles( $this->settings->get_cache_option( 'imdbcachedir' ) ),
					'size_cache_total'          => $cache_mngmt_class->cache_getfoldersize( $this->settings->get_cache_option( 'imdbcachedir' ) ),
					'list_movie_cache'          => $cache_mngmt_class->get_imdb_object_per_cat( 'movie' ),
					'list_people_cached'        => $cache_mngmt_class->get_imdb_object_per_cat( 'people' ),
					'size_cache_pics'           => $cache_mngmt_class->cache_getfoldersize( $this->settings->get_cache_option( 'imdbphotoroot' ) ),
					'lum_that'                  => $this,
					'this_cache_manage_page'    => $this->page_cache_manage,
					'query_cache_info'          => $cache_mngmt_class->get_cache_query_info( $this->settings->get_cache_option( 'imdbcachedir' ) ),
				], /** Add an array with vars to send in the template */
			);
		}
	}
}

