<?php
/**
 * Class WP-CLI Commands
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Tools\Files;
use Lumiere\Admin\Copy_Templates\Copy_Theme;
use Lumiere\Admin\Crons\Cron;
use Lumiere\Enums\Item_Type;
use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Config\Get_Options_Person;
use WP_CLI;
use ReflectionClass;
use ReflectionMethod;

/**
 * WP_CLI commands
 * These commands are only available when using wp-cli
 *
 * Call this class in command-line: "wp lum"
 * Methods that can be called in wp-cli must be 1/ private, and 2/ start with 'sub_'
 *
 * @since 4.1.2
 * @see \WP_CLI the wp-cli methods
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Config\Settings
 * @phpstan-import-type OPTIONS_DATA_MOVIE from \Lumiere\Config\Settings_Movie
 * @phpstan-import-type OPTIONS_DATA_PERSON from \Lumiere\Config\Settings_Person
 */
final class Cli_Commands {

	/**
	 * Traits
	 */
	use Files;

	/**
	 * Admin options vars
	 * @var array<string, mixed>
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 * Constructor
	 */
	public function __construct() {
		$admin_options = get_option( Get_Options::get_admin_tablename(), [] );
		$this->imdb_admin_values = is_array( $admin_options ) ? $admin_options : [];
	}

	/**
	 * Main entry point for registering WP-CLI commands.
	 */
	public static function start(): void {
		WP_CLI::add_command(
			'lum',
			function ( array $args, array $assoc_args ): void {
				$instance   = new self();
				$subcommands = $instance->get_available_subcommands();
				$subcommand  = $args[0] ?? '';
				$method_name = 'sub_' . $subcommand;

				if ( $subcommand === '' ) {
					WP_CLI::log( 'The WP-CLI for Lumière WordPress Plugin is active. Available subcommands: ' . implode( ', ', $subcommands ) );
					return;
				}

				if ( ! in_array( $subcommand, $subcommands, true ) || ! method_exists( $instance, $method_name ) ) {
					WP_CLI::error( sprintf( "'%s' is not a valid subcommand. Valid options: %s", $subcommand, implode( ', ', $subcommands ) ) );
				}

				$instance->{$method_name}( $args, $assoc_args );
			}
		);
	}

	/**
	 * Dynamically retrieves available subcommands via Reflection.
	 *
	 * @return array<int, string>
	 */
	private function get_available_subcommands(): array {
		$reflection  = new ReflectionClass( $this );
		$methods     = $reflection->getMethods( ReflectionMethod::IS_PRIVATE );
		$subcommands = [];

		foreach ( $methods as $method ) {
			if ( str_starts_with( $method->name, 'sub_' ) ) {
				$subcommands[] = substr( $method->name, 4 );
			}
		}

		return $subcommands;
	}

	/**
	 * Subcommand "update_options"
	 *
	 * Updates admin|data_movie|data_person|cache options in the database.
	 *
	 * @param array<int, string> $args
	 * @param array<string, mixed> $dashed_extra_args
	 */
	private function sub_update_options( array $args, array $dashed_extra_args ): void {
		$target_type = $args[1] ?? '';
		$table_name  = $this->resolve_option_table( $target_type );

		if ( $table_name === null ) {
			WP_CLI::error( "Missing or invalid option type. Usage:\nwp lum update_options admin|data_movie|data_person|cache --array_key=new_value" );
		}

		assert( is_string( $table_name ) );

		if ( count( $dashed_extra_args ) === 0 ) {
			WP_CLI::error( "At least one option argument is required. Usage:\nwp lum update_options admin|data_movie|data_person|cache --array_key=new_value" );
		}

		$database_options = get_option( $table_name );
		if ( ! is_array( $database_options ) ) {
			WP_CLI::error( sprintf( 'The options for "%s" are not initialized in the database.', $target_type ) );
		}

		foreach ( $dashed_extra_args as $raw_key => $value ) {
			[ $array_key, $subkey ] = $this->parse_option_key( $raw_key );

			if ( ! array_key_exists( $array_key, $database_options ) ) {
				WP_CLI::error( sprintf( 'The option "%s" does not exist. Available keys: %s', $array_key, implode( ', ', array_keys( $database_options ) ) ) );
			}

			$parsed_value = $this->parse_input_value( $value );

			if ( $subkey !== null ) {
				if ( ! is_array( $database_options[ $array_key ] ) ) {
					$database_options[ $array_key ] = [];
				}
				$database_options[ $array_key ][ $subkey ] = $this->sanitize_option_value( $array_key, $parsed_value );
			} elseif ( is_array( $parsed_value ) && is_array( $database_options[ $array_key ] ) ) {
				$sanitized_group = [];
				foreach ( $parsed_value as $k => $v ) {
					$sanitized_group[ $k ] = $this->sanitize_option_value( $array_key, $v );
				}
				$database_options[ $array_key ] = array_replace( $database_options[ $array_key ], $sanitized_group );
			} else {
				$database_options[ $array_key ] = $this->sanitize_option_value( $array_key, $parsed_value );
			}

			if ( is_array( $database_options[ $array_key ] ) ) {
				$encoded   = wp_json_encode( $database_options[ $array_key ] );
				$log_value = is_string( $encoded ) ? $encoded : '[]';
			} else {
				$log_value = (string) $database_options[ $array_key ];
			}

			WP_CLI::log( sprintf( 'Updated option "%s" => %s', $array_key, $log_value ) );
		}

		update_option( $table_name, $database_options );

		// Handle Side Effects
		$cron = new Cron();
		if ( array_key_exists( 'imdbcachekeepsizeunder', $dashed_extra_args ) ) {
			$cron->cron_add_delete_oversize();
		}

		if ( array_key_exists( 'imdbcacheautorefreshcron', $dashed_extra_args ) ) {
			$cron->cron_add_delete_cache();
		}

		WP_CLI::success( 'Lumière options updated successfully.' );
	}

