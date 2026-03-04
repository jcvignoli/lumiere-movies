<?php declare( strict_types = 1 );
/**
 * Frontend Trait for pages including movies
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.2
 * @package       lumieremovies
 */

namespace Lumiere\Frontend;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Enums\Popup_Type;
use Lumiere\Frontend\Link_Maker\Link_Factory;
use Lumiere\Plugins\Logger;
use Lumiere\Tools\Data;

/**
 * Frontend trait
 * Popups, movie, widget and taxonomy use this trait
 * Allow to use the logger, function utilities, and settings
 *
 * @phpstan-import-type TITLESEARCH_RETURNSEARCH from \Lumiere\Plugins\Manual\Imdbphp
 * @phpstan-import-type LINKMAKERCLASSES from \Lumiere\Frontend\Link_Maker\Link_Factory
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 */
trait Main {

	/**
	 * Class for building links, i.e. Highslide
	 * Built in class Link Factory
	 *
	 * @INFO: if import-type instead of putting in full the info Var, phpstan requires to add this property to all classes that use it!
	 * @var \Lumiere\Frontend\Link_Maker\Interface_Linkmaker $link_maker The factory class will determine which class to use
	 */
	public \Lumiere\Frontend\Link_Maker\Interface_Linkmaker $link_maker;

	/**
	 * Logging
	 */
	public Logger $logger;

	/**
	 * Start logger
	 *
	 * @param null|string $logger_name Title for the logger output
	 */
	public function start_logger( ?string $logger_name = null ): void {
		// Start Logger class, if no name was passed build it with method get_current_classname().
		$this->logger = new Logger( $logger_name ?? Data::get_current_classname( __CLASS__ ) );
	}

	/**
	 * Start the Link_Maker through Link_Factory, send everything to a property
	 */
	public function start_linkmaker(): void {
		// Instanciate link maker classes (\Lumiere\Link_Maker\Link_Factory)
		$options = get_option( Get_Options::get_admin_tablename(), [] );
		$this->link_maker = Link_Factory::select_link_type( $options );
	}

	/**
	 * Are we currently on an AMP URL?
	 * Always return `false` and show PHP Notice if called before the `wp` hook.
	 *
	 * @since 3.7.1
	 * @since 4.4.1 Added check of AMP__VERSION constant
	 * @return bool true if amp url, false otherwise
	 */
	public function is_amp_page(): bool {

		global $pagenow;

		/** @psalm-suppress RedundantCondition, UndefinedConstant */
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

		// If url contains ?amp and AMP__VERSION constant found, it is a frontpage AMP page
		if (
		( str_contains( sanitize_text_field( wp_unslash( strval( $_SERVER['REQUEST_URI'] ?? '' ) ) ), '?amp' ) && defined( 'AMP__VERSION' ) )
		|| ( isset( $_GET ['wpamp'] ) && defined( 'AMP__VERSION' ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- it's detection, not submission!
		|| ( isset( $_GET ['amp'] ) && defined( 'AMP__VERSION' ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- it's detection, not submission!
		) {
			return true;
		}

		return false;
	}

	/**
	 * Detect if the current URL is a popup
	 *
	 * @since 4.3
	 * @since 4.6 added "'/' . " in str_contains() at the begining
	 * @since 4.7 added ". '/'" in str_contains() in the end
	 * @since 4.7.1 final slash is conditional according to the permalink structure, using new var $is_last_chara_slash
	 * @since 4.7.4 Use of Popup_Type enum instead of hardcoded Settings constant
	 *
	 * @return bool True if the page is a Lumiere popup
	 */
	public function is_popup_page(): bool {

		$get_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( strval( $_SERVER['REQUEST_URI'] ) ) ) : null;
		$is_last_chara_slash = get_option( 'permalink_structure' ) !== false && substr( get_option( 'permalink_structure' ), -1 ) === '/' ? '/' : '';
		if (
			isset( $get_request_uri )
			&&
			(
				str_contains( $get_request_uri, '/' . Popup_Type::from_key( 'film' )->value . $is_last_chara_slash )
				|| str_contains( $get_request_uri, '/' . Popup_Type::from_key( 'movie_search' )->value . $is_last_chara_slash )
				|| str_contains( $get_request_uri, '/' . Popup_Type::from_key( 'person' )->value . $is_last_chara_slash )
			)
		) {
			return true;
		}
		return false;
	}
}

