<?php declare( strict_types = 1 );
/**
 * Cache options class
 * Child of Admin_Menu
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin\Submenu;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Admin\Cache_Tools;
use Lumiere\Admin\Admin_Menu;

/**
 * Display cache admin menu
 *
 * @since 4.0 Methods moved from this class into Cache_Tools, using templates instead of having templates here
 */
class Cache extends Admin_Menu {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Construct parent class
		parent::__construct();
	}

	/**
	 * Display the body
	 *
	 * @param Cache_Tools $cache_tools_class To create cache folder if it doesn't exists
	 * @param string $nonce nonce from Admin_Menu to be checked when doing $_GET checks
	 * @see \Lumiere\Admin\Admin_Menu::call_admin_subclass() Calls this method
	 */
	protected function lum_submenu_start( Cache_Tools $cache_tools_class, string $nonce ): void {

		// First part of the menu
		$this->include_with_vars(
			'admin-menu-first-part',
			[ $this ], /** Add an array with vars to send in the template */
			self::TRANSIENT_ADMIN,
		);

		// Make sure cache folder exists and is writable
		$cache_tools_class->lumiere_create_cache( true );

		// Show the vars if debug is activated.
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {
			$this->lumiere_display_vars( $this->imdb_cache_values, 'var_dump', null ); // Method in trait Admin_General.
		}

		// Cache submenu.
		$this->include_with_vars(
			'cache/admin-cache-submenu',
			[ $this ], /** Add an array with vars to send in the template */
			self::TRANSIENT_ADMIN,
		);

		if (
			wp_verify_nonce( $nonce, 'check_display_page' ) > 0
			&& isset( $_GET['page'] ) && str_contains( $this->page_cache_option, sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) === true
			&& ! isset( $_GET['subsection'] )
		) {

			// Cache options menu.
			$size = $this->lumiere_format_bytes( $cache_tools_class->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbcachedir'] ) );
			$this->include_with_vars(
				'cache/admin-cache-options',
				[ $size ], /** Add an array with vars to send in the template */
				self::TRANSIENT_ADMIN,
			);

		} elseif (
			isset( $_GET['page'] ) && str_contains( $this->page_cache_option, sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) === true
			&& isset( $_GET['subsection'] ) && $_GET['subsection'] === 'manage'
			&& wp_verify_nonce( $nonce, 'check_display_page' ) > 0
		) {
			// Cache managment menu.
			$this->include_with_vars(
				'cache/admin-cache-manage',
				[
					$cache_tools_class->lumiere_cache_countfolderfiles( $this->imdb_cache_values['imdbcachedir'] ), // nb of cached files
					$cache_tools_class->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbcachedir'] ), // cache total size
					$cache_tools_class->lumiere_get_movie_cache(), // list of movies cached
					$cache_tools_class->lumiere_get_people_cache(), // list of people cached
					$cache_tools_class->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbphotoroot'] ), // picture cache size
					$this,
					$this->page_cache_manage,
					$cache_tools_class->lumiere_get_cache_query_info( $this->imdb_cache_values['imdbcachedir'] ), // array of query files info
				], /** Add an array with vars to send in the template */
				self::TRANSIENT_ADMIN,
			);
		}
	}
}

