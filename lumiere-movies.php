<?php
// Lumière wordpress plugin
//
// (c) 2005-21 Prometheus group
// https://www.jcvignoli.com/blog
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// *****************************************************************

/*
Plugin Name: Lumière
Plugin URI: https://www.jcvignoli.com/blog/lumiere-movies-wordpress-plugin
Description: Add to every movie title tagged with &lt;!--imdb--&gt; (...) &lt;!--/imdb--&gt; a link to an <a href="https://www.imdb.com"><acronym title="internet movie database">imdb</acronym></a> popup. Can also display data related to movies either in a <a href="widgets.php">widget</a> or inside a post. Perfect for your movie reviews. Cache handling. Have a look at the <a href="admin.php?page=imdblt_options">options page</a>.
Version: 3.0
Author: jcv
Author URI: https://www.jcvignoli.com/blog
*/ 

// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) 
	die('You are not allowed to call this page directly.');

# Bootstrap with requires
require_once ( plugin_dir_path( __FILE__ ) . '/bootstrap.php' );

# Executed upon plugin activated/deactivated
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'lumiere_activation' );

function lumiere_activation() {
	if (is_admin()) { // Prevents activation bug with Fatal Error: Table ‘actionscheduler_actions’ doesn’t exist
		$start = new lumiere_core;
		$start->lumiere_make_htaccess();
	}

	$lumiere_folder_cache = IMDBLTABSPATH . 'cache';
	if ( ! is_dir( $lumiere_folder_cache ) ) {
		$lumiere_folder_cache_images = $lumiere_folder_cache . '/images';
		wp_mkdir_p( $lumiere_folder_cache );
		chmod( $lumiere_folder_cache, 0777 );
		wp_mkdir_p( $lumiere_folder_cache_images );
		chmod( $lumiere_folder_cache_images, 0777 );
	}
	flush_rewrite_rules();
}

### Lumiere Classes start

if (class_exists("lumiere_settings_conf")) {
	$imdb_ft = new lumiere_settings_conf();
	$imdb_admin_values = $imdb_ft->get_imdb_admin_option();
	$imdb_widget_values = $imdb_ft->get_imdb_widget_option();
	$imdb_cache_values = $imdb_ft->get_imdb_cache_option();
}

if (class_exists("lumiere_core")) {
	global $imdb_ft, $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;
	$start = new lumiere_core();
}

// *********************
// ********************* CLASS lumiere_core
// *********************

//namespace imdblt;

class lumiere_core {
	public $bypass;

	/*constructor*/
	function __construct () {
		$this->lumiere_plugin_start();
	}

	/**
	1.- Create inc/.htaccess upon plugin activation
	**/

