<?php
 #############################################################################
 # Lumiere Movies                                                            #
 # written by Prometheus group                                               #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #       			                                                	#
 #  Function : Configuration file             				     	#
 #       	  			                                    	#
 #											#
 #############################################################################

#--------------------------------------------------=[ define constants ]=--
$imdb_admin_values['imdbplugindirectory'] = isset($imdb_admin_values['imdbplugindirectory']) ? $imdb_admin_values['imdbplugindirectory'] : plugins_url() . '/' . plugin_basename( dirname(__FILE__) ) . '/';
define('IMDBLTURLPATH', $imdb_admin_values['imdbplugindirectory'] );
define('IMDBLTFILE', plugin_basename( dirname(__FILE__)) );
define('IMDBLTABSPATH', str_replace("\\","/", WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__) ) . '/' ));
define('IMDBBLOG', "https://www.jcvignoli.com/blog");
define('IMDBHOMEPAGE', IMDBBLOG . '/lumiere-movies-wordpress-plugin');
define('IMDBPHP_CONFIG',dirname(__FILE__) . '/config.php');

#--------------------------------------------------=[ configuration class ]=--

// use the original class in src/Imdb/Config.php
use \Imdb\Config;

class imdb_settings_conf extends mdb_config {

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
	'imdbplugindirectory' => get_bloginfo('url').'/wp-content/plugins/lumiere-movies/',
	'imdbpluginpath' => IMDBLTABSPATH,
	'imdbwebsite' => "www.imdb.com",
	'imdbcoversize' => false,
	'imdbcoversizewidth' => '100',
	#--------------------------------------------------=[ Technical ]=--

