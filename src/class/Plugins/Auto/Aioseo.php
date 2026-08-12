<?php
/**
 * Class for AIOSEO
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Plugins\Auto;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;
use Lumiere\Plugins\Plugins_Interface;

/**
 * Plugin to ensure Lumiere compatibility with AIOSEO plugin
 * The styles/scripts are supposed to go in construct with add_action()
 * Can method get_active_plugins() to get an extra property $active_plugins, as available in {@link Plugins_Start::activate_plugins()}
 * Executed in Frontend only
 *
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 */
final class Aioseo implements Plugins_Interface {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Determine whether AIOSEO is activated
	 *
	 * @return bool true if AIOSEO is active
	 */
	public static function is_active(): bool {
		return defined( 'AIOSEO_PHP_VERSION_DIR' );
	}

	/**
	 * Start the plugin
	 * @param array<string, class-string<Plugins_Interface>> $active_plugins Plugins that are activated
	 */
	public function init( array $active_plugins ): void {

		// Disable AIOSEO plugin in Popup pages, no need to promote those pages.
		if ( $this->is_popup_page() === true ) { // function in Main trait
			add_filter( 'aioseo_disable', '__return_true' );
		}
	}
}
