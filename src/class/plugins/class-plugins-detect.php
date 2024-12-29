<?php declare( strict_types = 1 );
/**
 * Plugins_Detect class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 2.0
 * @package lumiere-movies
 */

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Frontend\Main;

/**
 * Detect which WP plugins are available in SUBFOLDER_PLUGINS_BIT subfolder and are active
 *
 * @phpstan-type AVAILABLE_AUTO_CLASSES 'amp'|'oceanwp'|'polylang'|'aioseo'|'irp'
 * @phpstan-type AVAILABLE_MANUAL_CLASSES 'imdbphp'|'logger'
 * @phpstan-type AVAILABLE_PLUGIN_CLASSES AVAILABLE_AUTO_CLASSES|AVAILABLE_MANUAL_CLASSES
 *
 * @since 3.7 Class created
 * @since 4.1 Use find_available_plugins() to find plugins in SUBFOLDER_PLUGINS_BIT folder, and get_active_plugins() returns an array of plugins available
 * @since 4.3 Use trait Main from Frontend to detect if it's an AMP Page
 */
class Plugins_Detect {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Subfolder name of the plugins that can be automatically started
	 */
	public const SUBFOLDER_PLUGINS_BIT = 'auto';

	/**
	 * Array of plugins currently in use
	 *
	 * @phpstan-var array<int, AVAILABLE_PLUGIN_CLASSES>
	 * @var array<int, string>
	 */
	public array $plugins_class;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugins_class = [];
	}

	/**
	 * Return list of plugins available in "external" subfolder
	 * Plugins located there are automatically checked
	 *
	 * @phpstan-return list<AVAILABLE_PLUGIN_CLASSES>
	 * @return list<string>
	 */
	private function find_available_plugins(): array {
		$available_plugins = [];
		$find_files = glob( __DIR__ . '/' . self::SUBFOLDER_PLUGINS_BIT . '/*' );
		$files = $find_files !== false ? array_filter( $find_files, 'is_file' ) : [];
		foreach ( $files as $file ) {
			/** @phpstan-var AVAILABLE_PLUGIN_CLASSES $filename */
			$filename = preg_replace( '~.*class-(.+)\.php$~', '$1', $file );
			$available_plugins[] = $filename;
		}
		return $available_plugins;
	}

	/**
	 * Return list of active plugins
	 * Use the plugin located in "external" subfolder to build the method names, then check if they are active
	 *
	 * @phpstan-return array<int, AVAILABLE_PLUGIN_CLASSES>
	 * @return array<int, string>
	 * @see Plugins_Detect::find_available_plugins() that builds the list of available plugins
	 */
	public function get_active_plugins(): array {
		foreach ( $this->find_available_plugins() as $plugin ) {
			$method = $plugin . '_is_active';
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
		return $this->lumiere_is_amp_page(); // Trait Main.
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

	/**
	 * Determine whether IRP (Intelly Related Post) is activated
	 *
	 * @return bool true if IRP plugin is active
	 */
	private function irp_is_active(): bool {
		return defined( 'IRP_PLUGIN_FILE' );
	}
}
