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

// use the original class in src/Imdb/Config.php
use \Imdb\Config;

use \Monolog\Logger;

class Settings extends Config {

	var $imdbAdminOptionsName = "imdbAdminOptions";
	var $imdbWidgetOptionsName = "imdbWidgetOptions";
	var $imdbCacheOptionsName = "imdbCacheOptions";
	public $imdb_admin_values;
	public $imdb_widget_values;
	public $imdb_cache_values;

	/** Constructor
	 **
	 **/
	function __construct() {

		// Construct parent class so we can send the settings
		parent::__construct();

		// Define Lumière constants
		$this->lumiere_define_constants();

$this->lumiere_create_cache();
		// Send to the global vars the options
		$this->imdb_admin_values = $this->get_imdb_admin_option();
		$this->imdb_widget_values = $this->get_imdb_widget_option();
		$this->imdb_cache_values = $this->get_imdb_cache_option();

		// Call the plugin translation
		load_plugin_textdomain('lumiere-movies', false, IMDBLTURLPATH . 'languages' );

		// Call the function to send the selected settings to imdbphp library
		$this->lumiere_send_config_imdbphp();


	}

	/** Define global constants
	 **
	 **
	 **/
	/* @TODO: work on the consistancy with function get_imdb_*_option, so this can be called either before or after, right now it's a mess */
	function lumiere_define_constants() {

		global $imdb_admin_values;

		/* CONSTANTS */
		$imdb_admin_values['imdbplugindirectory'] = isset($imdb_admin_values['imdbplugindirectory']) ? $imdb_admin_values['imdbplugindirectory'] : plugin_dir_url( __DIR__ );
		if(!defined('IMDBLTURLPATH'))
			define('IMDBLTURLPATH', $imdb_admin_values['imdbplugindirectory'] );
		if(!defined('IMDBLTABSPATH'))
			define('IMDBLTABSPATH',  plugin_dir_path( __DIR__ ) ); # would be better WP_PLUGIN_DIR . '/lumiere-movies/' ??
		if(!defined('IMDBLTFILE'))
			define('IMDBLTFILE', plugin_basename( dirname(__FILE__)) );
		if(!defined('IMDBBLOG'))
			define('IMDBBLOG', 'https://www.jcvignoli.com/blog');
		if(!defined('IMDBBLOGENGLISH'))
			define('IMDBBLOGENGLISH', IMDBBLOG . "/en");
		if(!defined('IMDBBLOG'))
			define('IMDBBLOGHIGHSLIDE', IMDBBLOG . '/wp-content/files/wordpress-lumiere-highslide-5.0.0.zip');
		if(!defined('IMDBHOMEPAGE'))
			define('IMDBHOMEPAGE', IMDBBLOGENGLISH . '/lumiere-movies-wordpress-plugin');
		if(!defined('IMDBABOUTENGLISH'))
			define('IMDBABOUTENGLISH', IMDBBLOGENGLISH . '/presentation-of-jean-claude-vignoli');
		if(!defined('IMDBPHP_CONFIG'))
			define('IMDBPHP_CONFIG', IMDBLTABSPATH . 'config.php');
		if(!defined('LUMIERE_VERSION')){
			$lumiere_version_recherche = file_get_contents( IMDBLTABSPATH . 'README.txt');
			$lumiere_version = preg_match('#Stable tag:\s(.+)\n#', $lumiere_version_recherche, $lumiere_version_match);
			define('LUMIERE_VERSION', $lumiere_version_match[1]);
		}
		if(!defined('LUMIERE_URLSTRING')){
			$LUMIERE_URLSTRING = (isset($imdb_admin_values['imdburlpopups'])) ? $imdb_admin_values['imdburlpopups'] : "/imdblt/";
			define('LUMIERE_URLSTRING', $LUMIERE_URLSTRING );
		}
		if(!defined('LUMIERE_URLSTRINGFILMS'))
			define('LUMIERE_URLSTRINGFILMS', LUMIERE_URLSTRING . "film/");
		if(!defined('LUMIERE_URLSTRINGPERSON'))
			define('LUMIERE_URLSTRINGPERSON', LUMIERE_URLSTRING. "person/");
		if(!defined('LUMIERE_URLSTRINGSEARCH'))
			define('LUMIERE_URLSTRINGSEARCH', LUMIERE_URLSTRING . "search/");
		if(!defined('LUMIERE_URLPOPUPSFILMS'))
			define('LUMIERE_URLPOPUPSFILMS', site_url() . LUMIERE_URLSTRINGFILMS );
		if(!defined('LUMIERE_URLPOPUPSPERSON'))
			define('LUMIERE_URLPOPUPSPERSON', site_url() . LUMIERE_URLSTRINGPERSON );
		if(!defined('LUMIERE_URLPOPUPSSEARCH'))
			define('LUMIERE_URLPOPUPSSEARCH', site_url() . LUMIERE_URLSTRINGSEARCH );

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
			'imdbpluginpath' => IMDBLTABSPATH,
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

	/** Send WordPress options to imdbphp library
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

		$this->lumiere_maybe_display_debug_pages();

		/** Where the local IMDB images reside (look for the "showtimes/" directory)
		*  This should be either a relative, an absolute, or an URL including the
		*  protocol (e.g. when a different server shall deliver them)
		* Cannot be changed in Lumière admin panel
		*/
		$this->imdb_img_url = isset($this->imdb_admin_values['imdbplugindirectory']).'/pics/showtimes' ?? NULL;

	}

	/** Prevent some pages to display debug
	 **
	 **
	 **/
	function lumiere_maybe_display_debug_pages() {

		// Display debug in admin for imdblt_options pages
		if ( (is_admin()) && ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options' ) ) ) {

			$this->debug = $this->imdb_admin_values['imdbdebug'] ?? NULL;

		// Do not display debug for admin pages that are not imdblt_options
		} elseif ( (is_admin()) && (! 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options' ) ) ) {

			$this->debug = false;

		// Diplay debug for all front pages
		} elseif (!is_admin())  {

			$this->debug = $this->imdb_admin_values['imdbdebug'] ?? NULL;

		}

	}

	/**
	 ** Create cache folder
	 **/
	function lumiere_create_cache() {

		$imdb_admin_values = $this->imdb_admin_values;
		
		/* Cache folder paths */
		$lumiere_folder_cache = WP_CONTENT_DIR . '/cache/lumiere/';
		$lumiere_folder_cache_images = WP_CONTENT_DIR . '/cache/lumiere/images';

		// Cache folder exist, exit
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


} //End class


?>
