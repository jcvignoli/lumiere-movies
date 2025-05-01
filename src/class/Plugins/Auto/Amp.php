<?php declare( strict_types = 1 );
/**
 * Class for Amp
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
 * Plugin to ensure Lumiere compatibility with AMP plugin
 * The styles/scripts are supposed to go in construct with add_action()
 * Can method get_active_plugins() to get an extra property $active_plugins, as available in {@link Plugins_Start::activate_plugins()}
 * Executed in Frontend only
 *
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 */
final class Amp {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	final public function __construct() {

		// Remove conflicting assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'remove_breaking_amp_assets' ] );

		// Remove admin bar in popups
		add_action( 'wp_enqueue_scripts', [ $this, 'remove_amp_switcher' ] );
	}

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
	 * @see Lumiere\Frontend\Movie::lumiere_show()
	 */
	public function is_amp_validating(): bool {
		return defined( 'AMP__VERSION' ) && str_contains( wp_debug_backtrace_summary(), 'AMP_Validation_Callback_Wrapper' );
	}
}

