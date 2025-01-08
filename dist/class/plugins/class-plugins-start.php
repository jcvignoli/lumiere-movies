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
 * @phpstan-import-type AVAILABLE_PLUGIN_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type AVAILABLE_PLUGIN_CLASSES_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type AVAILABLE_MANUAL_CLASSES_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type AVAILABLE_MANUAL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @see \Lumiere\Plugins\Plugins_Detect Detect the plugins available should be instanciated
 */
class Plugins_Start {

	/**
	 * Array of active classes
	 * The active class can be used when they exist and called with this property
	 *
	 * @var array<string, object>
	 * @phpstan-var array<AVAILABLE_PLUGIN_CLASSES_KEYS, AVAILABLE_PLUGIN_CLASSES>
	 */
	public array $plugins_classes_active;

	/**
	 * Constructor
	 * @param array<string, string>|array<string>|null $extra_classes Extra classes to add
	 * @phpstan-param array<AVAILABLE_MANUAL_CLASSES_KEYS, AVAILABLE_MANUAL_CLASSES_KEYS>|null $extra_classes Extra classes to add
	 */
	public function __construct( ?array $extra_classes = [] ) {

		// Get the active plugins.
		$array_plugin_names = ( new Plugins_Detect() )->get_active_plugins();

		// Add an extra class in properties.
		if ( isset( $extra_classes ) && count( $extra_classes ) > 0 ) {
			$array_plugin_names = $this->add_extra_plugins( $extra_classes, $array_plugin_names );
		}

		$this->plugins_classes_active = $this->start_active_plugins( $array_plugin_names );
	}

	/**
	 * Start the plugins and return those who got activated
	 * Classes are located in Plugins_Detect::SUBFOLDER_PLUGINS_BIT
	 *
	 * @phpstan-param array<AVAILABLE_PLUGIN_CLASSES_KEYS, class-string<AVAILABLE_PLUGIN_CLASSES>|non-falsy-string> $active_plugins
	 * @return array<string, object>
	 * @phpstan-return array<AVAILABLE_PLUGIN_CLASSES_KEYS, AVAILABLE_PLUGIN_CLASSES> Classes have been activated
	 */
	private function start_active_plugins( array $active_plugins ): array {

		$all_plugins_activated = [];

		foreach ( $active_plugins as $plugin_name => $plugin_path ) {

			/** @phpstan-var AVAILABLE_PLUGIN_CLASSES $current_plugin_activated */
			$current_plugin_activated = new $plugin_path( $active_plugins ); // Instanciate plugin classes.
			$all_plugins_activated[ $plugin_name ] = $current_plugin_activated;
			if ( method_exists( $plugin_path, 'start_init_hook' ) ) {
				// $current_plugin_activated::start_init_hook();
				/**
				 * @psalm-suppress UndefinedMethod
				 * @phpstan-ignore argument.type
				 */
				add_action( 'init', [ $current_plugin_activated, 'start_init_hook' ], 40 ); // 40 so make sure it's always executed.
			}
		}
		return $all_plugins_activated;
	}

	/**
	 * Add extra manual classe(s)
	 * They're not in SUBFOLDER_PLUGINS_BIT, they're in "plugins"
	 *
	 * @param array<string, string> $extra_classes Extra classes to add, ie [ 'imdbphp' => 'imdbphp' ]
	 * @phpstan-param array<AVAILABLE_MANUAL_CLASSES_KEYS, AVAILABLE_MANUAL_CLASSES_KEYS> $extra_classes
	 * @param array<string, class-string|string> $array_plugin_names
	 * @phpstan-param array<AVAILABLE_PLUGIN_CLASSES_KEYS, class-string<AVAILABLE_PLUGIN_CLASSES>|non-falsy-string> $array_plugin_names
	 * @return array<string, class-string|string>
	 * @phpstan-return ($array_plugin_names is array<AVAILABLE_PLUGIN_CLASSES_KEYS, class-string<AVAILABLE_PLUGIN_CLASSES>> ? array<AVAILABLE_PLUGIN_CLASSES_KEYS, class-string<AVAILABLE_PLUGIN_CLASSES>> : array<AVAILABLE_PLUGIN_CLASSES_KEYS, non-falsy-string>)
	 */
	private function add_extra_plugins( array $extra_classes, array $array_plugin_names ): array {
		if ( count( $extra_classes ) === 0 ) {
			return $array_plugin_names;
		}

		foreach ( $extra_classes as $extra_class_name => $extra_class_path ) {
			$full_class_name = __NAMESPACE__ . '\\' . ucfirst( $extra_class_path );
			if ( class_exists( $full_class_name ) ) {
				$array_plugin_names[ $extra_class_name ] = $full_class_name;
			}
		}
		return $array_plugin_names;
	}
}
