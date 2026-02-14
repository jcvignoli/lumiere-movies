<?php declare( strict_types = 1 );
/**
 * Class for displaying the Frontend
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Widget\Widget_Frontpage;
use Lumiere\Frontend\Post\Front_Parser;
use Lumiere\Frontend\Post\Find_Items;
use Lumiere\Frontend\Popups\Popup_Factory;
use Lumiere\Config\Get_Options;

/**
 * Start everything for frontend pages
 * Register and enqueue the common scripts and stylesheets for Popups and Movie classes
 * Redirect to Popupus if query var 'popup' is found in URL
 *
 * @since 4.1
 *
 * @see \Lumiere\Frontend\Main Settings and plugins
 * @see \Lumiere\Config\Settings URL vars for query_var 'popup'
 * @see \Lumiere\Alteration\Rewrite_Rules for URL rewriting using query_var 'popup'
 * @see Popups {@link \Lumiere\Frontend\Popups\Popup_Person}, {@link \Lumiere\Frontend\Popups\Popup_Movie} and {@link \Lumiere\Frontend\Popups\Popup_Movie_Search} using parent class Popup_Head and interface Popup_Basic
 */
final class Frontend {

	/**
	 * Constructor
	 */
	public function __construct(
		Front_Parser $front_parser = new Front_Parser(),
		Find_Items $find_items = new Find_Items(),
		Widget_Frontpage $widget_front = new Widget_Frontpage(),
		Popup_Factory $popup_factory = new Popup_Factory(),
	) {

		if ( is_admin() ) {
			return;
		}

		// Registers javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'frontpage_register_assets' ] );

		// Execute javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'frontpage_execute_assets' ] );

		/**
		 * Movie's related actions and filters
		 */
		add_action( 'init', [ $front_parser, 'start' ], 11 );
		add_filter( 'lum_display_movies_box', [ $front_parser, 'lum_display_movies_box' ], 10, 1 );
		add_filter( 'lum_display_persons_box', [ $front_parser, 'lum_display_persons_box' ], 10, 1 );
		add_filter( 'lum_find_movie_id', [ $find_items, 'find_movie_imdb_id' ], 10, 1 );
		add_filter( 'lum_find_person_id', [ $find_items, 'find_person_imdb_id' ], 10, 1 );

		/**
		 * Calendar's related action
		 */
		add_filter( 'lum_coming_soon', [ 'Lumiere\Frontend\Coming_Soon', 'init' ], 10, 5 );

		/**
		 * Widget's related action
		 */
		add_action( 'init', [ $widget_front, 'start' ], 11 );

		/**
		 * Add filter for Popups
		 */
		add_filter( 'template_include', [ $popup_factory, 'maybe_find_template' ] );
	}

	/**
	 * @see \Lumiere\Core
	 */
	public static function start(): void {
		$that = new self();
	}

	/**
	 * Register frontpage scripts and styles
	 */
	public function frontpage_register_assets(): void {

		// hide/show script
		wp_register_script(
			'lumiere_hide_show',
			Get_Options::LUM_JS_URL . 'lumiere_hide_show.min.js',
			[ 'jquery' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere_hide_show.min.js' ) ),
			[ 'strategy' => 'defer' ]
		);

		// Frontpage scripts
		wp_register_script(
			'lumiere_scripts',
			Get_Options::LUM_JS_URL . 'lumiere_scripts.min.js',
			[ 'jquery' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere_scripts.min.js' ) ),
			[ 'strategy' => 'async' ]
		);

		// Main style
		wp_register_style(
			'lumiere_style_main',
			Get_Options::LUM_CSS_URL . 'lumiere.min.css',
			[],
			strval( filemtime( Get_Options::LUM_CSS_PATH . 'lumiere.min.css' ) )
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
		wp_add_inline_script( 'lumiere_scripts', Get_Options::get_scripts_frontend_vars() );

		// Do not enqueue it more than once.
		if ( wp_script_is( 'lumiere_scripts', 'enqueued' ) === false ) {
			wp_enqueue_script( 'lumiere_scripts' );
		}
	}
}
