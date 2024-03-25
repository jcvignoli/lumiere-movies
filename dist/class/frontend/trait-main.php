<?php declare( strict_types = 1 );
/**
 * Frontend Trait for pages including movies
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

use Lumiere\Plugins\Plugins_Start;
use Lumiere\Tools\Settings_Global;
use Lumiere\Tools\Data;
use Lumiere\Link_Makers\Link_Factory;
use Lumiere\Plugins\Logger;
use Lumiere\Plugins\Imdbphp;

/**
 * Frontend trait
 * Popups, movie, widget and taxonomy use this trait
 * Allow to use the logger, function utilities, and settings
 *
 * @phpstan-type PluginClasses \Imdb\Config
 */
trait Main {

	/**
	 * Traits
	 */
	use Settings_Global, Data;

	/**
	 * Name of the plugins active
	 *
	 * @see Plugins_Start::plugins_active_names
	 * @since 4.0.3
	 * @var array<string>
	 */
	public array $plugins_active_names = [];

	/**
	 * Classes that have been activated
	 *
	 * @var array<mixed> $plugins_classes_active
	 *
	 * @see Plugins_Start::plugins_classes_active
	 * @since 4.0.3
	 */
	public array $plugins_classes_active = [];

	/**
	 * Class for building links, i.e. Highslide
	 * Built in class Link Factory
	 *
	 * @phpstan-var \Lumiere\Link_Makers\Bootstrap_Links|\Lumiere\Link_Makers\AMP_Links|\Lumiere\Link_Makers\Highslide_Links|\Lumiere\Link_Makers\Classic_Links|\Lumiere\Link_Makers\No_Links $link_maker The factory class will determine which class to use
	 */
	public object $link_maker;

	/**
	 * Logging
	 */
	public Logger $logger;

	/**
	 * Name of the class
	 * Mainly utilised in logs
	 */
	public string $classname;

	/**
	 * Is the current page an editing page?
	 */
	/* @deprecated since 18 03 2024
	private bool $is_editor_page = false;
	*/

	/**
	 * Constructor-like
	 *
	 * @param null|string $logger_name Title for the logger output
	 * @param bool $screen_output Whether to output Monolog on screen or not
	 */
	public function start_main_trait( ?string $logger_name = null, bool $screen_output = true ): void {

		/**
		 * Get Global Settings class properties.
		 * Create the properties needed
		 */
		$this->get_settings_class();
		$this->get_db_options();

		// Start Logger class, if no name was passed build it with method get_current_classname().
		$this->logger = new Logger( $logger_name ?? $this->get_current_classname(), $screen_output );

		// Instanciate link maker classes (\Lumiere\Link_Maker\Link_Factory)
		$this->link_maker = Link_Factory::lumiere_link_factory_start();

		// Get name of the class, in trait Data.
		$this->classname = $this->get_current_classname();

		/* @deprecated since 18 03 2024
		// Start checking if current page is block editor
		add_action( 'init', [ $this, 'lumiere_frontend_is_editor' ], 0 );

		// Start the debugging
		add_action( 'plugins_loaded', [ $this, 'lumiere_frontend_maybe_start_debug' ], 1 );
		*/
	}

	/**
	 * Build list of active plugins and send them in properties
	 *
	 * @param array<int, object>|array{} $extra_class An extra class to instanciate
	 * @since 4.0.3
	 */
	public function activate_plugins( array $extra_class = [] ): void {

		$extra_class[] = new Imdbphp();
		$plugins = new Plugins_Start( $extra_class );
		$this->plugins_active_names = $plugins->plugins_active_names;
		$this->plugins_classes_active = $plugins->plugins_classes_active;
	}

	/**
	 * Detect whether it is a block editor (gutenberg) page
	 */
	/* @deprecated since 18 03 2024
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

	}*/

	/**
	 * Start debug if conditions are met
	 */
	/* @deprecated since 18 03 2024
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

	}*/

	/**
	 * Remove html links <a></a>
	 *
	 * @param string $text text to be cleaned from every html link
	 * @return string $output text that has been cleaned from every html link
	 */
	public function lumiere_remove_link( string $text ): string {

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
	public function lumiere_url_check_polylang_rewrite( string $url ): string {

		$final_url = null;
		/* testing if really needed
		if ( count($this->plugins_active_names) === 0 ) {
			$this->lumiere_set_plugins_array();
		}
		*/
		if ( in_array( 'polylang', $this->plugins_active_names, true ) ) {
			$replace_url = str_replace( home_url(), trim( pll_home_url(), '/' ), $url );
			$final_url = trim( $replace_url, '/' );
		}
		return $final_url ?? $url;
	}

	/**
	 * Are we currently on an AMP URL?
	 * Always return `false` and show PHP Notice if called before the `wp` hook.
	 *
	 * @since 3.7.1
	 * @return bool true if amp url, false otherwise
	 */
	public function lumiere_is_amp_page(): bool {

		global $pagenow;

		// If url contains ?amp, it must be an AMP page
		if ( str_contains( $_SERVER['REQUEST_URI'] ?? '', '?amp' )
		|| isset( $_GET ['wpamp'] )
		|| isset( $_GET ['amp'] )
		) {
			return true;
		}

		if (
			is_admin()
			/**
			 * If kept, breaks blog pages these functions can be executed very early
				|| is_embed()
				|| is_feed()
			*/
			|| ( isset( $pagenow ) && in_array( $pagenow, [ 'wp-login.php', 'wp-signup.php', 'wp-activate.php' ], true ) )
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			|| ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
		) {
			return false;
		}

		// Since we are checking later (amp_is_request()) a function that execute late, make sure we can execute it
		if ( did_action( 'wp' ) === 0 ) {
			return false;
		}

		return function_exists( 'amp_is_request' ) && amp_is_request();
	}
}

