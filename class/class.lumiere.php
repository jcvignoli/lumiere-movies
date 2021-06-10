<?php

// *********************
// ********************* CLASS lumiere_core
// *********************

// namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die('You can not call directly this page');
}

if (class_exists("lumiere_settings_conf")) {
	$imdb_ft = new lumiere_settings_conf();
	$imdb_admin_values = $imdb_ft->get_imdb_admin_option();
	$imdb_widget_values = $imdb_ft->get_imdb_widget_option();
	$imdb_cache_values = $imdb_ft->get_imdb_cache_option();
}

class lumiere_core {
	private $bypass; /* this value is not in use anymore, to be removed */

	/*constructor*/
	function __construct () {

	global $imdb_ft, $imdb_admin_values, $imdb_widget_values;

		// Be sure WP is running
		if (function_exists('add_action')) {

			// redirect popups URLs to follow inc/.htaccess rules
			add_action( 'init', [ $this, 'lumiere_popup_redirect' ], 0);
			add_action( 'init', [ $this, 'lumiere_popup_redirect_include' ], 0);

			// add taxonomies in wordpress (from functions.php)
			if ($imdb_admin_values['imdbtaxonomy'] == 1) {
				add_action( 'init', 'lumiere_create_taxonomies', 0 );

				// search for all imdbtaxonomy* in config array, 
				// if active write a filter to add a class to the link to the taxonomy page
				foreach ( lumiere_array_key_exists_wildcard($imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
					if ($value == 1) {
						$filter_taxonomy = str_replace('imdbtaxonomy', '', "term_links-" . $imdb_admin_values['imdburlstringtaxo'] . $key);
						add_filter( $filter_taxonomy, [ $this, 'lumiere_taxonomy_add_class_to_links'] );
					}
				}
				add_action( 'admin_init', [ $this, 'lumiere_copy_template_taxo_redirect' ], 0);
			}

			#add_action( 'init', [ $this, 'lumiere_highslide_download_redirect' ], 0);#function deactivated upon wordpress plugin team request

			add_action( 'init', [ $this, 'lumiere_gutenberg_search_redirect' ], 0);

			// Check if Gutenberg is active
			if ( function_exists( 'register_block_type' ) )
				add_action('init', [ $this, 'lumiere_register_gutenberg_blocks' ],0);

			if (is_admin()) {
				// add admin menu
				if (isset($imdb_ft)) {
					add_action('admin_menu', [ $this, 'lumiere_admin_panel' ] );
				}

				// add admin header
				add_action('admin_enqueue_scripts', [ $this, 'lumiere_add_head_admin' ] );
				// add admin tinymce button for wysiwig editor
				add_action('admin_enqueue_scripts', [ $this, 'lumiere_register_tinymce' ] );
				// add admin quicktag button for text editor
				add_action('admin_footer', [ $this, 'lumiere_register_quicktag' ], 100);
				// add footer
				add_action('admin_footer', [ $this, 'lumiere_add_footer_admin' ], 100 );
			}

		    	// head for main blog
			add_action('wp_head', [ $this, 'lumiere_add_head_blog' ], 0);
			add_action('wp_head', [ $this, 'lumiere_add_metas' ], 5);

			// add new name to popups
			add_filter('pre_get_document_title', [ $this, 'lumiere_change_popup_title' ]);

			// add links to popup
			add_filter('the_content', [ $this, 'lumiere_linking' ] );
			add_filter('the_excerpt', [ $this, 'lumiere_linking' ] );

		    	// delete next line if you don't want to run Lumiere Movies through comments
			add_filter('comment_text', [ $this, 'lumiere_linking' ] );

			// add data inside a post
			add_action('the_content', [ $this, 'lumiere_tags_transform' ] );
			add_action('the_content', [ $this, 'lumiere_tags_transform_id' ] );

			// Footer actions
			add_action('wp_footer', [ $this, 'lumiere_add_footer_blog' ] );

			// On updating plugin
			add_action( 'upgrader_process_complete', 'lumiere_on_upgrade_completed' );

			// register widget
			add_action('plugins_loaded', 'lumiere_register_widget');
		}
	}

	/**
	1.- Create inc/.htaccess upon plugin activation
	**/

	function lumiere_make_htaccess_admin(){
		lumiere_make_htaccess(); // in class/functions.php
	}

	/**
	2.- Replace <span class="lumiere_link_maker"> tags inside the posts
	**/

	##### a) Looks for what is inside tags  <span class="lumiere_link_maker"> ... </span> 
	#####    and build a popup link
	function lumiere_link_finder($correspondances){
		global $imdb_admin_values;

		$correspondances = $correspondances[0];
		preg_match('/<span class="lumiere_link_maker">(.+?)<\/span>/i', $correspondances, $link_parsed);

		// link construction

		if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup
			$link_parsed = lumiere_popup_highslide_film_link ($link_parsed) ;
		} else {						// classic popup
		    	$link_parsed = lumiere_popup_classical_film_link ($link_parsed) ;
		}

		return $link_parsed;
	}

