<?php declare( strict_types = 1 );
/**
 * Edit popups <head>
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
 * Edit <head> for popups
 *
 * @since 3.11
 */
class Head_Popups {

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

		$this->settings_class = new Settings();

		// Exist if it is not a popup.
		if ( $this->is_popup_page() === false ) {
			return;
		}

		$this->imdbphp_class = new Imdbphp();

		// Remove useless or uneeded actions
		$this->filters_and_actions();

		// Add Lumière <head> data in popups
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
	 * Detect if the current page is a popup
	 *
	 * @since 3.11.4
	 * @return bool True if the page is a Lumiere popup
	 */
	private function is_popup_page(): bool {
		if (
			isset( $_SERVER['REQUEST_URI'] )
			&&
			(
				str_contains( $_SERVER['REQUEST_URI'], $this->settings_class->lumiere_urlstringfilms )
				|| str_contains( $_SERVER['REQUEST_URI'], $this->settings_class->lumiere_urlstringsearch )
				|| str_contains( $_SERVER['REQUEST_URI'], $this->settings_class->lumiere_urlstringperson )
			)
		) {
			return true;
		}
		return false;
	}

	/**
	 * Run all modification to the head
	 *
	 * @since 3.11.4
	 * @return void The class was instanciated
	 */
	private function filters_and_actions(): void {

		// Remove aioseo if it exists.
		if ( defined( 'AIOSEO_PHP_VERSION_DIR' ) ) {
			add_filter( 'aioseo_disable', '__return_true' );
		}

		// Remove OceanWP useless classes in popups
		$current_theme = wp_get_theme();
		if (
			strlen( $current_theme->get( 'Name' ) ) > 0
			&& str_contains( strtolower( $current_theme->get( 'Name' ) ), 'oceanwp' ) === true
			&& class_exists( '\OCEANWP_Theme_Class' )
		) {
			remove_action( 'after_setup_theme', [ '\OCEANWP_Theme_Class', 'classes' ], 4 );
			remove_action( 'after_setup_theme', [ '\OCEANWP_Theme_Class', 'theme_setup' ], 10 );
			remove_action( 'widgets_init', [ '\OCEANWP_Theme_Class', 'register_sidebars' ] );
			remove_action( 'wp_enqueue_scripts', [ '\OCEANWP_Theme_Class', 'theme_css' ] );
		}

		// Prevent WordPress from inserting a few things
		remove_action( 'wp_head', 'rel_canonical' ); // remove canonical
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 ); // remove shortlink
		remove_action( 'wp_head', 'start_post_rel_link', 10 ); // remove random post link
		remove_action( 'wp_head', 'parent_post_rel_link', 10 ); // remove parent post link
		remove_action( 'wp_head', 'rsd_link' ); // remove really simple discovery link
		remove_action( 'wp_head', 'adjacent_posts_rel_link', 10 ); // remove the next and previous post links
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
		remove_action( 'wp_head', 'feed_links', 2 ); // remove rss feed links (make sure you add them in yourself if youre using feedblitz or an rss service)
		remove_action( 'wp_head', 'feed_links_extra', 3 ); // removes all extra rss feed links
		remove_action( 'wp_head', 'wp_generator' ); // remove wordpress version
		remove_action( 'wp_head', 'wp_site_icon', 99 ); // Prevent WordPress from inserting favicons.
	}

	/**
	 * Edit tags in <head> of popups
	 */
	public function lumiere_add_metas_popups(): void {

		$my_canon = '';
		$sanitized_film = filter_input( INPUT_GET, 'film', FILTER_SANITIZE_URL ) ?? false;
		$sanitized_info = filter_input( INPUT_GET, 'info', FILTER_SANITIZE_URL ) ?? false;
		$sanitized_mid = filter_input( INPUT_GET, 'mid', FILTER_SANITIZE_URL ) ?? false;

		echo "\t\t" . '<!-- Lumière! Movies -->';

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

		echo "\n\t\t" . '<!-- /Lumière! Movies -->' . "\n";
	}
}
