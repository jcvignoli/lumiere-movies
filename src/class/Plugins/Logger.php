<?php declare( strict_types = 1 );
/**
 * Class extanding Monolog Logger.
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

// use Lumiere library.
use Lumiere\Tools\Data;
use Lumiere\Tools\Files;
use Lumiere\Config\Open_Options;

// use Monolog library in /vendor/.
use Lumiere\Vendor\Monolog\Logger as LoggerMonolog;
use Lumiere\Vendor\Monolog\Handler\NullHandler;
use Lumiere\Vendor\Monolog\Handler\StreamHandler;
use Lumiere\Vendor\Monolog\Formatter\LineFormatter;
use Lumiere\Vendor\Monolog\Processor\IntrospectionProcessor;
use Lumiere\Vendor\Monolog\Processor\WebProcessor;

/**
 * Using Monolog Logger
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 */
final class Logger {

	// Trait including the database settings.
	use Open_Options, Files;

	/**
	 * Won't be executed on these pages
	 */
	const PAGES_PROHIBITED = [ '/wp-admin/admin-ajax.php', '/wp-admin/post.php', '/wp-json/wp/v2/posts' ];

	/**
	 * Property that is used all over the classes to display the log
	 */
	public ?LoggerMonolog $log;

	/**
	 * Constructor
	 *
	 * @param string $logger_name Title of Monolog logger
	 * @param bool $screen_output whether to output Monolog on screen or not
	 */
	public function __construct( string $logger_name = 'unknownOrigin', bool $screen_output = true ) {

		// Get global settings class properties.
		$this->get_db_options(); // In Open_Options trait.

		$this->log = $this->set_logger( $this->imdb_admin_values, $logger_name, $screen_output );
	}

	/**
	 * Function to call the Monolog Logger but with no info
	 * Usefull when do not want to execute anything, when log() is executed to early and breaks the layout
	 * @info: do not know why, but imdbGraphQL doesn't accept "null" as a value when calling Name or Title, so created this fake method
	 * @since 4.3 Method created
	 *
	 * @return LoggerMonolog the Monolog class
	 */
	public function log_null(): LoggerMonolog {
		return new LoggerMonolog( 'null' );
	}