	// Kept for compatibility purposes:  <!--imdb--> still works
	function lumiere_link_finder_oldway($correspondances){
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


	##### b) Replace <span class="lumiere_link_maker"></span> with links
	function lumiere_linking($text) {
		// replace all occurences of <span class="lumiere_link_maker">(.+?)<\/span> into internal popup
		$pattern = '/<span class="lumiere_link_maker">(.+?)<\/span>/i';
		$text = preg_replace_callback($pattern, [ $this, 'lumiere_link_finder' ] ,$text);

		// Kept for compatibility purposes:  <!--imdb--> still works
		$pattern_two = '/<!--imdb-->(.*?)<!--\/imdb-->/i';
		$text_two = preg_replace_callback($pattern_two, [ $this, 'lumiere_link_finder_oldway' ] ,$text);


		return $text_two;
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
		return preg_replace_callback($pattern, [ $this, 'parse_lumiere_tag_transform' ], $text);
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
		return preg_replace_callback($pattern, [ $this, 'parse_lumiere_tag_transform_id' ], $text);
	}

	/**
	5.-  Add tags buttons <span class="lumiere_link_maker"> to editing interfaces
	**/

	##### a) HTML part
	function lumiere_register_quicktag() {
		global $imdb_admin_values;
		wp_enqueue_script( "lumiere_quicktag_addbutton", $imdb_admin_values['imdbplugindirectory'] ."js/lumiere_admin_quicktags.js", array( 'quicktags' ));
	}

	##### b) tinymce part (wysiwyg display)

	function lumiere_register_tinymce() {
		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;

		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", [ $this, "lumiere_tinymce_addbutton" ] );
			add_filter('mce_buttons', [ $this, 'lumiere_tinymce_button_position' ] );
		}
	}

	function lumiere_tinymce_button_position($buttons) {
		array_push($buttons, "separator", "lumiere_tiny");
		return $buttons;
	}
	// Load the TinyMCE plugin
	function lumiere_tinymce_addbutton($plugin_array) {
		global $imdb_admin_values;
		$plugin_array['lumiere_tiny'] = $imdb_admin_values['imdbplugindirectory'] . 'js/lumiere_admin_tinymce_editor.js';
		return $plugin_array;
	}

	##### c) guntenberg block
	function lumiere_register_gutenberg_blocks() {
		global $imdb_admin_values;

		wp_register_script( "lumiere_gutenberg_main", 
			$imdb_admin_values['imdbplugindirectory'] . 'blocks-gutenberg/main-block.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor','wp-components','wp-i18n','wp-data' ], 
			LUMIERE_VERSION );

		wp_register_script( "lumiere_gutenberg_buttons", 
			$imdb_admin_values['imdbplugindirectory'] . 'blocks-gutenberg/buttons.js',
			[ 'wp-element', 'wp-compose','wp-components','wp-i18n','wp-data' ], 
			LUMIERE_VERSION );

