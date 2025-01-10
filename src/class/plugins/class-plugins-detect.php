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
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;

/**
 * Detect which WP plugins are available in SUBFOLDER_PLUGINS_BIT subfolder and are active
 * This class only returns automatically finded classes
 *
 * @phpstan-type AVAILABLE_AUTO_CLASSES \Lumiere\Plugins\Auto\Amp|\Lumiere\Plugins\Auto\Oceanwp|\Lumiere\Plugins\Auto\Polylang|\Lumiere\Plugins\Auto\Aioseo|\Lumiere\Plugins\Auto\Irp
 * @phpstan-type AVAILABLE_MANUAL_CLASSES \Lumiere\Plugins\Imdbphp|\Lumiere\Plugins\Logger
 * @phpstan-type AVAILABLE_AUTO_CLASSES_KEYS 'amp'|'oceanwp'|'polylang'|'aioseo'|'irp'
 * @phpstan-type AVAILABLE_MANUAL_CLASSES_KEYS 'imdbphp'|'logger'
 * @phpstan-type AVAILABLE_PLUGIN_CLASSES_KEYS AVAILABLE_AUTO_CLASSES_KEYS|AVAILABLE_MANUAL_CLASSES_KEYS
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
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Return list of plugins available in "external" subfolder
	 * Plugins located there are automatically checked
	 *
	 * @phpstan-return list<AVAILABLE_AUTO_CLASSES_KEYS>
	 * @return list<string>
	 */
	private function find_available_auto_plugins(): array {
		$available_plugins = [];
		$find_files = glob( __DIR__ . '/' . self::SUBFOLDER_PLUGINS_BIT . '/*' );
		$files = $find_files !== false ? array_filter( $find_files, 'is_file' ) : [];
		foreach ( $files as $file ) {
			/** @phpstan-var AVAILABLE_AUTO_CLASSES_KEYS $filename */
			$filename = preg_replace( '~.*class-(.+)\.php$~', '$1', $file );
			$available_plugins[] = $filename;
		}
		return $available_plugins;
	}

	/**
	 * Return list of active plugins
	 * Put "null" in associative array if the plugin is inactive, and then filters null plugins to return only active ones
	 * Use the plugin located in "SUBFOLDER_PLUGINS_BIT" subfolder to build the method names, then check if they are active
	 *
	 * @return array<string, class-string|null>
	 * @phpstan-return array<AVAILABLE_PLUGIN_CLASSES_KEYS, class-string<AVAILABLE_PLUGIN_CLASSES>|non-falsy-string>
	 *
	 * @see Plugins_Detect::find_available_plugins() that builds the list of available plugins
	 */
	public function get_active_plugins(): array {
		$plugins_class = [];
		$available_plugins = $this->find_available_auto_plugins();
		foreach ( $available_plugins as $plugin ) {
			$method = $plugin . '_is_active';
			if ( method_exists( $this, $method ) && $this->{$method}() === true ) {
				$subfolder_plugins = strlen( self::SUBFOLDER_PLUGINS_BIT ) > 0 ? ucfirst( self::SUBFOLDER_PLUGINS_BIT ) . '\\' : '';
				// @phpstan-var class-string<AVAILABLE_AUTO_CLASSES> $namespace_class
				$namespace_class = __NAMESPACE__ . '\\' . $subfolder_plugins . ucfirst( $plugin );
				// @phpstan-var array<AVAILABLE_PLUGIN_CLASSES_KEYS, class-string<AVAILABLE_PLUGIN_CLASSES>> $plugins_class
				$plugins_class[ $plugin ] = $namespace_class;
				continue;
			}

			// @phpstan-var array<AVAILABLE_PLUGIN_CLASSES_KEYS, null> $plugins_class
			$plugins_class[ $plugin ] = null;
		}
		return $this->filter_active_plugins( $plugins_class );
	}

	/**
	 * Filter in an array plugins that are not active
	 * If the array-value is null, the plugin will be removed from the list.
	 *
	 * @param array<string, string|null> $plugin_name
	 * @phpstan-param array<AVAILABLE_PLUGIN_CLASSES_KEYS, class-string<AVAILABLE_PLUGIN_CLASSES>|non-falsy-string|null> $plugin_name An array of the plugins active
	 * @return array<string, class-string>
	 * @phpstan-return array<AVAILABLE_PLUGIN_CLASSES_KEYS, class-string<AVAILABLE_PLUGIN_CLASSES>|non-falsy-string>
	 */
	private function filter_active_plugins( array $plugin_name ): array {
		return array_filter( $plugin_name, 'is_string' );
	}

	/**
	 * Determine whether OceanWP is activated
	 *
	 * @return bool true if OceanWP them is active
	 */
	private function oceanwp_is_active(): bool {
		return class_exists( 'OCEANWP_Theme_Class' ) && has_filter( 'ocean_display_page_header' ) === true;
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
