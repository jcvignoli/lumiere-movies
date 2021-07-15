<?php

 #############################################################################
 # Lumière! Movies WordPress Plugin                                          #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #       			                                                	#
 #  Function : Configuration file             				     	#
 #											#
 #############################################################################

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) 
	wp_die('You can not call directly this page');

// use IMDbPHP config class in class/imdbphp/Imdb/Config.php
use \Imdb\Config;

// use Monolog classes in class/imdbphp/Monolog/
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\WebProcessor;


class Settings extends Config {

	/* Editable Options vars */
	var $imdbAdminOptionsName = "imdbAdminOptions";
	var $imdbWidgetOptionsName = "imdbWidgetOptions";
	var $imdbCacheOptionsName = "imdbCacheOptions";

	/* Options vars */
	public $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

	/* Websites constants */
	const IMDBBLOG = 'https://www.jcvignoli.com/blog';
	const IMDBBLOGENGLISH = self::IMDBBLOG . '/en';
	const IMDBBLOGHIGHSLIDE = self::IMDBBLOG . '/wp-content/files/wordpress-lumiere-highslide-5.0.0.zip';
	const IMDBHOMEPAGE =  self::IMDBBLOGENGLISH . '/lumiere-movies-wordpress-plugin';
	const IMDBABOUTENGLISH = self::IMDBBLOGENGLISH . '/presentation-of-jean-claude-vignoli';
	const IMDBPHPGIT = 'https://github.com/tboothman/imdbphp/';
	const LUMIERE_WORDPRESS = 'https://wordpress.org/extend/plugins/lumiere-movies/';
	const LUMIERE_GIT = 'https://github.com/jcvignoli/lumiere-movies';

	/* URL Strings for popups */
	public $lumiere_urlstring, $lumiere_urlstringfilms, $lumiere_urlstringperson, 
	$lumiere_urlstringsearch, $lumiere_urlpopupsfilms, $lumiere_urlpopupsperson, 
	$lumiere_urlpopupsearch;

	/* Internal URL pages constants */
	const move_template_taxonomy_page = 'inc/move_template_taxonomy.php';
	const highslide_download_page = 'inc/highslide_download.php';
	const gutenberg_search_page = 'inc/gutenberg-search.php';
	const gutenberg_search_url_string = 'lumiere/search/';
	const gutenberg_search_url = '/wp-admin/' . self::gutenberg_search_url_string;
	const popup_search_url = 'inc/popup-search.php';
	const popup_movie_url = 'inc/popup-imdb_movie.php';
	const popup_person_url = 'inc/popup-imdb_person.php';
	/* Include all pages of Lumière plugin */
	public $lumiere_list_all_pages;

	/* Store Lumière plugin version
	 *
	 */
	public $lumiere_version;

	/* Logger class built by lumiere_start_logger() 
	 * Meant to be utilised by movie/person pages
	 * 
	 */
	public $loggerclass;
	/* Where to write the log (here default WordPress log) */
	const debug_log_path = WP_CONTENT_DIR . '/debug.log';
	/* Set to false to use Logger instead of Monolog */
	var $isMonologActive = true;


	/* Is the current page WordPress Gutenberg editor?
	 *
	 */
	public $isGutenberg;

	/* List of types of people available */
	var $array_people = array( 'actor', 'composer', 'creator', 'director', 'producer', 'writer' );

	/* List of types of people available */
	var $array_items = array( 'color', 'country', 'genre', 'keywords', 'language' );