		/*wp_register_script( "lumiere_gutenberg_sidebar", 
			$imdb_admin_values['imdbplugindirectory'] . 'blocks-gutenberg/sidebar.js',
			[ 'wp-blocks', 'wp-element', 'wp-plugins', 'wp-compose', 'wp-edit-post', 'wp-editor','wp-components','wp-i18n','wp-data' ], 
			filemtime( $imdb_admin_values['imdbplugindirectory'] . 'blocks-gutenberg/sidebar.js') );
		*/

		wp_register_style( "lumiere_gutenberg_main", 
			$imdb_admin_values['imdbplugindirectory'] . 'blocks-gutenberg/main-block.css',
			[ 'wp-edit-blocks' ], 
			LUMIERE_VERSION );

		// Register block script and style.
		register_block_type( 'lumiere/main', [
			'style' => 'lumiere_gutenberg_main', // Loads both on editor and frontend.
			'editor_script' => 'lumiere_gutenberg_main', // Loads only on editor.
		] );

		register_block_type( 'lumiere/buttons', [
			'editor_script' => 'lumiere_gutenberg_buttons', // Loads only on editor.
		] );

		/*register_block_type( 'lumiere/sidebar', [
			'editor_script' => 'lumiere_gutenberg_sidebar', // Loads only on editor.
		] );*/

	}

	/**
	6.- Add the stylesheet & javascript to pages head
	**/

	##### a) outside admin part
	function lumiere_add_head_blog ($bypass=NULL){
		global $imdb_admin_values;

		// Load js and css in /imdblt/ URLs or if the function is called with lumiere_add_head_blog("inc.movie")
		if ( ($bypass="inc.movie") || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRING ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/inc/' ) ) ) {

			// Highslide popup
			if ($imdb_admin_values['imdbpopup_highslide'] == 1) {
				wp_enqueue_script( "lumiere_highslide", $imdb_admin_values['imdbplugindirectory'] ."js/highslide/highslide-with-html.min.js", array(), LUMIERE_VERSION);
				wp_enqueue_script( "lumiere_highslide_options", $imdb_admin_values['imdbplugindirectory'] ."js/highslide-options.js", array(), LUMIERE_VERSION);
				// Pass variable to javascript highslide-options.js
				wp_add_inline_script( 'lumiere_highslide_options', 'const highslide_vars = ' . json_encode( array(
    					'imdb_path' => $imdb_admin_values['imdbplugindirectory'],
    					'popup_border_colour' => $imdb_admin_values['imdbpopuptheme'],
				) ) , 'before');
				wp_enqueue_style( "lumiere_highslide", $imdb_admin_values['imdbplugindirectory'] ."css/highslide.css", array(), LUMIERE_VERSION);
			}

			// Use local template lumiere.css if it exists in current theme folder
			if (file_exists (TEMPLATEPATH . "/lumiere.css") ) { // an lumiere.css exists inside theme folder, take it!
				wp_enqueue_style('imdblt_lumierecss', get_stylesheet_directory_uri() . '/lumiere.css', array(), LUMIERE_VERSION);
		 	} else {
				wp_enqueue_style('imdblt_lumierecss', $imdb_admin_values['imdbplugindirectory'] .'css/lumiere.css', array(), LUMIERE_VERSION);
		 	}

			// OceanWp template css fix
			// enqueue lumiere.css only if using oceanwp template
			# Popups
			if ( ( 0 === stripos( get_template_directory_uri(), site_url() . '/wp-content/themes/oceanwp' ) ) && ( str_contains( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRING ) ) ) {
				wp_enqueue_style('lumiere_subpages_css_oceanwpfixes', $imdb_admin_values['imdbplugindirectory'] ."css/lumiere_subpages-oceanwpfixes.css", array(), LUMIERE_VERSION);
			# Wordpress posts/pages
			} elseif ( 0 === stripos( get_template_directory_uri(), site_url() . '/wp-content/themes/oceanwp' ) ){ 
				wp_enqueue_style('lumiere_extrapagescss_oceanwpfixes', $imdb_admin_values['imdbplugindirectory'] ."css/lumiere_extrapages-oceanwpfixes.css", array(), LUMIERE_VERSION);
			} 
		}
	}

	function lumiere_add_footer_blog( $bypass=NULL ){
		global $imdb_admin_values;

// Unactivated, so the scripts can be run anywhere
// To do: add an option in admin to activate/unactivate a pass-by

		// Load js and css in /imdblt/ URLs or if the function is called with lumiere_add_footer_blog("inc.movie")
		//if ( ($bypass=="inc.movie") || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRING ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/inc/' ) ) ) {

			wp_enqueue_script( "lumiere_hide_show", $imdb_admin_values['imdbplugindirectory'] ."js/lumiere_hide_show.js", array(), LUMIERE_VERSION);

			wp_enqueue_script( "lumiere_scripts", $imdb_admin_values['imdbplugindirectory'] ."js/lumiere_scripts.js", array(), LUMIERE_VERSION);

			// Pass variable to javascript lumiere_scripts.js
			wp_add_inline_script( 'lumiere_scripts', 'const lumiere_vars = ' . json_encode( array(
				'popupLarg' => $imdb_admin_values['popupLarg'],
				'popupLong' => $imdb_admin_values['popupLong'],
				'imdb_path' => $imdb_admin_values['imdbplugindirectory'],
				'urlpopup_film' => LUMIERE_URLPOPUPSFILMS,
				'urlpopup_person' => LUMIERE_URLPOPUPSPERSON,
			) ) , 'before');
		//}
	}

	##### b) admin part
	function lumiere_add_head_admin () {
		global $imdb_admin_values;

		wp_enqueue_style('lumiere_css_admin', $imdb_admin_values['imdbplugindirectory'] . "css/lumiere-admin.css", array(), LUMIERE_VERSION);

		wp_enqueue_script('common'); // script needed for meta_boxes (in help.php)
		wp_enqueue_script('wp-lists'); // script needed for meta_boxes (in help.php)
		wp_enqueue_script('postbox'); // script needed for meta_boxes (in help.php)
		wp_enqueue_script('jquery'); // script needed by all js

		wp_enqueue_script( "lumiere_scripts_admin", $imdb_admin_values['imdbplugindirectory'] ."js/lumiere_scripts_admin.js", array(), LUMIERE_VERSION);
		// Pass variable to javascripts in admin part
		wp_add_inline_script( 'lumiere_scripts_admin', 'const lumiere_admin_vars = ' . json_encode( array(
			'imdb_path' => $imdb_admin_values['imdbplugindirectory'],
			'wordpress_path' => site_url(),
		) ) , 'before');
	}

	function lumiere_add_footer_admin () {
		global $imdb_admin_values;

		wp_enqueue_script( "lumiere_hide_show", $imdb_admin_values['imdbplugindirectory'] ."js/lumiere_hide_show.js", array(), LUMIERE_VERSION);

	}

	/**
	7.- Add the admin menu
	**/

	function lumiere_admin_panel() {
		global $imdb_ft, $imdb_admin_values;

		if (!isset($imdb_ft)) 
			return;

		if (function_exists('add_options_page') && ($imdb_admin_values['imdbwordpress_bigmenu'] == 0 ) ) {
			add_options_page('Lumière Options', '<img src="'. $imdb_admin_values['imdbplugindirectory']. 'pics/lumiere-ico13x13.png" align="absmiddle"> Lumière', 'administrator', 'imdblt_options', 'printAdminPage' );

			// third party plugin
			add_filter('ozh_adminmenu_icon_imdblt_options', [ $this, 'ozh_imdblt_icon' ] );
		}
		if (function_exists('add_submenu_page') && ($imdb_admin_values['imdbwordpress_bigmenu'] == 1 ) ) {
			// big menu for many pages for admin sidebar
			add_menu_page( 'Lumière Options', '<i>Lumière</i>' , 8, 'imdblt_options', 'printAdminPage', $imdb_admin_values['imdbplugindirectory'].'pics/lumiere-ico13x13.png', 65);
			add_submenu_page( 'imdblt_options' , esc_html__('Lumière options page', 'lumiere-movies'), esc_html__('General options', 'lumiere-movies'), 8, 'imdblt_options');
			add_submenu_page( 'imdblt_options' , esc_html__('Widget & In post options page', 'lumiere-movies'), esc_html__('Widget/In post', 'lumiere-movies'), 8, 'imdblt_options&subsection=widgetoption', 'printAdminPage' );
			add_submenu_page( 'imdblt_options',  esc_html__('Cache management options page', 'lumiere-movies'), esc_html__('Cache management', 'lumiere-movies'), 8, 'imdblt_options&subsection=cache', 'printAdminPage');
			add_submenu_page( 'imdblt_options' , esc_html__('Help page', 'lumiere-movies'), esc_html__('Help', 'lumiere-movies'), 8, 'imdblt_options&subsection=help', 'printAdminPage' );
			//
		}

		if (function_exists('add_action') ) {

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

		if (!empty($moviename) && ($external == "external")) {	// call function from external (using parameter "external")
			$imdballmeta[0] = $moviename;// especially made to be integrated (ie, inside a php code)
							// can't accept caching through ob_start

			echo "<div class='imdbincluded'>";
			require_once ( $imdb_admin_values['imdbpluginpath'] . "inc/imdb-movie.inc.php" );
			echo "</div>";

		}

		if (($external == "external") && ($filmid))  {	// call function from external (using parameter "external" )
								// especially made to be integrated (ie, inside a php code)
								// can't accept caching through ob_start
			$imdballmeta = 'imdb-movie-widget-noname';
			$moviespecificid = $filmid;

			echo "<div class='imdbincluded'>";
			require_once ( $imdb_admin_values['imdbpluginpath'] . "inc/imdb-movie.inc.php" );
			echo "</div>";

		}

		ob_start(); // ob_start (cache) system to display data precisely where there're wished) -> start record

		if (!empty($moviename) && (empty($external))) {	// new way (using a parameter - imdb movie name)
			$imdballmeta = $moviename;

			echo "<div class='imdbincluded'>";
			require_once ( $imdb_admin_values['imdbpluginpath'] . "inc/imdb-movie.inc.php" );
			echo "</div>";

			$out1 = ob_get_contents(); //put the record into value
		}

		if (($filmid) && (empty($external)))  {		// new way (using a parameter - imdb movie id)
			$imdballmeta = 'imdb-movie-widget-noname';
			$moviespecificid = $filmid;

			//removed, pointless
			// not /imdblt/ path, but needs scripts and css to work, added 'inc.movie'
			//add_action('wp_head', $this->lumiere_add_head_blog('inc.movie') ,1 );

			echo "<div class='imdbincluded'>";
			require_once ( IMDBLTABSPATH . "inc/imdb-movie.inc.php" );
			echo "</div>";

			//removed, pointless
			//add_action('wp_footer', $this->lumiere_add_footer_blog('inc.movie') ,1 );

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
		return $imdb_admin_values['imdbplugindirectory']. 'pics/lumiere-ico13x13.png';
	}

	/**
	10.- Add admin menu to the toolbar
	**/

	function add_admin_toolbar_menu($admin_bar) {
		global $imdb_admin_values;

		$admin_bar->add_menu( array('id'=>'imdblt-menu','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/lumiere-ico13x13.png' width='16' height='16' />&nbsp;&nbsp;". 'Lumière','href'  => 'admin.php?page=imdblt_options', 'meta'  => array('title' => esc_html__('Lumière Menu'), ),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-options','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-general.png' width='16px' />&nbsp;&nbsp;".esc_html__('General options'),'href'  =>'admin.php?page=imdblt_options','meta'  => array('title' => esc_html__('General options'),),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-widget-options','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-widget-inside.png' width='16px' />&nbsp;&nbsp;".esc_html__('Widget options'),'href'  =>'admin.php?page=imdblt_options&subsection=widgetoption','meta'  => array('title' => esc_html__('Widget options'),),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-cache-options','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-cache.png' width='16px' />&nbsp;&nbsp;".esc_html__('Cache options'),'href'  =>'admin.php?page=imdblt_options&subsection=cache','meta' => array('title' => esc_html__('Cache options'),),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-help','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-help.png' width='16px' />&nbsp;&nbsp;".esc_html__('Help'),'href' =>'admin.php?page=imdblt_options&subsection=help','meta'  => array('title' => esc_html_e('Help'),),) );

	}

	/**
	11.- Redirect the popups to a proper URL (goes with to inc/.htaccess)
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

			$url = (!empty($match_query_film_film[0])) ? LUMIERE_URLSTRINGFILMS . $match_query_film_film[0] . "/" : LUMIERE_URLSTRINGFILMS . $match_query_film_mid[0] . "/" ;
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
			$url = (!empty($match_query_film_film[0])) ? LUMIERE_URLSTRINGFILMS . $match_query_film_film[0] . "/" : LUMIERE_URLSTRINGFILMS . $match_query_film_mid[0] . "/" ;

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
			$url = LUMIERE_URLSTRINGPERSON . $match_query_person_mid[0] . "/" ;

	      		//wp_redirect(  add_query_arg( 'mid' => $match_query_mid[1][0], $url ) , 301 ); # one arg only
			wp_safe_redirect( add_query_arg( array( 'mid' => $match_query_person_mid[0], 'film' => $match_query_person_film[1][0], 'info' => $match_query_person_info[0]), get_site_url(null, $url ) ) );
			exit();
		}
	}

	// pages to be included when the redirection is done
	function lumiere_popup_redirect_include() {
		global $imdb_admin_values;

		// Include films popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGSEARCH ) ) {
			require_once ( $imdb_admin_values['imdbpluginpath'] . 'inc/popup-search.php' );
		}

		// Include films popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGFILMS ) ) {
			require_once ( $imdb_admin_values['imdbpluginpath'] . 'inc/popup-imdb_movie.php' );
		}

		// Include persons popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGPERSON ) ) {
			require_once ( $imdb_admin_values['imdbpluginpath'] . 'inc/popup-imdb_person.php' );
		}
	}

	/**
	12.- Change the title of the popups according to the movie's or person's data
	**/
	function lumiere_change_popup_title($title) {
		global $imdb_cache_values, $imdb_ft;

		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRING ) ){

			// Add cache dir to properly save data in real cache dir
			$imdb_ft->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;

			// Display the title if /url/films
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGFILMS ) ) {
				if ( (isset($_GET['mid'])) && (!empty($_GET['mid'])) ) {
					$movieid_sanitized = sanitize_text_field( $_GET['mid'] );
					$movie = new Imdb\Title($movieid_sanitized, $imdb_ft);
					$filmid_sanitized = esc_html($movie->title());
				} elseif ( (!isset($_GET['mid'])) && (isset($_GET['film'])) ){
					$filmid_sanitized = lumiere_name_htmlize($_GET['film']);
				}

				$title_name = isset($movieid_sanitized) ? $filmid_sanitized : sanitize_text_field($_GET['film']);
				$title = isset($title_name ) ? esc_html__('Informations about ', 'lumiere-movies') . $title_name. " - Lumi&egrave;re movies" : esc_html__('Unknown', 'lumiere-movies') . '- Lumi&egrave;re movies';

			// Display the title if /url/person
			} elseif ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGPERSON ) ){

				if ( (isset($_GET['mid'])) && (!empty($_GET['mid'])) ) {
					$mid_sanitized = sanitize_text_field($_GET['mid']);
					$person = new Imdb\Person($mid_sanitized, $imdb_ft);
					$person_name_sanitized = sanitize_text_field( $person->name() );
				}
				$title = isset($person_name_sanitized ) ? esc_html__('Informations about ', 'lumiere-movies') . $person_name_sanitized. " - Lumi&egrave;re movies" : esc_html__('Unknown', 'lumiere-movies') . '- Lumi&egrave;re movies';

			// Display the title if /url/search
			} elseif ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGSEARCH ) ){
				$title_name = isset($_GET['film']) ? esc_html($_GET['film']) : esc_html__('No query entered', 'lumiere-movies');
				$title = esc_html__('Search query for ', 'lumiere-movies') . $title_name . " - Lumi&egrave;re movies ";
			}

			return $title;
		}
	}

	/**
	13.- A Include highslide_download.php if string highslide=yes
	**/
	function lumiere_highslide_download_redirect() {
		global $imdb_admin_values;

		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options&highslide=yes' ) ) {
			require_once ( $imdb_admin_values['imdbpluginpath'] . 'inc/highslide_download.php' );
		}
	}
	/**
	13.- B Include gutenberg-search.php if string gutenberg=yes
	**/
	function lumiere_gutenberg_search_redirect() {
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/lumiere/search/' ) ) {
			require_once ( $imdb_admin_values['imdbpluginpath'] . 'inc/gutenberg-search.php' );
		}
	}

	/**
	14.- Include move_template_taxonomy.php if string taxotype=
	**/
	function lumiere_copy_template_taxo_redirect() {
		global $imdb_admin_values;

		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=taxo&taxotype=' ) ) {
			require_once ( $imdb_admin_values['imdbpluginpath'] . 'inc/move_template_taxonomy.php' );
		}
	}

	/**
	15.- Add a class to taxonomy links (constructed in imbd-movie.inc.php)
	**/
	function lumiere_taxonomy_add_class_to_links($links) {
	    return str_replace('<a href="', '<a class="linktaxonomy" href="', $links);
	}

	/**
	16.- Add new meta tags
	**/
	function lumiere_add_metas() {

		// Change the metas only for popups
		if ( ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGFILMS ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGSEARCH ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGPERSON ) ) )
{

			# ADD FAVICONS
			echo "\t\t" . '<!-- Lumiere Movies -->';
			echo "\n" . '<link rel="apple-touch-icon" sizes="180x180" href="' . IMDBLTURLPATH . 'pics/favicon/apple-touch-icon.png" />';
			echo "\n" . '<link rel="icon" type="image/png" sizes="32x32" href="' . IMDBLTURLPATH . 'pics/favicon/favicon-32x32.png" />';
			echo "\n" . '<link rel="icon" type="image/png" sizes="16x16" href="' . IMDBLTURLPATH . 'pics/favicon/favicon-16x16.png" />';
			echo "\n" . '<link rel="manifest" href="' . IMDBLTURLPATH . 'pics/favicon/site.webmanifest" />';

			# ADD CANONICAL
			// Canonical for search popup
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGSEARCH ) ) {
				$film_sanitized = ""; $film_sanitized = isset($_GET['film']) ? lumiere_name_htmlize($_GET['film']) : "";
				$my_canon = LUMIERE_URLPOPUPSSEARCH . '?film=' . $film_sanitized . '&norecursive=yes' ;
			}

			// Canonical for movies popups
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGFILMS ) ) {
				$mid_sanitized = isset($_GET['mid']) ? sanitize_text_field($_GET['mid']) : "";
				$film_sanitized = ""; $film_sanitized = isset($_GET['film']) ? lumiere_name_htmlize($_GET['film']) : "";
				$info_sanitized = ""; $info_sanitized = isset($_GET['info']) ? esc_html($_GET['info']) : "";
				$my_canon = LUMIERE_URLPOPUPSFILMS . '?film=' . $film_sanitized . '&mid=' . $mid_sanitized. '&info=' . $info_sanitized;
			}

			// Canonical for people popups
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . LUMIERE_URLSTRINGPERSON ) ) {
				$mid_sanitized = isset($_GET['mid']) ? sanitize_text_field($_GET['mid']) : "";
				$info_sanitized = isset($_GET['info']) ? esc_html($_GET['info']) : "";
				$my_canon = LUMIERE_URLPOPUPSPERSON . $mid_sanitized . '/?mid=' . $mid_sanitized . '&info=' . $info_sanitized;
			}

			echo "\n" . '<link rel="canonical" href="' . $my_canon . '" />';
			if (isset($film_sanitized))
				echo "\n" . '<meta property="article:tag" content="' . $film_sanitized . '" />';
			echo "\n\t\t" . '<!-- Lumiere Movies -->'."\n";

			remove_action('wp_head', 'rel_canonical'); # prevents Wordpress from inserting a canon tag
			remove_action('wp_head', 'wp_site_icon', 99); # prevents Wordpress from inserting favicons
		}
	}

	/**
	17.- Create cache folder
	**/
	function lumiere_create_cache() {

		/* Cache folder paths */
		$lumiere_folder_cache = WP_CONTENT_DIR . '/cache/lumiere/';
		$lumiere_folder_cache_images = WP_CONTENT_DIR . '/cache/lumiere/images';

		// We can write in wp-content/cache
		if ( wp_mkdir_p( $lumiere_folder_cache ) &&  wp_mkdir_p( $lumiere_folder_cache_images ) ) {
			chmod( $lumiere_folder_cache, 0777 );
			chmod( $lumiere_folder_cache_images, 0777 );
		// We can't write in wp-content/cache, so write in wp-content/plugins/lumiere/cache instead
		} else {
			$lumiere_folder_cache = IMDBLTABSPATH . 'cache';
			$lumiere_folder_cache_images = $lumiere_folder_cache . '/images';
			wp_mkdir_p( $lumiere_folder_cache );
			chmod( $lumiere_folder_cache, 0777 );
			wp_mkdir_p( $lumiere_folder_cache_images );
			chmod( $lumiere_folder_cache_images, 0777 );
		}
	}

	/**
	18.- Run on plugin update
	**/
	function lumiere_on_upgrade_completed( $upgrader_object, $options ) {

		/* Prevent wrong user to activate the plugin */
		if ( ! current_user_can( 'activate_plugins' ) )
			 return;

		// The path to plugin's main file
		$plugin_version = plugin_basename( __FILE__ );

		// If an update has taken place and the updated type is plugins and the plugins element exists
		if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {

			// Iterate through the plugins being updated and check if ours is there
			foreach( $options['plugins'] as $plugin ) {

				// If it is Lumière!, update
				if( $plugin == $plugin_version ) {

					/* Create/update htaccess file */
					$this->lumiere_make_htaccess_admin();

					/* Refresh rewrite rules */
					//flush_rewrite_rules(); # don't need anymore, it is executed in init

					/* update options, not needed, done in config.php automatically
					$all_lumiere_options[] = get_option($imdb_ft->imdbAdminOptionsName);
					$all_lumiere_options[] = get_option($imdb_ft->imdbWidgetOptionsName);
					$all_lumiere_options[] = get_option($imdb_ft->imdbCacheOptionsName);
					foreach ($all_lumiere_options as $each_lumiere_option){
						foreach ($each_lumiere_option as $key => $option){
							if(!isset($key)){
								$each_lumiere_option[$key] = $option;
								add_option($each_lumiere_option, $imdbOptions[$key]);
							}
						}
					}*/
				}
			}
		}
	}

	/**
	19.- Run on plugin activation
	**/
	function lumiere_on_activation() {
		/* debug
		ob_start(); */

		/* Prevent wrong user to activate the plugin */
		if ( ! current_user_can( 'activate_plugins' ) )
			 return;

		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "activate-plugin_{$plugin}" );

		/* Start the class */
		if (is_admin()) { // Prevents activation bug with Fatal Error: Table ‘actionscheduler_actions’ doesn’t exist
			$this->lumiere_make_htaccess_admin();
			$this->lumiere_create_cache();
		}

		/* Refresh rewrite rules */
		flush_rewrite_rules();

		/* debug
		trigger_error(ob_get_contents(),E_USER_ERROR);*/
	}

} // end class


?>