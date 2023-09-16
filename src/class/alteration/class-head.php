<?php declare( strict_types = 1 );
/**
 * Edit <head>
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
use Imdb\Person;

/**
 * Edit <head>
 * Currently for *popups*
 *
 * This class makes sure that Lumière! rewriting rules are
 * 1/ added only if they don't exist in WP options table
 * 2/ Rules for Polylang are always installed (even if Polylang is not)
 * 3/ On closing the class, check if the rules are correctly added. If they aren't, a flush_rewrite_rules() is done
 * @since 3.11
 */
class Head {

	/**
	 * Lumiere\Imdbphp class
	 *
	 */
	private Imdbphp $imdbphp_class;

	/**
	 * Lumiere\Settings class
	 */
	private Settings $settings_class;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->imdbphp_class = new Imdbphp();
		$this->settings_class = new Settings();

		// Edit <head> in popups
		add_action( 'wp_head', [ $this, 'lumiere_add_metas_popups' ], 5 );
	}

	/**
	 * Static instanciation of the class
	 * Needed to be called in add_actions()
	 *
	 * @return void The class was instanciated
	 */
	public static function lumiere_static_start(): void {
		$static_start = new self();
	}

	/**
	 * Edit tags in <head> of popups
	 */
	public function lumiere_add_metas_popups(): void {

		$my_canon = '';
		$sanitized_film = filter_input( INPUT_GET, 'film', FILTER_SANITIZE_URL ) ?? false;
		$sanitized_info = filter_input( INPUT_GET, 'info', FILTER_SANITIZE_URL ) ?? false;
		$sanitized_mid = filter_input( INPUT_GET, 'mid', FILTER_SANITIZE_URL ) ?? false;

		// Change the metas only for popups.
		if (
			( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settings_class->lumiere_urlstringfilms ) )
			|| ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settings_class->lumiere_urlstringsearch ) )
			|| ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settings_class->lumiere_urlstringperson ) )
			) {

			echo "\t\t" . '<!-- Added by Lumière! Movies -->';

			// Add nofollow for robots.
			echo "\n" . '<meta name="robots" content="nofollow" />';

			// Add favicons.
			echo "\n" . '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( $this->settings_class->lumiere_pics_dir . 'favicon/apple-touch-icon.png' ) . '" />';
			echo "\n" . '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( $this->settings_class->lumiere_pics_dir . 'favicon/favicon-32x32.png' ) . '" />';
			echo "\n" . '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url( $this->settings_class->lumiere_pics_dir . 'favicon/favicon-16x16.png' ) . '" />';
			echo "\n" . '<link rel="manifest" href="' . esc_url( $this->settings_class->lumiere_pics_dir . 'favicon/site.webmanifest' ) . '" />';

			// Add canonical.
			// Canonical for search popup.
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->settings_class->lumiere_urlstringsearch ) && $sanitized_film !== false ) {

				$my_canon = $this->settings_class->lumiere_urlpopupsearch . '?film=' . $sanitized_film . '&norecursive=yes';
				echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';
			}

			// Canonical for movies popups.
			if ( str_contains( $_SERVER['REQUEST_URI'], $this->settings_class->lumiere_urlstringfilms ) && $sanitized_mid !== false ) {

				$url_str_info = $sanitized_info === false ? '' : '&info=' . $sanitized_info;
				$url_str_film = $sanitized_film === false ? '' : '&film=' . $sanitized_film;
				$str_film = $sanitized_film === false ? '' : $sanitized_film . '/';
				$my_canon = $this->settings_class->lumiere_urlpopupsfilms . $str_film . '?mid=' . $sanitized_mid . $url_str_film . $url_str_info;

				echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';

				if ( strlen( $str_film ) > 0 ) {
					echo "\n" . '<meta property="article:tag" content="' . esc_attr( $str_film ) . '" />';
				}
			}

			// Canonical for people popups.
			if ( str_contains( $_SERVER['REQUEST_URI'], $this->settings_class->lumiere_urlstringperson ) && $sanitized_mid !== false ) {

				$url_str_info = $sanitized_info === false ? '' : '&info=' . $sanitized_info;
				$my_canon = $this->settings_class->lumiere_urlpopupsperson . $sanitized_mid . '/?mid=' . $sanitized_mid . $url_str_info;

				echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';

				$person = new Person( $sanitized_mid, $this->imdbphp_class );
				if ( strlen( $person->name() ) > 0 ) {
					echo "\n" . '<meta property="article:tag" content="' . esc_attr( $person->name() ) . '" />';
				}
			}

			echo "\n\t\t" . '<!-- /Lumiere Movies -->' . "\n";

			// Prevent WordPress from inserting a canonical tag.
			remove_action( 'wp_head', 'rel_canonical' );

			// Prevent WordPress from inserting favicons.
			remove_action( 'wp_head', 'wp_site_icon', 99 );
		}
	}
}
