<?php declare( strict_types = 1 );
/**
 * Class for Amp
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

/**
 * Plugin to ensure Lumiere compatibility with AMP plugin
 * The styles/scripts are supposed to go in construct with add_action(), the methods can be called with Plugins_Start $this->plugins_classes_active
 *
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 */
class Amp {

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

		// Remove conflicting assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_remove_breaking_amp_assets' ] );
	}

	/**
	 * Static start
	 */
	public function lumiere_start(): void {
		/** Run whatever you want */
	}

	/**
	 * Remove conflicting AMP assets
	 * Check if they are registered before removing them
	 *
	 * @return void Scripts and Styles are deregistered
	 */
	public function lumiere_remove_breaking_amp_assets(): void {

		$styles_deregister = [
			// Those assets are not found when AMP is active and reported as not found by chrome dev tools.
			// Added by OCEAN_WP or maybe other themes.
			'font-awesome',
			'simple-line-icons',
			// Added by Elementor plugin.
			'elementor-icons',
		];

		$scripts_deregister = [
			// Added by Lumière!
			'lumiere_scripts',
			'lumiere_hide_show',
			'lumiere_bootstrap_core',
			'lumiere_bootstrap_scripts',
		];

		foreach ( $scripts_deregister as $script ) {
			if ( wp_script_is( $script, $list = 'registered' ) === true ) {
				wp_deregister_script( $script );
			}
		}

		foreach ( $styles_deregister as $style ) {
			if ( wp_style_is( $style, $list = 'registered' ) === true ) {
				wp_deregister_style( $style );
			}
		}
	}

}