	/** Constructor
	 **
	 **/
	function __construct() {

		// Construct parent class so we can send the settings
		parent::__construct();

		// Detect if it is gutenberg, but doesn't work
		add_action ('current_screen', [$this, 'lumiere_is_gutenberg'] );

		// Define Lumière constants
		$this->lumiere_define_constants();

		// Make sure cache folder exists and is writable
		$this->lumiere_create_cache();

		// Send to the global vars the options
		$this->imdb_admin_values = $this->get_imdb_admin_option();
		$this->imdb_widget_values = $this->get_imdb_widget_option();
		$this->imdb_cache_values = $this->get_imdb_cache_option();

		// Call the plugin translation
		load_plugin_textdomain('lumiere-movies', false, plugin_dir_url( __DIR__ ) . 'languages' );

		// Call the function to send the selected settings to imdbphp library
		$this->lumiere_send_config_imdbphp();

		// Build the list of all pages included in Lumière plugin (utilised to load class.movie.php)
		$this->lumiere_list_all_pages = array( 
			$this->imdb_admin_values['imdburlstringtaxo'],
			$this->lumiere_urlstringfilms, 
			$this->lumiere_urlstringperson, 
			$this->lumiere_urlstringsearch, 
			self::move_template_taxonomy_page, 
			self::highslide_download_page, 
			self::gutenberg_search_page, 
			self::gutenberg_search_url, 
			self::popup_search_url, 
			self::popup_movie_url, 
			self::popup_person_url
		);
	}

	/** Define global constants
	 **
	 **
	 **/
	/* @TODO: work on the consistancy with function get_imdb_*_option, so this can be called either before or after */
	function lumiere_define_constants() {

		global $imdb_admin_values;

		/* BUILD $imdb_admin_values['imdbplugindirectory'] */
		$imdb_admin_values['imdbplugindirectory'] = isset($imdb_admin_values['imdbplugindirectory']) ? $imdb_admin_values['imdbplugindirectory'] : plugin_dir_url( __DIR__ );

		/* BUILD LUMIERE_VERSION */
		$lumiere_version_recherche = file_get_contents( plugin_dir_path( __DIR__ ) . 'README.txt');
		$lumiere_version = preg_match('#Stable tag:\s(.+)\n#', $lumiere_version_recherche, $lumiere_version_match);
		$this->lumiere_version = $lumiere_version_match[1];

		/* BUILD URLSTRINGS for popups */
		$this->lumiere_urlstring = (isset($imdb_admin_values['imdburlpopups'])) ? $imdb_admin_values['imdburlpopups'] : "/imdblt/";
		$this->lumiere_urlstringfilms = $this->lumiere_urlstring . "film/";
		$this->lumiere_urlstringperson = $this->lumiere_urlstring . "person/";
		$this->lumiere_urlstringsearch = $this->lumiere_urlstring . "search/";
		$this->lumiere_urlpopupsfilms = site_url() . $this->lumiere_urlstringfilms;
		$this->lumiere_urlpopupsperson = site_url() . $this->lumiere_urlstringperson;
		$this->lumiere_urlpopupssearch = site_url() . $this->lumiere_urlstringsearch;	

	}

	/** Returns the array of ADMIN options
	 **
	 **
	 **/
	function get_imdb_admin_option() {

		$imdbAdminOptions = array(

			#--------------------------------------------------=[ Basic ]=--
			'blog_adress' => get_bloginfo('url'),
			'imdbplugindirectory_partial' => '/wp-content/plugins/lumiere-movies/',
			'imdbpluginpath' => plugin_dir_path( __DIR__ ),
			'imdburlpopups' => '/imdblt/',
			'imdbkeepsettings' => true,
			'imdburlstringtaxo' => 'imdblt_',
			'imdbwebsite' => "www.imdb.com",
			'imdbcoversize' => false,
			'imdbcoversizewidth' => '100',
			#--------------------------------------------------=[ Technical ]=--

			'imdb_utf8recode'=> true,
			'imdbmaxresults' => 10,
			'imdbpopuptheme' => 'white',
			'popupLarg' => '540',
			'popupLong' => '350',
			'imdbintotheposttheme' => 'grey',
			'imdblinkingkill' => false,
			'imdbautopostwidget' => false,
			'imdbimgdir' => 'pics/',
			'imdblanguage' => "en",
			'imdbdirectsearch' => true, 		/* not available in the admin interface */
			/*'imdbsourceout' => false,*/
			'imdbdebug' => false,
			'imdbwordpress_bigmenu'=>false,
			'imdbwordpress_tooladminmenu'=>true,
			'imdbpopup_highslide'=>true,
			'imdbtaxonomy'=> true,
			'imdbHowManyUpdates'=> 1, # for use in class.update-options.php
			'imdbseriemovies' => 'movies+series', /* options: by movies, series, movies+series, videogames */

		);
		$imdbAdminOptions['imdbplugindirectory'] = $imdbAdminOptions['blog_adress'] 
									. $imdbAdminOptions['imdbplugindirectory_partial'];

		$imdbOptions = get_option($this->imdbAdminOptionsName);

		if (!empty($imdbOptions)) {

			foreach ($imdbOptions as $key => $option) {
				$imdbAdminOptions[$key] = $option;
			}

			// Agregate var to construct 'imdbplugindirectory'
			$imdbAdminOptions['imdbplugindirectory'] = $imdbAdminOptions['blog_adress'] 
										. $imdbAdminOptions['imdbplugindirectory_partial'];
		}

		update_option($this->imdbAdminOptionsName, $imdbAdminOptions);

		return $imdbAdminOptions;

	} 


