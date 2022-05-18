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

use \Lumiere\Link_Makers\No_Links;
use \Lumiere\Link_Makers\Highslide_Links;
use \Lumiere\Link_Makers\Classic_Links;
use \Lumiere\Plugins\Logger;
use \Lumiere\Utils;

class Link_Factory {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Class \Lumiere\Logger
	 *
	 */
	public Logger $logger;

	/**
	 * Class constructor
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

		// Start Logger class.
		$this->logger = new Logger( 'Link_FactoryClass' );

		add_action( 'plugins_loader', [ $this, 'lumiere_select_link_maker' ], 0 );
	}

	/**
	 * Select which class to use to build the HTML links.
	 * @return object Class to build the links in Frontend with.
	 */
	public function lumiere_select_link_maker (): object {

		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		/**
		 * General Lumiere Function
		 * Checks if the current page is AMP
		 */
		if ( Utils::lumiere_is_amp_page() === true ) {

			$logger->debug( '[Lumiere][LinkFactoryClass] Running AMP class No_Links' );
			return new No_Links();

		}

		if ( $this->imdb_admin_values['imdblinkingkill'] === '1' ) {

			$logger->debug( '[Lumiere][LinkFactoryClass] Running LinkingKill class No_Links' );
			return new No_Links();

		}

		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

			$logger->debug( '[Lumiere][LinkFactoryClass] Running Highslide class' );
			return new Highslide_Links();

		}

		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '0' ) {

			$logger->debug( '[Lumiere][LinkFactoryClass] Running Classic class' );
			return new Classic_Links();

		}

		// By default, return classical popup
		$logger->debug( '[Lumiere][LinkFactoryClass] Running Classic class' );
		return new Classic_Links();

	}

}
