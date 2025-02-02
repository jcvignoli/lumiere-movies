<?php declare( strict_types = 1 );
/**
 * Class for displaying the Frontend
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Popups\Popup_Person;
use Lumiere\Frontend\Popups\Popup_Movie;
use Lumiere\Frontend\Popups\Popup_Movie_Search;
use Lumiere\Frontend\Main;
use Lumiere\Plugins\Plugins_Start;
use Lumiere\Settings;

/**
 * Start everything for frontend pages
 * Register and enqueue the common scripts and stylesheets for Popups and Movie classes
 * Redirect to Popupus if query var 'popup' is found in URL
 *
 * @since 4.1
 *
 * @see \Lumiere\Frontend\Main Settings and plugins
 * @see \Lumiere\Settings URL vars for query_var 'popup'
 * @see \Lumiere\Alteration\Rewrite_Rules for URL rewriting using query_var 'popup'
 * @see Popups {@link \Lumiere\Frontend\Popups\Popup_Person}, {@link \Lumiere\Frontend\Popups\Popup_Movie} and {@link \Lumiere\Frontend\Popups\Popup_Movie_Search} using parent class Popup_Head and interface Popup_Basic
 */
class Frontend {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct() {

		if ( is_admin() ) {
			return;
		}

		// Get Global Settings class properties.
		$this->start_main_trait();

		/**
		 * Get an array with all objects plugins
		 * Always loads IMDBPHP plugin
		 */
		$plugins_start = ( new Plugins_Start( [ 'imdbphp' ] ) )->plugins_classes_active;

		// Registers javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'frontpage_register_assets' ] );

		// Execute javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'frontpage_execute_assets' ] );

		/**
		 * Display movie(s) into the post.
		 */
		add_action( 'init', fn() => Movie::start( $plugins_start ), 11 );

		/**
		 * Display Widget
		 */
		add_action( 'init', fn() => Widget_Frontpage::start( $plugins_start ), 11 );

		// Display popups.
		add_filter( 'template_include', [ $this, 'popup_redirect_include' ] );
	}

	/**
	 * @see \Lumiere\Core
	 */
	public static function lumiere_static_start(): void {
		$that = new self();
	}

	/**
	 * Register frontpage scripts and styles
	 */
	public function frontpage_register_assets(): void {

		// hide/show script
		wp_register_script(
			'lumiere_hide_show',
			Settings::LUM_JS_URL . 'lumiere_hide_show.min.js',
			[ 'jquery' ],
			strval( filemtime( Settings::LUM_JS_PATH . 'lumiere_hide_show.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);

		// Frontpage scripts
		wp_register_script(
			'lumiere_scripts',
			Settings::LUM_JS_URL . 'lumiere_scripts.min.js',
			[ 'jquery' ],
			strval( filemtime( Settings::LUM_JS_PATH . 'lumiere_scripts.min.js' ) ),
			[ 'strategy' => 'async' ]
		);

		// Main style
		wp_register_style(
			'lumiere_style_main',
			Settings::LUM_CSS_URL . 'lumiere.min.css',
			[],
			strval( filemtime( Settings::LUM_CSS_PATH . 'lumiere.min.css' ) )
		);

		// Customized style: register instead of the main style a customised main style located in active theme directory
		if ( file_exists( get_stylesheet_directory() . '/lumiere.css' ) ) {

			wp_deregister_style( 'lumiere_style_main' ); // remove standard style

			wp_register_style(
				'lumiere_style_main',
				get_stylesheet_directory() . '/lumiere.css',
				[],
				strval( filemtime( get_stylesheet_directory() . '/lumiere.css' ) )
			);
		}
	}

	/**
	 * Execute Frontpage stylesheets & javascripts.
	 */
	public function frontpage_execute_assets(): void {

		wp_enqueue_style( 'lumiere_style_main' );

		wp_enqueue_script( 'lumiere_hide_show' );

		/**
		 * Pass variables to javascript lumiere_scripts.js.
		 * These variables contains popup sizes, color, paths, etc.
		 */
		wp_add_inline_script(
			'lumiere_scripts',
			Settings::get_scripts_frontend_vars(),
		);

		// Do not enqueue it more than once.
		if ( wp_script_is( 'lumiere_scripts', 'enqueued' ) === false ) {
			wp_enqueue_script( 'lumiere_scripts' );
		}
	}

	/**
	 * Popups redirection, return a new text replacing the normal expected text
	 * Use template_redirect hook to call it
	 * 1. A var in {@see \Lumiere\Settings::define_constants_after_globals()} is made available (for movie, people, search, etc.)
	 * 2. That var is compared against the query_var 'popup' in a switch() function here in {@link Frontend::popup_redirect_include()}
	 * 3. If found, it returns the relevant Popup class, method get_layout() (which echoes instead of returning, needs therefore an ending return)
	 *
	 * @param string $template_path The path to the page of the theme currently in use
	 * @return string The template path if no popup was found, the popup otherwise
	 */
	public function popup_redirect_include( string $template_path ): string {

		$query_popup = get_query_var( 'popup' );

		// The query var doesn't exist, exit.
		if ( ! isset( $query_popup ) ) {
			return $template_path;
		}

		// 'popup' query_var must match against Settings::URL_BIT_POPUPS_* vars that are encoded in javascript URL.
		switch ( $query_popup ) {
			case 'film':
				( new Popup_Movie() )->get_layout();
				return '';
			case 'person':
				( new Popup_Person() )->get_layout();
				return '';
			case 'movie_search':
				( new Popup_Movie_Search() )->get_layout();
				return '';
		}

		// No valid popup was found, return normal template_path.
		return $template_path;
	}
}