	/** Returns the array of CACHE options
	 **
	 **
	 **/
	function get_imdb_cache_option() {

		$imdbCacheOptions = array(

			'imdbcachedir_partial' => 'wp-content/cache/lumiere/',
			'imdbstorecache' => true, 			/* not available in the admin interface */
			'imdbusecache' => true,
			'imdbconverttozip' => true, 		/* not available in the admin interface */
			'imdbusezip' => true, 			/* not available in the admin interface */
			'imdbcacheexpire' => "2592000", // one month
			'imdbcachedetails'=> true,
			'imdbcachedetailsshort'=> false,

		);

		$imdbCacheOptions['imdbcachedir'] = ABSPATH . $imdbCacheOptions['imdbcachedir_partial'];
		$imdbCacheOptions['imdbphotoroot'] = $imdbCacheOptions['imdbcachedir'] . 'images/';
		$imdbCacheOptions['imdbphotodir'] = content_url() . '/cache/lumiere/images/';

		$imdbOptionsc = get_option($this->imdbCacheOptionsName);
		$imdbOptions = get_option($this->imdbAdminOptionsName);

		if (!empty($imdbOptionsc)) {

			foreach ($imdbOptionsc as $key => $option) {
				$imdbCacheOptions[$key] = $option;
			}

			// Agregate vars to construct 'imdbcachedir
			$imdbCacheOptions['imdbcachedir'] =  ABSPATH . $imdbCacheOptions['imdbcachedir_partial'];

			// Agregate vars to construct 'imdbphotoroot
			$imdbCacheOptions['imdbphotoroot'] =  $imdbCacheOptions['imdbcachedir'] . 'images/';
		}
		if (!empty($imdbOptions)){

			// Agregate vars to construct 'imdbphotodir'
			$imdbCacheOptions['imdbphotodir'] =  $imdbOptions['blog_adress'] 
									. '/' 
									. $imdbCacheOptions['imdbcachedir_partial'] 
									. 'images/';
		}

		update_option($this->imdbCacheOptionsName, $imdbCacheOptions);

		return $imdbCacheOptions;

	} 

