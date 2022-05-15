<?php declare( strict_types = 1 );
/**
 * Class for selecting the link maker
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

use \Lumiere\Link_makers\No_Links;
use \Lumiere\Link_makers\Highslide;
use \Lumiere\Link_makers\Classical_Links;

class Link_Factory {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * \Lumière\Plugins class
	 * Array of plugins in use
	 * From trait frontend
	 *
	 * @var array<int, string>
	 */
	private array $plugins_in_use = [];

	/**
	 * Class constructor
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

	}

	/**
	 * Select which class to use to build the HTML links.
	 * @return object Class to build the links in Frontend with.
	 */
	public function lumiere_select_link_maker (): object {
		/*
		if ( in_array( 'AMP', $this->plugins_in_use, true ) === false ) {

			return new No_Links();

		}
		*/

		if ( $this->imdb_admin_values['imdblinkingkill'] === '1' ) {

			return new No_Links();

		}

		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

			return new Highslide_Links();

		}

		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '0' ) {

			return new Classic_Links();

		}

		// By default, return classical popup
		return new Classic_Links();

	}

}
