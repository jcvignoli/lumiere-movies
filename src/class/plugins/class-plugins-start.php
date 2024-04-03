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
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Plugins\Plugins_Detect;

/**
 * Instanciate the plugins that are available and in active
 *
 * @phpstan-import-type AVAILABLE_PLUGIN_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @see \Lumiere\Plugins\Plugins_Detect It detects the plugins that are active should be instanciated
 */
class Plugins_Start {

	/**
	 * Array of active plugins
	 *
	 * @phpstan-var array<int, AVAILABLE_PLUGIN_CLASSES|string>
	 * @var array<int, string>
	 */
	public array $plugins_active_names;

	/**
	 * Array of active classes
	 * The active class can be used when they exist and called with this property
	 *
	 * @var array<string, object|Imdbphp|Logger>
	 */
	public array $plugins_classes_active;

	/**
	 * Constructor
	 * @param array<int, object|Imdbphp|Logger>|null $extra_classes Extra classes to add
	 */
	public function __construct( array $extra_classes = null ) {

		// Get the active plugins.
		$detect_class = new Plugins_Detect();
		$this->plugins_active_names = $detect_class->get_active_plugins();
		$this->plugins_classes_active = $this->start_plugins();

		// Add an extra class in properties.
		if ( isset( $extra_classes ) ) {
			$this->add_extra_plugins( $extra_classes );
		}
	}

	/**
	 * Build list of plugins active in array $plugin_class
	 */
	public static function lumiere_static_start(): void {
		$class = new self();
	}

	/**
	 * Start the plugins and return those who got activated
	 * Classes are located in Plugins_Detect::SUBFOLDER_PLUGINS_BIT
	 *
	 * @return array<string, object|Imdbphp|Logger>
	 */
	private function start_plugins(): array {

		$get_classes_active = [];

		foreach ( $this->plugins_active_names as $plugin ) {

			$subfolder_plugins = strlen( Plugins_Detect::SUBFOLDER_PLUGINS_BIT ) > 0 ? ucfirst( Plugins_Detect::SUBFOLDER_PLUGINS_BIT ) . '\\' : '';
			$plugin_name = __NAMESPACE__ . '\\' . $subfolder_plugins . ucfirst( $plugin );

			if ( class_exists( $plugin_name ) ) {
				$plugin_class = new $plugin_name( $this->plugins_active_names ); // Instanciate plugin classes.
				$get_classes_active[ $plugin ] = $plugin_class;
				if ( method_exists( $plugin_class, 'start_init_hook' ) ) {
					$plugin_class::start_init_hook(); // Extra functions to be started in init
				}
			}
		}
		return $get_classes_active;
	}

	/**
	 * Add to properties extra classes
	 * @param array<int, object|Imdbphp|Logger> $extra_classes Extra classes to add, they're not in SUBFOLDER_PLUGINS_BIT, they're in "plugins"
	 */
	private function add_extra_plugins( array $extra_classes ): void {

		if ( count( $extra_classes ) === 0 ) {
			return;
		}

		foreach ( $extra_classes as $extra_class ) {
			$get_class = strrchr( get_class( $extra_class ), '\\' );
			$classname = $get_class !== false ? strtolower( substr( $get_class, 1 ) ) : false;
			if ( is_string( $classname ) ) {
				$this->plugins_active_names[] = $classname;
				$this->plugins_classes_active[ $classname ] = $extra_class;
			}
		}
	}
}