	function lumiere_make_htaccess(){
		/* vars */
		$imdblt_blog_subdomain = site_url( '', 'relative' ) ?? ""; #ie: /subdirectory-if-exists/
		$imdblt_plugin_full_path = plugin_dir_path( __FILE__ ) ?? wp_die( esc_html__("There was an error when generating the htaccess file.", "imdb") ); # ie: /fullpathtoplugin/subdirectory-if-exists/wp-content/plugins/lumiere-movies/
		$imdblt_plugin_path = str_replace( $imdblt_blog_subdomain, "", wp_make_link_relative( plugin_dir_url( __FILE__ ))); #ie: /wp-content/plugins/lumiere-movies/
		$imdblt_htaccess_file = $imdblt_plugin_full_path  . "/inc/.htaccess" ?? wp_die( esc_html__("There was an error when generating the htaccess file.", "imdb") ); # ie: /fullpathtoplugin/subdirectory-if-exists/wp-content/plugins/lumiere-movies/inc/.htaccess
		$imdblt_slug_path_movie = "imdblt/film";
		$imdblt_slug_path_person = "imdblt/person";

		// .htaccess text, including Rewritebase with $blog_subdomain
		$imdblt_htaccess_file_txt = "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase ".$imdblt_blog_subdomain."/"."\n\n";

		# highslide
		$imdblt_htaccess_file_txt .= "## highslide_download.php\nRewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/highslide_download.php [NC]"."\n"."RewriteRule ^.+$ wp-admin/admin.php?page=imdblt_options [L,R,QSA]"."\n\n";

		## move_template_taxonomy.php
		$imdblt_htaccess_file_txt .= "## highslide_download.php\nRewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/move_template_taxonomy.php [NC]"."\n"."RewriteRule ^.+$ wp-admin/admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=taxo [L,R,QSA]"."\n\n";

		# popup-search
		$imdblt_htaccess_file_txt .= "## popup-search.php\nRewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-search.php\?film=([^\s]+)(&norecursive=[^\s]+)?"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_movie."/%1/ [L,R,QSA]"."\n\n";

		# popup-imdb-movie.php
		$imdblt_htaccess_file_txt .= "## popup-imdb_movie.php"."\n"."RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_movie.php\?film=([^\s]+) [NC]\nRewriteRule ^.+$ ".$imdblt_slug_path_movie."/%1/ [L,R,QSA]"."\n\n";
		$imdblt_htaccess_file_txt .= "RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_movie.php\?mid=([^\s]+)&film=&info=([^\s]+)? [NC]"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_movie."/%1/ [L,R,QSA]"."\n\n";
		$imdblt_htaccess_file_txt .= "RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_movie.php\?mid=?([^&#]+)&film=([^\s]+)?(&info=[^\s]*) [NC]"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_movie."/%2/ [L,R,QSA]"."\n\n";
		$imdblt_htaccess_file_txt .= "RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_movie.php\?mid=([^\s]+) [NC]"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_movie."/%1/ [L,R,QSA]"."\n\n";

		# popup-imdb_person.php
		$imdblt_htaccess_file_txt .= "## popup-imdb_person.php"."\n"."RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_person.php\?mid=([^&#]+)&(film=[^\s]+)(&info=[^\s]+)? [NC]"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_person."/%1/ [L,R,QSA]"."\n\n";
		$imdblt_htaccess_file_txt .= "RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_person.php\?mid=([^\s]+) [NC]"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_person."/%1/ [L,R,QSA]"."\n\n";
		$imdblt_htaccess_file_txt .= "</IfModule>"."\n";

		// write the .htaccess file and close
		if (isset($imdblt_htaccess_file)) {
			file_put_contents($imdblt_htaccess_file, $imdblt_htaccess_file_txt.PHP_EOL);
			// lumiere_notice(1, esc_html__( 'htaccess file successfully generated.', 'imdb') ); # is not displayed
		} else {
			wp_die(lumiere_notice(3, esc_html__( 'Failed creating htaccess file.', 'imdb') ));
			//lumiere_notice(3, esc_html__( 'Failed creating htaccess file.', 'imdb') ); 
		}
	}

	/**
	2.- Replace <!--imdb--> tags inside the posts
	**/

	##### a) Looks for what is inside tags  <!--imdb--> ...  <!--/imdb--> and constructs a link
	function parse_imdb_tags($correspondances){
		global $imdb_admin_values;
	    
		$correspondances = $correspondances[0];
		preg_match("/<!--imdb-->(.*?)<!--\/imdb-->/i", $correspondances, $link_parsed);

		// link construction

		if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup
			$link_parsed = lumiere_popup_highslide_film_link ($link_parsed) ;
		} else {						// classic popup
		    	$link_parsed = lumiere_popup_classical_film_link ($link_parsed) ;
		}

