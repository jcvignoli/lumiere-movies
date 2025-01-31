<?php declare( strict_types = 1 );
/**
 * Start the Plugins class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 4.1
 * @package lumiere-movies
 */

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Plugins\Plugins_Detect;

/**
 * Instanciate the plugins that are available and in active
 *
 * @phpstan-import-type PLUGINS_AUTO_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_AUTO_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_ALL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_ALL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 *
 * @see \Lumiere\Plugins\Plugins_Detect Detect the plugins available should be instanciated
 */
class Plugins_Start {

	/**
	 * Array of active classes
	 * The active class can be used when they exist and called with this property
	 *
	 * @var array<string, object>
	 * @phpstan-var array{PLUGINS_ALL_KEYS?: PLUGINS_ALL_CLASSES}
	 */
	public array $plugins_classes_active;

	/**
	 * Constructor
	 * @param array<string>|null $extra_manual_classes Extra classes to add
	 * @phpstan-param array<PLUGINS_MANUAL_KEYS>|null $extra_manual_classes
	 */
	public function __construct( ?array $extra_manual_classes = null ) {

		// Get the active plugins.
		$array_plugin_names = ( new Plugins_Detect() )->get_active_plugins();

		// Add an extra class in properties.
		if ( isset( $extra_manual_classes ) && count( $extra_manual_classes ) > 0 ) {
			$array_plugin_names = $this->add_manual_to_auto_plugins( $extra_manual_classes, $array_plugin_names );
		}

		$this->plugins_classes_active = $this->activate_plugins( $array_plugin_names );
	}

	/**
	 * Start the plugins and return those who got activated
	 * Classes are located in Plugins_Detect::SUBFOLDER_PLUGINS_BIT
	 *
	 * @param array<string, class-string> $active_plugins
	 * @phpstan-param array{PLUGINS_ALL_KEYS?: class-string<PLUGINS_ALL_CLASSES>} $active_plugins
	 * @phpstan-return array{PLUGINS_ALL_KEYS?: PLUGINS_ALL_CLASSES}
	 */
	private function activate_plugins( array $active_plugins ): array {

		$all_plugins_activated = [];

		foreach ( $active_plugins as $plugin_name => $plugin_path ) {
			$current_plugin_activated = new $plugin_path(); // Instanciate plugin classes.
			$all_plugins_activated[ $plugin_name ] = $current_plugin_activated;
			// Start get_active_plugins() method in class if the method exists. The method allows to get the active plugins as strings.
			if ( method_exists( $current_plugin_activated, 'get_active_plugins' ) ) {
				$current_plugin_activated->get_active_plugins( $active_plugins );
				//add_action( 'init', fn() => $current_plugin_activated->get_active_plugins( $active_plugins ), 20 ); // 20 so make sure it's always executed.
			}
		}
		/** @psalm-var array{PLUGINS_ALL_KEYS?: PLUGINS_ALL_CLASSES} $all_plugins_activated (No idea why Psalm needs this) */
		return $all_plugins_activated;
	}

	/**
	 * Add extra manual classe(s)
	 * They're not in SUBFOLDER_PLUGINS_AUTO, they're in "plugins/manual"
	 *
	 * @param array<string> $extra_classes Extra classes to add, ie [ 'imdbphp' ]
	 * @phpstan-param non-empty-array<PLUGINS_MANUAL_KEYS> $extra_classes
	 * @param array<string, class-string> $array_plugin_names
	 * @phpstan-param array{PLUGINS_AUTO_KEYS?: class-string<PLUGINS_AUTO_CLASSES>} $array_plugin_names
	 * @return array<string, class-string>
	 * @phpstan-return array{PLUGINS_ALL_KEYS?: class-string<PLUGINS_ALL_CLASSES>}
	 */
	private function add_manual_to_auto_plugins( array $extra_classes, array $array_plugin_names ): array {

		foreach ( $extra_classes as $extra_class_name ) {
			/** @phpstan-var class-string<PLUGINS_MANUAL_CLASSES> $full_class_name */
			$full_class_name = __NAMESPACE__ . '\\' . ucfirst( Plugins_Detect::SUBFOLDER_PLUGINS_MANUAL ) . '\\' . ucfirst( $extra_class_name );
			if ( class_exists( $full_class_name ) ) {
				$array_plugin_names[ $extra_class_name ] = $full_class_name;
			}
		}
		return $array_plugin_names;
	}

	/**
	 * Is the plugin activated?
	 *
	 * @since 4.3
	 * @param string $plugin Plugin's name
	 * @return bool True if active
	 */
	public function is_plugin_active( string $plugin ): bool {
		return in_array( $plugin, array_keys( $this->plugins_classes_active ), true );
	}
}
