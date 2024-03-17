<?php declare( strict_types = 1 );
/**
 * Start the Plugins class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 4.0.3
 * @package lumiere-movies
 */

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Plugins\Plugins_Detect;

/**
 * Activate the plugins that are available automatically
 *
 * @see \Lumiere\Plugins\Plugins_Detect It detects the plugins that are actives and needed to be activated here
 * @phpstan-import-type PLUGINS_AVAILABLE from \Lumiere\Plugins\Plugins_Detect
 */
class Plugins_Start {

	/**
	 * Array of active plugins
	 *
	 * @phpstan-var Plugins_Detect::PLUGINS_TO_CHECK $plugins_active_names
	 * @psalm-var array<string> $plugins_active_names
	 * @var array<string> $plugins_active_names
	 */
	public array $plugins_active_names;

	/**
	 * Array of active classes
	 * The active class can be used when they exist and called with this property
	 *
	 * @phpstan-var array<string, PLUGINS_AVAILABLE> $plugins_classes_active
	 * @psalm-var array<string, object> $plugins_classes_active
	 * @var array<string, object> $plugins_classes_active
	 */
	public array $plugins_classes_active = [];

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get the active plugins
		$detect_class = new Plugins_Detect();
		$this->plugins_active_names = $detect_class->get_active_plugins();
		$this->start_plugins();
	}

	/**
	 * Build list of plugins active in array $plugin_class
	 */
	public static function lumiere_static_start(): void {
		$class = new self();
	}

	/**
	 * Start the plugins and send them to property $plugins_classes_active
	 */
	private function start_plugins(): void {

		foreach ( $this->plugins_active_names as $plugin ) {

			$plugin_name = __NAMESPACE__ . '\\' . ucfirst( $plugin );

			if ( class_exists( $plugin_name ) ) {
				/** @phpstan-var PLUGINS_AVAILABLE $plugin_class */
				$plugin_class = new $plugin_name( $this->plugins_active_names );
				$this->plugins_classes_active[ $plugin ] = $plugin_class;
				//add_action( 'init', fn() => $plugin_class->lumiere_start() );
			}
		}
	}
}
