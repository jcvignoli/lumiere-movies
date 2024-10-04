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
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Tools\Settings_Global;

/**
 * Plugin to ensure Lumiere compatibility with AIOSEO plugin
 * The styles/scripts are supposed to go in construct with add_action(), the methods can be called with Plugins_Start $this->plugins_classes_active
 *
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 */
class Aioseo {

	/**
	 * Traits
	 */
	use Settings_Global;

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
		if ( $this->is_popup_page() === true ) {
			add_filter( 'aioseo_disable', '__return_true' );
		}
	}

	/**
	 * Static start for extra functions not to be run in self::__construct. No $this available!
	 */
	public static function start_init_hook(): void {}

	/**
	 * Detect if the current page is a popup
	 *
	 * @since 3.11.4
	 * @return bool True if the page is a Lumiere popup
	 */
	private function is_popup_page(): bool {
		if (
			isset( $_SERVER['REQUEST_URI'] )
			&&
			(
				str_contains( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->config_class->lumiere_urlstringfilms )
				|| str_contains( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->config_class->lumiere_urlstringsearch )
				|| str_contains( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->config_class->lumiere_urlstringperson )
			)
		) {
			return true;
		}
		return false;
	}
}