	/** Returns the array of WIDGET options
	 **
	 **
	 **/
	function get_imdb_widget_option() {

		$imdbWidgetOptions = array(

			'imdbwidgettitle' => true,
			'imdbwidgetpic' => true,
			'imdbwidgetruntime' => false,
			'imdbwidgetdirector' => true,
			'imdbwidgetcountry' => false,
			'imdbwidgetactor' => true,
			'imdbwidgetactornumber' => '10',
			'imdbwidgetcreator' => false,
			'imdbwidgetrating' => false,
			'imdbwidgetlanguage' => false,
			'imdbwidgetgenre' => true,
			'imdbwidgetwriter' => true,
			'imdbwidgetproducer' => false,
			'imdbwidgetproducernumber' => false,
			'imdbwidgetkeywords' => false,
			'imdbwidgetprodcompany' => false,
			'imdbwidgetplot' => false,
			'imdbwidgetplotnumber' => false,
			'imdbwidgetgoofs' => false,
			'imdbwidgetgoofsnumber' => false,
			'imdbwidgetcomments' => false,
			'imdbwidgetquotes' => false,
			'imdbwidgetquotesnumber' => false,
			'imdbwidgettaglines' => false,
			'imdbwidgettaglinesnumber' => false,
			'imdbwidgetcolors' => false,
			'imdbwidgetalsoknow' => false,
			'imdbwidgetalsoknownumber' => false,
			'imdbwidgetcomposer' => false,
			'imdbwidgetsoundtrack' => false,
			'imdbwidgetsoundtracknumber' => false,
			'imdbwidgetofficialsites' => false,
			'imdbwidgetsource' => false,
			'imdbwidgetonpost' => true,
			'imdbwidgetonpage' => true,
			'imdbwidgetyear' => false,
			'imdbwidgettrailer' => false,
			'imdbwidgettrailernumber' => false,

			'imdbwidgetorder'=>array("title" => "1", "pic" => "2","runtime" => "3", "director" => "4", "country" => "5", "actor" => "6", "creator" => "7", "rating" => "8", "language" => "9","genre" => "10","writer" => "11","producer" => "12", "keywords" => "13", "prodcompany" => "14", "plot" => "15", "goofs" => "16", "comments" => "17", "quotes" => "18", "taglines" => "19", "colors" => "20", "alsoknow" => "21", "composer" => "22", "soundtrack" => "23", "trailer" => "24", "officialsites" => "25", "source" => "26" ),

			'imdbtaxonomycolor' => false,
			'imdbtaxonomycomposer' => false,
			'imdbtaxonomycountry' => false,
			'imdbtaxonomycreator' => false,
			'imdbtaxonomydirector' => false,
			'imdbtaxonomygenre' => true,
			'imdbtaxonomykeywords' => false,
			'imdbtaxonomylanguage' => false,
			'imdbtaxonomyproducer' => false,
			'imdbtaxonomyactor' => false,
			'imdbtaxonomywriter' => false,
			'imdbtaxonomytitle' => false,

		);

		$imdbOptionsw = get_option($this->imdbWidgetOptionsName);

		if (!empty($imdbOptionsw)) {
			foreach ($imdbOptionsw as $key => $option)
				$imdbWidgetOptions[$key] = $option;
		}

		update_option($this->imdbWidgetOptionsName, $imdbWidgetOptions);

		return $imdbWidgetOptions;

	} 

	/** Send Lumiere options to imdbphp parent class
	 **
	 **
	 **/
	function lumiere_send_config_imdbphp() {

		$this->language = $this->imdb_admin_values['imdblanguage'] ?? NULL;
		$this->cachedir = $this->imdb_cache_values['imdbcachedir'] ?? NULL;
		$this->photodir = $this->imdb_cache_values['imdbphotoroot'] ?? NULL;// ?imdbphotoroot? Bug imdbphp?
		$this->cache_expire = $this->imdb_cache_values['imdbcacheexpire'] ?? NULL;
		$this->photoroot = $this->imdb_cache_values['imdbphotodir'] ?? NULL; // ?imdbphotodir? Bug imdbphp?
		$this->storecache = $this->imdb_cache_values['imdbstorecache'] ?? NULL;
		$this->usecache = $this->imdb_cache_values['imdbusecache'] ?? NULL;
		$this->converttozip = $this->imdb_cache_values['imdbconverttozip'] ?? NULL;
		$this->usezip = $this->imdb_cache_values['imdbusezip'] ?? NULL;

		// Execute the lumiere_maybe_display_debug_pages() through add action so gutenberg save doesn't get broken
		// @TODO: check if can be removed, all pages call it directly
		add_action('template_redirect', [$this, 'lumiere_maybe_display_debug_pages']);

		/** Where the local IMDB images reside (look for the "showtimes/" directory)
		*  This should be either a relative, an absolute, or an URL including the
		*  protocol (e.g. when a different server shall deliver them)
		* Cannot be changed in Lumière admin panel
		*/
		$this->imdb_img_url = isset($this->imdb_admin_values['imdbplugindirectory']).'/pics/showtimes' ?? NULL;

	}

