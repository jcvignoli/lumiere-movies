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
use Lumiere\Vendor\Monolog\Logger as MonologLogger;
use Lumiere\Vendor\Monolog\Level;
use Lumiere\Vendor\Monolog\LogRecord;
use Lumiere\Vendor\Monolog\Handler\NullHandler;
use Lumiere\Vendor\Monolog\Handler\StreamHandler;
use Lumiere\Vendor\Monolog\Formatter\LineFormatter;
use Lumiere\Vendor\Monolog\Processor\IntrospectionProcessor;
use Lumiere\Vendor\Monolog\Processor\WebProcessor;
use Lumiere\Vendor\Psr\Log\AbstractLogger;
use Lumiere\Vendor\Psr\Log\NullLogger;
use Stringable;

/**
 * Custom Monolog Logger extending Monolog directly.
 *
 * Handles logging configuration, file streaming, on-screen output,
 * and context processor registration based on plugin settings.
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 */
final class Logger extends AbstractLogger {

	/**
	 * Traits
	 */
	use Files;

	/**
	 * Encapsulated Monolog instance.
	 */
	private MonologLogger $logger;

	/**
	 * Constructor.
	 *
	 * Initializes the parent Monolog Logger channel and configures stream handlers.
	 *
	 * @param string|null           $logger_name   Optional channel name. Defaults to current class name.
	 * @param bool                  $screen_output Optional flag to allow screen output. Default true.
	 * @param Settings_Service  $settings      Optional settings service instance for dependency injection.
	 */
	public function __construct(
		?string $logger_name = null,
		bool $screen_output = true,
		private Settings_Service $settings = new Settings_Service()
	) {
		$final_name = $logger_name ?? Data::get_current_classname( __CLASS__ );
		$this->logger = new MonologLogger( $final_name );
		$this->configure_logger( $screen_output, $settings );
	}

	/**
	 * Logs with an arbitrary level (PSR-3 v3 requirement).
	 *
	 * @param mixed              $level   Log level.
	 * @param string|Stringable $message Log message.
	 * @param array<mixed>       $context Contextual log data.
	 * @return void
	 */
	#[\Override]
	public function log( mixed $level, string|Stringable $message, array $context = [] ): void {
		$this->logger->log( $level, $message, $context );
	}

	/**
	 * Instantiates a standalone NullLogger for silent operations or fallbacks.
	 *
	 * @since 4.3 Method created
	 * @return NullLogger A PSR-3 compliant null logger instance.
	 */
	public function log_null(): NullLogger {
		return new NullLogger();
	}

	/**
	 * Sets up logger verbosity, timezone, and attaches active stream handlers.
	 *
	 * If global debugging is disabled, attaches a NullHandler to suppress all output safely.
	 *
	 * @param bool             $screen_output Flag determining if on-screen output is allowed for this instance.
	 * @param Settings_Service $settings      Settings provider instance.
	 * @return void
	 */
	private function configure_logger( bool $screen_output, Settings_Service $settings ): void {
		/** @phpstan-param OPTIONS_ADMIN $admin_options */
		$admin_options = $settings->get_admin_options();
		$is_debug_enabled = ( $admin_options['imdbdebug'] ?? '0' ) === '1';

		if ( ! $is_debug_enabled ) {
			$this->logger->pushHandler( new NullHandler() );
			return;
		}

		$this->logger->setTimezone( wp_timezone() );
		$verbosity = MonologLogger::toMonologLevel( $admin_options['imdbdebuglevel'] );

		$this->attach_file_handler( $admin_options, $verbosity );

		if ( current_user_can( 'manage_options' ) || wp_doing_cron() ) {
			$this->attach_screen_handler( $admin_options, $verbosity, $screen_output );
		}
	}

