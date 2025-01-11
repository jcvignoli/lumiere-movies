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

namespace Lumiere\Plugins\Manual;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

// use IMDbPHP config class in /vendor/.
use Imdb\Config as Imdbphp_Config;

/**
 * Child class of \Imdb\Config
 * Get the settings and sends them to \Imdb\Config
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Tools\Settings_Global
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Tools\Settings_Global
 */
class Imdbphp extends Imdbphp_Config {
 // phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Can't change snakeCase properties defined in an external class

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
