<?php declare( strict_types = 1 );
/**
 * Class WP-CLI Commands
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */
namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	die( 'You can not call directly this page' );
}

use WP_CLI;

/**
 * WP_CLI commands
 * These commands are only available when using wp-cli
 * @since 4.1.2
 */
class Cli_Commands {

	/**
	 * Starting static method
	 */
	public static function lumiere_static_start(): void {

		WP_CLI::add_command(
			'lumiereworks',
			function( array $args ) {
				WP_CLI::log( 'The Lumière WordPress Plugin is working, congrats!' );
			}
		);
	}
}

