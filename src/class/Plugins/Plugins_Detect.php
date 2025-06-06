<?php declare( strict_types = 1 );
/**
 * Plugins_Detect class
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;

/**
 * Detect which WP plugins are available in SUBFOLDER_PLUGINS_AUTO subfolder and are active
 * This class only returns automatically active plugins
 *
 * @phpstan-type PLUGINS_AUTO_KEYS 'amp'|'aioseo'|'irp'|'oceanwp'|'polylang'
 * @phpstan-type PLUGINS_AUTO_CLASSES \Lumiere\Plugins\Auto\Amp|\Lumiere\Plugins\Auto\Oceanwp|\Lumiere\Plugins\Auto\Polylang|\Lumiere\Plugins\Auto\Aioseo|\Lumiere\Plugins\Auto\Irp
 * @phpstan-type PLUGINS_MANUAL_KEYS 'imdbphp'
 * @phpstan-type PLUGINS_MANUAL_CLASSES \Lumiere\Plugins\Manual\Imdbphp
 * @phpstan-type PLUGINS_ALL_KEYS PLUGINS_AUTO_KEYS|PLUGINS_MANUAL_KEYS
 * @phpstan-type PLUGINS_ALL_CLASSES PLUGINS_AUTO_CLASSES|PLUGINS_MANUAL_CLASSES
 *
 * @since 3.7 Class created
 * @since 4.1 Use find_available_plugins() to find plugins in SUBFOLDER_PLUGINS_AUTO folder, and get_active_plugins() returns an array of plugins available
 * @since 4.3 Use trait Main from Frontend to detect if it's an AMP Page
 */
final class Plugins_Detect {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Subfolder name of the plugins that can be automatically started
	 */
	public const SUBFOLDER_PLUGINS_AUTO = 'Auto';
	public const SUBFOLDER_PLUGINS_MANUAL = 'Manual';

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Return list of plugins available in "external" subfolder
	 * Plugins located there are automatically checked
	 *
	 * @phpstan-return list<PLUGINS_AUTO_KEYS>
	 * @return list<string>
	 */
	private function find_available_auto_plugins(): array {
		$available_plugins = [];
		$find_files = glob( __DIR__ . '/' . self::SUBFOLDER_PLUGINS_AUTO . '/*' );
		$files = $find_files !== false ? array_filter( $find_files, 'is_file' ) : [];

		foreach ( $files as $file ) {
			/** @phpstan-var PLUGINS_AUTO_KEYS $filename */
			$filename = preg_replace( '~.*/(.+)\.php$~', '$1', $file );
			$available_plugins[] = $filename;
		}
		return $available_plugins;
	}

	/**
	 * Return list of active plugins
	 * Put "null" in associative array if the plugin is inactive, and then filters null plugins to return only active ones
	 * Use the plugin located in "SUBFOLDER_PLUGINS_AUTO" subfolder to build the method names, then check if they are active
	 *
	 * @return array<string, class-string>
	 * @phpstan-return array<PLUGINS_AUTO_KEYS, class-string<PLUGINS_AUTO_CLASSES>>
	 *
	 * @see Plugins_Detect::find_available_plugins() that builds the list of available plugins
	 */
	public function get_active_plugins(): array {
		$plugins_class = [];
		$available_plugins = $this->find_available_auto_plugins();
		foreach ( $available_plugins as $plugin ) {
			$method = $plugin . '_is_active';
			if ( method_exists( $this, $method ) && $this->{$method}() === true ) {
				$subfolder_plugins = ucfirst( self::SUBFOLDER_PLUGINS_AUTO ) . '\\';
				/** @phpstan-var class-string<PLUGINS_AUTO_CLASSES> $namespace_class */
				$namespace_class = __NAMESPACE__ . '\\' . $subfolder_plugins . ucfirst( $plugin );
				$plugins_class[ $plugin ] = $namespace_class;
				continue;
			}
			$plugins_class[ $plugin ] = null;
		}

		return array_filter( $plugins_class, 'is_string' );
	}

	/**
	 * Determine whether OceanWP is activated
	 *
	 * @return bool true if OceanWP them is active
	 */
	private function oceanwp_is_active(): bool {
		return class_exists( 'OCEANWP_Theme_Class' ) && defined( 'OCEANWP_THEME_DIR' );
	}

	/**
	 * Determine whether AMP is activated
	 *
	 * @return bool true if AMP plugin is active
	 */
	private function amp_is_active(): bool {
		return $this->is_amp_page(); // is_amp_page() in Trait Main.
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
