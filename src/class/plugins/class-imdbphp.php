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
lum_check_display();

// use IMDbPHP config class in /vendor/.
use Imdb\Config as Imdbphp_Config;

/**
 * Child class of \Imdb\Config
 * Get the settings and sends them to \Imdb\Config
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Tools\Settings_Global
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Tools\Settings_Global
 */
 // phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Can't change snakeCase properties defined in an external class
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

		$this->useLocalization = true; // Not an option in Lumière!, always use localization
		$this->language = ''; // Disable language so it's not used but $this->country only.
		$this->country = strtoupper( $this->imdb_admin_values['imdblanguage'] );
		$this->cacheDir = rtrim( $this->imdb_cache_values['imdbcachedir'], '/' ); #get rid of last '/'
		$this->photodir = $this->imdb_cache_values['imdbphotodir'];// ?imdbphotoroot? Bug imdbphp?
		$this->cacheExpire = intval( $this->imdb_cache_values['imdbcacheexpire'] );
		$this->photoroot = $this->imdb_cache_values['imdbphotoroot']; // ?imdbphotodir? Bug imdbphp?
		$this->cacheUse = $this->imdb_cache_values['imdbusecache'] === '1' ? true : false;
		$this->cacheStore = $this->cacheUse === false ? false : true; // Not an option in Lumière!, don't store cache if cache is not used
		$this->cacheUseZip = $this->cacheUse === false ? false : true; // Not an option in Lumière!, not in admin interface, always true if using cache
		$this->cacheConvertZip = $this->cacheUse === false ? false : true; // Not an option in Lumière!, not in admin interface, always true if using cache
		$this->curloptTimeout = intval( $this->imdb_admin_values['imdbdelayimdbrequest'] );

		/**
		 * Where the local IMDB images reside (look for the "showtimes/" directory)
		 * This should be either a relative, an absolute, or an URL including the
		 * protocol (e.g. when a different server shall deliver them)
		 * Cannot be changed in Lumière admin panel
		 */
		//      $this->imdb_img_url = LUMIERE_WP_PATH . 'assets/pics/showtimes';

		/**
		 * These two are hardcoded at 800 in IMDbPHP Config class
		 * can't be changed in admin panel, only below
		 */
		$this->thumbnailWidth = 140;
		$this->thumbnailHeight = 207;
	}

	   /**
		* Activate cache
		* Ensure that cache is active
		*
		* @see \Lumiere\Frontend\Frontend
		*/
	public function activate_cache(): void {
			$this->cacheUse = true;
			$this->cacheStore = true;
			$this->cacheUseZip = true;
	}

}

