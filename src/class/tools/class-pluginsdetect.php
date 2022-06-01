<?php declare( strict_types = 1 );
/**
 * Class to detect which WP plugins are in use and compatible with Lumière!
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

class PluginsDetect {

	/**
	 * Array of plugins in use
	 *
	 * @var array<int, string> $plugins_class
	 */
	public array $plugins_class = [];

	/**
	 * Activate OceanWP template compatibility
	 * Whether activate OceanWP functions in Lumière!
	 * @var bool $lumiere_oceanwp_active set to false to unactivate
	 */
	public bool $lumiere_oceanwp_active = true;

	/**
	 * Activate AMP compatibility
	 * Whether activate AMP functions in Lumière!
	 * @var bool $lumiere_amp_active set to false to unactivate
	 */
	public bool $lumiere_amp_active = true;

	/**
	 * Activate Polylang compatibility
	 * Whether activate Polylang functions in Lumière!
	 * @var bool $lumiere_polylang_active set to false to unactivate
	 */
	public bool $lumiere_polylang_active = true;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// If init is too late, use lumiere_detect_active_plugins hook so we can activate manually.
		add_action(
			'lumiere_plugins_detect',
			function(): void {
				$this->lumiere_detect_active_plugins();
			}
		);

		// Initialise
		$this->lumiere_detect_active_plugins();
	}

	/**
	 * Build list of plugins active in array $plugin_class
	 *
	 */
	public function lumiere_detect_active_plugins(): void {

		// AMP
		if ( $this->amp_is_active() === true ) {
			$this->plugins_class[] = 'AMP';
		}
		// Polylang
		if ( $this->polylang_is_active() === true ) {
			$this->plugins_class[] = 'POLYLANG';
		}

		// OceanWP
		if ( $this->oceanwp_is_active() === true ) {
			$this->plugins_class[] = 'OCEANWP';
		}

	}

	/**
	 * Determine whether OceanWP is activated
	 *
	 * @return bool true if OceanWP them is active
	 */
	protected function oceanwp_is_active(): bool {

		if ( $this->lumiere_oceanwp_active === false ) {
			return false;
		}

		return class_exists( 'OCEANWP_Theme_Class' );
	}

	/**
	 * Determine whether AMP is activated
	 *
	 * @return bool true if AMP plugin is active
	 */
	protected function amp_is_active(): bool {

		if ( $this->lumiere_amp_active === false ) {
			return false;
		}

		return function_exists( 'amp_is_request' ) && amp_is_request();

	}

	/**
	 * Determine whether Polylang is activated
	 *
	 * @return bool true if Polylang plugin is active
	 */
	protected function polylang_is_active(): bool {

		if ( $this->lumiere_polylang_active === false ) {
			return false;
		}

		return function_exists( 'pll_count_posts' );

	}

}