		return $link_parsed;
	}

	##### b) Replace  <!--imdb--> tags with links 
	function lumiere_linking($text) {
		$pattern = '/<!--imdb-->(.*?)<!--\/imdb-->/i';
		$text = preg_replace_callback($pattern,array(&$this, 'parse_imdb_tags'),$text);
		return $text;
	}

	/**
	3.- Replace [imdblt]movieID[/imdblt] tags inside posts (as an automation of lumiere_external_call function)
	**/

	##### a) Looks for what is inside tags [imdblt] .... [/imdblt] and include the movies data

	function parse_lumiere_tag_transform ($text) {
		global $imdb_admin_values, $wp_query;
		$imdballmeta[] = $text[1];
		return $this->lumiere_external_call($imdballmeta);
	}

	##### b) Replace [imdblt] .... [/imdblt] tags with movies data
	function lumiere_tags_transform ($text) {
		$pattern = "'\[imdblt\](.*?)\[/imdblt\]'si";
		return preg_replace_callback($pattern, array(&$this, 'parse_lumiere_tag_transform'),$text);
	}

	/**
	4.- Replace [imdbltid]movieID[/imdbltid] tags inside posts (with lumiere_external_call function)
	**/

	##### a) Looks for what is inside tags [imdbltid] .... [/imdbltid] and include the movies data

	function parse_lumiere_tag_transform_id ($text) {
		global $imdb_admin_values, $wp_query;
		$imdballmeta = $text[1];
		return $this->lumiere_external_call('','',$imdballmeta);
	}

	##### b) Replace [imdblt] .... [/imdblt] tags with movies data
	function lumiere_tags_transform_id ($text) {
		$pattern = "'\[imdbltid\](.*?)\[/imdbltid\]'si";
		return preg_replace_callback($pattern, array(&$this, 'parse_lumiere_tag_transform_id'),$text);
	}

	/**
	5.-  Add tags button <!--imdb--> <!--/imdb--> to writing admin page
	**/

	##### a) HTML part
	function lumiere_add_quicktag() {
		global $imdb_admin_values;
		if (wp_script_is('quicktags')){
			wp_enqueue_script( "imdblt_add_quicktag", $imdb_admin_values['imdbplugindirectory'] ."js/qtags-addbuttons.js");
	    }
	}

	##### b) tinymce part (wysiwyg display)

	function lumiere_add_buttons() {
	   // Don't bother doing this stuff if the current user lacks permissions
	   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
	     return;
	 
	   // Add only in Rich Editor mode
	   if ( get_user_option('rich_editing') == 'true') {
	     add_filter("mce_external_plugins", [ $this, "add_imdb_tinymce_plugin" ] );
	     add_filter('mce_buttons', [ $this, 'register_imdb_button' ] );
	   }
	}
	function register_imdb_button($buttons) {
		array_push($buttons, "separator", "imdb");
		return $buttons;
	}
	// Load the TinyMCE plugin 
	function add_imdb_tinymce_plugin($plugin_array) {
		global $imdb_admin_values;
		$plugin_array['imdb'] = $imdb_admin_values['imdbplugindirectory'] . 'js/tinymce_editor_imdblt_plugin.js';
		return $plugin_array;
	}

	// Change the title of the popups according to the movie's or person's data
	function change_popup_title($title) {
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/imdblt/' ) ){
			
			if ($_GET['film'])
				$title = sanitize_text_field($_GET['film']). " - Lumière wordpress - ";
			/* find a way to get person's name
			elseif ($_GET['person'])
				$title = sanitize_text_field($_GET['person']). " - Lumière wordpress - ";
			*/

			return $title;
		}
	}
	/**
	6.- Add the stylesheet & javascript to pages head
	**/ 

	##### a) outside admin part
	public function lumiere_add_head_blog ($bypass=NULL){
		global $imdb_admin_values;

		// Load js and css in /imdblt/ URLs or if the function is called with lumiere_add_head_blog("inc.movie")
		if ( ($bypass=="inc.movie") || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/imdblt/' ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/inc/' ) ) ) { 

			// Highslide popup
			if ($imdb_admin_values['imdbpopup_highslide'] == 1) {
				wp_enqueue_script( "imdblt_highslide", $imdb_admin_values['imdbplugindirectory'] ."js/highslide/highslide-with-html.min.js", array(), "5.0");
				wp_enqueue_script( "imdblt_highslide_options", $imdb_admin_values['imdbplugindirectory'] ."js/highslide-options.js");
				// Pass variable to javascript highslide-options.js
				$dataToBePassedHighslide = array(
				    'imdb_path' => $imdb_admin_values['imdbplugindirectory']
				);
				wp_localize_script( "imdblt_highslide_options", 'php_vars', $dataToBePassedHighslide );
				wp_enqueue_style( "imdblt_highslide", $imdb_admin_values['imdbplugindirectory'] ."css/highslide.css"); 
			}

			// Use local template imdb.css if it exists in current theme folder
			if (file_exists (TEMPLATEPATH . "/imdb.css") ) { // an imdb.css exists inside theme folder, take it! 
				wp_enqueue_style('imdblt_imdbcss', bloginfo('stylesheet_directory') . "imdb.css");
		 	} else {
				wp_enqueue_style('imdblt_imdbcss', $imdb_admin_values['imdbplugindirectory'] ."css/imdb.css");
		 	}

			// OceanWp template css fix
			// enqueue imdb.css only if using oceanwp template
			if ( ( stripos( TEMPLATEPATH, '/wp-content/themes/oceanwp' ) ) && ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/imdblt/' ) ) ) {
				wp_enqueue_style('imdblt_imdbcss_oceanwpfixes', $imdb_admin_values['imdbplugindirectory'] ."css/imdb-oceanwpfixes.css");
			}
		}
	}

	function lumiere_add_footer_blog( $bypass=NULL ){
		global $imdb_admin_values; 

		// Load js and css in /imdblt/ URLs or if the function is called with lumiere_add_footer_blog("inc.movie")
		if ( ($bypass=="inc.movie") || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/imdblt/' ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/inc/' ) ) ) { 

			wp_enqueue_script( "imdblt_hide-show_csp", $imdb_admin_values['imdbplugindirectory'] ."js/hide-show_csp.js");

			wp_enqueue_script( "csp_inline_scripts", $imdb_admin_values['imdbplugindirectory'] ."js/csp_inline_scripts.js");

			// Pass variable to javascript csp_inline_scripts.js
			$dataToBePassedcsp_inline_scripts = array(
				'popupLarg' => $imdb_admin_values['popupLarg'],
				'popupLong' => $imdb_admin_values['popupLong'],
				'imdb_path' => $imdb_admin_values['imdbplugindirectory']
			);
			wp_localize_script( "csp_inline_scripts", 'csp_inline_scripts_vars', $dataToBePassedcsp_inline_scripts );
		}
	}

	##### b) admin part
	function lumiere_add_head_admin () {
		$this->lumiere_add_css_admin ();
		$this->lumiere_add_js_admin ();
	}
	function lumiere_add_css_admin() {
		global $imdb_admin_values;
		wp_enqueue_style('imdblt_css_admin', $imdb_admin_values['imdbplugindirectory'] . "css/imdb-admin.css");
	}
	function lumiere_add_js_admin () {
		global $imdb_admin_values;
		wp_enqueue_script('common'); // script needed for meta_boxes (ie, help.php)
		wp_enqueue_script('wp-lists'); // script needed for meta_boxes (ie, help.php)
		wp_enqueue_script('postbox'); // script needed for meta_boxes (ie, help.php)
		wp_enqueue_script('jquery'); // script needed by highslide and maybe others
		wp_enqueue_script('imdblt_un-active-boxes', $imdb_admin_values['imdbplugindirectory'] . "js/un-active-boxes.js");
		wp_enqueue_script('imdblt_movevalues-formeselectboxes', $imdb_admin_values['imdbplugindirectory'] . "js/movevalues-formselectboxes.js");

		wp_enqueue_script( "csp_inline_scripts_admin", $imdb_admin_values['imdbplugindirectory'] ."js/csp_inline_scripts_admin.js");
		// Pass variable to javascript highslide-options.js
		$dataToCSPAdmin = array(
		    'imdb_path' => $imdb_admin_values['imdbplugindirectory'],
		);
		wp_localize_script( "csp_inline_scripts_admin", 'php_vars', $dataToCSPAdmin );
	} 

	/**
	7.- Add the admin menu
	**/

	function lumiere_admin_panel() {
		global $imdb_ft, $imdb_admin_values;
		if (!isset($imdb_ft)) {
			return;
		}
		
		if (function_exists('add_options_page') && ($imdb_admin_values['imdbwordpress_bigmenu'] == 0 ) ) {
			add_options_page('Lumière Options', '<img src="'. $imdb_admin_values['imdbplugindirectory']. 'pics/imdb.gif"> Lumière', 'administrator', 'imdblt_options', [ $imdb_ft, 'printAdminPage'] );

			// third party plugin
			add_filter('ozh_adminmenu_icon_imdblt_options', [ $this, 'ozh_imdblt_icon' ] );
		}
		if (function_exists('add_submenu_page') && ($imdb_admin_values['imdbwordpress_bigmenu'] == 1 ) ) {
			// big menu for many pages for admin sidebar
			add_menu_page( 'Lumière Options', '<i>Lumière</i>' , 8, 'imdblt_options', [ $imdb_ft, 'printAdminPage' ], $imdb_admin_values['imdbplugindirectory'].'pics/imdb.gif');
			add_submenu_page( 'imdblt_options' , esc_html__('Lumière options page', 'imdb'), esc_html__('General options', 'imdb'), 8, 'imdblt_options');
			add_submenu_page( 'imdblt_options' , esc_html__('Widget & In post options page', 'imdb'), esc_html__('Widget/In post', 'imdb'), 8, 'imdblt_options&subsection=widgetoption', [ $imdb_ft, 'printAdminPage'] );
			add_submenu_page( 'imdblt_options',  esc_html__('Cache management options page', 'imdb'), esc_html__('Cache management', 'imdb'), 8, 'imdblt_options&subsection=cache', [ $imdb_ft, 'printAdminPage' ]);
			add_submenu_page( 'imdblt_options' , esc_html__('Help page', 'imdb'), esc_html__('Help', 'imdb'), 8, 'imdblt_options&subsection=help', [ $imdb_ft, 'printAdminPage'] );
			//
		}

		if (function_exists('add_action') ) {
			// scripts & css
			add_action('admin_enqueue_scripts', [ $this, 'lumiere_add_head_admin' ] );
			// buttons
			add_action('admin_print_footer_scripts', [ $this, 'lumiere_add_quicktag' ] );
			
			// add imdblt menu in toolbar menu (top wordpress menu)
			if ($imdb_admin_values['imdbwordpress_tooladminmenu'] == 1 )
				add_action('admin_bar_menu', [ $this, 'add_admin_toolbar_menu' ],70 );
		}
	}

	/**
	8.- Function external call (ie, inside a post) 
	    can come from [imdblt] and [imdbltid]
	**/

	function lumiere_external_call ($moviename="", $external="", $filmid="") {
		global $imdb_admin_values, $imdb_widget_values, $wp_query;

	/*	if (empty($moviename) && empty($filmid)) { // old way (no parameter) - old plugin compatibility purpose
			$filmid = $wp_query->post->ID;
			$imdballmeta = get_post_meta($filmid, 'imdb-movie-widget', false);
			echo "<div class='imdbincluded'>";
			include ( "inc/imdb-movie.inc.php" );
			echo "</div>";
		}
	* Unactivated 20210430
	*/
		if (!empty($moviename) && ($external == "external")) {	// call function from external (using parameter "external") 
			$imdballmeta[0] = $moviename;// especially made to be integrated (ie, inside a php code)
							// can't accept caching through ob_start

			// add head that is only for /imdblt/ URLs
			add_action('wp_head', $this->lumiere_add_head_blog('inc.movie') ,1 );
	
			echo "<div class='imdbincluded'>";
			require_once ( IMDBLTABSPATH . "inc/imdb-movie.inc.php" );
			echo "</div>";

			add_action('wp_footer', $this->lumiere_add_footer_blog('inc.movie') ,1 );
		} 
		
		if (($external == "external") && ($filmid))  {	// call function from external (using parameter "external" ) 
								// especially made to be integrated (ie, inside a php code)
								// can't accept caching through ob_start
			$imdballmeta = 'imdb-movie-widget-noname';
			$moviespecificid = $filmid;

			// add head that is only for /imdblt/ URLs
			add_action('wp_head', $this->lumiere_add_head_blog('inc.movie') ,1 );

			echo "<div class='imdbincluded'>";
			require_once ( IMDBLTABSPATH . "inc/imdb-movie.inc.php" );
			echo "</div>";

			add_action('wp_footer', $this->lumiere_add_footer_blog('inc.movie') ,1 );
		}

		ob_start(); // ob_start (cache) system to display data precisely where there're wished) -> start record

		if (!empty($moviename) && (empty($external))) {	// new way (using a parameter - imdb movie name)
			$imdballmeta = $moviename;

			// add normal $this->lumiere_add_head_blog call, without 'inc.movie' since it is inside a post and not a widget
			add_action('wp_head', $this->lumiere_add_head_blog() ,1 );

			echo "<div class='imdbincluded'>";
			require_once ( IMDBLTABSPATH . "inc/imdb-movie.inc.php" );
			echo "</div>";

			add_action('wp_footer', $this->lumiere_add_footer_blog() ,1 );

			$out1 = ob_get_contents(); //put the record into value
		} 

		if (($filmid) && (empty($external)))  {		// new way (using a parameter - imdb movie id)
			$imdballmeta = 'imdb-movie-widget-noname';
			$moviespecificid = $filmid;

			// add normal $this->lumiere_add_head_blog call, without 'inc.movie' since it is inside a post and not a widget
			add_action('wp_head', $this->lumiere_add_head_blog() ,1 );

			echo "<div class='imdbincluded'>";
			require_once ( IMDBLTABSPATH . "inc/imdb-movie.inc.php" );
			echo "</div>";

			add_action('wp_footer', $this->lumiere_add_footer_blog() ,1 );

			$out2 = ob_get_contents(); //put the record into value
		}

		ob_end_clean(); // end record
		return $out1.$out2;
	}

	/**
	9.- Add icon for Admin Drop Down Icons
	* http://planetozh.com/blog/my-projects/wordpress-admin-menu-drop-down-css/
	**/

	function ozh_imdblt_icon() {
		global $imdb_admin_values;
		return $imdb_admin_values['imdbplugindirectory']. 'pics/imdb.gif';
	}

	/**
	10.- Add admin menu to the toolbar
	**/

	function add_admin_toolbar_menu($admin_bar) {
		global $imdb_admin_values;

		$admin_bar->add_menu( array('id'=>'imdblt-menu','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/imdb.gif' width='16px' />&nbsp;&nbsp;". 'Lumière','href'  => 'admin.php?page=imdblt_options', 'meta'  => array('title' => esc_html__('Lumière Menu'), ),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-options','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-general.png' width='16px' />&nbsp;&nbsp;".esc_html__('General options'),'href'  =>'admin.php?page=imdblt_options','meta'  => array('title' => esc_html__('General options'),),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-widget-options','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-widget-inside.png' width='16px' />&nbsp;&nbsp;".esc_html__('Widget options'),'href'  =>'admin.php?page=imdblt_options&subsection=widgetoption','meta'  => array('title' => esc_html__('Widget options'),),) );
		
		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-cache-options','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-cache.png' width='16px' />&nbsp;&nbsp;".esc_html__('Cache options'),'href'  =>'admin.php?page=imdblt_options&subsection=cache','meta' => array('title' => esc_html__('Cache options'),),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-help','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-help.png' width='16px' />&nbsp;&nbsp;".esc_html__('Help'),'href' =>'admin.php?page=imdblt_options&subsection=help','meta'  => array('title' => esc_html_e('Help'),),) );

	}

	/**
	11.- Redirect the popups to a proper URL (similar to inc/.htaccess)
	**/
	function lumiere_popup_redirect() {
		// The popup is for films
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/inc/popup-search.php' ) ) {
			$query_film=preg_match_all('#film=(.*)#', $_SERVER['REQUEST_URI'], $match_query_film, PREG_UNMATCHED_AS_NULL );
			$match_query_film_film=explode("&",$match_query_film[1][0]);
			$query_mid=preg_match_all('#mid=(.*)#', $_SERVER['REQUEST_URI'], $match_query_mid, PREG_UNMATCHED_AS_NULL );
			$match_query_film_mid=explode("&",$match_query_mid[1][0]);
			$query_info=preg_match_all('#info=(.*)#', $_SERVER['REQUEST_URI'], $match_query_info, PREG_UNMATCHED_AS_NULL );
			$query_norecursive=preg_match_all('#norecursive=(.*)#', $_SERVER['REQUEST_URI'], $match_query_norecursive, PREG_UNMATCHED_AS_NULL );

			$url = (!empty($match_query_film_film[0])) ? "/imdblt/film/" . $match_query_film_film[0] . "/" : "/imdblt/film/" . $match_query_film_mid[0] . "/" ;
			wp_safe_redirect( add_query_arg( array( 'film' => $match_query_film_film[0], 'mid' => $match_query_film_mid[0],'info' => $match_query_info[1][0], 'norecursive' => $match_query_norecursive[1][0]), get_site_url(null, $url ) ) );
			exit();
		}
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/inc/popup-imdb_movie.php' ) ) {

			$query_film=preg_match_all('#film=(.*)#', $_SERVER['REQUEST_URI'], $match_query_film, PREG_UNMATCHED_AS_NULL );
			$match_query_film_film=explode("&",$match_query_film[1][0]);
			$query_mid=preg_match_all('#mid=(.*)#', $_SERVER['REQUEST_URI'], $match_query_mid, PREG_UNMATCHED_AS_NULL );
			$match_query_film_mid=explode("&",$match_query_mid[1][0]);
			$query_info=preg_match_all('#info=(.*)#', $_SERVER['REQUEST_URI'], $match_query_info, PREG_UNMATCHED_AS_NULL );
			$query_norecursive=preg_match_all('#norecursive=(.*)#', $_SERVER['REQUEST_URI'], $match_query_norecursive, PREG_UNMATCHED_AS_NULL );
			$url = (!empty($match_query_film_film[0])) ? "/imdblt/film/" . $match_query_film_film[0] . "/" : "/imdblt/film/" . $match_query_film_mid[0] . "/" ;

			wp_safe_redirect( add_query_arg( array( 'film' => $match_query_film_film[0], 'mid' => $match_query_film_mid[0],'info' => $match_query_info[1][0], 'norecursive' => $match_query_norecursive[1][0]), get_site_url(null, $url ) ) );
			exit();

		}
		// The popup is for persons
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/inc/popup-imdb_person.php' ) ) {
			$query_person_mid=preg_match('#mid=(.*)#', $_SERVER['REQUEST_URI'], $match_query_mid, PREG_UNMATCHED_AS_NULL );
			$match_query_person_mid=explode ( "&", $match_query_mid[1] );
			$query_person_info=preg_match_all('#info=(.*)#', $_SERVER['REQUEST_URI'], $match_query_info, PREG_UNMATCHED_AS_NULL );
			$match_query_person_info=explode ( "&", $match_query_info[1] );
			$query_person_film=preg_match_all('#film=(.*)&?#', $_SERVER['REQUEST_URI'], $match_query_person_film, PREG_UNMATCHED_AS_NULL );
			$url = "/imdblt/person/" . $match_query_person_mid[0] . "/" ;

	      		//wp_redirect(  add_query_arg( 'mid' => $match_query_mid[1][0], $url ) , 301 ); # one arg only
			wp_safe_redirect( add_query_arg( array( 'mid' => $match_query_person_mid[0], 'film' => $match_query_person_film[1][0], 'info' => $match_query_person_info[0]), get_site_url(null, $url ) ) );
			exit();
		}

	}

	function lumiere_popup_redirect_include() {

		// Include films popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/imdblt/film/' ) ) {
			require_once ( $imdb_admin_values['imdbplugindirectory'] . 'inc/popup-imdb_movie.php' );
		}

		// Include persons popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/imdblt/person/' ) ) {
			require_once ( $imdb_admin_values['imdbplugindirectory'] . 'inc/popup-imdb_person.php' );
		}
	}

	/**
	12.- Include highslide_download.php if string highslide=yes
	**/
	function lumiere_highslide_download_redirect() {
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options&highslide=yes' ) ) {
			require_once ( $imdb_admin_values['imdbplugindirectory'] . 'inc/highslide_download.php' );
		}
	}

	/**
	13.- Include move_template_taxonomy.php if string taxotype=*
	**/
	function lumiere_copy_template_taxo_redirect() {
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=taxo&taxotype=' ) ) {
			require_once ( $imdb_admin_values['imdbplugindirectory'] . 'inc/move_template_taxonomy.php' );
		}
	}

	// *********************
	// ********************* Automatisms & executions
	// *********************

	function lumiere_plugin_start () {
		global $imdb_configs_values, $imdb_ft, $imdb_admin_values;

		// Be sure WP is running
		if (function_exists('add_action')) {

			// redirect popups URLs to follow inc/.htaccess rules
			add_action( 'init', [ $this, 'lumiere_popup_redirect' ], 0);
			add_action( 'init', [ $this, 'lumiere_popup_redirect_include' ], 0);

			add_action( 'init', [ $this, 'lumiere_highslide_download_redirect' ], 0);
			add_action( 'init', [ $this, 'lumiere_copy_template_taxo_redirect' ], 0);

		    	// css for main blog
			add_action('wp_head', [ $this, 'lumiere_add_head_blog' ],1 );
			add_action('wp_footer', [ $this, 'lumiere_add_footer_blog' ] );

			// add new name to popups
			add_filter('pre_get_document_title', [ $this, 'change_popup_title' ]);

			// add links to popup
			add_filter('the_content', [ $this, 'lumiere_linking' ], 11);
			add_filter('the_excerpt', [ $this, 'lumiere_linking' ], 11);

		    	// delete next line if you don't want to run Lumiere Movies through comments
			add_filter('comment_text', [ $this, 'lumiere_linking' ], 11);

			// add data inside a post
			add_action('the_content', [ $this, 'lumiere_tags_transform' ], 11);
			add_action('the_content', [ $this, 'lumiere_tags_transform_id' ], 11);

			// add admin menu
			if (isset($imdb_ft)) {
				add_action('admin_menu', [ $this, 'lumiere_admin_panel' ] );
				add_action('init', [ $this, 'lumiere_add_buttons' ] );
			}

			// add taxonomies in wordpress (from functions.php)
			if ($imdb_admin_values['imdbtaxonomy'] == 1) 
				add_action( 'init', 'lumiere_create_taxonomies', 0 );

			// register widget
			add_action('plugins_loaded', 'register_imdbwidget');
		}
	}

	

} // end class

/* Function: create_imdblt_table
* Create mysql Preferences Tables
*/

/*
add_action('activate_lumiere-movies/lumiere-movies.php', 'create_imdblt_table');
function create_imdblt_table() {
	global $wpdb;
	if(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
		include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
	} elseif(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	} else {
		die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
	}
	// Create IMDbLT Table
	$charset_collate = '';
	if($wpdb->supports_collation()) {
		if(!empty($wpdb->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if(!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}
	$create_table = array();
	$create_table['imdblt'] = "CREATE TABLE $wpdb->imdblt (".
				"id int(10) NOT NULL auto_increment,".
				"category varchar(20) character set utf8 NOT NULL default '',".
				"option varchar(100) character set utf8 NOT NULL default '',".
				"value varchar(200) character set utf8 NOT NULL default '',".
				"PRIMARY KEY (imdblt_id)) $charset_collate;";
	maybe_create_table($wpdb->prepare($wpdb->imdblt), $create_table['imdblt']);
}
* To be implemented at some point
*/

?>
