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

		// Get Global Settings class properties.
		$this->start_main_trait();

		// Registers javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'frontpage_register_assets' ] );

		// Execute javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'frontpage_execute_assets' ] );

		// Start Movies into the post.
		add_action( 'init', [ 'Lumiere\Frontend\Movie', 'lumiere_movie_start' ], 11 );

		// Start Widgets.
		add_action( 'init', fn() => Widget_Frontpage::lumiere_widget_frontend_start(), 11 );

		// Get plugins
		add_action( 'init', [ $this, 'set_plugins_if_needed' ], 11 );

		// Redirect to popups
		add_filter( 'template_redirect', [ $this, 'popup_redirect_include' ] );
	}

	/**
	 * @see \Lumiere\Core
	 */
	public static function lumiere_static_start(): void {

		if ( is_admin() ) {
			return;
		}

		$that = new self();
	}

	/**
	 * Register frontpage scripts and styles
	 */
	public function frontpage_register_assets(): void {

		// hide/show script
		wp_register_script(
			'lumiere_hide_show',
			$this->config_class->lumiere_js_dir . 'lumiere_hide_show.min.js',
			[ 'jquery' ],
			strval( filemtime( $this->config_class->lumiere_js_path . 'lumiere_hide_show.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);

		// Frontpage scripts
		wp_register_script(
			'lumiere_scripts',
			$this->config_class->lumiere_js_dir . 'lumiere_scripts.min.js',
			[ 'jquery' ],
			strval( filemtime( $this->config_class->lumiere_js_path . 'lumiere_scripts.min.js' ) ),
			[ 'strategy' => 'async' ]
		);

		// Main style
		wp_register_style(
			'lumiere_style_main',
			$this->config_class->lumiere_css_dir . 'lumiere.min.css',
			[],
			strval( filemtime( $this->config_class->lumiere_css_path . 'lumiere.min.css' ) )
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
			$this->config_class->lumiere_scripts_vars,
		);

		// Do not enqueue it more than once.
		if ( wp_script_is( 'lumiere_scripts', 'enqueued' ) === false ) {
			wp_enqueue_script( 'lumiere_scripts' );
		}
	}

	/**
	 * Start Plugins_Start class
	 * Is instanciated only if not instanciated already
	 */
	public function set_plugins_if_needed(): void {
		$this->maybe_activate_plugins(); // In Trait Main.
	}

	/**
	 * Popups redirection, return a new text replacing the normal expected text
	 * Use template_redirect hook to call it
	 * 1. A var in {@see \Lumiere\Settings::define_constants_after_globals()} is made available (for movie, people, search, etc.)
	 * 2. That var is compared against the query_var 'popup' in a switch() function here in {@link Frontend::popup_redirect_include()}
	 * 3. If found, it returns the relevant Popup class
	 *
	 * @param string $template_path The path to the page of the theme currently in use
	 * @return Popup_Movie_Search|Popup_Person|Popup_Movie|string The template path if no popup was found, the popup otherwise
	 */
	public function popup_redirect_include( string $template_path ): \Lumiere\Frontend\Popups\Popup_Basic|string {

		$query_popup = get_query_var( 'popup' );

		// The query var doesn't exist, exit.
		if ( ! isset( $query_popup ) ) {
			return $template_path;
		}

		// 'popup' query_var must match against $this->config_class->lumiere_urlstring* vars.
		switch ( $query_popup ) {
			case 'film':
				return new Popup_Movie();
			case 'person':
				// Build the virtual page class
				return new Popup_Person();
			case 'movie_search':
				// Build the virtual page class
				return new Popup_Movie_Search();
		}
		// No popup was found, return normal template_path.
		return $template_path;
	}
}
