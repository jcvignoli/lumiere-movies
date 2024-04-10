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
 *
 * Call this class in command-line: "wp lum"
 * Methods that can be called in wp-cli must be 1/ private, and 2/ start with 'sub_'
 * Adding a private method starting with "sub_" will automatically create a subcommand
 *
 * @since 4.1.2
 * @see \WP_CLI the wp-cli methods
 * @see \ReflectionClass Allows to retrieve the methods
 * @see \ReflectionMethod Allows to specify we want private methods
 * @phpstan-import-type OPTIONS_ADMIN from Settings
 * @phpstan-import-type OPTIONS_CACHE from Settings
 * @phpstan-import-type OPTIONS_DATA from Settings
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
			'lum',
			/**
			 * @param array<int, string> $args The first argument only is used to detect which subcommand run, such as "wp lum update_admin_option"
			 * @param array<string, string> $assoc_args The list of arguments passed in --something="", [] if empty.
			 */
			function( array $args, array $assoc_args ): void {

				// Start the class.
				$that = new self();

				// Call the method in charge of the subcommand.
				$method_name = isset( $args[0] ) ? 'sub_' . $args[0] : '';
				if ( count( $args ) > 0 && method_exists( $that, $method_name ) && in_array( $method_name, $that->list_subcommands, true ) ) {

					$that->{$method_name}( $args, $assoc_args );
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
	 * Subcommand "update_option"
	 *
	 * Meant to update admin|data|cache options in the database
	 * Pass the var like that:
	 *  --array-key=new_value
	 * Ex:  --imdbdebug=0
	 *
	 * Pass the database to update admin|data|cache
	 * wp lum update_options admin|data|cache
	 *
	 * @param array<int, string> $args The first argument only is used to detect which subcommand run, such as "wp lum update_options "
	 * @param array<string, string> $dashed_extra_args The list of arguments passed in --something="", [] if empty.
	 * @param-phpstan array<string, string>|OPTIONS_ADMIN|OPTIONS_CACHE|OPTIONS_DATA> $dashed_extra_args
	 */
	private function sub_update_options( array $args, array $dashed_extra_args ): void {

		// If no second main argument was passed, we don't know which database update, so exit.
		if ( ! isset( $args[1] ) || in_array( $args[1], [ 'admin', 'data', 'cache' ], true ) === false ) {
			WP_CLI::error( "The second argument is missing or wrong, the command must comply with:\nwp lum update_options admin|data|cache --array-key=new_value" );
		}

		// If no extra dashed arguments passed or more than one, exit.
		if ( count( $dashed_extra_args ) !== 1 ) {
			WP_CLI::error( 'Use one extra argument as follows: wp lum update_options admin|data|cache --array-key=new_value' );
		}

		// Build the constant to call in Settings - can be admin, cache or data
		$settings_const = constant( '\Lumiere\Settings::LUMIERE_' . strtoupper( $args[1] ) . '_OPTIONS' );

		// Get options from DB and get the (first) array key from the passed values in $dashed_extra_args.
		$database_options = get_option( $settings_const );
		$offset = array_key_first( $dashed_extra_args );

		// Exit if the array key doesn't exist in Lumière! DB admin options
		/** @psalm-suppress PossiblyNullArgument -- can never be null! */
		if ( array_key_exists( $offset, $database_options ) === false ) {
			WP_CLI::error( 'This var does not exist, only accepted: ' . implode( ', ', array_keys( $database_options ) ) );

		}

		// Build new array and update database.
		$database_options[ $offset ] = $dashed_extra_args[ $offset ];
		update_option( $settings_const, $database_options );

		WP_CLI::success( 'Updated var ' . $offset . ' with value ' . $database_options[ $offset ] );
	}
}

