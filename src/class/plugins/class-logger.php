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
use Lumiere\Config\Get_Options;

// use Monolog library in /vendor/.
use Monolog\Logger as LoggerMonolog;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\IntrospectionProcessor;

/**
 * Using Monolog Logger
 * @Todo: Very poorly written, should extend Monolog
 */
class Logger {

	// Trait including the database settings.
	use Open_Options, Files;

	/**
	 * Is the current page an editing page?
	 */
	private bool $is_editor_page;

	/**
	 * Won't be executed on these pages
	 */
	const PAGES_PROHIBITED = [ '/wp-admin/admin-ajax.php', '/wp-admin/post.php', '/wp-json/wp/v2/posts' ];

	/**
	 * Class Monolog\Logger
	 */
	public LoggerMonolog $log;

	/**
	 * Constructor
	 *
	 * @param string $logger_name Title of Monolog logger
	 * @param bool $screen_output whether to output Monolog on screen or not
	 */
	public function __construct( string $logger_name = 'unknownOrigin', bool $screen_output = true ) {

		// Get global settings class properties.
		$this->get_db_options(); // In Open_Options trait.

		// Run WordPress block editor identificator giving value to $this->is_editor_page.
		$this->is_editor_page = $this->lumiere_is_screen_editor();

		$this->log = $this->set_logger( $logger_name, $screen_output );
	}

