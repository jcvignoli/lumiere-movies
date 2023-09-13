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
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

// use Lumiere library.
use Lumiere\Utils;

// use Monolog library in /vendor/.
use Monolog\Logger as LoggerMonolog;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\IntrospectionProcessor;

class Logger {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

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

		// Construct Global Settings trait.
		$this->settings_open();

		// Send the variables passed in construct to global properties
		$this->logger_name = $logger_name;
		$this->screen_output = $screen_output;

		// By default, start at init.
		add_action(
			'init',
			function(): void {
					$this->lumiere_start_logger( $this->logger_name, $this->screen_output );
			},
			0
		);

		// If init is too late, use lumiere_logger hook so we can activate manually.
		add_action(
			'lumiere_logger',
			function(): void {
					$this->lumiere_start_logger( $this->logger_name, $this->screen_output );
			}
		);

	}

	/**
	 * Detect if the current page is a classic or block editor page
	 *
	 */
	private function lumiere_is_screen_editor(): bool {

		/** Kept for memory.
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

			$this->is_editor_page = true;
			return true;

		}

		// If the referer of current page is a specific one, set $is_editor_page on true.
		// This is useful when saving a post in editor interface.
		$referer = strlen( $_SERVER['REQUEST_URI'] ) > 0 ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$pages_prohibited = [ '/wp-admin/admin-ajax.php', '/wp-admin/post.php', '/wp-json/wp/v2/posts' ];
		if ( Utils::lumiere_array_contains_term( $pages_prohibited, $_SERVER['REQUEST_URI'] ) ) {

			$this->is_editor_page = true;
			return true;

		}

		$this->is_editor_page = false;
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
	 * @return void the logger in $logger_class
	 */
	public function lumiere_start_logger ( ?string $logger_name, ?bool $screen_output = true ): void {

		// Get local vars and send to global class properties if set, if empty get the global vars.
		$logger_name = isset( $logger_name ) ? $this->logger_name = $logger_name : $logger_name = $this->logger_name;
		$screen_output = isset( $screen_output ) ? $this->screen_output = $screen_output : $screen_output = $this->screen_output;

		// Run WordPress block editor identificator giving value to $this->is_editor_page.
		$this->lumiere_is_screen_editor();

		// Start Monolog logger.
		if ( ( current_user_can( 'manage_options' ) && $this->imdb_admin_values['imdbdebug'] === '1' ) || ( $this->imdb_admin_values['imdbdebug'] === '1' && defined( 'DOING_CRON' ) && DOING_CRON ) ) {

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
				if ( is_file( $final_log_file ) === false || is_writable( $final_log_file ) === false ) {
					$final_log_file = $this->create_log( $final_log_file );
				}
				if ( $final_log_file === null ) {
					$this->logger_class = new LoggerMonolog( $logger_name );
					$this->logger_class->pushHandler( new NullHandler() );
					error_log( '***WP Lumiere Plugin ERROR***: not using log file ' );
					return;
				}
				// Use the latest log path value, it can have changed in create_log().
				$this->imdb_admin_values['imdbdebuglogpath'] = $final_log_file;

				// Add the file, the line, the class, the function to the log.
				$this->logger_class->pushProcessor( new IntrospectionProcessor( $logger_verbosity ) );
				$filelogger = new StreamHandler( $this->imdb_admin_values['imdbdebuglogpath'], $logger_verbosity );

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

				// Change the format
				$output = "[%level_name%] %message%<br />\n";
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
		return $this->logger_class;
	}

	/**
	 * Make sure debug log exists and is writable.
	 * @since 3.7.1
	 * @since 3.9.1, is a method, and using fopen and added error_log(), if file creation
	 * in wp-content fails try with Lumière plugin folder
	 *
	 * @param string $log_file Log file with the full path
	 * @param bool $second_try Whether the function is called a second time
	 * @return null|string Null if log creation was unsuccessful, Log full path file if successfull
	 */
	private function create_log( string $log_file, $second_try = false ): ?string {

		// Log file exists and is writable, hakuna matata, exit.
		if ( is_file( $log_file ) === true && is_writable( $log_file ) === true ) {
			return $log_file;
		}

		// Debug file doesn't exist, create it.
		$fp = fopen( $log_file, 'w' );
		if ( $fp === false ) {
			error_log( '***WP Lumiere Plugin ERROR***: cannot create debug file ' . $log_file );
		} else {
			fputs( $fp, '' );
			fclose( $fp );
			error_log( '***WP Lumiere Plugin***: created debug file ' . $log_file );
		}

		// Debug file exists, but it is not writable.
		if ( is_file( $log_file ) === true && is_writable( $log_file ) === false ) {
			// Permissions on the file are not correct, change them.
			if ( chmod( $log_file, 0775 ) === false ) {
				error_log( '***WP Lumiere Plugin ERROR***: cannot change permission of debug file ' . $log_file );
			} else {
				error_log( '***WP Lumiere Plugin***: changed chmod permissions debug file ' . $log_file );
			}
		}

		// If couldnt create debug file in wp-content, change the path to Lumière plugin folder.
		// This is run only on the first call of the method, using $second_try.
		if ( ( is_file( $log_file ) === false || is_writable( $log_file ) === false ) && $second_try === false ) {
			$log_file = $this->imdb_admin_values['imdbpluginpath'] . 'debug.log';
			$this->create_log( $log_file, true );
			error_log( '***WP Lumiere Plugin***: debug file could not be written in normal place, using plugin folder: ' . $log_file );
		}

		// If this failed again, send an Apache error message and exit.
		if ( is_file( $log_file ) === false || is_writable( $log_file ) === false ) {
			error_log( '***WP Lumiere Plugin ERROR***: Tried everything, cannot create any debug log both in wp-content and in Lumiere plugin folder.' );
			return null;
		}

		return $log_file;
	}

}

