<?php
/**
 * Class extending Monolog Logger.
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

// Lumiere libraries.
use Lumiere\Tools\Data;
use Lumiere\Tools\Files;
use Lumiere\Config\Settings_Service;

// Monolog/Psr libraries.
use Lumiere\Vendor\Monolog\Logger as LoggerMonolog;
use Lumiere\Vendor\Monolog\Level;
use Lumiere\Vendor\Monolog\Handler\NullHandler;
use Lumiere\Vendor\Monolog\Handler\StreamHandler;
use Lumiere\Vendor\Monolog\Formatter\LineFormatter;
use Lumiere\Vendor\Monolog\Processor\IntrospectionProcessor;
use Lumiere\Vendor\Monolog\Processor\WebProcessor;
use Lumiere\Vendor\Psr\Log\LoggerInterface;
use Lumiere\Vendor\Psr\Log\LoggerTrait;
use Lumiere\Vendor\Psr\Log\NullLogger;

/**
 * Monolog Logger
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 */
final class Logger implements LoggerInterface {

	/**
	 * Traits
	 */
	use LoggerTrait;
	use Files;

	/**
	 * Won't be executed on these pages
	 */
	const PAGES_PROHIBITED = [ '/wp-admin/admin-ajax.php', '/wp-admin/post.php', '/wp-json/wp/v2/posts' ];

	/**
	 * Property that is used all over the classes to display the log
	 */
	public ?LoggerInterface $log;

	/**
	 * Constructor
	 *
	 * @param string|null $logger_name Title of Monolog logger
	 * @param bool $screen_output whether to output Monolog on screen or not
	 * @param Settings_Service $settings
	 */
	public function __construct(
		?string $logger_name = null,
		bool $screen_output = true,
		private Settings_Service $settings = new Settings_Service()
	) {
		$final_name = $logger_name ?? Data::get_current_classname( __CLASS__ );
		$this->log = $this->set_logger( $final_name, $screen_output );
	}

	/**
	 * Function to call the Monolog Logger but with no info
	 * Usefull when do not want to execute anything, when log() is executed to early and breaks the layout
	 * @info: do not know why, but imdbGraphQL doesn't accept "null" as a value when calling Name or Title, so created this fake method
	 * @since 4.3 Method created
	 *
	 * @return LoggerInterface the Monolog class
	 */
	public function log_null(): LoggerInterface {
		return new NullLogger();
	}

	/**
	 * Delegate standard PSR-3 calls ($this->debug, $this->info, etc.) to $this->log
	 */
	public function log( $level, string|\Stringable $message, array $context = [] ): void {
		$this->log?->log( $level, $message, $context );
	}

	/**
	 * Detect if the current page is a classic or block editor page
	 * @return bool True if it is a block editor page
	 */
	private function is_screen_editor(): bool {

		/**
		 * If the page called is post or post-new, set $is_editor_page on true.
		 * This is useful when displaying a post.
		 */
		if ( isset( $GLOBALS['pagenow'] )
			&& (
				$GLOBALS['pagenow'] === 'post.php'
				|| $GLOBALS['pagenow'] === 'post-new.php'
			)
		) {
			return true;
		}

		/**
		 * If the referer of current page is a specific one, set $is_editor_page on true.
		 * This is useful when saving a post in editor interface.
		 */
		$referer = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( (string) $_SERVER['REQUEST_URI'] ) ) : '';
		if ( Data::array_contains_term( self::PAGES_PROHIBITED, $referer ) ) {
			return true;
		}

		/**
		 * test with WP_Screen class
		 */
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
			if ( $current_screen !== null ) {
				return $current_screen->is_block_editor();
			}
		}

		return false;
	}

	/**
	 * Start and select which Logger to use
	 *
	 * @param string $logger_name: title applied to the logger in the logs under origin
	 * @param bool $screen_output Optional: whether to display the screen output.
	 *
	 * @return LoggerInterface the logger in set in $monolog_class
	 */
	private function set_logger( string $logger_name, bool $screen_output = true ): LoggerInterface {

		/** @phpstan-param OPTIONS_ADMIN $imdb_admin_values */
		$imdb_admin_values = $this->settings->get_admin_options();
		$is_debug_enabled  = ( $imdb_admin_values['imdbdebug'] ?? '0' ) === '1';

		if ( ! $is_debug_enabled ) {
			return $this->log_null();
		}

		$monolog_class = new LoggerMonolog( $logger_name );
		$monolog_class->setTimezone( wp_timezone() );
		$logger_verbosity = LoggerMonolog::toMonologLevel( $imdb_admin_values['imdbdebuglevel'] );

		$monolog_class = $this->save_logger( $monolog_class, $imdb_admin_values, $logger_verbosity );

		if ( current_user_can( 'manage_options' ) || wp_doing_cron() ) {
			$monolog_class = $this->display_logger( $monolog_class, $imdb_admin_values, $logger_verbosity, $screen_output );
		}
		return $monolog_class;
	}

	/**
	 * Save log if option activated
	 *
	 * @param LoggerMonolog $monolog_class
	 * @param array<string, string> $imdb_admin_values Options in database
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @param Level $logger_verbosity
	 * @return LoggerMonolog
	 */
	private function save_logger(
		LoggerMonolog $monolog_class,
		array $imdb_admin_values,
		Level $logger_verbosity
	): LoggerMonolog {

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
	 * @param Level $logger_verbosity
	 * @param bool $screen_output Optional: whether to display the screen output.
	 * @return LoggerMonolog
	 */
	private function display_logger(
		LoggerMonolog $monolog_class,
		array $imdb_admin_values,
		Level $logger_verbosity,
		bool $screen_output
	): LoggerMonolog {
		if (
			// IF: option 'debug on screen' is activated.
			$imdb_admin_values['imdbdebugscreen'] !== '1'
			// IF: variable 'output on screen' is selected.
			|| $screen_output === false
			// IF: the page is not block editor (gutenberg).
			|| $this->is_screen_editor() === true
		) {
			return $monolog_class;
		}

		// Change the format. @since 4.8 using lum_debug class that is only available in admin.
		$output = nl2br( "[%level_name%][Lumiere]%message%\n" );
		$formater_class = new LineFormatter( $output );

		// Change the handler, php://output is the only working (on my machine)
		$stream_class = new StreamHandler( 'php://output', $logger_verbosity );
		$stream_class->setFormatter( $formater_class );

		// Utilise the new handler and format
		$monolog_class->pushHandler( $stream_class );

		return $monolog_class;
	}
}
