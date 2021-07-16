<?php

// *********************
// ********************* CLASS lumiere_core
// *********************

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
	wp_die('You can not call directly this page');

class Core {

	/* Options vars
	 * 
	 */
	private $imdb_admin_values, $imdb_widget_values,$imdb_cache_values;

	/* \Lumiere\Settings class
	 * 
	 */
	private $settingsclass;

	/*constructor*/
	function __construct () {

		global $config, $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

		$config = new \Lumiere\Settings();
		$this->settingsclass = $config;
		$imdb_admin_values = $config->get_imdb_admin_option();
		$imdb_widget_values = $config->get_imdb_widget_option();
		$imdb_cache_values = $config->get_imdb_cache_option();
		$this->imdb_admin_values = $config->get_imdb_admin_option();
		$this->imdb_widget_values = $config->get_imdb_widget_option();
		$this->imdb_cache_values = $config->get_imdb_cache_option();

		// Start Utils class
		$utils = new \Lumiere\Utils();

		// Be sure WP is running
		if (function_exists('add_action')) {

			// redirect popups URLs
			add_action( 'init', [ $this, 'lumiere_popup_redirect' ], 0);
			add_action( 'init', [ $this, 'lumiere_popup_redirect_include' ], 0);

			// add taxonomies in wordpress (from functions.php)
			if ( (isset($imdb_admin_values['imdbtaxonomy'])) && ($imdb_admin_values['imdbtaxonomy'] == 1) ) {

				add_action( 'init', [$this, 'lumiere_create_taxonomies' ], 0 );

				// search for all imdbtaxonomy* in config array, 
				// if active write a filter to add a class to the link to the taxonomy page
				foreach ( lumiere_array_key_exists_wildcard($imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
					if ($value == 1) {
						$filter_taxonomy = str_replace('imdbtaxonomy', '', "term_links-" . $imdb_admin_values['imdburlstringtaxo'] . $key);
						add_filter( $filter_taxonomy, [ $this, 'lumiere_taxonomy_add_class_to_links'] );
					}
				}

				// redirect calls to move_template_taxonomy.php
				add_filter( 'admin_init', function( $template ) {
					if ( isset( $_GET['taxotype'] ) ) {
						require( plugin_dir_path( __DIR__ ) . \Lumiere\Settings::move_template_taxonomy_page );

					}
				} );

			}

			/* ## Highslide download library, function deactivated upon wordpress plugin team request
			add_filter( 'init', function( $template ) {
				if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options&highslide=yes' ) )
					require_once ( plugin_dir_path( __DIR__ ) . \Lumiere\Settings::highslide_download_page );

			} );*/

			add_filter( 'init', function( $template ) {
				if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . \Lumiere\Settings::gutenberg_search_url ) )
					require_once ( plugin_dir_path( __DIR__ ) . \Lumiere\Settings::gutenberg_search_page );

			} );

	
			add_action('admin_init', [ $this, 'lumiere_register_gutenberg_blocks' ],0);

			// add admin menu
			if (isset($config)) 
				add_action('admin_menu', [ $this, 'lumiere_admin_panel' ] );

			// add admin header
			add_action('admin_enqueue_scripts', [ $this, 'lumiere_add_head_admin' ] );

			// add admin tinymce button for wysiwig editor
			add_action('admin_enqueue_scripts', [ $this, 'lumiere_register_tinymce' ] );

			// add admin quicktag button for text editor
			add_action('admin_footer', [ $this, 'lumiere_register_quicktag' ], 100);

			// add footer
			add_action('admin_footer', [ $this, 'lumiere_add_footer_admin' ], 100 );

		    	// head for frontpage blog
			add_action('wp_head', [ $this, 'lumiere_add_head_blog' ], 0);
			add_action('wp_head', [ $this, 'lumiere_add_metas' ], 5);

			// add new title to popups
			add_filter('pre_get_document_title', [ $this, 'lumiere_change_popup_title' ]);

			if  (! is_admin() ) { 	// Run the transformation of links to popups
							// Do not execute for admin interface
							// -> Avoids the execution in gutenberg that brings json error on updating posts
				// add links to popup
				add_filter('the_content', [ $this, 'lumiere_linking' ] );
				add_filter('the_excerpt', [ $this, 'lumiere_linking' ] );

			    	// delete next line if you don't want to run Lumiere Movies through comments
				add_filter('comment_text', [ $this, 'lumiere_linking' ] );

			}

			// Footer actions
			add_action('wp_footer', [ $this, 'lumiere_add_footer_blog' ] );

			// On updating lumiere plugin
			add_action( 'upgrader_process_complete', [$this, 'lumiere_on_lumiere_upgrade_completed' ], 10, 2 );

			// Add cron schedules
			add_action('lumiere_cron_hook', [$this, 'lumiere_cron_exec_once'], 0);
		}
	}

	/**
	1.- Do the add_actions and add filters
	**/
	/*
	function lumiere_run_actions_filters(){

		// insert here the filters and actions
	}*/

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

		if ($imdb_admin_values['imdbpopup_highslide'] == 1) { 	// highslide popup

			$link_parsed = $this->lumiere_popup_highslide_film_link ($link_parsed) ;

		} else {							// classic popup

		    	$link_parsed = $this->lumiere_popup_classical_film_link ($link_parsed) ;

		}

		return $link_parsed;
	}

	// Kept for compatibility purposes:  <!--imdb--> still works
	function lumiere_link_finder_oldway($correspondances){
		global $imdb_admin_values;

		$correspondances = $correspondances[0];
		preg_match("/<!--imdb-->(.*?)<!--\/imdb-->/i", $correspondances, $link_parsed);

		// link construction

		if ($imdb_admin_values['imdbpopup_highslide'] == 1) { 	// highslide popup

			$link_parsed = $this->lumiere_popup_highslide_film_link ($link_parsed) ;

		} else {							// classic popup

		    	$link_parsed = $this->lumiere_popup_classical_film_link ($link_parsed) ;

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
	3.-  Add tags buttons <span class="lumiere_link_maker"> to editing interfaces
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
			$this->settingsclass->lumiere_version );

		wp_register_script( "lumiere_gutenberg_buttons", 
			$imdb_admin_values['imdbplugindirectory'] . 'blocks-gutenberg/buttons.js',
			[ 'wp-element', 'wp-compose','wp-components','wp-i18n','wp-data' ], 
			$this->settingsclass->lumiere_version );

		/*wp_register_script( "lumiere_gutenberg_sidebar", 
			$imdb_admin_values['imdbplugindirectory'] . 'blocks-gutenberg/sidebar.js',
			[ 'wp-blocks', 'wp-element', 'wp-plugins', 'wp-compose', 'wp-edit-post', 'wp-editor','wp-components','wp-i18n','wp-data' ], 
			filemtime( $imdb_admin_values['imdbplugindirectory'] . 'blocks-gutenberg/sidebar.js') );
		*/

		wp_register_style( "lumiere_gutenberg_main", 
			$imdb_admin_values['imdbplugindirectory'] . 'blocks-gutenberg/main-block.css',
			array('wp-edit-blocks'), 
			$this->settingsclass->lumiere_version );

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
	4.- Add the stylesheet & javascript to pages head
	**/

	##### a) outside admin part
	function lumiere_add_head_blog (){
		global $imdb_admin_values;

		// Load js and css in /imdblt/, inc/, LUMIERE_URLSTRING URLs
		// Dunno why removing $bypass condition prevents to load below assets
		if ( ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringfilms ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/inc/' ) ) || ($bypass="inc.movie") ) {

			// Highslide popup
			if ($imdb_admin_values['imdbpopup_highslide'] == 1) {
				wp_enqueue_script( "lumiere_highslide", $imdb_admin_values['imdbplugindirectory'] ."js/highslide/highslide-with-html.min.js", array(), $this->settingsclass->lumiere_version);
				wp_enqueue_script( "lumiere_highslide_options", $imdb_admin_values['imdbplugindirectory'] ."js/highslide-options.js", array(), $this->settingsclass->lumiere_version);
				// Pass variable to javascript highslide-options.js
				wp_add_inline_script( 'lumiere_highslide_options', 'const highslide_vars = ' . json_encode( array(
    					'imdb_path' => $imdb_admin_values['imdbplugindirectory'],
    					'popup_border_colour' => $imdb_admin_values['imdbpopuptheme'],
				) ) , 'before');
				wp_enqueue_style( "lumiere_highslide", $imdb_admin_values['imdbplugindirectory'] ."css/highslide.css", array(), $this->settingsclass->lumiere_version);
			}

			// Use local template lumiere.css if it exists in current theme folder
			if (file_exists (TEMPLATEPATH . "/lumiere.css") ) { // an lumiere.css exists inside theme folder, take it!
				wp_enqueue_style('lumiere_main', get_stylesheet_directory_uri() . '/lumiere.css', array(), $this->settingsclass->lumiere_version);
		 	} else {
				wp_enqueue_style('lumiere_main', $imdb_admin_values['imdbplugindirectory'] .'css/lumiere.css', array(), $this->settingsclass->lumiere_version);
		 	}

			// OceanWp template css fix
			// enqueue lumiere.css only if using oceanwp template
			# Popups
			if ( ( 0 === stripos( get_template_directory_uri(), site_url() . '/wp-content/themes/oceanwp' ) ) && ( str_contains( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstring ) ) ) {
				wp_enqueue_style('lumiere_subpages_css_oceanwpfixes', $imdb_admin_values['imdbplugindirectory'] ."css/lumiere_subpages-oceanwpfixes.css", array(), $this->settingsclass->lumiere_version);
			# Wordpress posts/pages
			} elseif ( 0 === stripos( get_template_directory_uri(), site_url() . '/wp-content/themes/oceanwp' ) ){ 
				wp_enqueue_style('lumiere_extrapagescss_oceanwpfixes', $imdb_admin_values['imdbplugindirectory'] ."css/lumiere_extrapages-oceanwpfixes.css", array(), $this->settingsclass->lumiere_version);
			} 
		}
	}

	/**
	5.- Add the stylesheet & javascript to pages footer
	**/
	function lumiere_add_footer_blog(){
		global $imdb_admin_values;

		// Limitation unactivated, so the scripts can be run anywhere
		// To do: add an option in admin to activate/unactivate a pass-by

		// Load js and css in /imdblt/ URLs or if the function is called with lumiere_add_footer_blog("inc.movie")
		//if ( ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstring ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/inc/' ) ) ) {

			wp_enqueue_script( "lumiere_hide_show", $imdb_admin_values['imdbplugindirectory'] ."js/lumiere_hide_show.js", array('jquery'), $this->settingsclass->lumiere_version);

			wp_enqueue_script( "lumiere_scripts", $imdb_admin_values['imdbplugindirectory'] ."js/lumiere_scripts.js", array('jquery'), $this->settingsclass->lumiere_version);

			// Pass variable to javascript lumiere_scripts.js
			wp_add_inline_script( 'lumiere_scripts', 'const lumiere_vars = ' . json_encode( array(
				'popupLarg' => $imdb_admin_values['popupLarg'],
				'popupLong' => $imdb_admin_values['popupLong'],
				'imdb_path' => $imdb_admin_values['imdbplugindirectory'],
				'urlpopup_film' => $this->settingsclass->lumiere_urlpopupsfilms,
				'urlpopup_person' => $this->settingsclass->lumiere_urlpopupsperson,
			) ) , 'before');
		//}
	}

	##### b) admin part
	function lumiere_add_head_admin () {
		global $imdb_admin_values;

		wp_enqueue_style('lumiere_css_admin', $imdb_admin_values['imdbplugindirectory'] . "css/lumiere-admin.css", array(), $this->settingsclass->lumiere_version);

		wp_enqueue_script('common'); // script needed for meta_boxes (in help.php)
		wp_enqueue_script('wp-lists'); // script needed for meta_boxes (in help.php)
		wp_enqueue_script('postbox'); // script needed for meta_boxes (in help.php)

		wp_enqueue_script( "lumiere_scripts_admin", $imdb_admin_values['imdbplugindirectory'] ."js/lumiere_scripts_admin.js", array('jquery'), $this->settingsclass->lumiere_version);
		// Pass variable to javascripts in admin part
		wp_add_inline_script( 'lumiere_scripts_admin', 'const lumiere_admin_vars = ' . json_encode( array(
			'imdb_path' => $imdb_admin_values['imdbplugindirectory'],
			'wordpress_path' => site_url(),
			'wordpress_admin_path' => admin_url(),
			'gutenberg_search_url_string' => \Lumiere\Settings::gutenberg_search_url_string,
			'gutenberg_search_url' => \Lumiere\Settings::gutenberg_search_url,
		) ) , 'before');

		// When on wordpress plugins.php admin page, show a confirmation dialogue if value imdbkeepsettings is set on delete Lumière! options
		if ( ( (!isset($imdb_admin_values['imdbkeepsettings'])) || ( $imdb_admin_values['imdbkeepsettings'] == false ) ) && ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/plugins.php' )) )
			wp_enqueue_script('lumiere_deactivation_plugin_message', $imdb_admin_values['imdbplugindirectory'] . 'js/lumiere_admin_deactivation_msg.js', array('jquery') );

	}

	function lumiere_add_footer_admin () {
		global $imdb_admin_values;

		wp_enqueue_script( "lumiere_hide_show", $imdb_admin_values['imdbplugindirectory'] ."js/lumiere_hide_show.js", array('jquery'), $this->settingsclass->lumiere_version);

	}

	/**
	6.- Add the admin menu
	**/

	function lumiere_admin_panel() {
		global $config, $imdb_admin_values;

		if (!isset($config)) 
			return;

		if (function_exists('add_options_page') && ($imdb_admin_values['imdbwordpress_bigmenu'] == 0 ) ) {
			add_options_page('Lumière Options', '<img src="'. $imdb_admin_values['imdbplugindirectory']. 'pics/lumiere-ico13x13.png" align="absmiddle"> Lumière', 'administrator', 'imdblt_options', 'lumiere_admin_pages' );

			// third party plugin
			add_filter('ozh_adminmenu_icon_imdblt_options', [ $this, 'ozh_imdblt_icon' ] );
		}
		if (function_exists('add_submenu_page') && ($imdb_admin_values['imdbwordpress_bigmenu'] == 1 ) ) {
			// big menu for many pages for admin sidebar
			add_menu_page( 'Lumière Options', '<i>Lumière</i>' , 'administrator', 'imdblt_options', 'lumiere_admin_pages', $imdb_admin_values['imdbplugindirectory'].'pics/lumiere-ico13x13.png', 65);
			add_submenu_page( 'imdblt_options' , esc_html__('Lumière options page', 'lumiere-movies'), esc_html__('General options', 'lumiere-movies'), 'administrator', 'imdblt_options');
			add_submenu_page( 'imdblt_options' , esc_html__('Data management', 'lumiere-movies'), esc_html__('Data management', 'lumiere-movies'), 'administrator', 'imdblt_options&subsection=dataoption', 'lumiere_admin_pages' );
			add_submenu_page( 'imdblt_options',  esc_html__('Cache management options page', 'lumiere-movies'), esc_html__('Cache management', 'lumiere-movies'), 'administrator', 'imdblt_options&subsection=cache', 'lumiere_admin_pages');
			add_submenu_page( 'imdblt_options' , esc_html__('Help page', 'lumiere-movies'), esc_html__('Help', 'lumiere-movies'), 'administrator', 'imdblt_options&subsection=help', 'lumiere_admin_pages' );
			//
		}

		if (function_exists('add_action') ) {

			// add imdblt menu in toolbar menu (top wordpress menu)
			if ($imdb_admin_values['imdbwordpress_tooladminmenu'] == 1 )
				add_action('admin_bar_menu', [ $this, 'add_admin_toolbar_menu' ],70 );
		}
	}


	/**
	7.- Add icon for Admin Drop Down Icons
	* http://planetozh.com/blog/my-projects/wordpress-admin-menu-drop-down-css/
	**/

	function ozh_imdblt_icon() {
		global $imdb_admin_values;
		return $imdb_admin_values['imdbplugindirectory']. 'pics/lumiere-ico13x13.png';
	}

	/**
	8.- Add admin menu to the toolbar
	**/

	function add_admin_toolbar_menu($admin_bar) {
		global $imdb_admin_values;

		$admin_bar->add_menu( array('id'=>'imdblt-menu','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/lumiere-ico13x13.png' width='16' height='16' />&nbsp;&nbsp;". 'Lumière','href'  => 'admin.php?page=imdblt_options', 'meta'  => array('title' => esc_html__('Lumière Menu'), ),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-options','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-general.png' width='16px' />&nbsp;&nbsp;".esc_html__('General options'),'href'  =>'admin.php?page=imdblt_options','meta'  => array('title' => esc_html__('General options'),),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-widget-options','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-widget-inside.png' width='16px' />&nbsp;&nbsp;".esc_html__('Data options'),'href'  =>'admin.php?page=imdblt_options&subsection=dataoption','meta'  => array('title' => esc_html__('Data options'),),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-cache-options','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-cache.png' width='16px' />&nbsp;&nbsp;".esc_html__('Cache options'),'href'  =>'admin.php?page=imdblt_options&subsection=cache','meta' => array('title' => esc_html__('Cache options'),),) );

		$admin_bar->add_menu( array('parent' => 'imdblt-menu','id' => 'imdblt-menu-help','title' => "<img src='".$imdb_admin_values['imdbplugindirectory']."pics/admin-help.png' width='16px' />&nbsp;&nbsp;".esc_html__('Help'),'href' =>'admin.php?page=imdblt_options&subsection=help','meta'  => array('title' => esc_html_e('Help'),),) );

	}

	/**
	9.- Redirect the popups to a proper URL
	**/
	function lumiere_popup_redirect() {

		// The popup is for films
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/' . \Lumiere\Settings::popup_search_url ) ) {

			$query_film=preg_match_all('#film=(.*)#', $_SERVER['REQUEST_URI'], $match_query_film, PREG_UNMATCHED_AS_NULL );
			$match_query_film_film=explode("&",$match_query_film[1][0]);
			$query_mid=preg_match_all('#mid=(.*)#', $_SERVER['REQUEST_URI'], $match_query_mid, PREG_UNMATCHED_AS_NULL );
			$match_query_film_mid=explode("&",$match_query_mid[1][0]);
			$query_info=preg_match_all('#info=(.*)#', $_SERVER['REQUEST_URI'], $match_query_info, PREG_UNMATCHED_AS_NULL );
			$query_norecursive=preg_match_all('#norecursive=(.*)#', $_SERVER['REQUEST_URI'], $match_query_norecursive, PREG_UNMATCHED_AS_NULL );

			$url = (!empty($match_query_film_film[0])) ? $this->settingsclass->lumiere_urlstringfilms . $match_query_film_film[0] . "/" : $this->settingsclass->lumiere_urlstringfilms . $match_query_film_mid[0] . "/" ;
			wp_safe_redirect( add_query_arg( array( 'film' => $match_query_film_film[0], 'mid' => $match_query_film_mid[0],'info' => $match_query_info[1][0], 'norecursive' => $match_query_norecursive[1][0]), get_site_url(null, $url ) ) );
			exit();
		}
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/' . \Lumiere\Settings::popup_movie_url ) ) {

			$query_film=preg_match_all('#film=(.*)#', $_SERVER['REQUEST_URI'], $match_query_film, PREG_UNMATCHED_AS_NULL );
			$match_query_film_film=explode("&",$match_query_film[1][0]);
			$query_mid=preg_match_all('#mid=(.*)#', $_SERVER['REQUEST_URI'], $match_query_mid, PREG_UNMATCHED_AS_NULL );
			$match_query_film_mid=explode("&",$match_query_mid[1][0]);
			$query_info=preg_match_all('#info=(.*)#', $_SERVER['REQUEST_URI'], $match_query_info, PREG_UNMATCHED_AS_NULL );
			$query_norecursive=preg_match_all('#norecursive=(.*)#', $_SERVER['REQUEST_URI'], $match_query_norecursive, PREG_UNMATCHED_AS_NULL );
			$url = (!empty($match_query_film_film[0])) ? $this->settingsclass->lumiere_urlstringfilms . $match_query_film_film[0] . "/" : $this->settingsclass->lumiere_urlstringfilms . $match_query_film_mid[0] . "/" ;

			wp_safe_redirect( add_query_arg( array( 'film' => $match_query_film_film[0], 'mid' => $match_query_film_mid[0],'info' => $match_query_info[1][0], 'norecursive' => $match_query_norecursive[1][0]), get_site_url(null, $url ) ) );
			exit();

		}
		// The popup is for persons
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-content/plugins/lumiere-movies/' . \Lumiere\Settings::popup_person_url ) ) {
			$query_person_mid=preg_match('#mid=(.*)#', $_SERVER['REQUEST_URI'], $match_query_mid, PREG_UNMATCHED_AS_NULL );
			$match_query_person_mid=explode ( "&", $match_query_mid[1] );
			$query_person_info=preg_match_all('#info=(.*)#', $_SERVER['REQUEST_URI'], $match_query_info, PREG_UNMATCHED_AS_NULL );
			$match_query_person_info=explode ( "&", $match_query_info[1] );
			$query_person_film=preg_match_all('#film=(.*)&?#', $_SERVER['REQUEST_URI'], $match_query_person_film, PREG_UNMATCHED_AS_NULL );
			$url = $this->settingsclass->lumiere_urlstringperson . $match_query_person_mid[0] . "/" ;

	      		//wp_redirect(  add_query_arg( 'mid' => $match_query_mid[1][0], $url ) , 301 ); # one arg only
			wp_safe_redirect( add_query_arg( array( 'mid' => $match_query_person_mid[0], 'film' => $match_query_person_film[1][0], 'info' => $match_query_person_info[0]), get_site_url(null, $url ) ) );
			exit();
		}
	}

	// pages to be included when the redirection is done
	function lumiere_popup_redirect_include() {
		global $imdb_admin_values;

		// Include films popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringsearch ) )
			require_once ( $imdb_admin_values['imdbpluginpath'] . \Lumiere\Settings::popup_search_url );


		// Include films popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringfilms ) )
			require_once ( $imdb_admin_values['imdbpluginpath'] . \Lumiere\Settings::popup_movie_url );


		// Include persons popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringperson ) )
			require_once ( $imdb_admin_values['imdbpluginpath'] . \Lumiere\Settings::popup_person_url );

	}

	/**
	10.- Change the title of the popups according to the movie's or person's data
	**/
	function lumiere_change_popup_title($title) {
		global $imdb_cache_values, $config;

		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstring ) ){

			// Add cache dir to properly save data in real cache dir
			$config->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;

			// Display the title if /url/films
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringfilms ) ) {
				if ( (isset($_GET['mid'])) && (!empty($_GET['mid'])) ) {
					$movieid_sanitized = sanitize_text_field( $_GET['mid'] );
					$movie = new \Imdb\Title($movieid_sanitized, $config);
					$filmid_sanitized = esc_html($movie->title());
				} elseif ( (!isset($_GET['mid'])) && (isset($_GET['film'])) ){
					$filmid_sanitized = lumiere_name_htmlize($_GET['film']);
				}

				$title_name = isset($movieid_sanitized) ? $filmid_sanitized : sanitize_text_field($_GET['film']);
				$title = isset($title_name ) ? esc_html__('Informations about ', 'lumiere-movies') . $title_name. " - Lumi&egrave;re movies" : esc_html__('Unknown', 'lumiere-movies') . '- Lumi&egrave;re movies';

			// Display the title if /url/person
			} elseif ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringperson ) ){

				if ( (isset($_GET['mid'])) && (!empty($_GET['mid'])) ) {
					$mid_sanitized = sanitize_text_field($_GET['mid']);
					$person = new \Imdb\Person($mid_sanitized, $config);
					$person_name_sanitized = sanitize_text_field( $person->name() );
				}
				$title = isset($person_name_sanitized ) ? esc_html__('Informations about ', 'lumiere-movies') . $person_name_sanitized. " - Lumi&egrave;re movies" : esc_html__('Unknown', 'lumiere-movies') . '- Lumi&egrave;re movies';

			// Display the title if /url/search
			} elseif ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringsearch ) ){
				$title_name = isset($_GET['film']) ? esc_html($_GET['film']) : esc_html__('No query entered', 'lumiere-movies');
				$title = esc_html__('Search query for ', 'lumiere-movies') . $title_name . " - Lumi&egrave;re movies ";
			}

			return $title;
		}

		// Change the title for the query search popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . \Lumiere\Settings::gutenberg_search_url ) )
			return esc_html__('Lumiere Query Interface', 'lumiere-movies');
	}

	/** # 2021 07 04 function obsolete
	11.- 	A Include highslide_download.php if string highslide=yes
	**/
	/*function lumiere_highslide_download_redirect() {
		global $imdb_admin_values;

		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options&highslide=yes' ) ) {
			require_once ( $imdb_admin_values['imdbpluginpath'] . \Lumiere\Settings::highslide_download_page );
		}
	}*/

	/** # 2021 07 04 function obsolete
	12.-	- B Include gutenberg-search.php if string gutenberg=yes
	**/
	/*function lumiere_gutenberg_search_redirect() {
		global $imdb_admin_values;

		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . \Lumiere\Settings::gutenberg_search_url ) )
			require_once ( $imdb_admin_values['imdbpluginpath'] . \Lumiere\Settings::move_template_taxonomy_page );

	}*/

	/** # 2021 07 04 function obsolete
	13.	- C Include move_template_taxonomy.php if string taxotype=
	**/
	/*function lumiere_copy_template_taxo_redirect() {
		global $imdb_admin_values;

		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=imdblt_options&subsection=dataoption&widgetoption=taxo&taxotype=' ) )
			require_once ( $imdb_admin_values['imdbpluginpath'] . \Lumiere\Settings::move_template_taxonomy );

	}*/

	/**
	14.- Add a class to taxonomy links (constructed in class.movie.php)
	**/
	function lumiere_taxonomy_add_class_to_links($links) {

	    return str_replace('<a href="', '<a class="linktaxonomy" href="', $links);

	}

	/**
	15.- Add new meta tags in popups <head>
	**/
	function lumiere_add_metas() {

		// Change the metas only for popups
		if ( ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringfilms ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringsearch ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringperson ) ) )
{

			# ADD FAVICONS
			echo "\t\t" . '<!-- Lumiere Movies -->';
			echo "\n" . '<link rel="apple-touch-icon" sizes="180x180" href="' . plugin_dir_url( __DIR__ ) . 'pics/favicon/apple-touch-icon.png" />';
			echo "\n" . '<link rel="icon" type="image/png" sizes="32x32" href="' . plugin_dir_url( __DIR__ ) . 'pics/favicon/favicon-32x32.png" />';
			echo "\n" . '<link rel="icon" type="image/png" sizes="16x16" href="' . plugin_dir_url( __DIR__ ) . 'pics/favicon/favicon-16x16.png" />';
			echo "\n" . '<link rel="manifest" href="' . plugin_dir_url( __DIR__ ) . 'pics/favicon/site.webmanifest" />';

			# ADD CANONICAL
			// Canonical for search popup
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringsearch ) ) {
				$film_sanitized = ""; $film_sanitized = isset($_GET['film']) ? lumiere_name_htmlize($_GET['film']) : "";
				$my_canon = $this->settingsclass->lumiere_urlpopupssearch . '?film=' . $film_sanitized . '&norecursive=yes' ;
			}

			// Canonical for movies popups
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringfilms ) ) {
				$mid_sanitized = isset($_GET['mid']) ? sanitize_text_field($_GET['mid']) : "";
				$film_sanitized = ""; $film_sanitized = isset($_GET['film']) ? lumiere_name_htmlize($_GET['film']) : "";
				$info_sanitized = ""; $info_sanitized = isset($_GET['info']) ? esc_html($_GET['info']) : "";
				$my_canon = $this->settingsclass->lumiere_urlpopupsfilms . '?film=' . $film_sanitized . '&mid=' . $mid_sanitized. '&info=' . $info_sanitized;
			}

			// Canonical for people popups
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settingsclass->lumiere_urlstringperson ) ) {
				$mid_sanitized = isset($_GET['mid']) ? sanitize_text_field($_GET['mid']) : "";
				$info_sanitized = isset($_GET['info']) ? esc_html($_GET['info']) : "";
				$my_canon = $this->settingsclass->lumiere_urlpopupsperson . $mid_sanitized . '/?mid=' . $mid_sanitized . '&info=' . $info_sanitized;
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
	16.- Create cache folder
	**/
	function lumiere_create_cache() {
		global $config, $imdb_cache_values;
		
		/* Cache folder paths */
		$lumiere_folder_cache = WP_CONTENT_DIR . '/cache/lumiere/';
		$lumiere_folder_cache_images = WP_CONTENT_DIR . '/cache/lumiere/images';

		// Cache folders do not exist
		if (!is_dir($lumiere_folder_cache_images)) {

			// We can write in wp-content/cache
			if ( wp_mkdir_p( $lumiere_folder_cache ) &&  wp_mkdir_p( $lumiere_folder_cache_images ) ) {
				chmod( $lumiere_folder_cache, 0777 );
				chmod( $lumiere_folder_cache_images, 0777 );
			// We can't write in wp-content/cache, so write in wp-content/plugins/lumiere/cache instead
			} else {
				$lumiere_folder_cache = plugin_dir_path( __DIR__ ) . 'cache';
				$lumiere_folder_cache_images = $lumiere_folder_cache . '/images';
				wp_mkdir_p( $lumiere_folder_cache );
				chmod( $lumiere_folder_cache, 0777 );
				wp_mkdir_p( $lumiere_folder_cache_images );
				chmod( $lumiere_folder_cache_images, 0777 );

				# Save the new option for the cache path
				$imdb_cache_values['imdbcachedir'] = $lumiere_folder_cache;
				update_option($config->imdbCacheOptionsName, $imdb_cache_values['imdbcachedir']);
			}
		}

	}

	/**
	17.- Run on lumiere update
	**/
	function lumiere_on_lumiere_upgrade_completed( $upgrader_object, $options ) {

		// The path to plugin's main file
		$plugin_version = plugin_basename( __FILE__ );

		// If an update has taken place and the updated type is plugins and the plugins element exists
		if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {

			/* write here what we want to do for any plugin */

			// Iterate through the plugins being updated and check if ours is there
			foreach( $options['plugins'] as $plugin ) {

				// If it is Lumière!, update
				if( $plugin == $plugin_version ) {

					/* write here what we want to do for Lumière */

					// Call the class to update options
					require_once __DIR__ . '/class.update-options.php';
					$start_update_options = new \Lumiere\UpdateOptions();

				}
			}
		}
	}

	/**
	18.- Run on plugin activation
	**/
	function lumiere_on_activation() {
		/* debug
		ob_start(); */

		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "activate-plugin_{$plugin}" );

		/* Actions from the class */
		$this->lumiere_create_cache();

		/* Set up the WP Cron */
		if (! wp_next_scheduled ( 'lumiere_cron_hook' )) {

			// Cron to run once, in 10 minutes
			wp_schedule_single_event( time() + 600, 'lumiere_cron_hook' );

			// Run week call
			//wp_schedule_event(time(), 'weekly', 'lumiere_cron_hook');
		}

		/* Refresh rewrite rules */
		flush_rewrite_rules();

		/* debug
		trigger_error(ob_get_contents(),E_USER_ERROR);*/
	}

	/**
	19.- Run on plugin deactivation
	**/
	function lumiere_on_deactivation() {

		global $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

		/****** Below actions are executed for everybody */

		// Remove WP Cron shoud it exists
		$timestamp = wp_next_scheduled( 'lumiere_cron_hook' );
		wp_unschedule_event( $timestamp, 'lumiere_cron_hook' );

		// Keep the settings if selected so
		if ( (isset($imdb_admin_values['imdbkeepsettings'])) && ( $imdb_admin_values['imdbkeepsettings'] == true ) ) {
			return;
		}

		/****** Below actions are not executed if the user selected to keep their settings */

		// search for all imdbtaxonomy* in config array, 
		// if a taxonomy is found, let's get related terms and delete them
		foreach ( lumiere_array_key_exists_wildcard($imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
			$filter_taxonomy = str_replace('imdbtaxonomy', '', $imdb_admin_values['imdburlstringtaxo']  . $key );

			# get all terms, even if empty
			$terms = get_terms( array(
				'taxonomy' => $filter_taxonomy,
				'hide_empty' => false
			) );

			# Delete taxonomy terms and unregister taxonomy
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, $filter_taxonomy ); 
				unregister_taxonomy( $filter_taxonomy );
			}
		}

		# Delete the options after needing them
		delete_option( 'imdbAdminOptions' ); 
		delete_option( 'imdbWidgetOptions' );
		delete_option( 'imdbCacheOptions' );

		# Remove cache
		if ( (isset($imdb_cache_values['imdbcachedir'])) && (is_dir($imdb_cache_values['imdbcachedir'])) ) {

			$utils->lumiere_unlinkRecursive($imdb_cache_values['imdbcachedir']);

		}

	}

	/**
	20.- Register taxomony
	 *
	 */
	function lumiere_create_taxonomies() {

		global $imdb_admin_values,$imdb_widget_values;

		foreach ( lumiere_array_key_exists_wildcard($imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
			$filter_taxonomy = str_replace('imdbtaxonomy', '', $key );

			if ($imdb_widget_values[ 'imdbtaxonomy'.$filter_taxonomy ] ==  1) {

				register_taxonomy($imdb_admin_values['imdburlstringtaxo'].$filter_taxonomy, array('page','post'), 
					array( 
		/* remove metaboxes from edit interface, keep the menu of post */
		'show_ui'			=> true,		/* whether to manage taxo in UI */
		'show_in_quick_edit'		=> false,		/* whether to show taxo in edit interface */
		'meta_box_cb'			=> false,		/* whether to show taxo in metabox */
		/* other settings */
		'hierarchical' 		=> false, 
		'public' 			=> true,
		'args'				=> array('lang' => 'en'),
		'menu_icon' 			=> $imdb_admin_values['imdbplugindirectory'].'pics/lumiere-ico13x13.png',
		'label' 			=> esc_html__("Lumière ".$filter_taxonomy, 'lumiere-movies'),
		'query_var' 			=> $imdb_admin_values['imdburlstringtaxo'].$filter_taxonomy, 
		'rewrite' 			=> array( 'slug' => $imdb_admin_values['imdburlstringtaxo'].$filter_taxonomy ) 
					)  
				);
			}
		}

		// Limit rewrites calls to taxonomy pages
/* too much resources utilised too often
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], esc_url( site_url( '', 'relative' ) . '/' . $imdb_admin_values['imdburlstringtaxo']) ) ) {

			flush_rewrite_rules();

		}
*/
	}

	/** Highslide popup function
	 ** 
	 ** constructs a HTML link to open a popup with highslide for searching a movie (using js/lumiere_scripts.js)
	 ** 
	 **/
	function lumiere_popup_highslide_film_link ($link_parsed, $popuplarg="", $popuplong="" ) {
		global $imdb_admin_values;
			
		if (! $popuplarg )
			$popuplarg=$imdb_admin_values["popupLarg"];

		if (! $popuplong )
			$popuplong=$imdb_admin_values["popupLong"];

		$parsed_result = '<a class="link-imdblt-highslidefilm" data-highslidefilm="' . lumiere_name_htmlize($link_parsed[1]) . '" title="' . esc_html__("Open a new window with IMDb informations", 'lumiere-movies') . '">' . $link_parsed[1] . "</a>&nbsp;";

		return $parsed_result;
	}

	/** Classical popup function
	 ** 
	 ** constructs a HTML link to open a popup for searching a movie (using js/lumiere_scripts.js)
	 ** 
	 **/
	function lumiere_popup_classical_film_link ($link_parsed, $popuplarg="", $popuplong="" ) {
		global $imdb_admin_values;
		
		if (! $popuplarg )
			$popuplarg=$imdb_admin_values["popupLarg"];

		if (! $popuplong )
			$popuplong=$imdb_admin_values["popupLong"];

		$parsed_result = '<a class="link-imdblt-classicfilm" data-classicfilm="' . lumiere_name_htmlize($link_parsed[1]) . '" title="' . esc_html__("Open a new window with IMDb informations", 'lumiere-movies') . '">' . $link_parsed[1] . "</a>&nbsp;";
		
		return $parsed_result;
	}

	/** Copy metas from one post in original language to another post in other language
	 ** Polylang version
	 ** not yet implemented, not sure if needed, maybe not, need further tests
	 ** to be called: add_filter('pll_copy_post_metas', 'lumiere_copy_post_metas_polylang', 10, 2)
	 **/
/*
	function lumiere_copy_post_metas_polylang( $metas, $sync) {

		if(!is_admin()) return false;
		if($sync) return $metas;
		global $current_screen;

		if($current_screen-post_type == 'wine'){ // substitue 'wine' with post type
			$keys = array_key(get_fields($_GET['imdbltid']));
			return array_merge($metas, $keys);
		}

		return $metas;

	}
*/

	/** Cron to run execute once
	 ** 
	 ** 
	 **/
	function lumiere_cron_exec_once() {

		// Update options
		// this udpate is also run in upgrader_process_complete, but the process is not reliable
		// Using the same updating process in a WP Cron
		require_once __DIR__ . '/class.update-options.php';
		$start_update_options = new \Lumiere\UpdateOptions();

		/* Debugging purposes, add 'imdbTestKey'		
		$config = new \Lumiere\Settings();
		$option_array_search = get_option($config->imdbAdminOptionsName);
		$option_array_search['imdbTestKey'] = 'imdbTestValue';
		update_option($config->imdbAdminOptionsName, $option_array_search);
		*/


	}

}

?>
