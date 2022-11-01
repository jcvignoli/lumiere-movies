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

class Amp {

	/**
	 * Header related WordPress actions
	 *
	 * @return void header actions have been executed
	 */
	public function lumiere_amp_remove_header(): void {

		// Remove conflicting assets. Use execution time 99 so we make sure it removes everything.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_remove_breaking_amp_assets' ], 99 );

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