	/**
	 * Configures and attaches a file stream handler and processors if file logging is enabled.
	 *
	 * Attaches WebProcessor and IntrospectionProcessor for extra context (URL, line, class, method).
	 * Falls back to NullHandler if the log file path cannot be created or written to.
	 *
	 * @param array<string, string> $admin_options Admin configuration settings.
	 * @phpstan-param OPTIONS_ADMIN $admin_options
	 * @param Level                 $verbosity Minimum Monolog logging level threshold.
	 * @return void
	 */
	private function attach_file_handler( array $admin_options, Level $verbosity ): void {
		if ( ( $admin_options['imdbdebuglog'] ) !== '1' ) {
			return;
		}

		$this->logger->pushProcessor( new WebProcessor( null, [ 'url', 'referrer' ] ) );

		/**
		 * Create log file if it doesn't exist, use null logger and exit if can't write to the log.
		 * @since 3.9.1 created maybe_create_log() method, using its output to exit if no path created.
		 * @since 4.6 moved method maybe_create_log() to trait Files
		 */
		$log_file = $this->maybe_create_log( $admin_options );

		// Cannot create the log file, use nullhandler, print error_log() and exit.
		if ( $log_file === null ) {
			$this->logger->pushHandler( new NullHandler() );
			error_log( '***WP Lumiere Plugin ERROR***: cannot write to log file' ); // @phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return;
		}

		$this->logger->pushProcessor( new IntrospectionProcessor( $verbosity ) );

		$stream = new StreamHandler( $log_file, $verbosity );
		$date_format = 'd-M-Y H:i:s';
		$output = "[%datetime%] %channel%.%level_name%: %message% %extra%\n";
		$stream->setFormatter( new LineFormatter( $output, $date_format ) );

		$this->logger->pushHandler( $stream );
	}

	/**
	 * Configures and attaches an on-screen output stream handler (`php://output`) if enabled.
	 *
	 * Will abort attachment if screen logging is disabled in settings, disallowed by
	 * $screen_output, or if the current execution context is within an editor screen.
	 *
	 * @param array<string, string> $admin_options       Admin configuration settings.
	 * @phpstan-param OPTIONS_ADMIN $admin_options
	 * @param Level                 $verbosity     Minimum Monolog logging level threshold.
	 * @param bool                  $screen_output Flag indicating whether on-screen printing is enabled.
	 * @return void
	 */
	private function attach_screen_handler( array $admin_options, Level $verbosity, bool $screen_output ): void {
		if ( ( $admin_options['imdbdebugscreen'] ) !== '1' || $screen_output === false ) {
			return;
		}

		/**
		 * Use an anonymous StreamHandler subclass to evaluate screen context dynamically at log execution time
		 * When plugin first loads, WP doesn't know what page or editor screen are
		 * By evaluating is_screen_editor() inside isHandling(), the check is delayed until the exact microsecond a log
		 * message is written—giving WordPress time to set up $GLOBALS['pagenow'] and screen states
		 */
		$stream = new class( 'php://output', $verbosity, $this ) extends StreamHandler {
			public function __construct( string $stream, Level $level, private Logger $logger ) {
				parent::__construct( $stream, $level );
			}
			#[\Override]
			public function isHandling( LogRecord $record ): bool {
				// method in Monolog\Handler\AbstractHandler.
				if ( $this->logger->is_screen_editor() ) {
					return false;
				}
				return parent::isHandling( $record );
			}
		};

		$output = nl2br( "[%level_name%][Lumiere]%message%\n" );
		$stream->setFormatter( new LineFormatter( $output ) );
		$this->logger->pushHandler( $stream );
	}

	/**
	 * Checks if the current request is operating within a classic or block editor screen context.
	 *
	 * @return bool True if the current request is on a block editor or prohibited admin page; false otherwise.
	 */
	public function is_screen_editor(): bool {
		if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return true;
		}
		if ( isset( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], [ 'post.php', 'post-new.php' ], true ) ) {
			return true;
		}
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen !== null && $screen->is_block_editor() ) {
				return true;
			}
		}
		return false;
	}
}
