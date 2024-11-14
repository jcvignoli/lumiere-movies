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
use Imdb\Config as Imdbphp_Config;

/**
 * Child class of \Imdb\Config
 * Get the settings and sends them to \Imdb\Config
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Settings
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Settings
 */
class Imdbphp extends Imdbphp_Config {

	/**
	 * Admin options
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 * Cache options
	 * @phpstan-var OPTIONS_CACHE $imdb_cache_values
	 */
	private array $imdb_cache_values;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Construct parent class.
		parent::__construct();

		// Get options from database.
		$this->imdb_admin_values = get_option( \Lumiere\Settings::get_admin_tablename() );
		$this->imdb_cache_values = get_option( \Lumiere\Settings::get_cache_tablename() );

		// Call the function to send the selected settings to imdbphp library.
		$this->lumiere_send_config_imdbphp();
	}

	/**
	 * Send Lumiere options to IMDbPHP parent class
	 * The values here will overwrite the properties in the parent class
	 *
	 * @see \Imdb\Config The parent class
	 * @see \Imdb\ImageProcessor::maybe_resize_image()
	 */
	private function lumiere_send_config_imdbphp(): void {

		$this->language = $this->imdb_admin_values['imdblanguage'];
		$this->cachedir = rtrim( $this->imdb_cache_values['imdbcachedir'], '/' ); #get rid of last '/'
		$this->photodir = $this->imdb_cache_values['imdbphotoroot'];// ?imdbphotoroot? Bug imdbphp?
		$this->cache_expire = intval( $this->imdb_cache_values['imdbcacheexpire'] );
		$this->photoroot = $this->imdb_cache_values['imdbphotodir']; // ?imdbphotodir? Bug imdbphp?
		$this->usecache = $this->imdb_cache_values['imdbusecache'] === '1' ? true : false;
		$this->storecache = $this->usecache === false ? false : true; // Not an option in Lumière!, don't store cache if cache is not used
		$this->usezip = $this->usecache === false ? false : true; // Not an option in Lumière!, not in admin interface, always true if using cache
		$this->delay_imdb_request = intval( $this->imdb_admin_values['imdbdelayimdbrequest'] );

		/**
		 * Where the local IMDB images reside (look for the "showtimes/" directory)
		 * This should be either a relative, an absolute, or an URL including the
		 * protocol (e.g. when a different server shall deliver them)
		 * Cannot be changed in Lumière admin panel
		 */
		$this->imdb_img_url = plugin_dir_path( dirname( __DIR__ ) ) . 'assets/pics/showtimes';

		/**
		 * These two are hardcoded at 800 in IMDbPHP Config class
		 * can't be changed in admin panel, only below
		 */
		$this->image_max_width = 800;
		$this->image_max_height = 800;
	}
}