	'imdb_utf8recode'=> true,
	'imdbmaxresults' => 10,
	'popupLarg' => '540',
	'popupLong' => '350',
	'imdbpicsize' => '25',
	'imdbpicurl' => 'pics/imdb-link.png',
	'imdbimgdir' => 'pics/',
	'imdblanguage' => "en-EN",
	'imdbdirectsearch' => true,
	/*'imdbsourceout' => false,*/
	'imdbdisplaylinktoimdb' => true,
	'PEAR' => false,
	'imdbwordpress_bigmenu'=>false,
	'imdbwordpress_tooladminmenu'=>true,
	'imdbpopup_highslide'=>false,
	'imdbtaxonomy'=> false,
	);
	$imdbOptions = get_option($this->imdbAdminOptionsName);
		if (!empty($imdbOptions)) {
			foreach ($imdbOptions as $key => $option)
				$imdbAdminOptions[$key] = $option;
		}
		update_option($this->imdbAdminOptionsName, $imdbAdminOptions);
		return $imdbAdminOptions;
	} // end function get_imdb_admin_option ()


	//Returns an array of cache options
	function get_imdb_cache_option() {

	$imdbCacheOptions = array(
	#--------------------------------------------------=[ Cache ]=--
	'imdbcachedir' => IMDBLTABSPATH . 'cache/',
	'imdbphotoroot' => IMDBLTABSPATH . 'cache/images/',
	'imdbphotodir' => IMDBLTURLPATH . 'cache/images/',
	'imdbstorecache' => true,
	'imdbusecache' => true,
	'imdbconverttozip' => true,
	'imdbusezip' => true,
	'imdbcacheexpire' => "2592000", // one month
	'imdbcachedetails'=> false,
	'imdbcachedetailsshort'=> false,
	);
	$imdbOptionsc = get_option($this->imdbCacheOptionsName);
		if (!empty($imdbOptionsc)) {
			foreach ($imdbOptionsc as $key => $option)
				$imdbCacheOptions[$key] = $option;
		}
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
	'imdbwidgetprodCompany' => false,
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
	'imdbwidgetofficialSites' => false,
	'imdbwidgetsource' => true,
	'imdbwidgetonpost' => true,
	'imdbwidgetonpage' => true,
	'imdbwidgetyear' => false,
	'imdbwidgettrailer' => false,

	'imdbwidgetorder'=>array("title" => "1", "pic" => "2","runtime" => "3", "director" => "4", "country" => "5", "actor" => "6", "creator" => "7", "rating" => "8", "language" => "9","genre" => "10","writer" => "11","producer" => "12", "keywords" => "13", "prodCompany" => "14", "plot" => "15", "goofs" => "16", "comments" => "17", "quotes" => "18", "taglines" => "19", "colors" => "20", "alsoknow" => "21", "composer" => "22", "soundtrack" => "23", "trailer" => "24", "officialSites" => "25", "source" => "26" ),

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

	//Prints out the admin page
	function printAdminPage() {
		$imdbOptions = $this->get_imdb_admin_option();
		$imdbOptionsc = $this->get_imdb_cache_option();
		$imdbOptionsw = $this->get_imdb_widget_option();

		if (isset($_POST['update_imdbSettings'])) { //--------------------save data selected (general options)

			foreach ($_POST as $key=>$postvalue) {
				$keynoimdb = str_replace ( "imdb_", "", $key);
				if (isset($_POST["$key"])) {
						$imdbOptions["$keynoimdb"] = $_POST["$key"];
				}
			}

			check_admin_referer('update_imdbSettings_check', 'update_imdbSettings_check'); // check if the refer is ok before saving data

			// display message on top
			imdblt_notice(1, '<strong>'. esc_html__( 'Options saved.', 'imdb') .'</strong>');

			update_option($this->imdbAdminOptionsName, $imdbOptions);

		}
		if (isset($_POST['reset_imdbSettings'])) { //---------------------reset options selected (general options)

			check_admin_referer('reset_imdbSettings_check', 'reset_imdbSettings_check'); // check if the refer is ok before saving data

			update_option($this->imdbAdminOptionsName, $imdbAdminOptions);

			// display message on top
			imdblt_notice(1, '<strong>'. esc_html__( 'Options reset.', 'imdb') .'</strong>');

			// refresh the page to reset display for values; &reset=true is the only way to reset all values and truly see them reset
			// also check plugin collision -> follow a link instead of automatic refresh
			if (!headers_sent()) {
				header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
			} else {
				imdblt_notice(1, '<strong>'. esc_html__( 'Plugin collision. Please follow ').'<a href="'.$_SERVER[ "REQUEST_URI"].'&reset=true">'.esc_html__( 'this link.', 'imdb').'</a>'.'</strong>');
			}
			//header("Location: ".$_SERVER[ "REQUEST_URI"]."&reset=true"); --- less sexy way

		}
		if  (isset($_POST['update_imdbwidgetSettings']) ) { //--------------save data selected (widget options)

			foreach ($_POST as $key=>$postvalue) {
				// Sanitize
				$key_sanitized = sanitize_text_field($key);

				// Keep $_POST['imdbwidgetorderContainer'] untouched 
				if ($key_sanitized == 'imdbwidgetorderContainer') continue;

				// remove "imdb_" from $key
				$keynoimdb = str_replace ( "imdb_", "", $key_sanitized);

				// Copy $_POST to $imdbOptionsw var
				if (isset($_POST["$key"])) {
					$imdbOptionsw["$keynoimdb"] = $_POST["$key_sanitized"];
				}
			}

			// Special part related to details order
			if (isset($_POST['imdbwidgetorderContainer']) ){
				// Sanitize
				$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
				$myinputs = $_POST['imdbwidgetorderContainer'];

				// increment the $key of one
				$data = array_combine(range(1, count($myinputs)), array_values($myinputs));

				// flip $key with $value
				$data = array_flip($data);

				// Put in the option
				$imdbOptionsw['imdbwidgetorder'] = $data;
			}

			// check if the refer is ok before saving data
			check_admin_referer('update_imdbwidgetSettings_check', 'update_imdbwidgetSettings_check');

			// make sure that data posted in imdb_imdbwidgetorder (options-widget.php) is not empty; 
			// otherwise, it will replace $imdbOptions['imdbwidgetorder'] values by an empty one.
			if ( $_POST['imdb_imdbwidgetorder'] == "empty") {

				if (!headers_sent()) {
					header("Location: ".$_SERVER[ "REQUEST_URI"], false);
					die();
				} else {
					imdblt_notice(1, '<strong>'. esc_html__( 'Error. You have to select a value.', 'imdb'). '</strong>' );
					die();
				}
			}

			update_option($this->imdbWidgetOptionsName, $imdbOptionsw);

			// display message on top
			imdblt_notice(1, '<strong>'. esc_html__( 'Options saved.', 'imdb') .'</strong>');

			// flush rewrite rules for taxonomy pages
			flush_rewrite_rules();

		 }
		if (isset($_POST['reset_imdbwidgetSettings'])) { // reset options selected  (widget options)

			check_admin_referer('reset_imdbwidgetSettings_check', 'reset_imdbwidgetSettings_check'); // check if the refer is ok before saving data
			update_option($this->imdbWidgetOptionsName, $imdbWidgetOptionsw);

			// display message on top
			imdblt_notice(1, '<strong>'. esc_html__( 'Options reset.', 'imdb') .'</strong>');

			// refresh the page to reset display for values; &reset=true is the only way to reset all values and truly see them reset
			// also check plugin collision -> follow a link instead of automatic refresh
			if (!headers_sent()) {
				header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
			} else {
				imdblt_notice(1, '<strong>'. esc_html__( 'Plugin collision. Please follow ').'<a href="'.$_SERVER[ "REQUEST_URI"].'&reset=true">'.esc_html__( 'this link.', 'imdb').'</a>'.'</strong>');
			}

			// flush rewrite rules for taxonomy pages
			flush_rewrite_rules();
		}

		if (isset($_POST['update_cache_options'])) { // save data selected (cache options)

			foreach ($_POST as $key=>$postvalue) {
				$keynoimdb = str_replace ( "imdb_", "", $key);
				if (isset($_POST["$key"])) {
						$imdbOptionsc["$keynoimdb"] = $_POST["$key"];
				}
			}

			check_admin_referer('update_cache_options_check', 'update_cache_options_check'); // check if the refer is ok before saving data

			// display message on top
			imdblt_notice(1, '<strong>'. esc_html__( 'Options saved.', 'imdb') .'</strong>');

			update_option($this->imdbCacheOptionsName, $imdbOptionsc);

		}
		if (isset($_POST['reset_cache_options'])) { // reset options selected (cache options)

			check_admin_referer('reset_cache_options_check', 'reset_cache_options_check'); // check if the refer is ok before saving data

			update_option($this->imdbCacheOptionsName, $imdbCacheOptions);

			// display message on top
			imdblt_notice(1, '<strong>'. esc_html__( 'Options reset.', 'imdb') .'</strong>');

			// refresh the page to reset display for values; &reset=true is the only way to reset all values and truly see them reset
			// also check plugin collision -> follow a link instead of automatic refresh
			if (!headers_sent()) {
				header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
			} else {
				imdblt_notice(1, '<strong>'. esc_html__( 'Plugin collision. Please follow ').'<a href="'.$_SERVER[ "REQUEST_URI"].'&reset=true">'.esc_html__( 'this link.', 'imdb').'</a>'.'</strong>');
			}
			//header("Location: ".$_SERVER[ "REQUEST_URI"]."&reset=true"); --- less sexy way

		}
		if (isset($_POST['reset_imdbltcache'])) {  // reset detected, delete all cache files (cache options)

			check_admin_referer('reset_imdbltcache_check', 'reset_imdbltcache_check'); // check if the refer is ok before saving data

			imdblt_notice(1, '<strong>'. esc_html__( 'All cache deleted.', 'imdb') .'</strong>');

			// refresh the page to reset display for values; &reset=true is the only way to reset all values and truly see them reset
			// also check plugin collision -> follow a link instead of automatic refresh
			if (!headers_sent()) {
				header("Refresh: 0;url=".$_SERVER[ "REQUEST_URI"]."&reset=true", false);
			} else {
				imdblt_notice(1, '<strong>'. esc_html__( 'Plugin collision. Please follow ').'<a href="'.$_SERVER[ "REQUEST_URI"].'&reset=true">'.esc_html__( 'this link.', 'imdb').'</a>'.'</strong>');
			}

			imdblt_unlinkRecursive ( $imdbOptionsc['imdbcachedir'] );


		}
		if (isset($_POST['update_imdbltcache'])) { // update detected, delete some cache files (cache options)

			check_admin_referer('update_imdbltcache_check', 'update_imdbltcache_check'); // check if the refer is ok before saving data

			imdblt_notice(1, '<strong>'. esc_html__( 'Selected cache deleted.', 'imdb') .'</strong>');

			// for movies
			for ($i = 0; $i < count ($_POST ['imdb_cachedeletefor']); $i++) {
				foreach ( glob($imdbOptionsc['imdbcachedir'].$_POST ['imdb_cachedeletefor'][$i].".*") as $cacheTOdelete) {
					if($cacheTOdelete == $imdbOptionsc['imdbcachedir'].'.' || $cacheTOdelete == $imdbOptionsc['imdbcachedir'].'..') {
						continue;
					}
					unlink ("$cacheTOdelete");
				}
			}

			// for people
			for ($i = 0; $i < count ($_POST ['imdb_cachedeletefor_people']); $i++) {
				foreach ( glob($imdbOptionsc['imdbcachedir'].$_POST ['imdb_cachedeletefor_people'][$i].".*") as $cacheTOdelete) {
					if($cacheTOdelete == $imdbOptionsc['imdbcachedir'].'.' || $cacheTOdelete == $imdbOptionsc['imdbcachedir'].'..') {
						continue;
					}
					unlink ("$cacheTOdelete");
				}
			}


		}
		//----------------------------------------------------------display the admin settings options ?>

<div class=wrap>
	<?php screen_icon('options-general'); ?>
	<h2><?php esc_html_e( "Lumiere Movies options", "imdb"); ?></h2>
	<br />

	<div class="subpage">

	<div align="left" class="imdblt_float_left">
		<img src="<?php echo IMDBLTURLPATH; ?>pics/admin-general.png" align="absmiddle" width="16px" />&nbsp;
		<a title="<?php esc_html_e( 'General Options', 'imdb'); ?>" href="<?php echo admin_url(); ?>admin.php?page=imdblt_options"> <?php esc_html_e( 'General Options', 'imdb'); ?></a>

		<?php 	### sub-page is relative to what is activated
			### check if widget is active, and/or direct search option
		if ( ($imdbOptions['imdbdirectsearch'] == "1") && (is_active_widget(widget_imdbwidget)) ){ ?>

		&nbsp;&nbsp;<img src="<?php esc_html_e( IMDBLTURLPATH . "pics/admin-widget-inside.png"); ?>" align="absmiddle" width="16px" />&nbsp;
		<a title="<?php esc_html_e( 'Widget/Inside post Options', 'imdb'); ?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption"); ?>"><?php esc_html_e( 'Widget/Inside post Options', 'imdb'); ?></a>
		<?php } elseif ( ($imdbOptions['imdbdirectsearch'] == "1") && (! is_active_widget(widget_imdbwidget)) ) { ?>
		&nbsp;&nbsp;<img src="<?php esc_html_e( IMDBLTURLPATH . "pics/admin-widget-inside.png"); ?>" align="absmiddle" width="16px" />&nbsp;
		<a title="<?php esc_html_e( 'Widget/Inside post Options', 'imdb'); ?>" href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption"); ?>"><?php esc_html_e( 'Widget/Inside post Options', 'imdb'); ?></a> (<em><a href="widgets.php"><?php esc_html_e( 'Widget unactivated', 'imdb'); ?>)</a></em>)

		<?php } elseif ( (!$imdbOptions['imdbdirectsearch'] == "1") && (is_active_widget(widget_imdbwidget)) )  { ?>
		&nbsp;&nbsp;<img src="<?php esc_html_e( IMDBLTURLPATH . "pics/admin-widget-inside.png"); ?>" align="absmiddle" width="16px" />&nbsp;
		<a title="<?php esc_html_e( 'Widget/Inside post Options', 'imdb'); ?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption"); ?>"><?php esc_html_e( 'Widget/Inside post Options', 'imdb'); ?></a> (<em><a href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options&generaloption=advanced#imdb_imdbdirectsearch_yes"); ?>"><?php esc_html_e( 'Direct search', 'imdb'); ?></a> <?php esc_html_e( 'unactivated', 'imdb'); ?></em>)

