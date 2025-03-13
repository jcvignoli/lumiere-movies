<?php declare( strict_types = 1 );
/**
 * Class for AIOSEO
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Plugins\Auto;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;

/**
 * Plugin to ensure Lumiere compatibility with AIOSEO plugin
 * The styles/scripts are supposed to go in construct with add_action()
 * Can method get_active_plugins() to get an extra property $active_plugins, as available in {@link Plugins_Start::activate_plugins()}
 * Executed in Frontend only
 *
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 */
class Aioseo {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	final public function __construct() {

		// Disable AIOSEO plugin in Popup pages, no need to promote those pages.
		if ( $this->is_popup_page() === true ) { // function in Main trait
			add_filter( 'aioseo_disable', '__return_true' );
		}
	}
}