	/**
	 * Detect if the current page is a classic or block editor page
	 * @return bool True if it is a block editor page
	 */
	private function lumiere_is_screen_editor(): bool {

		/**
		 * If the page called is post or post-new, set $is_editor_page on true.
		 * This is useful when displaying a post.
		 */
		if ( isset( $GLOBALS['hook_suffix'] )
			&& (
				$GLOBALS['hook_suffix'] === 'post.php'
				|| $GLOBALS['hook_suffix'] === 'post-new.php'
			)
		) {
			return true;
		}

		/**
		 * If the referer of current page is a specific one, set $is_editor_page on true.
		 * This is useful when saving a post in editor interface.
		 */
		$referer = esc_url_raw( wp_unslash( strval( $_SERVER['REQUEST_URI'] ?? '' ) ) );
		if ( Data::array_contains_term( self::PAGES_PROHIBITED, $referer ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Start and select which Logger to use
	 *
	 * @param array<string, string> $imdb_admin_values Options in database
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @param string $logger_name: title applied to the logger in the logs under origin
	 * @param bool $screen_output Optional: whether to display the screen output.
	 *
	 * @return LoggerMonolog the logger in set in $monolog_class
	 */
	private function set_logger( array $imdb_admin_values, string $logger_name, bool $screen_output = true ): LoggerMonolog {

		if (
			( current_user_can( 'manage_options' ) && isset( $imdb_admin_values['imdbdebug'] ) && $imdb_admin_values['imdbdebug'] === '1' )
			|| ( isset( $imdb_admin_values['imdbdebug'] ) && $imdb_admin_values['imdbdebug'] === '1' && wp_doing_cron() === true )
		) {

			// Start Monolog class.
			$monolog_class = new LoggerMonolog( $logger_name );
			$monolog_class->setTimezone( wp_timezone() );

			/**
			 * Set the verbosity from database option and build the constant.
			 * @phpstan-var value-of<\Lumiere\Vendor\Monolog\Level::VALUES> $logger_verbosity
			 * @psalm-var int $logger_verbosity $logger_verbosity
			 */
			$logger_verbosity = constant( LoggerMonolog::class . '::' . $imdb_admin_values['imdbdebuglevel'] );

			// Save to file if function is activated.
			$monolog_class = $this->save_logger( $monolog_class, $imdb_admin_values, $logger_verbosity );

			// Display on screen the log if function is activated.
			$monolog_class = $this->display_logger( $monolog_class, $imdb_admin_values, $logger_verbosity, $screen_output );

			return $monolog_class;
		} elseif ( isset( $imdb_admin_values['imdbdebug'] ) && $imdb_admin_values['imdbdebug'] === '1' ) {

			// Start Monolog class.
			$monolog_class = new LoggerMonolog( $logger_name );
			$monolog_class->setTimezone( wp_timezone() );

			/**
			 * Set the verbosity from database option and build the constant.
			 * @phpstan-var value-of<\Lumiere\Vendor\Monolog\Level::VALUES> $logger_verbosity
			 * @psalm-var int $logger_verbosity $logger_verbosity
			 */
			$logger_verbosity = constant( LoggerMonolog::class . '::' . $imdb_admin_values['imdbdebuglevel'] );

			// Save to file if function is activated.
			$monolog_class = $this->save_logger( $monolog_class, $imdb_admin_values, $logger_verbosity );

			return $monolog_class;
		}

		// Run null logger for all other cases.
		return $this->log_null();
	}

	/**
	 * Save log if option activated
	 *
	 * @param LoggerMonolog $monolog_class
	 * @param array<string, string> $imdb_admin_values Options in database
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @param int $logger_verbosity
	 * @phpstan-param value-of<\Lumiere\Vendor\Monolog\Level::VALUES> $logger_verbosity
	 * @psalm-param int $logger_verbosity
	 * @return LoggerMonolog
	 */
	private function save_logger( LoggerMonolog $monolog_class, array $imdb_admin_values, int $logger_verbosity ): LoggerMonolog {

		if ( $imdb_admin_values['imdbdebuglog'] !== '1' ) {
			return $monolog_class;
		}

		// Add current url and referrer to the log
		$monolog_class->pushProcessor( new WebProcessor( null, [ 'url', 'referrer' ] ) );

		/**
		 * Create log file if it doesn't exist, use null logger and exit if can't write to the log.
		 * @since 3.9.1 created maybe_create_log() method, using its output to exit if no path created.
		 * @since 4.6 moved method maybe_create_log() to trait Files
		 */
		$final_log_file = $this->maybe_create_log( $imdb_admin_values ); // In trait Files.

		// Cannot create the log file, use nullhandler, print error_log() and exit.
		if ( $final_log_file === null ) {
			$monolog_class->pushHandler( new NullHandler() );
			error_log( '***WP Lumiere Plugin ERROR***: cannot use any log file' ); // @phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return $monolog_class;
		}

		// Add the file, the line, the class, the function to the log.
		/** @psalm-suppress InvalidArgument (psalm can resolve value-of<\Lumiere\Vendor\Monolog\Level::VALUES>) */
		$monolog_class->pushProcessor( new IntrospectionProcessor( $logger_verbosity ) );

		// Change the date and output formats of the log.
		$date_format = 'd-M-Y H:i:s';
		$output = "[%datetime%] %channel%.%level_name%: %message% %extra%\n";
		$stream_class = new StreamHandler( $final_log_file, $logger_verbosity );
		$stream_class->setFormatter( new LineFormatter( $output, $date_format ) );
		$monolog_class->pushHandler( $stream_class );

		return $monolog_class;
	}

	/**
	 * Display errors on screen if option activated
	 * Avoid to display on screen when using block editor
	 *
	 * @param LoggerMonolog $monolog_class
	 * @param array<string, string> $imdb_admin_values Options in database
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @param int $logger_verbosity
	 * @phpstan-param value-of<\Lumiere\Vendor\Monolog\Level::VALUES> $logger_verbosity
	 * @psalm-param int $logger_verbosity
	 * @param bool $screen_output Optional: whether to display the screen output.
	 * @return LoggerMonolog
	 */
	private function display_logger( LoggerMonolog $monolog_class, array $imdb_admin_values, int $logger_verbosity, bool $screen_output ): LoggerMonolog {
		if (
			// IF: option 'debug on screen' is activated.
			$imdb_admin_values['imdbdebugscreen'] === '1'
			// IF: variable 'output on screen' is selected.
			&& $screen_output === true
			// IF: the page is not block editor (gutenberg).
			&& $this->lumiere_is_screen_editor() === false
		) {
			// Change the format. @since 4.0.1 added class lumiere_wrap that is only in admin.
			$output = "<div class=\"lumiere_wrap\">[%level_name%][Lumiere]%message%</div>\n";
			$formater_class = new LineFormatter( $output );

			// Change the handler, php://output is the only working (on my machine)
			$stream_class = new StreamHandler( 'php://output', $logger_verbosity );
			$stream_class->setFormatter( $formater_class );

			// Utilise the new handler and format
			$monolog_class->pushHandler( $stream_class );
		}
		return $monolog_class;
	}
}

