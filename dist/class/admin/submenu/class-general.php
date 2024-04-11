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
use Lumiere\Admin\Admin_Menu;

/**
 * Display General options menu
 * @since 4.0 Using templates instead of having templates here
 */
class General extends Admin_Menu {

	/**
	 * Pages name
	 */
	private const PAGES_NAMES = [
		'menu_first'        => 'admin-menu-first-part',
		'menu_submenu'      => 'general/admin-general-submenu',
		'general_options'   => 'general/admin-general-layout',
		'advanced_options'  => 'general/admin-general-advanced',
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
	 * Display the options
	 *
	 * @param Cache_Tools $cache_tools_class To create cache folder if it doesn't exists
	 * @param string $nonce nonce from Admin_Menu to be checked when doing $_GET checks
	 * @see \Lumiere\Admin\Admin_Menu::call_admin_subclass() Calls this method
	 */
	protected function lum_submenu_start( Cache_Tools $cache_tools_class, string $nonce ): void {

		// First part of the menu.
		$this->include_with_vars(
			self::PAGES_NAMES['menu_first'],
			[ $this ], /** Add an array with vars to send in the template */
			self::TRANSIENT_ADMIN,
		);

		// Create the cache if it doesn't exists.
		$cache_tools_class->lumiere_create_cache( true );

		// Show the vars if debug is activated.
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {
			$this->lumiere_display_vars( $this->imdb_admin_values, 'no_var_dump', null ); // Method in trait Admin_General.
		}

		// Submenu.
		$this->include_with_vars(
			self::PAGES_NAMES['menu_submenu'],
			[ $this->config_class->lumiere_pics_dir, $this->page_general_base, $this->page_general_advanced ], /** Add an array with vars to send in the template */
			self::TRANSIENT_ADMIN,
		);

		// The body.
		if (
			// General options.
			wp_verify_nonce( $nonce, 'check_display_page' ) > 0
			&& isset( $_GET['page'] ) && str_contains( $this->page_general_base, $_GET['page'] ) === true
			&& ! isset( $_GET['subsection'] )
		) {
			$this->include_with_vars(
				self::PAGES_NAMES['general_options'],
				[ $this->config_class->lumiere_pics_dir ], /** Add an array with vars to send in the template */
				self::TRANSIENT_ADMIN,
			);

		} elseif (
			// Advanced options.
			isset( $_GET['page'] ) && str_contains( $this->page_general_advanced, $_GET['page'] ) === true
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

