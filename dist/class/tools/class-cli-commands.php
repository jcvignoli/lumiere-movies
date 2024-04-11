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
namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	die( 'You can not call directly this page' );
}

use Lumiere\Settings;
use Lumiere\Tools\Files;
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
	 * Traits
	 */
	use Files;

	/**
	 * Admin options vars
	 * @var array<string, string>
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 */
	private array $imdb_admin_values;

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
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
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
	 *  --array_key=new_value
	 *  Ex:  --imdbdebug=0
	 *
	 * Pass the database to update admin|data|cache
	 * wp lum update_options admin|data|cache
	 *
	 * @param array<int, string> $args The first argument only is used to detect which subcommand run, such as "wp lum update_options "
	 * @param array<string, 'admin'|'data'|'cache'> $dashed_extra_args The list of arguments passed as in --array_key=new_value, [] if empty.
	 * @param-phpstan array<string, string>|OPTIONS_ADMIN|OPTIONS_CACHE|OPTIONS_DATA> $dashed_extra_args
	 */
	private function sub_update_options( array $args, array $dashed_extra_args ): void {

		// If no second main argument was passed, we don't know which database update, so exit.
		if ( ! isset( $args[1] ) || in_array( $args[1], [ 'admin', 'data', 'cache' ], true ) === false ) {
			WP_CLI::error( "The second argument is missing or wrong, the command must comply with:\nwp lum update_options admin|data|cache --array_key=new_value" );
		}

		// If no extra dashed arguments passed or more than one, exit.
		if ( count( $dashed_extra_args ) !== 1 ) {
			WP_CLI::error( "Use one extra argument as follows:\nwp lum update_options admin|data|cache --array_key=new_value" );
		}

		// Build the constant to call in Settings - can be admin, cache or data
		$settings_const = constant( '\Lumiere\Settings::LUMIERE_' . strtoupper( $args[1] ) . '_OPTIONS' );

		// Get options from DB and get the (first) array key from the passed values in $dashed_extra_args.
		$database_options = get_option( $settings_const );
		$array_key = array_key_first( $dashed_extra_args );

		// Exit if the array key doesn't exist in Lumière! DB admin options
		/** @psalm-suppress PossiblyNullArgument -- can never be null! */
		if ( array_key_exists( $array_key, $database_options ) === false ) {
			WP_CLI::error( 'This var does not exist, only accepted: ' . implode( ', ', array_keys( $database_options ) ) );

		}

		// Build new array and update database.
		$database_options[ $array_key ] = $dashed_extra_args[ $array_key ];
		update_option( $settings_const, $database_options );

		WP_CLI::success( 'Updated var ' . $array_key . ' with value ' . $database_options[ $array_key ] );
	}

	/**
	 * Subcommand "copy_taxo"
	 *
	 * Meant to copy taxonomy templates
	 * Pass the arguments like that:
	 *  copy_taxo items|people --template=items_name|people_name
	 *  Ex: wp lum copy_taxo items --template=genre
	 *
	 * @param array<int, 'items'|'people'> $args The first argument only is used to detect which subcommand run, "items|people"
	 * @param array<string, string> $dashed_extra_args The argument passed in --template=color|actor|etc, [] if empty.
	 */
	private function sub_copy_taxo( array $args, array $dashed_extra_args ): void {

		// Build the principal vars.
		$template_types = [ 'items', 'people' ];
		$items = [ 'color', 'country', 'genre', 'keyword', 'language' ];
		$people = [ 'actor', 'composer', 'creator', 'director', 'producer', 'writer' ];
		$all = array_merge( $items, $people );
		$array_items = [ $template_types[0] => $items ];
		$array_people = [ $template_types[1] => $people ];
		$array_all = array_merge( $array_items, $array_people );

		// Get the vars passed in command-line.
		$control = array_key_first( $dashed_extra_args );
		$taxonomy = $dashed_extra_args[ $control ] ?? '';

		// If no extra dashed arguments passed or more than one, if not in valid array, or not using --template="", exit.
		if ( count( $dashed_extra_args ) !== 1 || ! isset( $array_all[ $args[1] ] ) || in_array( $taxonomy, $array_all[ $args[1] ], true ) === false || $control !== 'template' ) {
			WP_CLI::error( "Selected options are wrong, must comply with:\nwp lum copy_taxo " . implode( '|', $template_types ) . ' --template=' . implode( '|', $all ) );
		}

		// Build source filename, except if no second main argument was passed exit.
		if ( in_array( $args[1], $template_types, true ) === true ) {
			$source_file = constant( '\Lumiere\Settings::TAXO_' . strtoupper( $args[1] ) . '_THEME' );
		} else {
			WP_CLI::error( "The extra argument must be either items or people as follows:\nwp lum copy_taxo " . implode( '|', $template_types ) . ' --template=' . implode( '|', $all ) );
		}

		// Build taxonomy new filename, directory and source.
		$template_name = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $taxonomy . '.php';
		$destination_full_name = get_stylesheet_directory() . '/' . $template_name;
		/** @psalm-suppress PossiblyUndefinedVariable -- psalm doesn't understand when building $source_full_name that WP_CLI::error is an exit function */
		$source_full_name = $this->imdb_admin_values['imdbpluginpath'] . $source_file; /** @phan-suppress-current-line PhanPossiblyUndeclaredVariable -- phan doesn't understand when building $source_full_name that WP_CLI::error is an exit function */

		// Copy templates to user's theme folder.
		global $wp_filesystem;
		$this->lumiere_wp_filesystem_cred( $destination_full_name ); // in trait Files.
		$wp_filesystem->touch( $destination_full_name );

		// Replace the content to adapt it to its new structure and wording.
		$content = $wp_filesystem->get_contents( $source_full_name );
		$content_cleaned = preg_replace( '~\*\sYou can replace.*automatically~s', '* Automatically copied from Lumiere! admin menu', $content );
		$content = $content_cleaned !== null ? str_replace( 'standard', $taxonomy, $content_cleaned ) : $content;
		$content = str_replace( 'Standard', ucfirst( $taxonomy ), $content );

		if ( $wp_filesystem->put_contents( $destination_full_name, $content ) === true ) {
			WP_CLI::success( 'The template ' . $template_name . ' has been copied to ' . $destination_full_name );
			return;
		}

		WP_CLI::error( 'Could not copy the selected template, check the permissions' );
	}
}

