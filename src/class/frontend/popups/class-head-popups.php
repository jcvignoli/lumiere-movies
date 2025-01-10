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
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;
use Lumiere\Tools\Validate_Get;
use Imdb\Name;

/**
 * Edit <head> for popups
 * Meant to be extended on children classes
 *
 * @since 3.11 created
 * @since 4.1 removed plugins related matter, moved to relevant classes, added activate plugins and trait Main
 * @since 4.3 Is parent class
 */
class Head_Popups {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get the properties from Main trait.
		$this->start_main_trait();

		// Exit if it is not a popup.
		if ( $this->is_popup_page() === false ) { // In Trait Main.
			return;
		}

		// Get Lumière plugins.
		add_action( 'wp_head', [ $this, 'set_plugins_if_needed' ], 7 ); // must be priority 7, class called with template_redirect.

		// Display the plugins active.
		add_action( 'wp_head', [ $this, 'display_plugins_log' ] );

		// Remove useless or unwanted filters and actions
		$this->remove_filters_and_actions();

		// Add Lumière <head> data in popups
		add_action( 'wp_head', [ $this, 'lumiere_add_metas_popups' ], 7 ); // must be priority 7, class called with template_redirect.
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
	 * Start Plugins_Start class
	 * Is instanciated only if not instanciated already
	 * Always loads IMDBPHP plugin
	 */
	public function set_plugins_if_needed(): void {
		$this->maybe_activate_plugins(); // In Trait Main.
	}

	/**
	 * Display which plugins are in use
	 *
	 * @return void
	 */
	public function display_plugins_log(): void {

		// Log Plugins_Start, $this->plugins_classes_active in Main trait.
		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_classes_active ) ) . ']' );
	}

	/**
	 * Remove filters and actions that are of no use
	 *
	 * @return void Filter and action were processed
	 * @since 3.11.4 created
	 */
	private function remove_filters_and_actions(): void {

		// Prevent WordPress from inserting a few things.
		remove_action( 'wp_head', 'rel_canonical' ); // remove canonical
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 ); // remove shortlink
		remove_action( 'wp_head', 'start_post_rel_link', 10 ); // remove random post link
		remove_action( 'wp_head', 'parent_post_rel_link', 10 ); // remove parent post link
		remove_action( 'wp_head', 'rsd_link' ); // remove really simple discovery link
		remove_action( 'wp_head', 'adjacent_posts_rel_link', 10 ); // remove the next and previous post links
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
		remove_action( 'wp_head', 'feed_links', 2 ); // remove rss feed links (make sure you add them in yourself if youre using feedblitz or an rss service)
		remove_action( 'wp_head', 'feed_links_extra', 3 ); // removes all extra rss feed links
		remove_action( 'wp_head', 'wp_generator' ); // remove WordPress version
		remove_action( 'wp_head', 'wp_site_icon', 99 ); // Prevent WordPress from inserting favicons.

		/**
		 * Remove admin bar if user is logged in.
		 * There is no need for such a function in pages that can't be edited
		 */
		if ( is_user_logged_in() === true ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}

	/**
	 * Edit tags in <head> of popups
	 */
	public function lumiere_add_metas_popups(): void {

		$my_canon = '';
		$sanitized_film = Validate_Get::sanitize_url( 'film' );
		$sanitized_info = Validate_Get::sanitize_url( 'info' );
		$sanitized_mid = Validate_Get::sanitize_url( 'mid' );

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
		if ( 0 === stripos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), site_url( '', 'relative' ) . $this->config_class->lumiere_urlstringsearch ) && $sanitized_film !== null ) {

			$my_canon = $this->config_class->lumiere_urlpopupsearch . '?film=' . $sanitized_film;
			echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';
		}

		// Canonical for movies popups.
		if ( str_contains( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), $this->config_class->lumiere_urlstringfilms ) && is_string( $sanitized_mid ) ) {

			$url_str_info = $sanitized_info === null ? '' : '&info=' . $sanitized_info;
			$url_str_film = $sanitized_film === null ? '' : '&film=' . $sanitized_film;
			$str_film = $sanitized_film === null ? '' : $sanitized_film . '/';
			$my_canon = $this->config_class->lumiere_urlpopupsfilms . $str_film . '?mid=' . $sanitized_mid . $url_str_film . $url_str_info;

			echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';

			if ( strlen( $str_film ) > 0 ) {
				echo "\n" . '<meta property="article:tag" content="' . esc_attr( $str_film ) . '" />';
			}
		}

		// Canonical for people popups.
		if ( str_contains( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), $this->config_class->lumiere_urlstringperson ) && is_string( $sanitized_mid ) ) {

			$url_str_info = $sanitized_info === null ? '' : '&info=' . $sanitized_info;
			$my_canon = $this->config_class->lumiere_urlpopupsperson . $sanitized_mid . '/?mid=' . $sanitized_mid . $url_str_info;

			echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';

			$person = new Name( $sanitized_mid, $this->plugins_classes_active['imdbphp'], $this->logger->log() );
			if ( strlen( $person->name() ) > 0 ) {
				echo "\n" . '<meta property="article:tag" content="' . esc_attr( $person->name() ) . '" />';
			}
		}

		echo "\n\t\t" . '<!-- /Lumière! Movies -->' . "\n";
	}
}
