<?php
/**
 * Plugin_Interface class
 *
 * @copyright (c) 2026, Lost Highway
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

/**
 * Interface for Plugins
 */
interface Plugins_Interface {

	/**
	 * Check if the underlying plugin or theme is active.
	 */
	public static function is_active(): bool;

	/**
	 * Initialize integration logic.
	 *
	 * @param array<string, class-string<Plugins_Interface>> $active_plugins
	 */
	public function init( array $active_plugins ): void;
}
