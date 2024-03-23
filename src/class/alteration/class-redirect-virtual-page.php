<?php declare( strict_types = 1 );
/**
 * Redirect to a virtual page
 *
 * @author      Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright   2023, Lost Highway
 *
 * @version     1.0
 * @package lumiere-movies
 */

namespace Lumiere\Alteration;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Settings;
use Lumiere\Plugins\Imdbphp;
use Lumiere\Frontend\Popups\Popup_Person;
use Lumiere\Frontend\Popups\Popup_Movie;
use Lumiere\Frontend\Popups\Popup_Search;
use Lumiere\Admin\Search;
use Lumiere\Tools\Data;
use Lumiere\Alteration\Virtual_Page;
use Imdb\Title;
use Imdb\Person;

/**
 * Redirect to a virtual page retrieving (for IMDB-related) the name and sending it and also sending the url to be created
 * Currently redirect to popups and class search
 *
 * @see \Lumiere\Alteration\Virtual_Page that allows the pages here to be made
 * @phpstan-import-type OPTIONS_CACHE from Settings
 */
class Redirect_Virtual_Page {

	/**
	 * Traits
	 */
	use Data;

	/**
	 * Lumiere\Imdbphp class
	 */
	private Imdbphp $imdbphp_class;

	/**
	 * Lumiere\Settings class
	 */
	private Settings $settings_class;

	/**
	 * @phpstan-var OPTIONS_CACHE $imdb_cache_values
	 */
	private array $imdb_cache_values;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );
		$this->settings_class = new Settings();
		$this->imdbphp_class = new Imdbphp();

		// Redirect to popups
		add_filter( 'template_redirect', [ $this, 'lumiere_popup_redirect_include' ], 9 ); // Must be executed with priority 9, one less than the classes called

		// Redirect class-search.php.
		add_filter( 'template_redirect', [ $this, 'lumiere_search_redirect' ] );
	}

	/**
	 * Static instanciation of the class
	 * Needed to be called in add_actions()
	 *
	 * @return void The class was instanciated
	 */
	public static function lumiere_static_start(): void {
		$redirect_class = new self();
	}

	/**
	 * Redirect search popup in admin
	 *
	 * @return Virtual_Page|string The virtual page if success, the template called if failed
	 */
	public function lumiere_search_redirect( string $template ): Virtual_Page|string {

		// Display only in admin area.
		if ( 0 === stripos( $_SERVER['REQUEST_URI'] ?? '', site_url( '', 'relative' ) . Settings::GUTENBERG_SEARCH_URL ) ) {

			// Build the virtual page class
			return new Virtual_Page(
				site_url() . Settings::GUTENBERG_SEARCH_URL,
				new Search(),
				'Lumiere Query Interface'
			);
		}
		return $template;
	}

	/**
	 * Popups redirection
	 * @since 4.0 Bots are banned for all popups, it's done here so no IMDbPHP calls for movies/people are done in case of redirect
	 * @since 4.0.1 Added bot banning if no referer, created method ban_bots_popups()
	 *
	 * @TODO Sanitization of GETs is a joke, use proper functions!
	 * @return string|Virtual_Page
	 */
	public function lumiere_popup_redirect_include( string $template ): string|Virtual_Page {

		$query_popup = get_query_var( 'popup' );

		// The query var doesn't exist, exit.
		if ( ! isset( $query_popup ) ) {
			return $template;
		}

		// Make sure we use cache. User may have decided not to use cache, but we need it to accelerate the call.
		if ( $this->imdb_cache_values['imdbusecache'] === '0' ) {
			$this->imdbphp_class->cachedir = $this->imdb_cache_values['imdbcachedir'];
		}

		switch ( $query_popup ) {
			case 'film':
				// Check if bots.
				$this->ban_bots_popups();

				// Set the title.
				$filmid_sanitized = ''; // initialisation.

				// If mid but no film, do a query using the mid.
				if ( ( isset( $_GET['mid'] ) ) && ( ! isset( $_GET['film'] ) ) ) {

					$movieid_sanitized = sanitize_text_field( strval( $_GET['mid'] ) );
					$movie = new Title( $movieid_sanitized, $this->imdbphp_class );
					$filmid_sanitized = esc_html( $movie->title() );
				}
				// Sanitize and initialize $_GET['film']
				$film_sanitized = isset( $_GET['film'] ) ? $this->lumiere_name_htmlize( $_GET['film'] ) : ''; // Method in trait Data.
				// Get the film ID if it exists, if not get the film name
				$title_name = strlen( $filmid_sanitized ) !== 0 ? $filmid_sanitized : $film_sanitized;

				$title = esc_html__( 'Informations about ', 'lumiere-movies' ) . $title_name . ' - Lumi&egrave;re movies';

				// Build the virtual page class
				return new Virtual_Page(
					$this->settings_class->lumiere_urlstringfilms,
					new Popup_Movie(),
					$title
				);

			case 'person':
				// Check if bots.
				$this->ban_bots_popups();

				// Set the title.
				if ( isset( $_GET['mid'] ) ) {
					$mid_sanitized = sanitize_text_field( strval( $_GET['mid'] ) );
					$person = new Person( $mid_sanitized, $this->imdbphp_class /* the class was forced to include the cache dir */ );
					$person_name_sanitized = sanitize_text_field( $person->name() );
				}
				$title = isset( $person_name_sanitized )
				? esc_html__( 'Informations about ', 'lumiere-movies' ) . $person_name_sanitized . ' - Lumi&egrave;re movies'
				: esc_html__( 'Unknown', 'lumiere-movies' ) . '- Lumi&egrave;re movies';

				// Build the virtual page class
				return new Virtual_Page(
					$this->settings_class->lumiere_urlstringperson,
					new Popup_Person(),
					$title
				);
			case 'search':
				// Check if bots.
				$this->ban_bots_popups();

				// Set the title.
				$filmname_sanitized = isset( $_GET['film'] ) ? ': [' . sanitize_text_field( $_GET['film'] ) . ']' : 'No name entered';

				// Build the virtual page class
				return new Virtual_Page(
					$this->settings_class->lumiere_urlstringsearch,
					new Popup_Search(),
					'Lumiere Query Interface ' . $filmname_sanitized
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
	 * Not putting the no HTTP_REFERER in Ban_Bots class, since do_action( 'lumiere_maybe_ban_bots' ) could be called
	 *  in taxonomy templates (those pages, like movie pages, should not ban bots, there is no reason to ban bots in full pages, only in popups)
	 * This method must be called inside the switch() function, when we know it's a popup. Otherwhise, the entire site could
	 *  become unavailable if no HTTP_REFERER was passed
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
