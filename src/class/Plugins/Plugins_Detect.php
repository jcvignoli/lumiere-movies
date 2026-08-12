<?php
/**
 * Plugins_Detect class
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Plugins\Plugins_Interface;

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
 * @since 4.8.2 rewrote the class, removed trait main
 */
final class Plugins_Detect {

	/**
	 * Registered manually triggered integration classes.
	 *
	 * @var array<string, class-string<Plugins_Interface>>
	 */
	private const MANUAL_PLUGINS = [
		'imdbphp' => Manual\Imdbphp::class,
	];

	/**
	 * Explicit map of auto-detected integrations.
	 * Eliminates runtime filesystem scanning via glob().
	 *
	 * @var array<string, class-string<Plugins_Interface>>
	 */
	private const AUTO_PLUGINS = [
		'amp'      => Auto\Amp::class,
		'aioseo'   => Auto\Aioseo::class,
		'irp'      => Auto\Irp::class,
		'oceanwp'  => Auto\Oceanwp::class,
		'polylang' => Auto\Polylang::class,
	];

	/**
	 * Evaluate all automatic plugins and return class strings for those currently active.
	 *
	 * @return array<string, class-string<Plugins_Interface>>
	 */
	public function get_active_plugins(): array {
		$active = [];

		foreach ( self::AUTO_PLUGINS as $key => $class_name ) {
			if ( class_exists( $class_name ) && $class_name::is_active() ) {
				$active[ $key ] = $class_name;
			}
		}

		return $active;
	}

	/**
	 * Evaluate specific manual keys and return class strings for those currently active.
	 *
	 * @param array<string> $keys Key names to check, e.g., ['imdbphp']
	 * @return array<string, class-string<Plugins_Interface>>
	 */
	public function get_manual_plugins( array $keys ): array {
		$manual = [];

		foreach ( $keys as $key ) {
			if ( isset( self::MANUAL_PLUGINS[ $key ] ) ) {
				$class_name = self::MANUAL_PLUGINS[ $key ];
				if ( class_exists( $class_name ) && $class_name::is_active() ) {
					$manual[ $key ] = $class_name;
				}
			}
		}
		return $manual;
	}
}
