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
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Tools\Ban_Bots;
use Lumiere\Alteration\Virtual_Page;
use Lumiere\Frontend\Popups\Popup_Person;
use Lumiere\Frontend\Popups\Popup_Movie;
use Lumiere\Frontend\Popups\Popup_Search;
use Lumiere\Frontend\Main;
use Imdb\Title;
use Imdb\Person;

/**
 * Start everything for frontend pages
 * Register and enqueue the common scripts and stylesheets
 * Popups redirect and bot banning happen here
 *
 * @since 4.1
 *
 * @see \Lumiere\Frontend\Main Settings and plugins
 * @see \Lumiere\Alteration\Virtual_Page For creating virtual pages, used by {@link \Lumiere\Frontend\Popups\Popup_Person}, {@link \Lumiere\Frontend\Popups\Popup_Movie} and {@link \Lumiere\Frontend\Popups\Popup_Search}
 * @see \Lumiere\Tools\Ban_Bots Ban the bots for virtual pages
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

		// Redirect to popups
		add_filter( 'template_redirect', [ $this, 'lumiere_popup_redirect_include' ], 9 ); // Must be executed with priority < 10

		// Registers javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_register_assets' ] );

		// Execute javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_frontpage_execute_assets' ] );

		// Start Movies into the post.
		add_action( 'init', fn() => Movie::lumiere_static_start(), 11 );

		// Start Widgets.
		add_action( 'init', fn() => Widget_Frontpage::lumiere_widget_frontend_start(), 11 );

		// Ban bots.
		add_action( 'init', fn() => Ban_Bots::lumiere_static_start(), 11 );
	}

	/**
	 * @see \Lumiere\Core
	 */
	public static function lumiere_static_start(): void {
		$start = new self();
	}

	/**
	 * Register frontpage scripts and styles
	 */
	public function lumiere_register_assets(): void {

		// hide/show script
		wp_register_script(
			'lumiere_hide_show',
			$this->config_class->lumiere_js_dir . 'lumiere_hide_show.min.js',
			[ 'jquery' ],
			strval( filemtime( $this->config_class->lumiere_js_path . 'lumiere_hide_show.min.js' ) ),
			true
		);

		// Frontpage scripts
		wp_register_script(
			'lumiere_scripts',
			$this->config_class->lumiere_js_dir . 'lumiere_scripts.min.js',
			[],
			strval( filemtime( $this->config_class->lumiere_js_path . 'lumiere_scripts.min.js' ) ),
			true
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
	public function lumiere_frontpage_execute_assets(): void {

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
	 * Popups redirection, return a new text replacing the normal expected text
	 * Use template_redirect hook to call it
	 *
	 * @since 4.0 Bots are banned for all popups, it's done here so no IMDbPHP calls for movies/people are done in case of redirect
	 * @since 4.0.1 Added bot banning if no referer, created method ban_bots_popups()
	 *
	 * @return string|Virtual_Page
	 */
	public function lumiere_popup_redirect_include( string $template ): string|Virtual_Page {

		$query_popup = get_query_var( 'popup' );

		// The query var doesn't exist, exit.
		if ( ! isset( $query_popup ) ) {
			return $template;
		}

		/**
		 * Start Plugins_Start class
		 * Is instanciated only if not instanciated already
		 * Use lumiere_set_plugins_array() in trait to set $plugins_active_names var in trait
		 */
		if ( count( $this->plugins_active_names ) === 0 ) {
			$this->activate_plugins();
		}

		// Make sure we use cache. User may have decided not to use cache, but we need it to accelerate the call.
		if ( $this->imdb_cache_values['imdbusecache'] === '0' ) {
			$this->plugins_classes_active['imdbphp']->activate_cache();
		}

		switch ( $query_popup ) {
			case 'film':
				// Check if bots.
				$this->ban_bots_popups();

				// Set the title.
				$filmid_sanitized = ''; // initialisation.

				// If mid but no film, do a query using the mid.
				if ( isset( $_GET['mid'] ) && ! isset( $_GET['film'] ) ) {
					$movieid_sanitized = esc_html( $_GET['mid'] );
					$movie = new Title( $movieid_sanitized, $this->plugins_classes_active['imdbphp'] );
					$filmid_sanitized = $movie->title();
				}

				// Sanitize and initialize $_GET['film']
				$film_sanitized = isset( $_GET['film'] ) ? $this->lumiere_name_htmlize( $_GET['film'] ) : ''; // Method in trait Data.

				// Get the film ID if it exists, if not get the film name
				$title_name = strlen( $filmid_sanitized ) > 0 ? $filmid_sanitized : $film_sanitized;

				/* translators: %1$s is a name */
				$title = sprintf( __( 'Informations about %1$s', 'lumiere-movies' ), $title_name ) . ' - Lumi&egrave;re movies';

				// Build the virtual page class
				return new Virtual_Page(
					$this->config_class->lumiere_urlstringfilms,
					new Popup_Movie(),
					esc_html( $title )
				);

			case 'person':
				// Check if bots.
				$this->ban_bots_popups();

				// Set the title.
				if ( isset( $_GET['mid'] ) ) {
					$mid_sanitized = sanitize_text_field( strval( $_GET['mid'] ) );
					$person = new Person( $mid_sanitized, $this->plugins_classes_active['imdbphp'] );
					$person_name_sanitized = $person->name();
				}

				$title = isset( $person_name_sanitized )
					/* translators: %1$s is a movie's title */
					? sprintf( __( 'Informations about %1$s', 'lumiere-movies' ), $person_name_sanitized ) . ' - Lumi&egrave;re movies'
					: __( 'Unknown - Lumière movies', 'lumiere-movies' );

				// Build the virtual page class
				return new Virtual_Page(
					$this->config_class->lumiere_urlstringperson,
					new Popup_Person(),
					esc_html( $title )
				);
			case 'search':
				// Check if bots.
				$this->ban_bots_popups();

				// Set the title.
				$filmname_sanitized = isset( $_GET['film'] ) ? ': [' . $_GET['film'] . ']' : __( 'No name entered', 'lumiere-movies' );
				/* translators: %1$s is the title of a movie */
				$title = sprintf( __( 'Lumiere Query Interface %1$s', 'lumiere-movies' ), ' ' . $filmname_sanitized );

				// Build the virtual page class
				return new Virtual_Page(
					$this->config_class->lumiere_urlstringsearch,
					new Popup_Search(),
					esc_html( $title )
				);
		}
		return $template;
	}

	/**
	 * Ban bots from getting Popups.
	 *
	 * 1/ Banned if certain conditions are met in class Ban_Bots::_construct() => action 'lumiere_maybe_ban_bots',
	 *  done before doing IMDbPHP queries in this class
	 * 2/ Ban if there is no HTTP_REFERER and user is not logged in Ban_Bots::_construct() => action 'lumiere_ban_bots_now', done here
	 *  Not putting the no HTTP_REFERER in Ban_Bots class, since do_action( 'lumiere_maybe_ban_bots' ) could be called
	 *      in taxonomy templates (those pages, like movie pages, should not ban bots, there is no reason to ban bots in full pages, only in popups)
	 * This method must be called inside the switch() function, when we know it's a popup. Otherwhise, the entire site could
	 *      become unavailable if no HTTP_REFERER was passed
	 * @since 4.0.1 Method added
	 * @return void Banned if conditions are met
	 */
	private function ban_bots_popups(): void {

		// Conditionally ban bots from getting the page, i.e. User Agent or IP.
		do_action( 'lumiere_maybe_ban_bots' );

		// Ban bots if no referer
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) && ! is_user_logged_in() ) {
			do_action( 'lumiere_ban_bots_now' );
		}
	}
}
