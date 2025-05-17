<?php declare( strict_types = 1 );
/**
 * Class to send variables to IMDbGraphqlPHP class.
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */

namespace Lumiere\Plugins\Manual;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Config\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;

use Lumiere\Vendor\Imdb\Config as Imdbphp_Config;
use Lumiere\Vendor\Imdb\Name;
use Lumiere\Vendor\Imdb\NameSearch;
use Lumiere\Vendor\Imdb\Title;
use Lumiere\Vendor\Imdb\TitleSearch;

use Lumiere\Vendor\Monolog\Logger;

/**
 * Child class of \Imdb\Config
 * Get the settings and sends them to \Imdb\Config
 * Gather methods to call IMDB
 *
 * Imdb\Title return definition
 * @phpstan-type TITLESEARCH_RETURNSEARCH array<array-key, array{imdbid: string, title: string, originalTitle: string, year: string, movietype: string, titleSearchObject: \Lumiere\Vendor\Imdb\Title}>
 * @phpstan-type NAMESEARCH_RETURNSEARCH array<array-key, array{id: string, name: string, titleSearchObject: \Lumiere\Vendor\Imdb\Name}>
 *
 */
final class Imdbphp extends Imdbphp_Config {
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Can't change snakeCase properties defined in an external class

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get options from database.
		$imdb_admin_values = get_option( Get_Options::get_admin_tablename(), [] );
		$imdb_cache_values = get_option( Get_Options::get_cache_tablename(), [] );

		/**
		 * Send Lumiere options to IMDbGraphqlPHP parent class
		 * The values here will overwrite the properties in the parent class
		 *
		 * @see \Lumiere\Vendor\Imdb\Config The parent class
		 */
		$this->throwHttpExceptions = false; // Not an option in Lumière!, prevent throwing Exceptions that stop the execution and prevent pages display
		$this->useLocalization = true; // Not an option in Lumière!, always use localization
		//$this->language = ''; // Disable language so it's not used but $this->country only.
		/**

		 */
		$this->country = $this->convert_lang( $imdb_admin_values['imdblanguage'] );
		$this->cacheDir = rtrim( $imdb_cache_values['imdbcachedir'], '/' ); #get rid of last '/'
		$this->photodir = $imdb_cache_values['imdbphotodir'];// ?imdbphotoroot? Bug imdbphp?
		$this->cacheExpire = intval( $imdb_cache_values['imdbcacheexpire'] );
		$this->photoroot = $imdb_cache_values['imdbphotoroot']; // ?imdbphotodir? Bug imdbphp?
		$this->cacheUse = $imdb_cache_values['imdbusecache'] === '1' ? true : false;
		$this->cacheStore = $this->cacheUse === false ? false : true; // Not an option in Lumière!, don't store cache if cache is not used
		$this->cacheUseZip = $this->cacheUse === false ? false : true; // Not an option in Lumière!, not in admin interface, always true if using cache
		$this->cacheConvertZip = $this->cacheUse === false ? false : true; // Not an option in Lumière!, not in admin interface, always true if using cache
		$this->curloptTimeout = intval( $imdb_admin_values['imdbdelayimdbrequest'] );
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
	 * Convert the WordPress style language to IMDbphp style
	 * @param string $language lang in WP style, ie en_GB, fr_FR, es_ES
	 * country set country code
	 * possible values:
	 * EN (English), FR (French), ES (Spanish), etc.
	 * @return string language in two positions, ie EN, FR, ES
	 */
	private function convert_lang( string $language ): string {
		$lang_shortened = explode( '_', $language );
		return strtoupper( $lang_shortened[0] );
	}

	/**
	 * Search a film according to its title, return an array of results
	 *
	 * @param string $title Movie's name
	 * @param Logger|null $logger
	 * @return array<array<string, \Lumiere\Vendor\Imdb\Title|int|string>>
	 * @phpstan-return TITLESEARCH_RETURNSEARCH
	 */
	public function search_movie_title( string $title, Logger|null $logger = null ): array {
		$search = new TitleSearch( $this, $logger );
		$return = $search->search( esc_html( $title ), Get_Options::get_type_search() );
		/** @psalm-var TITLESEARCH_RETURNSEARCH $return Dunno why it must be specified here again... */
		return $return;
	}

	/**
	 * Search a Person according to its name, return an array of results
	 *
	 * @param string $name Person's name
	 * @param Logger|null $logger
	 * @return array<array-key, mixed>|array{}
	 * @phpstan-return NAMESEARCH_RETURNSEARCH
	 */
	public function search_person_name( string $name, Logger|null $logger = null ): array {
		$search = new NameSearch( $this, $logger );
		/** @psalm-var NAMESEARCH_RETURNSEARCH $return Dunno why it must be specified here again... */
		$return = $search->search( esc_html( $name ) );
		return $return;
	}

	/**
	 * Get the Title class
	 * Can execute all methods of the class Title, fits perfectly in a class property
	 *
	 * @param string $movie_id Movie's id to do the Title's query
	 * @return \Lumiere\Vendor\Imdb\Title class instanciated with the movie's id
	 */
	public function get_title_class( string $movie_id, Logger|null $logger = null ): Title {
		return new Title( $movie_id, $this, $logger );
	}

	/**
	 * Get the Name class
	 * Can execute all methods of the class Title, fits perfectly in a class property
	 *
	 * @param string $person_id Person's id to do the Name's query
	 * @return \Lumiere\Vendor\Imdb\Name class instanciated with the person's id
	 */
	public function get_name_class( string $person_id, Logger|null $logger = null ): Name {
		return new Name( $person_id, $this, $logger );
	}
}
