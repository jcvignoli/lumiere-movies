<?php declare( strict_types = 1 );
/**
 * Class for AIOSEO
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Plugins\Auto;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Tools\Settings_Global;
use Lumiere\Frontend\Main;

/**
 * Plugin to ensure Lumiere compatibility with AIOSEO plugin
 * The styles/scripts are supposed to go in construct with add_action(), the methods can be called with Plugins_Start $this->plugins_classes_active
 * Executed in Frontend only
 *
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type LINKMAKERCLASSES from \Lumiere\Link_Makers\Link_Factory
 */
class Aioseo {

	/**
	 * Traits
	 */
	use Settings_Global, Main;

	/**
	 * Class for building links, i.e. Highslide
	 * Built in class Link Factory
	 *
	 * @phpstan-var LINKMAKERCLASSES $link_maker The factory class will determine which class to use
	 */
	public object $link_maker;

	/**
	 * List of plugins active (including current class)
	 * @var array<string> $active_plugins
	 * @phpstan-ignore-next-line -- Property Lumiere\Plugins\Amp::$active_plugins is never read, only written -- want to keep the possibility in the future
	 */
	private array $active_plugins;

	/**
	 * Constructor
	 * @param array<string> $active_plugins
	 */
	final public function __construct( array $active_plugins ) {

		// Get the list of active plugins.
		$this->active_plugins = $active_plugins;

		// Get $config_class from Settings_Global trait.
		$this->get_settings_class();

		// Disable AIOSEO plugin in Popup pages, no need to promote those pages.
		if ( $this->is_popup_page() === true ) { // function in Main trait
			add_filter( 'aioseo_disable', '__return_true' );
		}
	}

	/**
	 * Static start for extra functions not to be run in self::__construct. No $this available!
	 */
	public static function start_init_hook(): void {}

}

