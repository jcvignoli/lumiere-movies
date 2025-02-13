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
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Link_Makers\Link_Factory;
use Lumiere\Plugins\Logger;
use Lumiere\Tools\Data;
use Lumiere\Config\Open_Options;
use Lumiere\Config\Get_Options;

/**
 * Frontend trait
 * Popups, movie, widget and taxonomy use this trait
 * Allow to use the logger, function utilities, and settings
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 * @phpstan-import-type LINKMAKERCLASSES from \Lumiere\Link_Makers\Link_Factory
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 */
trait Main {

	/**
	 * Traits
	 */
	use Open_Options;

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
	 */
	public function start_main_trait( ?string $logger_name = null ): void {

		// Get global settings class properties.
		$this->get_db_options(); // In Open_Options trait.

		// Start Logger class, if no name was passed build it with method get_current_classname().
		$this->logger = new Logger( $logger_name ?? Data::get_current_classname( __CLASS__ ) );

		// Instanciate link maker classes (\Lumiere\Link_Maker\Link_Factory)
		$this->link_maker = Link_Factory::select_link_type();
	}

	/**
	 * Are we currently on an AMP URL?
	 * Always return `false` and show PHP Notice if called before the `wp` hook.
	 *
	 * @since 3.7.1
	 * @return bool true if amp url, false otherwise
	 */
	public function is_amp_page(): bool {

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
	 * Detect if the current URL is a popup
	 *
	 * @since 4.3
	 * @return bool True if the page is a Lumiere popup
	 */
	public function is_popup_page(): bool {

		$get_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : null;
		if (
			isset( $get_request_uri )
			&&
			(
				str_contains( $get_request_uri, Get_Options::LUM_URL_BIT_POPUPS['film'] )
				|| str_contains( $get_request_uri, Get_Options::LUM_URL_BIT_POPUPS['movie_search'] )
				|| str_contains( $get_request_uri, Get_Options::LUM_URL_BIT_POPUPS['person'] )
			)
		) {
			return true;
		}
		return false;
	}
}

