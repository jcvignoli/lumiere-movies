<?php declare( strict_types = 1 );
/**
 * Plugins_Detect class
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
 * @phpstan-type PLUGINS_AVAILABLE External\Amp|External\Oceanwp|External\Polylang|External\Aioseo
 * @phpstan-type FILES_AVAILABLE 'amp'|'oceanwp'|'polylang'|'aioseo'
 * @since 4.0.3 Use find_available_plugins() to find plugins in External folder, and get_active_plugins() returns an array of plugins available
 */
class Plugins_Detect {

	/**
	 * Plugins that could be activated
	 */
	const PLUGINS_TO_CHECK = [ 'amp', 'polylang', 'oceanwp', 'aioseo' ];

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
	 * Return list of plugins available in external subfolder
	 * Plugins located there are automatically checked
	 *
	 * @phpstan-return list<FILES_AVAILABLE|null>
	 * @return list<string|null>
	 */
	private function find_available_plugins(): array {
		$available_plugins = [];
		$find_files = glob( __DIR__ . '/external/*' );
		$files = $find_files !== false ? array_filter( $find_files, 'is_file' ) : [];
		foreach ( $files as $file ) {
			/** @phpstan-var FILES_AVAILABLE $filename */
			$filename = preg_replace( '~.*class-(.+)\.php$~', '$1', $file );
			$available_plugins[] = $filename;
		}
		return $available_plugins;
	}

	/**
	 * Return list of plugins active in array $plugin_class
	 * Use the plugin located in "external" subfolder to build the method names
	 *
	 * @return array<string>
	 * @see Plugins_Detect::find_available_plugins() that build the list of Plugins available
	 */
	public function get_active_plugins(): array {
		foreach ( $this->find_available_plugins() as $plugin ) {
			$method = $plugin !== null ? $plugin . '_is_active' : '';
			if ( method_exists( $this, $method ) && $this->{$method}() === true ) { // @phan-suppress-current-line PhanUndeclaredMethod -- bad phan!
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
		return function_exists( 'pll_current_language' );
	}

	/**
	 * Determine whether Aioseo is activated
	 *
	 * @return bool true if Polylang plugin is active
	 */
	private function aioseo_is_active(): bool {
		return defined( 'AIOSEO_PHP_VERSION_DIR' );
	}
}
