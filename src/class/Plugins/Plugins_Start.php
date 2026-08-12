<?php
/**
 * Start the Plugins class
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Plugins\Plugins_Detect;
use Lumiere\Plugins\Plugins_Interface;

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
 * @since 4.1
 * @since 4.8.2 rewrote the class
 */
final class Plugins_Start {

	/**
	 * Active integration instances indexed by key.
	 *
	 * @var array<string, Plugins_Interface>
	 */
	private array $active_plugins = [];

	/**
	 * Constructor
	 * @param array<string>|null $extra_manual_keys Keys for manual integrations (e.g. ['imdbphp'])
	 */
	public function __construct( ?array $extra_manual_keys = null ) {

		$detector = new Plugins_Detect();

		// Step 1: Detect automatic plugins that are active
		$plugin_classes = $detector->get_active_plugins();

		// Step 2: Detect requested manual plugins that are active and merge them
		if ( isset( $extra_manual_keys ) && $extra_manual_keys !== [] ) {
			$manual_classes = $detector->get_manual_plugins( $extra_manual_keys );
			$plugin_classes = array_merge( $plugin_classes, $manual_classes );
		}

		// Step 3: Instantiate all active classes
		foreach ( $plugin_classes as $key => $class_name ) {
			$this->active_plugins[ $key ] = new $class_name();
		}

		// Step 4: Boot each instance, passing the complete list of active plugin class names
		foreach ( $this->active_plugins as $instance ) {
			$instance->init( $plugin_classes );
		}
	}

	/**
	 * Check if a specific integration is active.
	 */
	public function is_plugin_active( string $key ): bool {
		return isset( $this->active_plugins[ $key ] );
	}

	/**
	 * Retrieve a specific active integration instance.
	 */
	public function get_plugin( string $key ): ?Plugins_Interface {
		return $this->active_plugins[ $key ] ?? null;
	}

	/**
	 * Get all active integration instances.
	 *
	 * @return array<string, Plugins_Interface>
	 */
	public function get_active_plugins(): array {
		return $this->active_plugins;
	}
}
