<?php declare( strict_types = 1 );
/**
 * Frontend Trait for pages including movies
 * Popups, movies are using this trait
 * Allow to use the logger, function utilities, and settings
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.2
 * @package lumiere-movies
 */

namespace Lumiere\Frontend;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\PluginsDetect;
use Lumiere\Tools\Utils;
use Lumiere\Link_Makers\Link_Factory;
use Lumiere\Plugins\Logger;
use Lumiere\Plugins\Imdbphp;
use Lumiere\Plugins\Polylang;

trait Main {

	// Global settings trait.
	use \Lumiere\Settings_Global;

	/**
	 * \Lumière\Plugins class
	 * Array of plugins in use
	 *
	 * @since 3.7
	 * @var array<int, string>
	 * @phpstan-ignore-next-line PHPStan complains that var is not defined for some contexts
	 */
	public array $plugins_in_use = [];

	/**
	 * Class for building links, i.e. Highslide
	 * Built in class Link Factory
	 *
	 * @var \Lumiere\Link_Makers\Bootstrap_Links|\Lumiere\Link_Makers\AMP_Links|\Lumiere\Link_Makers\Highslide_Links|\Lumiere\Link_Makers\Classic_Links|\Lumiere\Link_Makers\No_Links $link_maker The factory class will determine which class to use
	 */
	public \Lumiere\Link_Makers\Bootstrap_Links|\Lumiere\Link_Makers\AMP_Links|\Lumiere\Link_Makers\Highslide_Links|\Lumiere\Link_Makers\Classic_Links|\Lumiere\Link_Makers\No_Links $link_maker;

	/**
	 * Class \Lumiere\Utils
	 *
	 */
	public Utils $utils_class;

	/**
	 * Class \Lumiere\Imdbphp
	 *
	 */
	public Imdbphp $imdbphp_class;

	/**
	 * Class \Lumiere\Logger
	 *
	 */
	public Logger $logger;

	/**
	 * Is the current page an editing page?
	 */
	private bool $is_editor_page = false;

	/**
	 * Constructor
	 *
	 * @param string $logger_name Title of Monolog logger
	 * @param bool $screen_output whether to output Monolog on screen or not
	 */
	public function __construct( string $logger_name = 'unknownOrigin', bool $screen_output = true ) {

		// Build Global settings.
		$this->settings_open();

		// Start Logger class.
		$this->logger = new Logger( $logger_name, $screen_output );

		// Start Utils class.
		$this->utils_class = new Utils();

		// Start Imdbphp class.
		$this->imdbphp_class = new Imdbphp();

		// Instanciate link maker classes (\Lumiere\Link_Maker\Link_Factory)
		$this->link_maker = Link_Factory::lumiere_link_factory_start();

		// Start checking if current page is block editor
		add_action( 'init', [ $this, 'lumiere_frontend_is_editor' ], 0 );

		// Start the debugging
		add_action( 'plugins_loaded', [ $this, 'lumiere_frontend_maybe_start_debug' ], 1 );

		// Display log of list of WP plugins compatible with Lumiere
		#add_action( 'the_post', [ $this, 'lumiere_log_plugins' ], 0 );

	}

	/**
	 * Display list of WP plugins compatible with Lumière!
	 * Use Logger class, already instancialised
	 *
	 * @since 3.7
	 */
	public function lumiere_log_plugins(): void {

		$this->logger->log()->debug( '[Lumiere] The following plugins compatible with Lumière! are in use: [' . join( ', ', $this->plugins_in_use ) . ' ]' );

	}

	/**
	 * Determine list of plugins active in array
	 * Build the PluginsDetect class and fill $this->plugins_in_use with the array of plugins in use
	 *
	 * @since 3.7
	 */
	public function lumiere_set_plugins_array(): void {

		$plugins = new PluginsDetect();
		$this->plugins_in_use = $plugins->plugins_class;

	}

	/**
	 * Detect whether it is a block editor (gutenberg) page
	 */
	public function lumiere_frontend_is_editor(): void {

		$referer = strlen( $_SERVER['REQUEST_URI'] ?? '' ) > 0 ? wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) : '';
		$pages_prohibited = [
			'/wp-admin/admin-ajax.php',
			'/wp-admin/widget.php',
			'/wp-admin/post.php',
			'/wp-admin/post-new.php',
			'/wp-json/wp/v2/posts',
		];
		if ( Utils::lumiere_array_contains_term( $pages_prohibited, $referer ) ) {

			$this->is_editor_page = true;

		}

	}

	/**
	 * Start debug if conditions are met
	 */
	public function lumiere_frontend_maybe_start_debug(): void {

		// If editor page, exit.
		// Useful for block editor pages (gutenberg).
		if ( $this->is_editor_page === true ) {
			return;
		}

		// If the user can't manage options and it's not a cron, exit.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// If debug is active.
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( '1' === $this->imdb_admin_values['imdbdebug'] ) && ( $this->utils_class->debug_is_active === false ) ) {

			$this->utils_class->lumiere_activate_debug();

		}

	}

	/**
	 * Remove html links <a></a>
	 *
	 * @param string $text text to be cleaned from every html link
	 * @return string $output text that has been cleaned from every html link
	 */
	public function lumiere_remove_link ( string $text ): string {

		$output = preg_replace( '/<a(.*?)>/', '', $text ) ?? $text;
		$output = preg_replace( '/<\/a>/', '', $output ) ?? $output;

		return $output;

	}

	/**
	 * Rewrite the provided link in Polylang format
	 * Check if Polylang exists
	 *
	 * @since 3.11
	 * @param string $url The URL to edit
	 * @return string The URL compatible with Polylang
	 */
	public function lumiere_url_check_polylang_rewrite ( string $url ): string {
		$final_url = null;
		$polylang_class = new Polylang();
		if ( $polylang_class->polylang_is_active() === true ) {
			$replace_url = str_replace( home_url(), trim( pll_home_url(), '/' ), $url );
			$final_url = trim( $replace_url, '/' );
		}
		return $final_url ?? $url;
	}
}

