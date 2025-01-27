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
use Lumiere\Tools\Data;
use Lumiere\Tools\Settings_Global;

/**
 * Frontend trait
 * Popups, movie, widget and taxonomy use this trait
 * Allow to use the logger, function utilities, and settings
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 * @phpstan-import-type AVAILABLE_MANUAL_CLASSES_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type LINKMAKERCLASSES from \Lumiere\Link_Makers\Link_Factory
 */
trait Main {

	/**
	 * Traits
	 */
	use Settings_Global;

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

		// Instanciate link maker classes (\Lumiere\Link_Maker\Link_Factory)
		$this->link_maker = Link_Factory::lumiere_link_factory_start();

		// Start Logger class, if no name was passed build it with method get_current_classname().
		$this->logger = new Logger( $logger_name ?? Data::get_current_classname( __CLASS__ ), $screen_output );
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
		if ( did_action( 'wp' ) === 1 && function_exists( 'amp_is_request' ) && amp_is_request() ) {
			return true;
		}

		// If url contains ?amp, it must be an AMP page
		if ( str_contains( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), '?amp' )
		|| isset( $_GET ['wpamp'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- it's detection, not submission!
		|| isset( $_GET ['amp'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- it's detection, not submission!
		) {
			return true;
		}

		return false;
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

