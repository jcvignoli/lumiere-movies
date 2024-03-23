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

namespace Lumiere\Frontend\Popups;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Frontend\Main;
use Imdb\Person;

/**
 * Edit <head> for popups
 *
 * @since 3.11 created
 * @since 4.0.3 removed plugins related matter, moved to relevant classes, added activate plugins and trait Main
 */
class Head_Popups {

	/**
	 * Traits
	 */
	use \Lumiere\Frontend\Main {
		Main::__construct as public __constructFrontend;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		// Construct Frontend Main trait.
		$this->__constructFrontend( __CLASS__ );

		// Exit if it is not a popup.
		if ( $this->is_popup_page() === false ) {
			return;
		}

		/**
		 * Start Plugins_Start class
		 * Is instanciated only if not instanciated already
		 * Use lumiere_set_plugins_array() in trait to set $plugins_active_names var in trait
		 */
		if ( count( $this->plugins_active_names ) === 0 ) {
			$this->activate_plugins();
		}

		add_action( 'wp_head', [ $this, 'display_plugins_log' ], 99 );

		// Remove useless or uneeded actions
		$this->filters_and_actions();

		// Add Lumière <head> data in popups
		add_action( 'wp_head', [ $this, 'lumiere_add_metas_popups' ], 7 ); // must be priority 7, one less than earliest wp_head.
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
	 * Display which plugins are in use
	 *
	 * @return void
	 */
	public function display_plugins_log(): void {

		// Log Plugins_Start, $this->plugins_classes_active in Main trait.
		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] The following plugins compatible with Lumière! are in use: [' . join( ', ', $this->plugins_active_names ) . ']' );
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
				str_contains( $_SERVER['REQUEST_URI'], $this->config_class->lumiere_urlstringfilms )
				|| str_contains( $_SERVER['REQUEST_URI'], $this->config_class->lumiere_urlstringsearch )
				|| str_contains( $_SERVER['REQUEST_URI'], $this->config_class->lumiere_urlstringperson )
			)
		) {
			return true;
		}
		return false;
	}

	/**
	 * Run all modification to the head
	 *
	 * @return void The class was instanciated
	 * @since 3.11.4 created
	 * @since 4.0.3 removed OceanWP specific actions remove, popups are built differently now
	 */
	private function filters_and_actions(): void {

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
		$sanitized_film = filter_input( INPUT_GET, 'film', FILTER_SANITIZE_URL );
		$sanitized_info = filter_input( INPUT_GET, 'info', FILTER_SANITIZE_URL );
		$sanitized_mid = filter_input( INPUT_GET, 'mid', FILTER_SANITIZE_URL );

		echo "\n\t\t" . '<!-- Lumière! Movies -->';

		// Add nofollow for robots.
		echo "\n" . '<meta name="robots" content="nofollow" />';

		// Add favicons.
		echo "\n" . '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( $this->config_class->lumiere_pics_dir . 'favicon/apple-touch-icon.png' ) . '" />';
		echo "\n" . '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( $this->config_class->lumiere_pics_dir . 'favicon/favicon-32x32.png' ) . '" />';
		echo "\n" . '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url( $this->config_class->lumiere_pics_dir . 'favicon/favicon-16x16.png' ) . '" />';
		echo "\n" . '<link rel="manifest" href="' . esc_url( $this->config_class->lumiere_pics_dir . 'favicon/site.webmanifest' ) . '" />';

		// Add canonical.
		// Canonical for search popup.
		if ( 0 === stripos( $_SERVER['REQUEST_URI'] ?? '', site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringsearch ) && $sanitized_film !== false ) {

			$my_canon = $this->config_class->lumiere_urlpopupsearch . '?film=' . $sanitized_film . '&norecursive=yes';
			echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';
		}

		// Canonical for movies popups.
		if ( str_contains( $_SERVER['REQUEST_URI'] ?? '', $this->config_class->lumiere_urlstringfilms ) && is_string( $sanitized_mid ) ) {

			$url_str_info = $sanitized_info === false ? '' : '&info=' . $sanitized_info;
			$url_str_film = $sanitized_film === false ? '' : '&film=' . $sanitized_film;
			$str_film = $sanitized_film === false ? '' : $sanitized_film . '/';
			$my_canon = $this->config_class->lumiere_urlpopupsfilms . $str_film . '?mid=' . $sanitized_mid . $url_str_film . $url_str_info;

			echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';

			if ( strlen( $str_film ) > 0 ) {
				echo "\n" . '<meta property="article:tag" content="' . esc_attr( $str_film ) . '" />';
			}
		}

		// Canonical for people popups.
		if ( str_contains( $_SERVER['REQUEST_URI'] ?? '', $this->config_class->lumiere_urlstringperson ) && is_string( $sanitized_mid ) ) {

			$url_str_info = $sanitized_info === false ? '' : '&info=' . $sanitized_info;
			$my_canon = $this->config_class->lumiere_urlpopupsperson . $sanitized_mid . '/?mid=' . $sanitized_mid . $url_str_info;

			echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';

			$person = new Person( $sanitized_mid, $this->imdbphp_class );
			if ( strlen( $person->name() ) > 0 ) {
				echo "\n" . '<meta property="article:tag" content="' . esc_attr( $person->name() ) . '" />';
			}
		}

		echo "\n\t\t" . '<!-- /Lumière! Movies -->' . "\n";
	}
}
