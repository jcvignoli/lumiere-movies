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
use \ReflectionClass;
use \ReflectionMethod;

/**
 * WP_CLI commands
 * These commands are only available when using wp-cli
 * Methods that can be called in wp-cli must be 1/ private, and 2/ start with 'sub_'
 *
 * @since 4.1.2
 * @see \WP_CLI the wp-cli methods
 * @see \ReflectionClass Allows to retrieve the methods
 * @see \ReflectionMethod Allows to specify we want private methods
 */
class Cli_Commands {

	/**
	 * List of subcommands built according to the private methods available in the class
	 * @see \Lumiere\Cli_Commands::get_private_methods()
	 *
	 * @var array<int, string> $list_subcommands
	 */
	private array $list_subcommands;

	/**
	 * List of subcommands built $list_subcommands as string
	 * @see \Lumiere\Cli_Commands::get_private_methods_asstring()
	 */
	private string $list_subcommands_asstring;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Build properties.
		$this->list_subcommands = $this->get_private_methods( new ReflectionClass( $this ) );
		$this->list_subcommands_asstring = $this->get_private_methods_asstring( $this->list_subcommands );
	}

	/**
	 * Build an array of private methods that start by 'sub_'
	 * Those methods are those which can be called as subcommands
	 *
	 * @param ReflectionClass<self> $reflec Class reflection with the current class
	 * @return array<int, string>
	 */
	private function get_private_methods( ReflectionClass $reflec ): array {

		$list_subcommands = [];

		$list_subcommands_object = $reflec->getMethods( ReflectionMethod::IS_PRIVATE );

		foreach ( $list_subcommands_object as $reflect_array ) {
			$private_method_name = $reflect_array->name;
			if ( str_starts_with( $private_method_name, 'sub_' ) ) {
				$list_subcommands[] = $private_method_name;
			}
		}
		return $list_subcommands;
	}

	/**
	 * Transform array of subcommands into a string
	 *
	 * @param array<int, string> $list_subcommands
	 */
	private function get_private_methods_asstring( array $list_subcommands ): string {
		return implode( ', ', str_replace( 'sub_', '', $list_subcommands ) );
	}

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
			function( array $args, array $assoc_args ): void {

				// Start the class.
				$that = new self();

				// Call the method in charge of the subcommand.
				$method_name = isset( $args[0] ) ? 'sub_' . $args[0] : '';
				if ( count( $args ) > 0 && method_exists( $that, $method_name ) && in_array( $method_name, $that->list_subcommands, true ) ) {

					$that->{$method_name}( $assoc_args );
					return;

					// If the subcommand doesn't exist as a class method, exit.
				} elseif ( count( $args ) > 0 && ! method_exists( $that, $method_name ) ) {

					WP_CLI::error( "'$args[0]' is not valid subcommand, valid subcommands: \"" . $that->list_subcommands_asstring . '"' );
				}

				// If not subcommand was passed, display this.
				WP_CLI::log( 'The WP Cli for Lumière WordPress Plugin is working but a subcommand is needed, available subcommands: "' . $that->list_subcommands_asstring . '"' );
			}
		);

	}

	/**
	 * Subcommand "initial"
	 * @param array<string, string> $assoc_args The list of arguments passed in --something=""
	 */
	private function sub_initial( array $assoc_args ): void {
		WP_CLI::success( 'Initial subcommand executed' );
	}
}