	/**
	 * Detect if the current page is a classic or block editor page
	 */
	private function lumiere_is_screen_editor(): bool {

		/** Kept for records.
		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once ABSPATH . '/wp-admin/includes/screen.php';
		}
		$screen = get_current_screen();
		$wp_is_block_editor = ( isset( $screen ) && ! is_null( $screen->is_block_editor() ) ) ? $screen->is_block_editor() : null;
		$post_type = ( isset( $screen ) && ! is_null( $screen->post_type ) ) ? $screen->post_type : null;
		*/

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
		$referer = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
		if ( Data::lumiere_array_contains_term( self::PAGES_PROHIBITED, $referer ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Start and select which Logger to use
	 *
	 * Can be called by the hook 'lumiere_logger_hook' or directly as a function
	 *
	 * @param string $logger_name: title applied to the logger in the logs under origin
	 * @param bool $screen_output: whether to display the screen output. Useful for plugin activation.
	 *
	 * @return LoggerMonolog the logger in set in $log
	 */
	private function set_logger( string $logger_name, bool $screen_output = true ): LoggerMonolog {

		// Start Monolog class.
		$log = new LoggerMonolog( $logger_name );

		/** @psalm-suppress UndefinedConstant, RedundantCondition -- Psalm can't deal with dynamic constants */
		if (
			( current_user_can( 'manage_options' ) && isset( $this->imdb_admin_values['imdbdebug'] ) && $this->imdb_admin_values['imdbdebug'] === '1' )
			|| ( isset( $this->imdb_admin_values['imdbdebug'] ) && $this->imdb_admin_values['imdbdebug'] === '1' && defined( 'DOING_CRON' ) && DOING_CRON )
		) {
			// Get the verbosity from options and build the constant.
			$logger_verbosity = constant( '\Monolog\Logger::' . $this->imdb_admin_values['imdbdebuglevel'] );

			/**
			 * Save log if option activated.
			 */
			if ( $this->imdb_admin_values['imdbdebuglog'] === '1' ) {

				// Add current url and referrer to the log
				//$logger->pushProcessor(new \Monolog\Processor\WebProcessor(NULL, array('url','referrer') ));

				// Create log file if it doesn't exist, use null logger and exit if can't write to the log.
				// @since 3.9.1 created create_log() method, using its output to exit if no path created.
				$final_log_file = $this->maybe_create_log( $this->imdb_admin_values['imdbdebuglogpath'] );

				if ( $final_log_file === null ) {
					$log->pushHandler( new NullHandler() );
					error_log( '***WP Lumiere Plugin ERROR***: cannot use any log file' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					return $log;
				}

				// Add the file, the line, the class, the function to the log.
				$log->pushProcessor( new IntrospectionProcessor( $logger_verbosity ) );
				$filelogger = new StreamHandler( $final_log_file, $logger_verbosity );

				// Change the date and output formats of the log.
				$date_format = 'd-M-Y H:i:s e';
				$output = "[%datetime%] %channel%.%level_name%: %message% %extra%\n";
				$screenformater = new LineFormatter( $output, $date_format );
				$filelogger->setFormatter( $screenformater );

				// Use the new format and processor.
				$log->pushHandler( $filelogger );
			}

			/**
			 * Display errors on screen if option activated.
			 * Avoid to display on screen when using block editor.
			 */
			if (
				// IF: option 'debug on screen' is activated.
				$this->imdb_admin_values['imdbdebugscreen'] === '1'
				// IF: variable 'output on screen' is selected.
				&& $screen_output === true
				// IF: the page is not block editor (gutenberg).
				&& $this->is_editor_page === false
			) {
				// Change the format. @since 4.0.1 added class lumiere_wrap that is only in admin.
				$output = "<div class=\"lumiere_wrap\">[%level_name%][Lumiere]%message%</div>\n";
				$screenformater = new LineFormatter( $output );

				// Change the handler, php://output is the only working (on my machine)
				$screenlogger = new StreamHandler( 'php://output', $logger_verbosity );
				$screenlogger->setFormatter( $screenformater );

				// Utilise the new handler and format
				$log->pushHandler( $screenlogger );
			}
			return $log;
		}

		// Run null logger for all other cases.
		$log->pushHandler( new NullHandler() );
		return $log;
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
	 * Make sure debug log exists and is writable.
	 * @since 3.7.1
	 * @since 3.9.1, is a method, and using fopen and added error_log(), if file creation in wp-content fails try with Lumière plugin folder
	 * @since 4.1.2 rewriting with global $wp_filesystem, refactorized, update the database with the new path if using Lumière plugin folder
	 *
	 * @param string $log_file Log file with the full path
	 * @param bool $second_try Whether the function is called a second time
	 * @return null|string Null if log creation was unsuccessful, Log full path file if successfull
	 */
	public function maybe_create_log( string $log_file, bool $second_try = false ): ?string {

		global $wp_filesystem;
		$this->lumiere_wp_filesystem_cred( $log_file ); // in trait Files.

		// Debug file doesn't exist, create it.
		if ( $wp_filesystem->is_file( $log_file ) === false ) {
			$wp_filesystem->put_contents( $log_file, '' );
			error_log( '***WP Lumiere Plugin***: Debug did not exist, created a debug file ' . $log_file ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		// Debug file permissions are wrong, change them.
		if ( $wp_filesystem->is_file( $log_file ) === true && $wp_filesystem->is_writable( $log_file ) === false ) {

			$fs_chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0770;

			// Permissions on the file are not correct, change them.
			if ( $wp_filesystem->chmod( $log_file, $fs_chmod ) === true ) {
				error_log( '***WP Lumiere Plugin***: changed chmod permissions debug file ' . $log_file ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				return $log_file;
			}
			error_log( '***WP Lumiere Plugin ERROR***: cannot change permission of debug file ' . $log_file ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		// If couldnt create debug file in wp-content, change the path to Lumière plugin folder.
		// This is run only on the first call of the method, using $second_try.
		if ( ( $wp_filesystem->is_file( $log_file ) === false || $wp_filesystem->is_writable( $log_file ) === false ) && $second_try === false ) {

			$log_file = $this->imdb_admin_values['imdbpluginpath'] . 'debug.log';

			// Update database with the new value for debug path.
			$new_options = get_option( Get_Options::get_admin_tablename() );
			$new_options['imdbdebuglogpath'] = $log_file;
			update_option( Get_Options::get_admin_tablename(), $new_options );

			error_log( '***WP Lumiere Plugin***: debug file could not be written in normal place, using plugin folder: ' . $log_file ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			$this->maybe_create_log( $log_file, true );
		}

		// If this failed again, send an Apache error message and exit.
		if ( is_file( $log_file ) === false || $wp_filesystem->is_writable( $log_file ) === false ) {
			error_log( '***WP Lumiere Plugin ERROR***: Tried everything, cannot create any debug log both neither in wp-content nor in Lumiere plugin folder.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return null;
		}
		return $log_file;
	}
}

