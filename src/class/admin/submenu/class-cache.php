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

use Lumiere\Tools\Utils;
use Lumiere\Admin\Cache_Tools;

/**
 * Display cache admin menu
 *
 * @since 4.0 Methods moved from this class into Cache_Tools, using templates instead of having templates here
 */
class Cache extends \Lumiere\Admin\Admin_Menu {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Construct parent class
		parent::__construct();

		// Logger: set to true to display debug on screen. => 20240225 Don't see why it is needed, will remove in the future
		// $this->logger->lumiere_start_logger( get_class( $this ), false );
	}

	/**
	 * Display the body
	 * @param Cache_Tools $cache_tools_class To create cache folder if it doesn't exists
	 */
	protected function display_cache_options( Cache_Tools $cache_tools_class ): void {

		// First part of the menu
		$this->include_with_vars( 'admin-menu-first-part', [ $this ] /** Add an array with vars to send in the template */ );

		// Make sure cache folder exists and is writable
		$cache_tools_class->lumiere_create_cache( true );

		// Show the vars if debug is activated.
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {

			// Activate debugging
			$this->utils_class->lumiere_activate_debug( $this->imdb_cache_values, 'no_var_dump', null ); # don't display set_error_handler("var_dump") that gets the page stuck in an endless loop

		}

		// Cache submenu.
		$this->include_with_vars( 'cache/admin-cache-submenu', [ $this ] /** Add an array with vars to send in the template */ );

		if (
			isset( $_GET['page'] ) && $_GET['page'] === 'lumiere_options_cache'
			&& ! isset( $_GET['subsection'] )
		) {

			// Cache options menu.
			$size = Utils::lumiere_format_bytes( $cache_tools_class->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbcachedir'] ) );
			$this->include_with_vars( 'cache/admin-cache-options', [ $size ] /** Add an array with vars to send in the template */ );

		} elseif (
			isset( $_GET['page'] ) && $_GET['page'] === 'lumiere_options_cache'
			&& isset( $_GET['subsection'] ) && $_GET['subsection'] === 'manage'
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
					$this->config_class,
					$this->page_cache_manage,
					$cache_tools_class->lumiere_get_cache_query_info( $this->imdb_cache_values['imdbcachedir'] ), // array of query files info
				] /** Add an array with vars to send in the template */
			);
		}
	}
}

