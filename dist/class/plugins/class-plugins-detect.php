<?php declare( strict_types = 1 );
/**
 * PluginsDetect class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 2.0
 * @since 3.7
 * @package lumiere-movies
 */

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

/**
 * Detect which WP plugins are in use and compatible with Lumière
 *
 * @phpstan-type PLUGINS_AVAILABLE Amp|Oceanwp|Polylang
 * @since 4.0.3 Use PLUGINS_TO_CHECK to detect, get_active_plugins() returns an array of plugins available
 */
class Plugins_Detect {

	/**
	 * Plugins that could be activated
	 */
	const PLUGINS_TO_CHECK = [ 'amp', 'polylang', 'oceanwp' ];

	/**
	 * Array of plugins currently in use
	 *
	 * @var array<mixed>
	 */
	public array $plugins_class;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugins_class = [];
	}

	/**
	 * Return list of plugins active in array $plugin_class
	 * Use the plugin list in PLUGINS_TO_CHECK to build the method names
	 *
	 * @return array<mixed>
	 */
	public function get_active_plugins(): array {

		foreach ( self::PLUGINS_TO_CHECK as $plugin ) {
			$method = $plugin . '_is_active';
			if ( method_exists( $this, $method ) && call_user_func( [ $this, $method ] ) === true ) {
				$this->plugins_class[] = $plugin;
			}
		}
		return $this->plugins_class;
	}

	/**
	 * Determine whether OceanWP is activated
	 *
	 * @return bool true if OceanWP them is active
	 */
	private function oceanwp_is_active(): bool {
		return class_exists( 'OCEANWP_Theme_Class' ) && has_filter( 'ocean_display_page_header' );
	}

	/**
	 * Determine whether AMP is activated
	 *
	 * @return bool true if AMP plugin is active
	 */
	private function amp_is_active(): bool {
		return function_exists( 'amp_is_request' ) && amp_is_request();
	}

	/**
	 * Determine whether Polylang is activated
	 *
	 * @return bool true if Polylang plugin is active
	 */
	private function polylang_is_active(): bool {
		return function_exists( 'pll_count_posts' ) && is_plugin_active( 'polylang/polylang.php' );
	}
}
