<?php declare( strict_types = 1 );
/**
 * General options class
 * Child of Admin_Menu
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin\Submenu;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Admin\Cache_Tools;

/**
 * Display General options menu
 * @since 4.0 Using templates instead of having templates here
 */
class General extends \Lumiere\Admin\Admin_Menu {

	/**
	 * Pages name
	 */
	private const PAGES_NAMES = [
		'menu_first'        => 'admin-menu-first-part',
		'menu_submenu'      => 'general/admin-general-submenu',
		'general_options'   => 'general/admin-general-layout',
		'advanced_options'  => 'general/admin-general-advanced',
		'signature'         => 'admin-menu-signature',
	];

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
	 * Display the options.
	 * @param Cache_Tools $cache_tools_class To create cache folder if it doesn't exists
	 */
	protected function display_general_options( Cache_Tools $cache_tools_class ): void {

		// First part of the menu.
		$this->include_with_vars( self::PAGES_NAMES['menu_first'], [ $this ] /** Add an array with vars to send in the template */ );

		// Create the cache if it doesn't exists.
		$cache_tools_class->lumiere_create_cache( true );

		// Show the vars if debug is activated.
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {

			// Start the class Utils to activate debug -> already started in admin_pages
			$this->utils_class->lumiere_activate_debug( $this->imdb_admin_values, 'no_var_dump', null );
		}

		// Submenu.
		$this->include_with_vars( self::PAGES_NAMES['menu_submenu'], [ $this->config_class->lumiere_pics_dir, $this->page_general_base, $this->page_general_advanced ] /** Add an array with vars to send in the template */ );

		// The body.
		if (
			// General options.
			isset( $_GET['page'] ) && $_GET['page'] === $this->menu_id
			&& ! isset( $_GET['subsection'] )
		) {
			$this->include_with_vars( self::PAGES_NAMES['general_options'], [ $this->config_class->lumiere_pics_dir ] /** Add an array with vars to send in the template */ );

		} elseif (
			// Advanced options.
			isset( $_GET['page'] ) && $_GET['page'] === $this->menu_id
			&& isset( $_GET['subsection'] ) && $_GET['subsection'] === 'advanced'
		) {
			$this->include_with_vars( self::PAGES_NAMES['advanced_options'], [] /** Add an array with vars to send in the template */ );
		}

		// Signature.
		$this->include_with_vars( self::PAGES_NAMES['signature'], [ $this->page_general_help_support ] /** Add an array with vars to send in the template */ );
	}
}