	/** Prevent some pages to display debug
	 ** Sends to parent imdbphp class the debug var
	 **
	 **/
	function lumiere_maybe_display_debug_pages() {

		// Display debug in admin for imdblt_options pages
		if ( (is_admin()) && ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options' ) ) ) {

			$this->debug = $this->imdb_admin_values['imdbdebug'] ?? NULL;

		// Do not display debug for admin pages that are not imdblt_options
		} elseif ( (is_admin()) && (! 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options' ) ) ) {

			$this->debug = false;
			return false;

		// Diplay debug for all front pages except for gutenberg edition
		} elseif ( (!is_admin()) && ($this->isGutenberg == false) /* latest condition doesn't work */ ) {

			$this->debug = $this->imdb_admin_values['imdbdebug'] ?? NULL;

		}

	}

	/** Create cache folder if it does not exist
	 ** 
	 **/
	function lumiere_create_cache() {

		$imdb_admin_values = $this->imdb_admin_values;
		
		/* Cache folder paths */
		$lumiere_folder_cache = WP_CONTENT_DIR . '/cache/lumiere/';
		$lumiere_folder_cache_images = WP_CONTENT_DIR . '/cache/lumiere/images';

		// Cache folders exist, exit
		if ( (is_dir($lumiere_folder_cache)) || (is_dir($lumiere_folder_cache_images)) )
			return;

		// We can write in wp-content/cache
		if ( wp_mkdir_p( $lumiere_folder_cache ) ) {
			chmod( $lumiere_folder_cache, 0777 );
		// We can't write in wp-content/cache, so write in wp-content/plugins/lumiere/cache instead
		} else {
			$lumiere_folder_cache = plugin_dir_path( __DIR__ ) . 'cache';
			wp_mkdir_p( $lumiere_folder_cache );
			chmod( $lumiere_folder_cache, 0777 );
		}

		// We can write in wp-content/cache/images
		if ( wp_mkdir_p( $lumiere_folder_cache_images ) ) {
			chmod( $lumiere_folder_cache_images, 0777 );
		// We can't write in wp-content/cache/images, so write in wp-content/plugins/lumiere/cache/images instead
		} else {
			$lumiere_folder_cache = plugin_dir_path( __DIR__ ) . 'cache';
			$lumiere_folder_cache_images = $lumiere_folder_cache . '/images';
			wp_mkdir_p( $lumiere_folder_cache_images );
			chmod( $lumiere_folder_cache_images, 0777 );

		}


	}

	/* Try to detect if the current page is gutenberg editor
	 * Doesn't work
	 */
	function lumiere_is_gutenberg(){

		global $current_screen;

		$screen = get_current_screen();
		
		if ( $screen->is_block_editor() ) {

			$this->isGutenberg == true;

		} else {

			$this->isGutenberg == false;

		}

	}

	/** Start and select which Logger to use
	 ** 
	 ** By default, Logger is utilised if the var $isMonologActive is set "false", Monolog is set "true"
	 **/
	public function lumiere_start_logger($page_name="originUnknown") {

		if ( ($this->imdb_admin_values['imdbdebug'] == 1) && ($this->isMonologActive == true) ){

			// We 
			$logger = new \Monolog\Logger( $page_name );

			// Add current url and referrer to the log
			//$logger->pushProcessor(new \Monolog\Processor\WebProcessor(NULL, array('url','referrer') ));

			// Write to log, default to WordPress default log
			//$logger->pushHandler (new StreamHandler( self::debug_log_path, Logger::DEBUG) );

			// Send the logger class to a current class var
			$logger->debug('[Lumiere] Monolog logger has been started...');
			$this->loggerclass = $logger; # this var is then utilised in the call in other pages

		} else {

			$this->loggerclass = NULL;# this var is then utilised in the call in other pages

		}

	}

	/** Retrieve selected type of search in admin
	 ** Depends of $imdb_admin_values['imdbseriemovies'] option
	 ** Utilised by popups
	 **/
	public function lumiere_select_type_search () {

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;

		switch ($imdb_admin_values['imdbseriemovies']) {

			case "movies" : 
			return array( \Imdb\TitleSearch::MOVIE );
			break;
	
			case "movies+series" : 
			return array( \Imdb\TitleSearch::MOVIE, \Imdb\TitleSearch::TV_SERIES );
			break;

			case "series" : 
			return array( \Imdb\TitleSearch::TV_SERIES );
			break;	

			case "videogames" :
			return array( \Imdb\Title::GAME );
			break;
		}

		return false;

	}

} //End class

?>
