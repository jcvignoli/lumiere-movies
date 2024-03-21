<?php declare( strict_types = 1 );
/**
 * Factory class for selecting the link maker
 * These link makers can open popups (Highslide, Classic) or remove all HTML links (No_Links)
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @since 3.8
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Link_Makers\AMP_Links;
use Lumiere\Link_Makers\No_Links;
use Lumiere\Link_Makers\Bootstrap_Links;
use Lumiere\Link_Makers\Highslide_Links;
use Lumiere\Link_Makers\Classic_Links;
use Lumiere\Tools\Settings_Global;

/**
 * Not utilised @phpstan-type LINKMAKERCLASSES AMP_Links|Bootstrap_Links|Classic_Links|Highslide_Links|No_Links
 */
class Link_Factory {

	/**
	 * Traits
	 */
	use Settings_Global;

	/**
	 * Class constructor
	 */
	public function __construct() {

		// Get Global Settings class properties.
		$this->get_db_options();

	}

	/**
	 * Select which class to use to build the HTML links.
	 * @return AMP_Links|Bootstrap_Links|Classic_Links|Highslide_Links|No_Links Class to build the links with.
	 */
	public function lumiere_select_link_maker (): AMP_Links|Bootstrap_Links|Classic_Links|Highslide_Links|No_Links {

		/**
		 * Checks if the current page is AMP
		 */
		if ( $this->lumiere_is_amp_page() === true ) {
			return new AMP_Links();
		}

		/**
		 * To not display links was selected in admin options
		 */
		if ( $this->imdb_admin_values['imdblinkingkill'] === '1' ) {
			return new No_Links();
		}

		/**
		 * Bootstrap is selected in admin options
		 */
		if ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'bootstrap' ) {
			return new Bootstrap_Links();

			/**
			 * Highslide is selected in admin options
			 */
		} elseif ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'highslide' ) {
			return new Highslide_Links();

			/**
			 * None was selected in admin options, display classic popups
			 */
		} elseif ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'classic' ) {
			return new Classic_Links();
		}

		// By default, return classical popup
		return new Classic_Links();

	}

	/**
	 * Static call of the current class
	 *
	 * @return AMP_Links|Bootstrap_Links|Classic_Links|Highslide_Links|No_Links Build the class
	 */
	public static function lumiere_link_factory_start (): AMP_Links|Bootstrap_Links|Classic_Links|Highslide_Links|No_Links {

		return ( new self() )->lumiere_select_link_maker();

	}

	/**
	 * Are we currently on an AMP URL?
	 * Always return `false` and show PHP Notice if called before the `wp` hook.
	 *
	 * @since 3.7.1
	 * @return bool true if amp url, false otherwise
	 */
	private function lumiere_is_amp_page(): bool {

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