	/**
	 * Subcommand "copy_taxo"
	 *
	 * Copies taxonomy templates to the active theme directory.
	 *
	 * @param array<int, string> $args
	 * @param array<string, string> $dashed_extra_args
	 */
	private function sub_copy_taxo( array $args, array $dashed_extra_args ): void {
		$movies_key   = Item_Type::MOVIE->value . 's';
		$people_key   = 'people';
		$taxonomy_map = [
			$movies_key => Get_Options_Movie::get_list_items_taxo(),
			$people_key => Get_Options_Movie::get_list_people_taxo(),
		];

		$target_type = $args[1] ?? '';
		$taxonomy    = $dashed_extra_args['template'] ?? '';

		$is_valid_type = isset( $taxonomy_map[ $target_type ] );
		$is_valid_taxo = $is_valid_type && in_array( $taxonomy, $taxonomy_map[ $target_type ], true );

		if ( count( $dashed_extra_args ) !== 1 || ! $is_valid_taxo ) {
			$all_taxonomies = array_merge( ...array_values( $taxonomy_map ) );
			WP_CLI::error(
				sprintf(
					"Invalid options supplied. Command format:\nwp lum copy_taxo %s --template=%s",
					implode( '|', array_keys( $taxonomy_map ) ),
					implode( '|', $all_taxonomies )
				)
			);
		}

		Copy_Theme::wp_cli_copy_theme( $taxonomy );

		$taxo_prefix = isset( $this->imdb_admin_values['imdburlstringtaxo'] ) && is_string( $this->imdb_admin_values['imdburlstringtaxo'] )
			? $this->imdb_admin_values['imdburlstringtaxo']
			: '';

		WP_CLI::success( sprintf( 'The template *%s%s* was copied successfully.', $taxo_prefix, $taxonomy ) );
	}

	/**
	 * Maps an option target keyword to its corresponding DB option name.
	 */
	private function resolve_option_table( string $target ): ?string {
		return match ( $target ) {
			'data_movie'  => Get_Options_Movie::get_data_tablename(),
			'data_person' => Get_Options_Person::get_data_person_tablename(),
			'admin'       => Get_Options::get_admin_tablename(),
			'cache'       => Get_Options::get_cache_tablename(),
			default       => null,
		};
	}

	/**
	 * Extracts key and optional subkey bracket notation (e.g. `option[subkey]`).
	 *
	 * @return array{0: string, 1: string|null}
	 */
	private function parse_option_key( string $key ): array {
		if ( preg_match( '/^([^\[]+)\[([^\]]+)\]$/', $key, $matches ) === 1 ) {
			return [ $matches[1], $matches[2] ];
		}

		return [ $key, null ];
	}

	/**
	 * Decodes JSON inputs if valid, otherwise returns the original value.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	private function parse_input_value( mixed $value ): mixed {
		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
				return $decoded;
			}
		}

		return $value;
	}

	/**
	 * Centralized sanitization pipeline for option values.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	private function sanitize_option_value( string $key, mixed $value ): mixed {
		if ( in_array( $key, [ 'order', 'imdbwidgetorder', 'number' ], true ) ) {
			return (string) absint( $value );
		}

		if ( $key === 'activated' ) {
			return $value === '1' ? '1' : '0';
		}

		if ( in_array( $key, [ 'imdburlpopups', 'imdbplugindirectory', 'imdbplugindirectory_partial' ], true ) ) {
			return esc_url_raw( (string) $value );
		}

		$numeric_suffixes = [ 'larg', 'long', 'width', 'results', 'request', 'Updates', 'expire', 'limit', 'number' ];
		foreach ( $numeric_suffixes as $suffix ) {
			if ( str_ends_with( $key, $suffix ) ) {
				return (string) absint( $value );
			}
		}

		return is_string( $value ) ? sanitize_text_field( $value ) : $value;
	}
}
