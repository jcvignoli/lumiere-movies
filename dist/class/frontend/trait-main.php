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
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Link_Makers\Link_Factory;
use Lumiere\Plugins\Imdbphp;
use Lumiere\Plugins\Logger;
use Lumiere\Plugins\Plugins_Start;
use Lumiere\Tools\Data;
use Lumiere\Tools\Settings_Global;

/**
 * Frontend trait
 * Popups, movie, widget and taxonomy use this trait
 * Allow to use the logger, function utilities, and settings
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from Settings_Global
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
	 * @since 4.1
	 * @var array<string>
	 */
	public array $plugins_active_names = [];

	/**
	 * Classes that have been activated
	 *
	 * @var array<mixed> $plugins_classes_active
	 *
	 * @see Plugins_Start::plugins_classes_active
	 * @since 4.1
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
	}

	/**
	 * Build list of active plugins and send them in properties
	 *
	 * @param array<int, object>|array{} $extra_class An extra class to instanciate
	 * @since 4.1
	 */
	public function activate_plugins( array $extra_class = [] ): void {

		$extra_class[] = new Imdbphp();
		$plugins = new Plugins_Start( $extra_class );
		$this->plugins_active_names = $plugins->plugins_active_names;
		$this->plugins_classes_active = $plugins->plugins_classes_active;
	}

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
	 * Checks if Polylang is active in plugins before replacing links
	 *
	 * @since 3.11
	 * @param string $url The URL to edit
	 * @return string The URL compatible with Polylang
	 */
	public function lumiere_url_check_polylang_rewrite( string $url ): string {

		$final_url = null;
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
		/** untrue, @obsolete since 4.3
		if ( str_contains( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), '?amp' )
		|| isset( $_GET ['wpamp'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- it's detection, not submission!
		|| isset( $_GET ['amp'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- it's detection, not submission!
		) {
			return true;
		}
		*/

		/** @psalm-suppress RedundantCondition, UndefinedConstant -- Psalm can't deal with dynamic constants */
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

		// Since we are checking later (amp_is_request()) a function which is executed late, make sure we are allowed execute it
		if ( did_action( 'wp' ) === 0 ) {
			return false; // If wp wasn't executed, we can't use amp_is_request()
		}

		return function_exists( 'amp_is_request' ) && amp_is_request();
	}
}

