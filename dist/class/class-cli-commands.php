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
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Starting static method
	 * Will add the WP Cli main command and subcommand
	 */
	public static function lumiere_static_start(): void {

		WP_CLI::add_command(
			'lumiere',
			/**
			 * @param array<int, string> $args The first argument only is used to detect which subcommand run, such as "wp lumiere initial"
			 * @param array<string, string> $assoc_args The list of arguments passed in --something="", [] if empty.
			 */
			function( array $args, array $assoc_args ) {

				// Start the class.
				$that = new self();

				// Call the method in charge of the subcommand.
				if ( isset( $args[0] ) && method_exists( __CLASS__, $args[0] ) ) {
					$that->{$args[0]}( $assoc_args );
					return;

					// If the subcommand doesn't exist as a class method, exit.
				} elseif ( isset( $args[0] ) && ! method_exists( __CLASS__, $args[0] ) ) {
					WP_CLI::error( "'$args[0]' is not valid subcommand" );
				}

				// If not subcommand is passed, display this.
				WP_CLI::log( 'The WP Cli for Lumière WordPress Plugin is working but a subcommand is needed' );
			}
		);

	}

	/**
	 * Subcommand initial
	 * @param array<string, string> $assoc_args The list of arguments passed in --something=""
	 */
	private function initial( array $assoc_args ): void {
		WP_CLI::success( 'Initial subcommand' );
	}
}

