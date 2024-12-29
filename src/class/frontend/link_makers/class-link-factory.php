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
use Lumiere\Frontend\Main;

/**
 * @phpstan-import-type LINKMAKERCLASSES from Abstract_Link_Maker
 */
class Link_Factory {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Class constructor
	 */
	public function __construct() {

		// Get Global Settings class properties.
		$this->get_db_options();

	}

	/**
	 * Select which class to use to build the HTML links.
	 * @phpstan-return LINKMAKERCLASSES Class to build the links with.
	 *
	 * @see \Lumiere\Frontend\Main::lumiere_is_amp_page()
	 */
	public function lumiere_select_link_maker (): AMP_Links|Bootstrap_Links|Classic_Links|Highslide_Links|No_Links {

		/**
		 * Checks if the current page is AMP
		 */
		if ( $this->lumiere_is_amp_page() === true ) { // Method in Main trait.
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
	 * @phpstan-return LINKMAKERCLASSES Build the class
	 */
	public static function lumiere_link_factory_start (): AMP_Links|Bootstrap_Links|Classic_Links|Highslide_Links|No_Links {

		return ( new self() )->lumiere_select_link_maker();

	}
}
