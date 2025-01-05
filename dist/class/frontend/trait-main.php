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
 * @phpstan-import-type AVAILABLE_MANUAL_CLASSES_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type LINKMAKERCLASSES from \Lumiere\Link_Makers\Link_Factory
 */
trait Main {

	/**
	 * Traits
	 */
	use Settings_Global, Data;

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
	 * @var \Lumiere\Link_Makers\AMP_Links|\Lumiere\Link_Makers\Bootstrap_Links|\Lumiere\Link_Makers\Classic_Links|\Lumiere\Link_Makers\Highslide_Links|\Lumiere\Link_Makers\No_Links $link_maker The factory class will determine which class to use
	 * @INFO: if import-type instead of putting in full the info Var, phpstan requires to add this property to all classes that use it!
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
		 * Create the properties needed
		 */
		$this->get_settings_class(); // In Trait Settings_Global.
		$this->get_db_options(); // In Trait Settings_Global.

		// Start Logger class, if no name was passed build it with method get_current_classname().
		$this->logger = new Logger( $logger_name ?? $this->get_current_classname(), $screen_output );

		// Instanciate link maker classes (\Lumiere\Link_Maker\Link_Factory)
		$this->link_maker = Link_Factory::lumiere_link_factory_start();

		// Get name of the class, in trait Data.
		$this->classname = $this->get_current_classname();
	}

	/**
	 * Get the list of active plugins and send an array to current trait properties
	 *
	 * @param array<string, string> $extra_class An extra class to instanciate
	 * @phpstan-param array<AVAILABLE_MANUAL_CLASSES_KEYS, AVAILABLE_MANUAL_CLASSES_KEYS> $extra_class An extra class to instanciate
	 * @since 4.1
	 */
	public function activate_plugins( array $extra_class = [] ): void {
		$this->plugins_classes_active = ( new Plugins_Start( $extra_class ) )->plugins_classes_active;
	}

	/**
	 * Build list of active plugins and send them in properties
	 * Always add an IMDBPHP extra class, needed by all classes.
	 *
	 * @param array<string, string> $extra_class An extra class to instanciate
	 * @phpstan-param array<AVAILABLE_MANUAL_CLASSES_KEYS, AVAILABLE_MANUAL_CLASSES_KEYS> $extra_class An extra class to instanciate
	 * @return void Extra classes have been instanciated
	 * @since 4.1
	 */
	public function maybe_activate_plugins( array $extra_class = [] ): void {
		$always_load = [];
		if ( count( $this->plugins_classes_active ) === 0 ) {
			$always_load['imdbphp'] = 'imdbphp';
			$all_classes = array_merge( $always_load, $extra_class );
			$this->activate_plugins( $all_classes );
		}
	}

	/**
	 * Remove html links <a></a>
	 *
	 * @param string $text text to be cleaned from every html link
	 * @return string $output text that has been cleaned from every html link
	 */
	public function lumiere_remove_link( string $text ): string {
		$output = preg_replace( '/<a(.*?)>/', '', $text ) ?? $text;
		return preg_replace( '/<\/a>/', '', $output ) ?? $output;
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
		if ( $this->is_plugin_active( 'polylang' ) === true  ) {
			$replace_url = str_replace( home_url(), trim( pll_home_url(), '/' ), $url );
			$final_url = trim( $replace_url, '/' );
		}
		return $final_url ?? $url;
	}

	/**
	 * Is the plugin activated?
	 *
	 * @since 4.3
	 * @param string $plugin Plugin's name
	 * @return bool True if active
	 */
	public function is_plugin_active( string $plugin ): bool {
		return in_array( $plugin, array_keys( $this->plugins_classes_active ), true );
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

	/**
	 * Detect if the current page is a popup
	 *
	 * @since 4.3
	 * @return bool True if the page is a Lumiere popup
	 */
	public function is_popup_page(): bool {

		// Create the properties $this->config_class
		$this->get_settings_class(); // In Trait Settings_Global.

		$get_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : null;
		if (
			isset( $get_request_uri )
			&&
			(
				str_contains( esc_url_raw( wp_unslash( $get_request_uri ) ), $this->config_class->lumiere_urlstringfilms )
				|| str_contains( esc_url_raw( wp_unslash( $get_request_uri ) ), $this->config_class->lumiere_urlstringsearch )
				|| str_contains( esc_url_raw( wp_unslash( $get_request_uri ) ), $this->config_class->lumiere_urlstringperson )
			)
		) {
			return true;
		}
		return false;
	}
}

