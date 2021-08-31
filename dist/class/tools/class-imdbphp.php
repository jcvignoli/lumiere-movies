<?php declare( strict_types = 1 );
/**
 * Class to send variables to IMDbPHP class.
 * This allow to use IMDbPHP with customised value of Lumiere
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

// use Lumiere library.
use \Lumiere\Settings;

// use IMDbPHP config class in /vendor/
use \Imdb\Config;

class Imdbphp extends Config {

	/**
	 * Admin options vars
	 * @var array{'imdbplugindirectory': string, 'imdbplugindirectory_partial': string, 'imdbpluginpath': string,'imdburlpopups': string,'imdbkeepsettings': string,'imdburlstringtaxo': string,'imdbcoversize': string,'imdbcoversizewidth': string, 'imdbmaxresults': int, 'imdbpopuptheme': string, 'imdbpopuplarg': string,'imdbpopuplong': string, 'imdbintotheposttheme': string, 'imdblinkingkill': string, 'imdbautopostwidget': string, 'imdblanguage': string, 'imdbdebug': string, 'imdbdebuglog': string, 'imdbdebuglogpath': string, 'imdbdebuglevel': string, 'imdbdebugscreen': string, 'imdbwordpress_bigmenu': string, 'imdbwordpress_tooladminmenu': string, 'imdbpopup_highslide': string, 'imdbtaxonomy': string, 'imdbHowManyUpdates': int, 'imdbseriemovies': string} $imdb_admin_values
	*/
	private array $imdb_admin_values;

	/**
	 * Cache options
	 * @var array{'imdbcachedir_partial': string, 'imdbstorecache': bool, 'imdbusecache': string, 'imdbconverttozip': bool, 'imdbusezip': bool, 'imdbcacheexpire': string, 'imdbcachedetailsshort': string,'imdbcachedir': string,'imdbphotoroot': string, 'imdbphotodir': string} $imdb_cache_values
	 */
	private array $imdb_cache_values;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct parent class
		parent::__construct();

		// Get database options.
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );

		// Get database options.
		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

		// Call the function to send the selected settings to imdbphp library
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

		/** Where the local IMDB images reside (look for the "showtimes/" directory)
		*  This should be either a relative, an absolute, or an URL including the
		*  protocol (e.g. when a different server shall deliver them)
		* Cannot be changed in Lumière admin panel
		*/
		$this->imdb_img_url = isset( $this->imdb_admin_values['imdbplugindirectory'] ) . '/pics/showtimes';

	}

}

