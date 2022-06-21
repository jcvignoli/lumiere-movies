<?php declare( strict_types = 1 );
/**
 * Class to send variables to IMDbPHP class.
 * This allows to use IMDbPHP with customised value of Lumière
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

// use IMDbPHP config class in /vendor/.
use \Imdb\Config;

/**
 * Child class of \Imdb\Config
 * Get all settings from \Lumiere\Settings_Global and sends them to \Imdb\Config
 */
class Imdbphp extends Config {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Construct parent class.
		parent::__construct();

		// Construct Global Settings trait.
		$this->settings_open();

		// Call the function to send the selected settings to imdbphp library.
		$this->lumiere_send_config_imdbphp();

	}

	/**
	 * Send Lumiere options to IMDbPHP parent class
	 *
	 */
	private function lumiere_send_config_imdbphp(): void {

		$this->language = $this->imdb_admin_values['imdblanguage'];
		$this->cachedir = rtrim( $this->imdb_cache_values['imdbcachedir'], '/' ); #get rid of last '/'
		$this->photodir = $this->imdb_cache_values['imdbphotoroot'];// ?imdbphotoroot? Bug imdbphp?
		$this->cache_expire = intval( $this->imdb_cache_values['imdbcacheexpire'] );
		$this->photoroot = $this->imdb_cache_values['imdbphotodir']; // ?imdbphotodir? Bug imdbphp?
		$this->storecache = $this->imdb_cache_values['imdbstorecache'];
		$this->usecache = boolval( $this->imdb_cache_values['imdbusecache'] ) ? true : false;
		$this->converttozip = $this->imdb_cache_values['imdbconverttozip'];
		$this->usezip = $this->imdb_cache_values['imdbusezip'];

		/**
		 * Where the local IMDB images reside (look for the "showtimes/" directory)
		 * This should be either a relative, an absolute, or an URL including the
		 * protocol (e.g. when a different server shall deliver them)
		 * Cannot be changed in Lumière admin panel
		 */
		$this->imdb_img_url = isset( $this->imdb_admin_values['imdbplugindirectory'] ) . '/pics/showtimes';

	}

}