<?php		} else { ?>
		&nbsp;&nbsp;<img src="<?php esc_html_e( IMDBLTURLPATH . "pics/admin-widget-inside.png"); ?>" align="absmiddle" width="16px" />&nbsp;
		<a title="<?php esc_html_e( 'Widget/Inside post Options', 'imdb'); ?>" href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&subsection=widgetoption"); ?>"><?php esc_html_e( 'Widget/Inside post Options', 'imdb'); ?></a> (<em><a href="<?php echo esc_url ( admin_url() . "admin.php?page=imdblt_options&generaloption=advanced#imdb_imdbdirectsearch_yes"); ?>"><?php esc_html_e( 'Direct search', 'imdb'); ?></a></em> & <em><a href="widgets.php"><?php esc_html_e( 'Widget unactivated', 'imdb'); ?></a></em>)

<?php 		} ?>
		&nbsp;&nbsp;<img src="<?php echo esc_url ( IMDBLTURLPATH . "pics/admin-cache.png"); ?>" align="absmiddle" width="16px" />&nbsp;
		<a title="<?php esc_html_e( 'Cache management', 'imdb'); ?>" href="<?php echo admin_url(); ?>admin.php?page=imdblt_options&subsection=cache"><?php esc_html_e( 'Cache management', 'imdb'); ?></a>
	</div>
	<div align="right" >
		&nbsp;&nbsp;<img src="<?php echo esc_url( IMDBLTURLPATH . "pics/admin-help.png"); ?>" align="absmiddle" width="16px" />&nbsp;
		<a title="<?php esc_html_e( 'How to use Lumiere Movies, check FAQs & changelog', 'imdb');?>" href="<?php echo esc_url( admin_url() . "admin.php?page=imdblt_options&subsection=help"); ?>">
			<?php esc_html_e( 'Lumiere Movies help', 'imdb'); ?>
		</a>
	</div>
	</div>

	<?php ### select the sub-page

	if (empty($_GET['subsection'])) { ?>
		<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="<?php echo $_SERVER[ "REQUEST_URI"]; ?>" >
			<?php include ( IMDBLTABSPATH . 'inc/options-general.php'); ?>
		</form>
<?php 	}
	if ($_GET['subsection'] == "widgetoption")  {	?>
		<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="<?php echo $_SERVER[ "REQUEST_URI"]; ?>" >
			<?php include ( IMDBLTABSPATH . 'inc/options-widget.php'); ?>
		</form>
<?php 	}
	elseif ($_GET['subsection'] == "cache")  {
		$test = $this->get_imdb_admin_option(); //this variable has to be sent to new page
		$engine = $test['imdbsourceout'];
		include ( IMDBLTABSPATH . 'inc/options-cache.php');}
	elseif ($_GET['subsection'] == "help")  {
		include ( IMDBLTABSPATH . 'inc/help.php');}
	// end subselection ?>

	<?php imdblt_admin_signature (); ?>

<?php		} //End function printAdminPage()

} //End class


#--------------------------------------------------=[ Language ]=--

// Where the language files resides
// Edit only if you know what you are doing

load_plugin_textdomain('imdb', false, IMDBLTURLPATH . 'language' );

#--------------------------------------------------=[ Class to be called from original imdb classes ]=--

class mdb_config extends Config {
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
	* @class imdb_config
	* @attribute string imdb_img_url
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

?>
