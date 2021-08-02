<?php

/**
 * Core Class : Main WordPress actions happen here
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 */

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
	private $configClass;

	/* \Lumière\Utils class
	 * 
	 * 
	 */
	private $utilsClass;

	/* Constructor
	 *
	 */
	function __construct () {

		global $config, $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

		$config = new \Lumiere\Settings();
		$this->configClass = $config;
		$imdb_admin_values = $config->get_imdb_admin_option();
		$imdb_widget_values = $config->get_imdb_widget_option();
		$imdb_cache_values = $config->get_imdb_cache_option();
		$this->imdb_admin_values = $config->get_imdb_admin_option();
		$this->imdb_widget_values = $config->get_imdb_widget_option();
		$this->imdb_cache_values = $config->get_imdb_cache_option();

		// Start Utils class
		$utilsClass = new \Lumiere\Utils();
		$this->utilsClass = $utilsClass;

		// redirect popups URLs
		add_action( 'init', [ $this, 'lumiere_popup_redirect' ], 0);
		add_action( 'init', [ $this, 'lumiere_popup_redirect_include' ], 0);

		/* ## Highslide download library, function deactivated upon wordpress plugin team request
		add_filter( 'init', function( $template ) {
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=lumiere_options&highslide=yes' ) )
				require_once ( plugin_dir_path( __DIR__ ) . \Lumiere\Settings::highslide_download_page );

		} );*/

		// Redirect gutenberg-search.php
		add_filter( 'init', function( $template ) {
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . \Lumiere\Settings::gutenberg_search_url ) )
				require_once ( plugin_dir_path( __DIR__ ) . \Lumiere\Settings::gutenberg_search_page );

		} );

		// Add Lumière taxonomy
		if ( (isset($imdb_admin_values['imdbtaxonomy'])) && ($imdb_admin_values['imdbtaxonomy'] == 1) ) {

			add_action( 'init', [$this, 'lumiere_create_taxonomies' ], 0 );

			// search for all imdbtaxonomy* in config array, 
			// if active write a filter to add a class to the link to the taxonomy page
			foreach ( $this->utilsClass->lumiere_array_key_exists_wildcard($imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
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

		// Admin interface

			// Admin class
			if ( is_admin() ){

				// Add admin menu
				require_once __DIR__ . '/Admin.php';
				$adminClass = new \Lumiere\Admin();
				add_action('init', [ $adminClass, 'lumiere_admin_menu' ] );
			}

			// Register admin scripts
			add_action('admin_enqueue_scripts', [ $this, 'lumiere_register_admin_assets' ], 0 );

			// Add admin header
			add_action('admin_enqueue_scripts', [ $this, 'lumiere_execute_admin_assets' ] );

			// Add admin tinymce button for wysiwig editor
			add_action('admin_enqueue_scripts', [ $this, 'lumiere_execute_tinymce' ], 2);


		// Frontpage

			// Registers javascripts and styles
			add_action('wp_enqueue_scripts', [ $this, 'lumiere_register_assets' ], 0);

			// Execute javascripts and styles
			add_action('wp_enqueue_scripts', [ $this, 'lumiere_execute_assets' ], 0);

			// Add metas tags
			add_action('wp_head', [ $this, 'lumiere_add_metas' ], 5);

			// Change title of popups
			add_filter('pre_get_document_title', [ $this, 'lumiere_change_popup_title' ]);

		// Register Gutenberg blocks
		add_action('enqueue_block_editor_assets', [ $this, 'lumiere_register_gutenberg_blocks' ]);

		// On updating lumiere plugin
		add_action( 'upgrader_process_complete', [$this, 'lumiere_on_lumiere_upgrade_completed' ], 10, 2 );

		// Add cron schedules
		add_action('lumiere_cron_hook', [$this, 'lumiere_cron_exec_once'], 0);

	}

	/*  Register frontpage scripts and styles
	 * 
	 */
	function lumiere_register_assets(){

		// Common assets to admin and frontpage
		$this->lumiere_register_both_assets();

		// Register frontpage script
		wp_register_script( 
			"lumiere_scripts", 
			$this->imdb_admin_values['imdbplugindirectory'] ."js/lumiere_scripts.js", 
			[ 'jquery' ], 
			$this->configClass->lumiere_version,
			true
		);

		// Register highslide scripts and styles
		wp_register_script( 
			'lumiere_highslide', 
			$this->imdb_admin_values['imdbplugindirectory'] ."js/highslide/highslide-with-html.min.js",
			array(), 
			$this->configClass->lumiere_version
		);
		wp_enqueue_script( 
			"lumiere_highslide_options", 
			$this->imdb_admin_values['imdbplugindirectory'] ."js/highslide-options.js", 
			[ 'lumiere_highslide' ], 
			$this->configClass->lumiere_version,
			true
		);
		wp_enqueue_style( 
			"lumiere_highslide", 
			$this->imdb_admin_values['imdbplugindirectory'] ."css/highslide.css", 
			array(), 
			$this->configClass->lumiere_version
		);

		// Register customised main style, located in active theme directory
		wp_register_style(
			'lumiere_style_custom',
			get_stylesheet_directory_uri() . '/lumiere.css', 
			array(), 
			$this->configClass->lumiere_version
		);

		// Register main style
		wp_register_style( 
			'lumiere_style_main', 
			$this->imdb_admin_values['imdbplugindirectory'] .'css/lumiere.css', 
			array(), 
			$this->configClass->lumiere_version
		);

		// Register OceanWP theme fixes for popups only
		wp_register_style(
			'lumiere_style_oceanwpfixes_popups', 
			$this->imdb_admin_values['imdbplugindirectory'] ."css/lumiere_subpages-oceanwpfixes.css",
			 array(), 
			$this->configClass->lumiere_version
		);

		// Register OceanWP theme fixes for all pages but popups
		wp_register_style(
			'lumiere_style_oceanwpfixes_general', 
			$this->imdb_admin_values['imdbplugindirectory'] ."css/lumiere_extrapages-oceanwpfixes.css",
			array(), 
			$this->configClass->lumiere_version
		);
	}

	/*  Register admin scripts and styles
	 * 
	 */
	function lumiere_register_admin_assets() {

		// Common assets to admin and frontpage
		$this->lumiere_register_both_assets();

		// Register paths, fake script to get a hook for add inline scripts
		wp_register_script(
			"lumiere_scripts_admin_vars", 
			'', 
			array(), 
			$this->configClass->lumiere_version, true 
		);	

		// Register admin styles
		wp_register_style(
			'lumiere_css_admin', 
			$this->imdb_admin_values['imdbplugindirectory'] . "css/lumiere-admin.css", 
			array(), 
			$this->configClass->lumiere_version
		);

		// Register admin scripts
		wp_register_script( 
			"lumiere_scripts_admin", 
			$this->imdb_admin_values['imdbplugindirectory'] ."js/lumiere_scripts_admin.js",
			[ 'jquery' ], 
			$this->configClass->lumiere_version
		);

		// Register gutenberg admin scripts
		wp_register_script( 
			"lumiere_scripts_admin_gutenberg", 
			$this->imdb_admin_values['imdbplugindirectory'] ."js/lumiere_scripts_admin_gutenberg.js",
			[ 'jquery' ],
			$this->configClass->lumiere_version
		);

		// Register confirmation script upon deactivation
		wp_register_script(
			'lumiere_deactivation_plugin_message', 
			$this->imdb_admin_values['imdbplugindirectory'] . 'js/lumiere_admin_deactivation_msg.js',
			[ 'jquery' ],
			$this->configClass->lumiere_version,
			true
		);

		// Quicktag
		wp_register_script( 
			"lumiere_quicktag_addbutton", 
			$this->imdb_admin_values['imdbplugindirectory'] ."js/lumiere_admin_quicktags.js",
			[ 'quicktags' ], 
			$this->configClass->lumiere_version,
			true
		);

	}

	/*  Common assets registration
	 *  For both admin and frontpage utilisation scripts and styles
	 * 
	 */
	function lumiere_register_both_assets() {

		// Register hide/show script
		wp_register_script( 
			"lumiere_hide_show", 
			$this->imdb_admin_values['imdbplugindirectory'] ."js/lumiere_hide_show.js", 
			[ 'jquery' ], 
			$this->configClass->lumiere_version,
			true
		);

	}


	/*  Register TinyMCE
	 * 
	 */
	function lumiere_execute_tinymce($hook) {

		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;

		// Add only in Rich Editor mode for post.php and post-new.php pages
		if ( 
			( get_user_option('rich_editing') == 'true') 
			&& ( ('post.php' === $hook) || ('post-new.php' === $hook) ) 
		) {

			add_filter("mce_external_plugins", [ $this, "lumiere_tinymce_addbutton" ] );
			add_filter('mce_buttons', [ $this, 'lumiere_tinymce_button_position' ] );

		}
	}

	/*  Position of TinyMCE button
	 * 
	 */
	function lumiere_tinymce_button_position($buttons) {

		array_push($buttons, "separator", "lumiere_tiny");

		return $buttons;

	}

	/*  Add TinyMCE button
	 * 
	 */
	function lumiere_tinymce_addbutton($plugin_array) {

		$imdb_admin_values = $this->imdb_admin_values;

		$plugin_array['lumiere_tiny'] = $imdb_admin_values['imdbplugindirectory'] . 'js/lumiere_admin_tinymce_editor.js';
		return $plugin_array;

	}

	/*  Register gutenberg blocks
	 * 
	 */
	function lumiere_register_gutenberg_blocks() {

		wp_register_script( 
			"lumiere_gutenberg_main", 
			$this->imdb_admin_values['imdbplugindirectory'] . 'blocks/main-block.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor','wp-components','wp-i18n','wp-data' ], 
			$this->configClass->lumiere_version 
		);

		wp_register_script( 
			"lumiere_gutenberg_buttons", 
			$this->imdb_admin_values['imdbplugindirectory'] . 'blocks/buttons.js',
			[ 'wp-element', 'wp-compose','wp-components','wp-i18n','wp-data' ], 
			$this->configClass->lumiere_version 
		);

		// Style
		wp_register_style( 
			"lumiere_gutenberg_main", 
			$this->imdb_admin_values['imdbplugindirectory'] . 'blocks/main-block.css',
			array( 'wp-edit-blocks' ), 
			$this->configClass->lumiere_version 
		);

		// Register block script and style.
		register_block_type( 
			'lumiere/main', [
				'style' => 'lumiere_gutenberg_main', // Loads both on editor and frontend.
				'editor_script' => 'lumiere_gutenberg_main', // Loads only on editor.
			] 
		);

		register_block_type( 
			'lumiere/buttons', [
				'editor_script' => 'lumiere_gutenberg_buttons', // Loads only on editor.
			] 
		);

		wp_enqueue_script( 'lumiere_scripts_admin_gutenberg');

	}

	/*  Add the stylesheet & javascript to frontpage
	 * 
	 */
	function lumiere_execute_assets (){


		// Use local template lumiere.css if there is one in current theme folder
		if (file_exists ( TEMPLATEPATH . "/lumiere.css" ) ) { # a lumiere.css exists inside theme folder, use it!
			wp_enqueue_style( 'lumiere_style_custom' );

	 	} else {

			wp_enqueue_style( 'lumiere_style_main' );
	 	}

		// OceanWp template css fix
		// enqueue lumiere.css only if using oceanwp template
		# Popups
		if ( 
			( 0 === stripos( get_template_directory_uri(), esc_url( site_url() . '/wp-content/themes/oceanwp' ) ) )
			 && 
			( $this->utilsClass->str_contains( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstring ) ) 
		) {

			wp_enqueue_style( 'lumiere_style_oceanwpfixes_popups' );

		# All other cases
		} elseif ( 0 === stripos( get_template_directory_uri(), esc_url( site_url() . '/wp-content/themes/oceanwp') ) ){ 

			wp_enqueue_style( 'lumiere_style_oceanwpfixes_general' );

		} 

		// Highslide popup
		if ($this->imdb_admin_values['imdbpopup_highslide'] == 1) {

			wp_enqueue_style('lumiere_highslide' );

			wp_enqueue_script( 'lumiere_highslide');

			// Pass variables to javascript highslide-options.js
			wp_add_inline_script( 
				'lumiere_highslide_options', 
				$this->configClass->lumiere_scripts_highslide_vars,
				'before',
			);

			wp_enqueue_script( 'lumiere_highslide_options' );

		}

		wp_enqueue_script( 'lumiere_hide_show' );

		wp_enqueue_script( 'lumiere_scripts');

		// Pass variable to javascript lumiere_scripts.js
		wp_add_inline_script( 
			'lumiere_scripts', 
			$this->configClass->lumiere_scripts_vars, 
			'before'
		);

	}

	/*  Add assets of Lumière admin pages
	 * 
	 */
	function lumiere_execute_admin_assets ($hook) {

		$imdb_admin_values = $this->imdb_admin_values;

		// Load scripts only on Lumière admin pages
		// + WordPress edition pages + Lumière own pages (ie gutenberg search)
		if ( 
			('toplevel_page_lumiere_options' === $hook) 
			|| ('widgets.php' === $hook) 
			|| ('post.php' === $hook) 
			|| ('post-new.php' === $hook) 
			|| ($this->utilsClass->lumiere_array_contains_term($this->configClass->lumiere_list_all_pages, $_SERVER['REQUEST_URI'])) 
		) {

			// Load main css
			wp_enqueue_style( 'lumiere_css_admin');

			// Load main js
			wp_enqueue_script( 'lumiere_scripts_admin');

			// Pass path variables to javascripts
			wp_add_inline_script( 
				'lumiere_scripts_admin', 
				$this->configClass->lumiere_scripts_admin_vars,
				'before'
			);

			// Load hide/show js
			wp_enqueue_script( 'lumiere_hide_show' );

		}

		// On 'plugins.php' show a confirmation dialogue if
		// 'imdbkeepsettings' is set on delete Lumière! options
		if ( ( (!isset($imdb_admin_values['imdbkeepsettings'])) || ( $imdb_admin_values['imdbkeepsettings'] == false ) ) && ('plugins.php' === $hook)  ) {

			wp_enqueue_script( 'lumiere_deactivation_plugin_message' );

		}

		//  Add Quicktag
		if ( (('post.php' === $hook) || ('post-new.php' === $hook)) && ( wp_script_is('quicktags' )) ) {

			wp_enqueue_script( 'lumiere_quicktag_addbutton');

		}

	}

	/**
	 **  Redirect the popups to a proper URL
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

			$url = (!empty($match_query_film_film[0])) ? $this->configClass->lumiere_urlstringfilms . $match_query_film_film[0] . "/" : $this->configClass->lumiere_urlstringfilms . $match_query_film_mid[0] . "/" ;
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
			$url = (!empty($match_query_film_film[0])) ? $this->configClass->lumiere_urlstringfilms . $match_query_film_film[0] . "/" : $this->configClass->lumiere_urlstringfilms . $match_query_film_mid[0] . "/" ;

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
			$url = $this->configClass->lumiere_urlstringperson . $match_query_person_mid[0] . "/" ;

	      		//wp_redirect(  add_query_arg( 'mid' => $match_query_mid[1][0], $url ) , 301 ); # one arg only
			wp_safe_redirect( add_query_arg( array( 'mid' => $match_query_person_mid[0], 'film' => $match_query_person_film[1][0], 'info' => $match_query_person_info[0]), get_site_url(null, $url ) ) );
			exit();
		}
	}

	// pages to be included when the redirection is done
	function lumiere_popup_redirect_include() {

		$imdb_admin_values = $this->imdb_admin_values;

		// Include films popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringsearch ) )
			require_once ( $imdb_admin_values['imdbpluginpath'] . \Lumiere\Settings::popup_search_url );


		// Include films popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringfilms ) )
			require_once ( $imdb_admin_values['imdbpluginpath'] . \Lumiere\Settings::popup_movie_url );


		// Include persons popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringperson ) )
			require_once ( $imdb_admin_values['imdbpluginpath'] . \Lumiere\Settings::popup_person_url );

	}

	/**
	 **  Change the title of the popups according to the movie's or person's data
	 **/
	function lumiere_change_popup_title($title) {

		$imdb_cache_values = $this->imdb_cache_values;
		$config = $this->configClass;

		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstring ) ){

			// Add cache dir to properly save data in real cache dir
			$config->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;

			// Display the title if /url/films
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringfilms ) ) {
				if ( (isset($_GET['mid'])) && (!empty($_GET['mid'])) ) {
					$movieid_sanitized = sanitize_text_field( $_GET['mid'] );
					$movie = new \Imdb\Title($movieid_sanitized, $config);
					$filmid_sanitized = esc_html($movie->title());
				} elseif ( (!isset($_GET['mid'])) && (isset($_GET['film'])) ){
					$filmid_sanitized = $this->utilsClass->lumiere_name_htmlize($_GET['film']);
				}

				$title_name = isset($movieid_sanitized) ? $filmid_sanitized : sanitize_text_field($_GET['film']);
				$title = isset($title_name ) ? esc_html__('Informations about ', 'lumiere-movies') . $title_name. " - Lumi&egrave;re movies" : esc_html__('Unknown', 'lumiere-movies') . '- Lumi&egrave;re movies';

			// Display the title if /url/person
			} elseif ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringperson ) ){

				if ( (isset($_GET['mid'])) && (!empty($_GET['mid'])) ) {
					$mid_sanitized = sanitize_text_field($_GET['mid']);
					$person = new \Imdb\Person($mid_sanitized, $config);
					$person_name_sanitized = sanitize_text_field( $person->name() );
				}
				$title = isset($person_name_sanitized ) ? esc_html__('Informations about ', 'lumiere-movies') . $person_name_sanitized. " - Lumi&egrave;re movies" : esc_html__('Unknown', 'lumiere-movies') . '- Lumi&egrave;re movies';

			// Display the title if /url/search
			} elseif ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringsearch ) ){
				$title_name = isset($_GET['film']) ? esc_html($_GET['film']) : esc_html__('No query entered', 'lumiere-movies');
				$title = esc_html__('Search query for ', 'lumiere-movies') . $title_name . " - Lumi&egrave;re movies ";
			}

			return $title;
		}

		// Change the title for the query search popup
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . \Lumiere\Settings::gutenberg_search_url ) )
			return esc_html__('Lumiere Query Interface', 'lumiere-movies');
	}

	/**
	 **  Add a class to taxonomy links (constructed in class.movie.php)
	 **/
	function lumiere_taxonomy_add_class_to_links($links) {

	    return str_replace('<a href="', '<a class="linktaxonomy" href="', $links);

	}

	/**
	 ** Add new meta tags in popups <head>
	 **/
	function lumiere_add_metas() {

		// Change the metas only for popups
		if ( ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringfilms ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringsearch ) ) || ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringperson ) ) )
{
			# ADD FAVICONS
			echo "\t\t" . '<!-- Lumiere Movies -->';
			echo "\n" . '<link rel="apple-touch-icon" sizes="180x180" href="' . plugin_dir_url( __DIR__ ) . 'pics/favicon/apple-touch-icon.png" />';
			echo "\n" . '<link rel="icon" type="image/png" sizes="32x32" href="' . plugin_dir_url( __DIR__ ) . 'pics/favicon/favicon-32x32.png" />';
			echo "\n" . '<link rel="icon" type="image/png" sizes="16x16" href="' . plugin_dir_url( __DIR__ ) . 'pics/favicon/favicon-16x16.png" />';
			echo "\n" . '<link rel="manifest" href="' . plugin_dir_url( __DIR__ ) . 'pics/favicon/site.webmanifest" />';

			# ADD CANONICAL
			// Canonical for search popup
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringsearch ) ) {
				$film_sanitized = ""; $film_sanitized = isset($_GET['film']) ? $this->utilsClass->lumiere_name_htmlize($_GET['film']) : "";
				$my_canon = $this->configClass->lumiere_urlpopupssearch . '?film=' . $film_sanitized . '&norecursive=yes' ;
			}

			// Canonical for movies popups
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringfilms ) ) {
				$mid_sanitized = isset($_GET['mid']) ? sanitize_text_field($_GET['mid']) : "";
				$film_sanitized = ""; $film_sanitized = isset($_GET['film']) ? $this->utilsClass->lumiere_name_htmlize($_GET['film']) : "";
				$info_sanitized = ""; $info_sanitized = isset($_GET['info']) ? esc_html($_GET['info']) : "";
				$my_canon = $this->configClass->lumiere_urlpopupsfilms . '?film=' . $film_sanitized . '&mid=' . $mid_sanitized. '&info=' . $info_sanitized;
			}

			// Canonical for people popups
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->configClass->lumiere_urlstringperson ) ) {
				$mid_sanitized = isset($_GET['mid']) ? sanitize_text_field($_GET['mid']) : "";
				$info_sanitized = isset($_GET['info']) ? esc_html($_GET['info']) : "";
				$my_canon = $this->configClass->lumiere_urlpopupsperson . $mid_sanitized . '/?mid=' . $mid_sanitized . '&info=' . $info_sanitized;
			}

			echo "\n" . '<link rel="canonical" href="' . $my_canon . '" />';
			if (isset($film_sanitized))
				echo "\n" . '<meta property="article:tag" content="' . $film_sanitized . '" />';
			echo "\n\t\t" . '<!-- Lumiere Movies -->'."\n";

			remove_action('wp_head', 'rel_canonical'); # prevents Wordpress from inserting a canonical tag
			remove_action('wp_head', 'wp_site_icon', 99); # prevents Wordpress from inserting favicons

		}

	}

	/**
	 ** Run on lumiere WordPress update
	 **/
	function lumiere_on_lumiere_upgrade_completed( $upgrader_object, $options ) {

		// Activate debug
		$this->utilsClass->lumiere_activate_debug();
		// Start the logger
		$this->configClass->lumiere_start_logger('coreLumiere');
		// Store the class so we can use it later
		$configClass = $this->configClass;

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
					require_once __DIR__ . '/Update-options.php';
					$start_update_options = new \Lumiere\UpdateOptions();

					$configClass->lumiere_maybe_log('info', "[Lumiere][core][updater] Lumière _on_plugin_upgrade_ hook successfully updated.");

				}
			}
		}
	}

	/**
	 ** Run on plugin activation
	 **/
	function lumiere_on_activation() {

		/* debug
		ob_start(); */

		// Activate debug
		$this->utilsClass->lumiere_activate_debug();
		// Start the logger
		$this->configClass->lumiere_start_logger('coreLumiere', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );
		// Store the class so we can use it later
		$configClass = $this->configClass;

		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "activate-plugin_{$plugin}" );

		/* Create the value of number of updates on first install */
		$this->configClass->lumiere_define_nb_updates();

		/* Create the cache folders */
		if ($this->configClass->lumiere_create_cache() == true){

			$configClass->lumiere_maybe_log('info', "[Lumiere][core][updater] Lumière _on_activation_ hook: cache successfully created.");

		}

		/* Set up WP Cron */
		if (! wp_next_scheduled ( 'lumiere_cron_hook' )) {

			// Runned thee times to make sure that no update is missed

			// Cron to run once, in 2 minutes
			wp_schedule_single_event( time() + 120, 'lumiere_cron_hook' );

			// Cron to run once, in 10 minutes
			wp_schedule_single_event( time() + 600, 'lumiere_cron_hook' );

			// Cron to run once, in 30 minutes
			wp_schedule_single_event( time() + 1800, 'lumiere_cron_hook' );

			// Run week call
			//wp_schedule_event(time(), 'weekly', 'lumiere_cron_hook');

			$configClass->lumiere_maybe_log('debug', "[Lumiere][core][updater] Lumière _on_activation_ hook: crons successfully set up.");

		} else {

			$configClass->lumiere_maybe_log('error', "[Lumiere][core][updater] Lumière _on_activation_ hook: could not set up crons.");

		}

		/* debug
		trigger_error(ob_get_contents(),E_USER_ERROR);*/
	}

	/**
	 ** Run on plugin deactivation
	 **/
	function lumiere_on_deactivation() {

		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;
		$imdb_cache_values = $this->imdb_cache_values;

		// Activate debug
		$this->utilsClass->lumiere_activate_debug();
		// Start the logger
		$this->configClass->lumiere_start_logger('coreLumiere');
		// Store the classes so we can use it later
		$configClass = $this->configClass;
		$utilsClass = $this->utilsClass;

		/****** Below actions are executed for everybody */

		// Remove WP Cron shoud it exists
		$timestamp = wp_next_scheduled( 'lumiere_cron_hook' );
		wp_unschedule_event( $timestamp, 'lumiere_cron_hook' );

		// Keep the settings if selected so
		if ( (isset($imdb_admin_values['imdbkeepsettings'])) && ( $imdb_admin_values['imdbkeepsettings'] == true ) ) {

			$configClass->lumiere_maybe_log('info', "[Lumiere][core][deactivation] Lumière deactivation: keep settings selected, process finished.");

			return;
		}

		/****** Below actions are not executed if the user selected to keep their settings */

		// search for all imdbtaxonomy* in config array, 
		// if a taxonomy is found, let's get related terms and delete them
		foreach ( $this->utilsClass->lumiere_array_key_exists_wildcard($imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
			$filter_taxonomy = str_replace('imdbtaxonomy', '', $imdb_admin_values['imdburlstringtaxo']  . $key );

			# get all terms, even if empty
			$terms = get_terms( array(
				'taxonomy' => $filter_taxonomy,
				'hide_empty' => false
			) );

			# Delete taxonomy terms and unregister taxonomy
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, $filter_taxonomy ); 

				$configClass->lumiere_maybe_log('info', "[Lumiere][core][deactivation] Taxonomy: term $term in $filter_taxonomy deleted.");

				unregister_taxonomy( $filter_taxonomy );

				$configClass->lumiere_maybe_log('info', "[Lumiere][core][deactivation] Taxonomy: taxonomy $filter_taxonomy deleted.");

			}
		}

		# Delete the options after needing them
		delete_option( 'imdbAdminOptions' ); 
		delete_option( 'imdbWidgetOptions' );
		delete_option( 'imdbCacheOptions' );

		$configClass->lumiere_maybe_log('info', "[Lumiere][core][deactivation] Lumière options deleted.");

		# Remove cache
		if ( (isset($imdb_cache_values['imdbcachedir'])) && (is_dir($imdb_cache_values['imdbcachedir'])) ) {

			$utilsClass->lumiere_unlinkRecursive($imdb_cache_values['imdbcachedir']);

			$configClass->lumiere_maybe_log('info', "[Lumiere][core][deactivation] Cache files and folder deleted.");

		} else {

			$configClass->lumiere_maybe_log('warning', "[Lumiere][core][deactivation] Cache was not removed.");

		}

	}

	/**
	 **  Run on plugin uninstall 
	 **  @TODO Function not active, should go into an uninstall.php
	 **/
	function lumiere_on_uninstall() {

		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;
		$imdb_cache_values = $this->imdb_cache_values;

		// Activate debug
		$this->utilsClass->lumiere_activate_debug();
		// Start the logger
		$this->configClass->lumiere_start_logger('coreLumiere');
		// Store the classes so we can use it later
		$configClass = $this->configClass;
		$utilsClass = $this->utilsClass;

		/****** Below actions are executed for everybody */

		// Remove WP Cron shoud it exists
		$timestamp = wp_next_scheduled( 'lumiere_cron_hook' );
		wp_unschedule_event( $timestamp, 'lumiere_cron_hook' );

		// Keep the settings if selected so
		if ( (isset($imdb_admin_values['imdbkeepsettings'])) && ( $imdb_admin_values['imdbkeepsettings'] == true ) ) {

			$configClass->lumiere_maybe_log('info', "[Lumiere][core][uninstall] Lumière uninstall: keep settings selected, process finished.");

			return;
		}

		/****** Below actions are not executed if the user selected to keep their settings */

		// search for all imdbtaxonomy* in config array, 
		// if a taxonomy is found, let's get related terms and delete them
		foreach ( $this->utilsClass->lumiere_array_key_exists_wildcard($imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
			$filter_taxonomy = str_replace('imdbtaxonomy', '', $imdb_admin_values['imdburlstringtaxo']  . $key );

			# get all terms, even if empty
			$terms = get_terms( array(
				'taxonomy' => $filter_taxonomy,
				'hide_empty' => false
			) );

			# Delete taxonomy terms and unregister taxonomy
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, $filter_taxonomy ); 

				$configClass->lumiere_maybe_log('info', "[Lumiere][core][uninstall] Taxonomy: term $term in $filter_taxonomy deleted.");

				unregister_taxonomy( $filter_taxonomy );

				$configClass->lumiere_maybe_log('info', "[Lumiere][core][uninstall] Taxonomy: taxonomy $filter_taxonomy deleted.");

			}
		}

		# Delete the options after needing them
		delete_option( 'imdbAdminOptions' ); 
		delete_option( 'imdbWidgetOptions' );
		delete_option( 'imdbCacheOptions' );

		$configClass->lumiere_maybe_log('info', "[Lumiere][core][uninstall] Lumière options deleted.");

		# Remove cache
		if ( (isset($imdb_cache_values['imdbcachedir'])) && (is_dir($imdb_cache_values['imdbcachedir'])) ) {

			$utilsClass->lumiere_unlinkRecursive($imdb_cache_values['imdbcachedir']);

			$configClass->lumiere_maybe_log('info', "[Lumiere][core][uninstall] Cache files and folder deleted.");

		} else {

			$configClass->lumiere_maybe_log('warning', "[Lumiere][core][uninstall] Cache was not removed.");

		}

	}


	/**
	 ** Register taxomony
	 **/
	function lumiere_create_taxonomies() {

		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		foreach ( $this->utilsClass->lumiere_array_key_exists_wildcard($imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
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

		// For debugging purpose
		// Update imdbHowManyUpdates option
/*		$config = new \Lumiere\Settings();
		$option_array_search = get_option($config->imdbAdminOptionsName);
		$option_array_search['imdbHowManyUpdates'] = '6'; # current number of updates
		update_option($config->imdbAdminOptionsName, $option_array_search);
*/

		// Update options
		// this udpate is also run in upgrader_process_complete, but the process is not reliable
		// Using the same updating process in a WP Cron
		require_once __DIR__ . '/Update-options.php';
		$start_update_options = new \Lumiere\UpdateOptions();

	}

}
?>
