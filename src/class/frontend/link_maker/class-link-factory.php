<?php declare( strict_types = 1 );
/**
 * Factory class for selecting the link maker
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @since 3.8
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Link_Maker;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Link_Maker\AMP_Links;
use Lumiere\Link_Maker\No_Links;
use Lumiere\Link_Maker\Bootstrap_Links;
use Lumiere\Link_Maker\Highslide_Links;
use Lumiere\Link_Maker\Classic_Links;
use Lumiere\Frontend\Main;
use Exception;

/**
 * The class select the "link makers" according to the current settings and plugins used
 * Some "link makers" build links that can open popups (Highslide, Classic) or remove HTML links (No_Links, AMP)
 *
 * @phpstan-type LINKMAKERCLASSES \Lumiere\Link_Maker\AMP_Links|\Lumiere\Link_Maker\Bootstrap_Links|\Lumiere\Link_Maker\Classic_Links|\Lumiere\Link_Maker\Highslide_Links|\Lumiere\Link_Maker\No_Links
 */
class Link_Factory {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Class for building links, i.e. Highslide
	 * Built in class Link Factory
	 *
	 * @phpstan-var LINKMAKERCLASSES The factory class will determine which class to use
	 */
	public object $link_maker;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->get_db_options(); // Method in Main trait.
	}

	/**
	 * Select which class to use to build the HTML links.
	 * @phpstan-return LINKMAKERCLASSES Class to build the links with.
	 *
	 * @see \Lumiere\Frontend\Main::is_amp_page() Detects if AMP is active and current
	 * @throws Exception if no link class was found
	 */
	public function select_link_maker(): Interface_Link_Maker {

		// Checks if the current page is AMP
		if ( $this->is_amp_page() === true ) { // Method in Main trait.
			return new AMP_Links();

			// Not display Lumière! links is selected in admin options
		} elseif ( $this->imdb_admin_values['imdblinkingkill'] === '1' ) {
			return new No_Links();

			// Bootstrap is selected in admin options
		} elseif ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'bootstrap' ) {
			return new Bootstrap_Links();

			// Highslide is selected in admin options
		} elseif ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'highslide' ) {
			return new Highslide_Links();

			// None was selected in admin options, display classic popups
		} elseif ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'classic' ) {
			return new Classic_Links();
		}

		throw new Exception( 'No Link Lumière class found, aborting!' );
	}

	/**
	 * Static call of the current class
	 *
	 * @phpstan-return LINKMAKERCLASSES Instance the relevant class
	 */
	public static function select_link_type(): Interface_Link_Maker {
		return ( new self() )->select_link_maker();
	}
}
