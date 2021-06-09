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

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die('You can not call directly this page');
}


/* CONSTANTS */
$imdb_admin_values['imdbplugindirectory'] = isset($imdb_admin_values['imdbplugindirectory']) ? $imdb_admin_values['imdbplugindirectory'] : plugin_dir_url( __DIR__ );
define('IMDBLTURLPATH', $imdb_admin_values['imdbplugindirectory'] );
define('IMDBLTABSPATH',  plugin_dir_path( __DIR__ ) );
define('IMDBLTFILE', plugin_basename( dirname(__FILE__)) );
define('IMDBBLOG', 'https://www.jcvignoli.com/blog');
define('IMDBBLOGENGLISH', IMDBBLOG . "/en");
define('IMDBBLOGHIGHSLIDE', IMDBBLOG . '/wp-content/files/wordpress-lumiere-highslide-5.0.0.zip');
define('IMDBHOMEPAGE', IMDBBLOGENGLISH . '/lumiere-movies-wordpress-plugin');
define('IMDBABOUTENGLISH', IMDBBLOGENGLISH . '/presentation-of-jean-claude-vignoli');
define('IMDBPHP_CONFIG', IMDBLTABSPATH . 'config.php');
$lumiere_version_recherche = file_get_contents( IMDBLTABSPATH . 'README.txt');
$lumiere_version = preg_match('#Stable tag:\s(.+)\n#', $lumiere_version_recherche, $lumiere_version_match);
# more constants at the end of the file


#--------------------------------------------------=[ configuration class ]=--


// use the original class in src/Imdb/Config.php
use \Imdb\Config;

class lumiere_settings_conf extends lumiere_send_config {

	var $imdbAdminOptionsName = "imdbAdminOptions";
	var $imdbWidgetOptionsName = "imdbWidgetOptions";
	var $imdbCacheOptionsName = "imdbCacheOptions";

	function __construct() {
		$this->get_imdb_admin_option();
		$this->get_imdb_widget_option();
		$this->get_imdb_cache_option();
	}

	//Returns an array of admin options
	function get_imdb_admin_option() {

	$imdbAdminOptions = array(
	#--------------------------------------------------=[ Basic ]=--
	'blog_adress' => get_bloginfo('url'),
	'imdbplugindirectory_partial' => '/wp-content/plugins/lumiere-movies/',
	'imdbpluginpath' => IMDBLTABSPATH,
	'imdburlpopups' => '/imdblt/',
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
	'imdbpicsize' => '25',
	'imdbpicurl' => 'pics/imdb-link.png',
	'imdbimgdir' => 'pics/',
	'imdblanguage' => "en-EN",
	'imdbdirectsearch' => true,
	/*'imdbsourceout' => false,*/
	'imdbdisplaylinktoimdb' => true,
	'imdbdebug' => false,
	'imdbwordpress_bigmenu'=>false,
	'imdbwordpress_tooladminmenu'=>true,
	'imdbpopup_highslide'=>true,
	'imdbtaxonomy'=> false,
	);
	$imdbAdminOptions['imdbplugindirectory'] = $imdbAdminOptions['blog_adress'] . $imdbAdminOptions['imdbplugindirectory_partial'];

	$imdbOptions = get_option($this->imdbAdminOptionsName);
		if (!empty($imdbOptions)) {
			foreach ($imdbOptions as $key => $option)
				$imdbAdminOptions[$key] = $option;
			// Since this value is outside of the main array, special treatment
			$imdbAdminOptions['imdbplugindirectory'] = $imdbAdminOptions['blog_adress'] .$imdbAdminOptions['imdbplugindirectory_partial'];

		}
		update_option($this->imdbAdminOptionsName, $imdbAdminOptions);
		return $imdbAdminOptions;
	} // end function get_imdb_admin_option ()


	//Returns an array of cache options
	function get_imdb_cache_option() {

	$imdbCacheOptions = array(
	#--------------------------------------------------=[ Cache ]=--
	//'imdbcachedir' => WP_CONTENT_DIR . '/cache/lumiere/', # is increment below
	//'imdbphotoroot' => '', # is increment below
	//'imdbphotodir' => content_url() . '/cache/lumiere/images/',
	'imdbcachedir_partial' => 'wp-content/cache/lumiere/',
	'imdbstorecache' => true,
	'imdbusecache' => true,
	'imdbconverttozip' => true,
	'imdbusezip' => true,
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
			foreach ($imdbOptionsc as $key => $option)
				$imdbCacheOptions[$key] = $option;

			// Since these values are outside of the main array, special treatment
			$imdbCacheOptions['imdbcachedir'] =  ABSPATH . $imdbCacheOptions['imdbcachedir_partial'];
			$imdbCacheOptions['imdbphotoroot'] =  $imdbCacheOptions['imdbcachedir'] . 'images/';
		}
		if (!empty($imdbOptions))
			// Since this value is outside of the main array, special treatment
			$imdbCacheOptions['imdbphotodir'] =  $imdbOptions['blog_adress'] . '/' . $imdbCacheOptions['imdbcachedir_partial'] . 'images/';

		update_option($this->imdbCacheOptionsName, $imdbCacheOptions);
		return $imdbCacheOptions;
	} // end function get_imdb_cache_option ()

