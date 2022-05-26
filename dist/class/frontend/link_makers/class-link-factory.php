<?php declare( strict_types = 1 );
/**
 * Factory class for selecting the link maker
 * These link makers can open popups (Highslide, Classic) or remove all HTML links (No_Links)
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @since 3.7.1
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Link_Makers\AMP_Links;
use \Lumiere\Link_Makers\No_Links;
use \Lumiere\Link_Makers\Bootstrap_Links;
use \Lumiere\Link_Makers\Highslide_Links;
use \Lumiere\Link_Makers\Classic_Links;
use \Lumiere\Utils;

class Link_Factory {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Class constructor
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

	}

	/**
	 * Select which class to use to build the HTML links.
	 * @return Bootstrap_Links|AMP_Links|No_Links|Highslide_Links|Classic_Links Class to build the links with.
	 */
	public function lumiere_select_link_maker (): Bootstrap_Links|AMP_Links|No_Links|Highslide_Links|Classic_Links {

		/**
		 * Checks if the current page is AMP
		 */
		if ( Utils::lumiere_is_amp_page() === true ) {

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
		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

			return new Bootstrap_Links();

		}

		/**
		 * Highslide is selected in admin options

		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

			return new Highslide_Links();

		}
		 */

		/**
		 * Highslide was unticked, display classic popups
		 */
		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '0' ) {

			return new Classic_Links();

		}

		// By default, return classical popup
		return new Classic_Links();

	}

}
