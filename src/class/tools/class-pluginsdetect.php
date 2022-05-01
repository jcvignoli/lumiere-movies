<?php declare( strict_types = 1 );
/**
 * Class to detect which WP plugins are in use
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

class PluginsDetect {

	/**
	 * Array of plugins in use
	 *
	 * @var array[] $pluginsClass
	 */
	public array $plugins_class = [];

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Initialise
		$this->lumiere_detect_active_plugins();
	}

	/**
	 * Build list of plugins active in var $pluginsClass pluginsClass
	 *
	 * @return void
	 */
	public function lumiere_detect_active_plugins(): void {

		// AMP
		if ( $this->amp_is_active() === true ) {
			array_push( $this->plugins_class, 'AMP' );
		}
		// Polylang
		if ( $this->polylang_is_active() === true ) {
			array_push( $this->plugins_class, 'POLYLANG' );
		}

	}

	/**
	 * Determine whether AMP is activated
	 *
	 * @since Lumière! v.3.7
	 *
	 * @return bool Is AMP plugin is active.
	 */
	protected function amp_is_active(): bool {

		return function_exists( 'amp_is_request' ) && amp_is_request();

	}

	/**
	 * Determine whether Polylang is activated
	 *
	 * @since Lumière! v.3.7
	 *
	 * @return bool Is Polylang Polylang plugin is active.
	 */
	protected function polylang_is_active(): bool {

		if ( function_exists( 'pll_count_posts' ) ) {
			return true;
		}

		return false;

	}

}
