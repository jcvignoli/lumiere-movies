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
use Lumiere\Frontend\Popup_Person;
use Lumiere\Frontend\Popup_Movie;
use Lumiere\Frontend\Popup_Search;
use Lumiere\Search;
use Lumiere\Tools\Utils;
use Imdb\Title;
use Imdb\Person;

/**
 * Redirect to a virtual page
 *
 * @phpstan-import-type OPTIONS_CACHE from Settings
 */
class Redirect_Virtual_Page {

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

		add_filter( 'template_redirect', [ $this, 'lumiere_popup_redirect_include' ], 2 ); // Must be executed with priority 2, 1 more of what the class was called

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
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . Settings::GUTENBERG_SEARCH_URL ) ) {

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
		if ( ! isset( $this->imdbphp_class->cachedir ) ) {
			$this->imdbphp_class->cachedir = $this->imdb_cache_values['imdbcachedir'];
		}

		switch ( $query_popup ) {
			case 'film':
				// Set the title.
				$filmid_sanitized = ''; // initialisation.

				// If mid but no film, do a query using the mid.
				if ( ( isset( $_GET['mid'] ) ) && ( ! isset( $_GET['film'] ) ) ) {

					$movieid_sanitized = sanitize_text_field( strval( $_GET['mid'] ) );
					$movie = new Title( $movieid_sanitized, $this->imdbphp_class );
					$filmid_sanitized = esc_html( $movie->title() );
				}
				// Sanitize and initialize $_GET['film']
				$film_sanitized = isset( $_GET['film'] ) ? Utils::lumiere_name_htmlize( $_GET['film'] ) : '';
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
}