	//Returns an array of widget options
	function get_imdb_widget_option() {
	#--------------------------------------------------=[ Widget ]=--

	$imdbWidgetOptions = array(
	'imdbautopostwidget' => false,
	'imdblinkingkill' => false,
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
	'imdbwidgetgenre' => false,
	'imdbwidgetwriter' => true,
	'imdbwidgetproducer' => false,
	'imdbwidgetkeywords' => false,
	'imdbwidgetprodcompany' => false,
	'imdbwidgetplot' => false,
	'imdbwidgetplotnumber' => false,
	'imdbwidgetgoofs' => false,
	'imdbwidgetgoofsnumber' => false,
	'imdbwidgetcomments' => false,
	'imdbwidgetcommentsnumber' => false,
	'imdbwidgetquotes' => false,
	'imdbwidgetquotesnumber' => false,
	'imdbwidgettaglines' => false,
	'imdbwidgettaglinesnumber' => false,
	'imdbwidgetcolors' => false,
	'imdbwidgetalsoknow' => false,
	'imdbwidgetcomposer' => false,
	'imdbwidgetsoundtrack' => false,
	'imdbwidgetsoundtracknumber' => false,
	'imdbwidgetofficialsites' => false,
	'imdbwidgetsource' => true,
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
	} // end function get_imdb_widget_option ()

} //End lumiere_settings_conf class


#--------------------------------------------------=[ Language ]=--

// Where the language files resides
// Edit only if you know what you are doing

load_plugin_textdomain('lumiere-movies', false, IMDBLTURLPATH . 'languages' );

#--------------------------------------------------=[ Class to send data to the master IMDbPHP classes ]=--

class lumiere_send_config extends Config {
	var $imdb_admin_values;
	var $imdb_cache_values;

	function __construct() {
	global $imdb_admin_values, $imdb_cache_values;

	$this->imdb_utf8recode = $imdb_admin_values['imdb_utf8recode'] ?? NULL;
	$this->imdbsite = $imdb_admin_values['imdbwebsite'] ?? NULL;
	$this->imdbplugindirectory = $imdb_admin_values['imdbplugindirectory'] ?? NULL;
	$this->language = $imdb_admin_values['imdblanguage'] ?? NULL;
	$this->maxresults = $imdb_admin_values['imdbmaxresults'] ?? NULL;
	$this->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;
	$this->photodir = $imdb_cache_values['imdbphotodir'] ?? NULL;
	$this->imdb_img_url = $imdb_cache_values['imdbimgdir'] ?? NULL;
	$this->cache_expire = $imdb_cache_values['imdbcacheexpire'] ?? NULL;
	$this->photoroot = $imdb_cache_values['imdbphotoroot'] ?? NULL;
	$this->storecache = $imdb_cache_values['imdbstorecache'] ?? NULL;
	$this->usecache = $imdb_cache_values['imdbusecache'] ?? NULL;
	$this->converttozip = $imdb_cache_values['imdbconverttozip'] ?? NULL;
	$this->usezip = $imdb_cache_values['imdbusezip'] ?? NULL;

	/** Where the local IMDB images reside (look for the "showtimes/" directory)
	*  This should be either a relative, an absolute, or an URL including the
	*  protocol (e.g. when a different server shall deliver them)
	* Cannot be changed in wordpress plugin
	*/
	$this->imdb_img_url = isset($imdb_admin_values['imdbplugindirectory']).'/pics/showtimes' ?? NULL;

	################################################# Browser agent used to get data; usually, doesn't need any change
	/** Set the default user agent (if none is detected)
	* @attribute string user_agent
	*/
	$this->default_agent = 'Mozilla/5.0 (X11; U; Linux i686; en; rv:1.9.2.3) Gecko/20100101 Firefox/80.0';
	/** Enforce the use of a special user agent
	* @attribute string force_agent
	*/
	$this->force_agent = '';
	/** Trigger the HTTP referer
	*  This is required in some places. However, if you think you need to disable
	*  this behaviour, do it here.
	*/
	$this->trigger_referer = TRUE;
	}
}

/* CONSTANTS related to the strings */

# put a the end so they can be called through a new class

$internal_call = new lumiere_settings_conf();
$imdb_admin_values = $internal_call->get_imdb_admin_option();
define('LUMIERE_VERSION', $lumiere_version_match[1]);
$LUMIERE_URLSTRING = (isset($imdb_admin_values['imdburlpopups'])) ? $imdb_admin_values['imdburlpopups'] : "/imdblt/";
define('LUMIERE_URLSTRING', $LUMIERE_URLSTRING );
define('LUMIERE_URLSTRINGFILMS', LUMIERE_URLSTRING . "film/");
define('LUMIERE_URLSTRINGPERSON', LUMIERE_URLSTRING. "person/");
define('LUMIERE_URLSTRINGSEARCH', LUMIERE_URLSTRING . "search/");
define('LUMIERE_URLPOPUPSFILMS', site_url() . LUMIERE_URLSTRINGFILMS );
define('LUMIERE_URLPOPUPSPERSON', site_url() . LUMIERE_URLSTRINGPERSON );
define('LUMIERE_URLPOPUPSSEARCH', site_url() . LUMIERE_URLSTRINGSEARCH );

?>
