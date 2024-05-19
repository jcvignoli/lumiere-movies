<?php declare( strict_types = 1 );
/**
 * Class of Logging.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

// use Lumiere library.
use Lumiere\Tools\Utils;
use Lumiere\Tools\Files;
use Lumiere\Tools\Settings_Global;
use Lumiere\Settings;

// use Monolog library in /vendor/.
use Monolog\Logger as LoggerMonolog;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\IntrospectionProcessor;

/**
 * Plugin for Monolog Logger
 */
class Logger {

	// Trait including the database settings.
	use Settings_Global, Files;

	/**
	 * Screen output, whether to show the logging on screen
	 * @var bool $screen_output
	 */
	private bool $screen_output = true;

	/**
	 * The name of the logger, shown as the origin
	 * @var string $logger_name
	 */
	private string $logger_name = 'unknownOrigin';

	/**
	 * Is the current page an editing page?
	 */
	private bool $is_editor_page;

	/**
	 * Class Monolog\Logger
	 */
	public LoggerMonolog $logger_class;

	/**
	 * Constructor
	 *
	 * @param string $logger_name Title of Monolog logger
	 * @param bool $screen_output whether to output Monolog on screen or not
	 */
	public function __construct( string $logger_name, bool $screen_output = true ) {

		// Get Global Settings class properties.
		$this->get_db_options();

		// Send the variables passed in construct to global properties.
		$this->logger_name = $logger_name;
		$this->screen_output = $screen_output;

		// Add a hook so we can activate manually. Using anonymous function so can send the params.
		add_action(
			'lumiere_logger',
			function(): void {
				$this->lumiere_start_logger( $this->logger_name, $this->screen_output );
			}
		);
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

		// If the page called is post or post-new, set $is_editor_page on true.
		// This is useful when display a post.
		if ( isset( $GLOBALS['hook_suffix'] )
			&& ( $GLOBALS['hook_suffix'] === 'post.php'
			|| $GLOBALS['hook_suffix'] === 'post-new.php' ) ) {

			return true;
		}

		// If the referer of current page is a specific one, set $is_editor_page on true.
		// This is useful when saving a post in editor interface.
		$referer = strlen( $_SERVER['REQUEST_URI'] ?? '' ) > 0 ? wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) : '';
		$pages_prohibited = [ '/wp-admin/admin-ajax.php', '/wp-admin/post.php', '/wp-json/wp/v2/posts' ];
		if ( Utils::lumiere_array_contains_term( $pages_prohibited, $_SERVER['REQUEST_URI'] ?? '' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Start and select which Logger to use
	 *
	 * Can be called by the hook 'lumiere_logger_hook' or directly as a function
	 *
	 * @param null|string $logger_name: title applied to the logger in the logs under origin
	 * @param null|bool $screen_output: whether to display the screen output. Useful for plugin activation.
	 *
	 * @return void the logger in set in $logger_class
	 */
	public function lumiere_start_logger( ?string $logger_name, ?bool $screen_output = true ): void {

		global $wp_filesystem;

		// Get local vars and send to global class properties if set, if empty get the global vars.
		$logger_name = isset( $logger_name ) ? $this->logger_name = $logger_name : $logger_name = $this->logger_name;
		$screen_output = isset( $screen_output ) ? $this->screen_output = $screen_output : $screen_output = $this->screen_output;

		// Run WordPress block editor identificator giving value to $this->is_editor_page.
		$this->is_editor_page = $this->lumiere_is_screen_editor();

		// Start Monolog logger.
		/** @psalm-suppress UndefinedConstant -- Const DOING_CRON is not defined => can't declare dynamic constants with Psalm */
		if (
			( current_user_can( 'manage_options' ) && $this->imdb_admin_values['imdbdebug'] === '1' )
			|| ( $this->imdb_admin_values['imdbdebug'] === '1' && defined( 'DOING_CRON' ) && DOING_CRON )
		) {

			// Start Monolog logger.
			$this->logger_class = new LoggerMonolog( $logger_name );

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
				$final_log_file = $this->imdb_admin_values['imdbdebuglogpath'];
				$final_log_file = $this->maybe_create_log( $final_log_file );

				if ( $final_log_file === null ) {
					$this->logger_class = new LoggerMonolog( $logger_name );
					$this->logger_class->pushHandler( new NullHandler() );
					error_log( '***WP Lumiere Plugin ERROR***: cannot use any log file' );
					return;
				}

				// Add the file, the line, the class, the function to the log.
				$this->logger_class->pushProcessor( new IntrospectionProcessor( $logger_verbosity ) );
				$filelogger = new StreamHandler( $final_log_file, $logger_verbosity );

				// Change the date and output formats of the log.
				$date_format = 'd-M-Y H:i:s e';
				$output = "[%datetime%] %channel%.%level_name%: %message% %extra%\n";
				$screenformater = new LineFormatter( $output, $date_format );
				$filelogger->setFormatter( $screenformater );

				// Utilise the new format and processor.
				$this->logger_class->pushHandler( $filelogger );

			}

			/**
			 * Display errors on screen if option activated.
			 * Avoid to display on screen when using block editor.
			 */
			if (
				// IF: option 'debug on screen' is activated.
				( $this->imdb_admin_values['imdbdebugscreen'] === '1' )
				// IF: variable 'output on screen' is selected.
				&& ( $screen_output === true )
				// IF: the page is not block editor (gutenberg).
				&& ( $this->is_editor_page === false )
			) {

				// Change the format. @since 4.0.1 added class lumiere_wrap that is only in admin.
				$output = "<div class=\"lumiere_wrap\">[%level_name%] %message%</div>\n";
				$screenformater = new LineFormatter( $output );

				// Change the handler, php://output is the only working (on my machine)
				$screenlogger = new StreamHandler( 'php://output', $logger_verbosity );
				$screenlogger->setFormatter( $screenformater );

				// Utilise the new handler and format
				$this->logger_class->pushHandler( $screenlogger );
			}
			return;
		}

		// Run null logger for all other cases.
		$this->logger_class = new LoggerMonolog( $logger_name );
		$this->logger_class->pushHandler( new NullHandler() );
	}

	/**
	 * Function to call the Monolog Logger
	 *
	 * @return LoggerMonolog the Monolog class
	 */
	public function log(): LoggerMonolog {

		// Start the logger.
		do_action( 'lumiere_logger' );

		return $this->logger_class;
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
	private function maybe_create_log( string $log_file, $second_try = false ): ?string {

		global $wp_filesystem;
		$this->lumiere_wp_filesystem_cred( $log_file ); // in trait files.

		// Debug file doesn't exist, create it.
		if ( $wp_filesystem->is_file( $log_file ) === false ) {
			$wp_filesystem->put_contents( $log_file, '' );
			error_log( '***WP Lumiere Plugin***: created debug file ' . $log_file );
		}

		// Debug file permissions are wrong, change them.
		if ( $wp_filesystem->is_file( $log_file ) === true && $wp_filesystem->is_writable( $log_file ) === false ) {

			$fs_chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0770;

			// Permissions on the file are not correct, change them.
			if ( $wp_filesystem->chmod( $log_file, $fs_chmod ) === true ) {
				error_log( '***WP Lumiere Plugin***: changed chmod permissions debug file ' . $log_file );
				return $log_file;
			}
			error_log( '***WP Lumiere Plugin ERROR***: cannot change permission of debug file ' . $log_file );
		}

		// If couldnt create debug file in wp-content, change the path to Lumière plugin folder.
		// This is run only on the first call of the method, using $second_try.
		if ( ( $wp_filesystem->is_file( $log_file ) === false || $wp_filesystem->is_writable( $log_file ) === false ) && $second_try === false ) {

			$log_file = $this->imdb_admin_values['imdbpluginpath'] . 'debug.log';

			// Update database with the new value for debug path.
			$new_options = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
			$new_options['imdbdebuglogpath'] = $log_file;
			update_option( Settings::LUMIERE_ADMIN_OPTIONS, $new_options );

			error_log( '***WP Lumiere Plugin***: debug file could not be written in normal place, using plugin folder: ' . $log_file );
			$this->maybe_create_log( $log_file, true );
		}

		// If this failed again, send an Apache error message and exit.
		if ( is_file( $log_file ) === false || $wp_filesystem->is_writable( $log_file ) === false ) {
			error_log( '***WP Lumiere Plugin ERROR***: Tried everything, cannot create any debug log both neither in wp-content nor in Lumiere plugin folder.' );
			return null;
		}

		return $log_file;
	}

}

