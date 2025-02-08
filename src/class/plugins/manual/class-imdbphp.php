<?php declare( strict_types = 1 );
/**
 * Class to send variables to IMDbGraphqlPHP class.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 2.0
 * @package lumiere-movies
 */

namespace Lumiere\Plugins\Manual;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Tools\Get_Options;

// use IMDbGraphqlPHP in /vendor/.
use Imdb\Config as Imdbphp_Config;
use Imdb\Name;
use Imdb\NameSearch;
use Imdb\Title;
use Imdb\TitleSearch;
use Monolog\Logger;

/**
 * Child class of \Imdb\Config
 * Get the settings and sends them to \Imdb\Config
 * Gather methods to call IMDB
 *
 * Imdb\Title return definition
 * @phpstan-type TITLESEARCH_RETURNSEARCH array<array-key, array{imdbid: string, title: string, originalTitle: string, year: string, movietype: string, titleSearchObject: \Imdb\Title}>
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Settings
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Settings
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
		$this->imdb_admin_values = get_option( Get_Options::get_admin_tablename() );
		$this->imdb_cache_values = get_option( Get_Options::get_cache_tablename() );

		/**
		 * Send Lumiere options to IMDbGraphqlPHP parent class
		 * The values here will overwrite the properties in the parent class
		 *
		 * @see \Imdb\Config The parent class
		 */
		$this->throwHttpExceptions = false; // Not an option in Lumière!, prevent throwing Exceptions that stop the execution and prevent pages display
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
	 * Force the cache activation
	 * Ensure that cache is active
	 *
	 * @see \Lumiere\Frontend\Popup\Head_Popups::add_metas_popups()
	 */
	public function activate_cache(): void {
		$this->cacheUse = true;
		$this->cacheStore = true;
		$this->cacheUseZip = true;
	}

	/**
	 * Search a film according to its title, return an array of results
	 *
	 * @param string $title Movie's name
	 * @param Logger|null $logger
	 * @return array<array-key, array<string, \Imdb\Title|string>>
	 * @phpstan-return TITLESEARCH_RETURNSEARCH
	 */
	public function search_movie_title( string $title, Logger|null $logger = null ): array {
		$search = new TitleSearch( $this, $logger );
		$return = $search->search( esc_html( $title ), Get_Options::get_type_search() );
		/** @psalm-var TITLESEARCH_RETURNSEARCH $return Dunno why it must be precised here again... */
		return $return;
	}

	/**
	 * Search a Person according to its name, return an array of results
	 *
	 * @param string $name Person's name
	 * @param Logger|null $logger
	 * @return array<array-key, mixed>|array{}
	 */
	public function search_person_name( string $name, Logger|null $logger = null ): array {
		$search = new NameSearch( $this, $logger );
		return $search->search( $name );
	}

	/**
	 * Get the Title class
	 * Can execute all methods of the class Title, fits perfectly in a class property
	 *
	 * @param string $movie_id Movie's id to do the Title's query
	 * @return \Imdb\Title class instanciated with the movie's id
	 */
	public function get_title_class( string $movie_id, Logger|null $logger = null ): Title {
		return new Title( $movie_id, $this, $logger );
	}

	/**
	 * Get the Name class
	 * Can execute all methods of the class Title, fits perfectly in a class property
	 *
	 * @param string $person_id Person's id to do the Name's query
	 * @return \Imdb\Name class instanciated with the person's id
	 */
	public function get_name_class( string $person_id, Logger|null $logger = null ): Name {
		return new Name( $person_id, $this, $logger );
	}
}
