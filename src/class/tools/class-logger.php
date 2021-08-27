<?php declare( strict_types = 1 );
/**
 * Class of Logging.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

// use Monolog library in /vendor/
use \Monolog\Logger as LoggerMonolog;
use \Monolog\Handler\NullHandler;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Processor\IntrospectionProcessor;

class Logger {

	/**
	 * Admin options
	 * @var array<string> $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 * Is the current page an editing page?
	 */
	private bool $is_editor_page;

	/**
	 * Class \Monolog\Logger
	 *
	 */
	public LoggerMonolog $logger_class;

	/**
	 * Constructor
	 *
	 * @param string $logger_name Title of Monolog logger
	 * @param bool $screenOutput whether to output Monolog on screen or not
	 */
	public function __construct( ?string $logger_name = 'unknownOrigin', ?bool $screenOutput = true ) {

		// Get database options.
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );

		// By default, start at init.
		add_action(
			'init',
			function() use ( $logger_name, $screenOutput ): void {
					$this->lumiere_start_logger( $logger_name, $screenOutput );
			},
			0
		);

		// If init is too late, use lumiere_logger hook so we can activate manually.
		add_action(
			'lumiere_logger',
			function() use ( $logger_name, $screenOutput ): void {
					$this->lumiere_start_logger( $logger_name, $screenOutput );
			}
		);

	}

	/**
	 * Detect if the current page is an editor page (post.php or post-new.php)
	 *
	 */
	private function lumiere_is_screen_editor(): bool {

		/*
		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once ABSPATH . '/wp-admin/includes/screen.php';
		}

		$screen = get_current_screen();
		$wp_is_block_editor = ( isset( $screen ) && ! is_null( $screen->is_block_editor() ) ) ? $screen->is_block_editor() : null;
		$post_type = ( isset( $screen ) && ! is_null( $screen->post_type ) ) ? $screen->post_type : null;
		*/
		if ( ! isset( $GLOBALS['hook_suffix'] ) || $GLOBALS['hook_suffix'] !== 'post.php' && $GLOBALS['hook_suffix'] !== 'post-new.php' ) {

			$this->is_editor_page = false;
			return false;

		}

		$this->is_editor_page = true;
		return true;

	}

	/**
	 * Start and select which Logger to use
	 *
	 * Can be called by the hook 'lumiere_logger_hook' or directly as a function
	 *
	 * @param string $logger_name: title applied to the logger in the logs under origin
	 * @param bool $screenOutput: whether to display the screen output. Useful for plugin activation.
	 *
	 * @return void the logger in $logger_class
	 */
	public function lumiere_start_logger ( ?string $logger_name, ?bool $screenOutput = true ): void {

		// Get local vars if passed in the function, if empty get the global vars.
		$logger_name = isset( $logger_name ) ? $logger_name : 'unknowOrigin';

		// Run WordPress block editor identificator giving value to $this->is_editor_page.
		$this->lumiere_is_screen_editor();

		// Start Monolog logger.
		if ( ( current_user_can( 'manage_options' ) && $this->imdb_admin_values['imdbdebug'] === '1' ) || ( $this->imdb_admin_values['imdbdebug'] === '1' && defined( 'DOING_CRON' ) && DOING_CRON ) ) {

			// Start Monolog logger.
			$this->logger_class = new LoggerMonolog( $logger_name );

			// Get the verbosity from options and build the constant.
			$logger_verbosity = isset( $this->imdb_admin_values['imdbdebuglevel'] ) ? constant( '\Monolog\Logger::' . $this->imdb_admin_values['imdbdebuglevel'] ) : constant( '\Monolog\LoggerMonolog::DEBUG' );

			/**
			 * Save log if option activated.
			 */
			if ( $this->imdb_admin_values['imdbdebuglog'] === '1' ) {

				// Add current url and referrer to the log
				//$logger->pushProcessor(new \Monolog\Processor\WebProcessor(NULL, array('url','referrer') ));

				// Add the file, the line, the class, the function to the log.
				$this->logger_class->pushProcessor( new IntrospectionProcessor( $logger_verbosity ) );
				$filelogger = new StreamHandler( $this->imdb_admin_values['imdbdebuglogpath'], $logger_verbosity );

				// Change the date and output formats of the log.
				$dateFormat = 'd-M-Y H:i:s e';
				$output = "[%datetime%] %channel%.%level_name%: %message% %extra%\n";
				$screenformater = new LineFormatter( $output, $dateFormat );
				$filelogger->setFormatter( $screenformater );

				// Utilise the new format and processor.
				$this->logger_class->pushHandler( $filelogger );
			}

			/**
			 * Display errors on screen if option activated.
			 * Avoid to display on screen when using block editor.
			 */
			if ( ( $this->imdb_admin_values['imdbdebugscreen'] === '1' ) && ( $screenOutput === true ) && ( $this->is_editor_page === false ) ) {

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

	public function log(): LoggerMonolog {
		return $this->logger_class;
	}

}
