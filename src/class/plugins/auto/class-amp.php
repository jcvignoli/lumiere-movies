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

namespace Lumiere\Plugins\Auto;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;

/**
 * Plugin to ensure Lumiere compatibility with AMP plugin
 * The styles/scripts are supposed to go in construct with add_action(), the methods can be called with Plugins_Start $this->active_plugins
 * Executed in Frontend only
 *
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type LINKMAKERCLASSES from \Lumiere\Link_Makers\Link_Factory
 */
class Amp {

	/**
	 * Traits
	 */
	use Main;

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

		// Remove conflicting assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'remove_breaking_amp_assets' ] );

		// Remove admin bar in popups
		add_action( 'wp_enqueue_scripts', [ $this, 'remove_amp_switcher' ] );
	}

	/**
	 * Static start for extra functions not to be run in self::__construct. No $this available!
	 */
	public static function start_init_hook(): void {}

	/**
	 * Remove conflicting AMP assets
	 * Check if they are registered before removing them
	 *
	 * @return void Scripts and Styles are deregistered
	 */
	public function remove_breaking_amp_assets(): void {

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
			if ( wp_script_is( $script, 'registered' ) === true ) {
				wp_deregister_script( $script );
			}
		}

		foreach ( $styles_deregister as $style ) {
			if ( wp_style_is( $style, 'registered' ) === true ) {
				wp_deregister_style( $style );
			}
		}
	}

	/**
	 * Remove switcher text if user is logged in and visiting a popup page
	 *
	 * @return void Admin bar has been removed
	 */
	public function remove_amp_switcher(): void {
		if ( is_user_logged_in() === true && $this->is_popup_page() === true ) { // Method is_popup_page() in Trait Main.
			add_action( 'amp_mobile_version_switcher_link_text', '__return_false' ); // @phpstan-ignore return.void (Action callback returns false but should not return anything)
		}
	}

	/**
	 * Detect on a given class if AMP is calling
	 * @return bool True if AMP is running a function of validation
	 *
	 * @see Lumiere\Frontend\Movie::lumiere_show() Calls this method without instanciating plugin, as it is executed ie during activation, amp may be not available
	 */
	public static function is_amp_validating(): bool {
		return defined( 'AMP__VERSION' ) && str_contains( wp_debug_backtrace_summary(), 'AMP_Validation_Callback_Wrapper' );
	}
}

