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
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;
use Lumiere\Frontend\Layout\Output_Popup;
use Lumiere\Tools\Validate_Get;
use Lumiere\Config\Get_Options;
use Lumiere\Plugins\Plugins_Start;

/**
 * Edit <head> for popups
 * Add stylesheets, css, check if popup, nonce, get plugins, get link_maker, use ban_bots, add metas html
 * Extended by children classes
 *
 * @since 3.11 created
 * @since 4.3 Is parent class, bots and nonce validation moved from class Frontend to here
 *
 * @phpstan-import-type PLUGINS_AUTO_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_AUTO_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_MANUAL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 */
class Head_Popups {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Lumière plugins started
	 *
	 * @var array<string, object>
	 * @phpstan-var array{'imdbphp': PLUGINS_MANUAL_CLASSES, PLUGINS_AUTO_KEYS: PLUGINS_AUTO_CLASSES}
	 */
	protected array $plugins_classes_active;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output_Popup $output_popup = new Output_Popup(),
	) {

		/**
		 * Get the properties.
		 */
		$this->start_main_trait(); // In Trait Main.

		// Is a popup or exit.
		if ( $this->is_popup_page() === false ) { // In Trait Main.
			wp_die( esc_html__( 'Lumière Movies: This should not have happened.', 'lumiere-movies' ) );
		}

		// Check nonce or exit.
		$this->check_nonce();

		/**
		 * Get an array with all objects plugins
		 * Always loads IMDBPHP plugin
		 * @psalm-suppress InvalidPropertyAssignmentValue
		 * @phpstan-ignore assign.propertyType (Array does not have offset 'imdbphp'. => it does, just called using it!)
		 */
		$this->plugins_classes_active = ( new Plugins_Start( [ 'imdbphp' ] ) )->plugins_classes_active;

		/**
		 * Ban bots
		 */
		add_action( 'wp_head', [ 'Lumiere\Tools\Ban_Bots', 'lumiere_static_start' ], 11 );

		// Remove useless or unwanted filters and actions
		$this->remove_filters_and_actions();

		// Add Lumière <head> data in popups
		add_action( 'wp_head', [ $this, 'add_metas_popups' ], 7 ); // must be priority 7
	}

	/**
	 * Check the nonce
	 * @return void Die if nonce is invalide
	 */
	private function check_nonce(): void {

		// Nonce. Always valid if admin is connected.
		$nonce_valid = ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ) ) > 0 ) || is_user_logged_in() === true ? true : false; // Created in Abstract_Link_Maker class.

		// If nonce is invalid or no title was provided, exit.
		if ( $nonce_valid === false ) {
			wp_die( esc_html__( 'Invalid or missing nonce.', 'lumiere-movies' ), 'Lumière Movies', [ 'response' => 400 ] );
		}
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
	public function add_metas_popups(): void {

		// Make sure we use cache. User may have decided not to use cache, but we need to accelerate the call.
		$this->plugins_classes_active['imdbphp']->activate_cache();

		$my_canon = '';
		$sanitized_film = Validate_Get::sanitize_url( 'film' );
		$sanitized_info = Validate_Get::sanitize_url( 'info' );
		$sanitized_mid = Validate_Get::sanitize_url( 'mid' );

		echo "\n\t\t" . '<!-- Lumière! Movies -->';

		// Add nofollow for robots.
		echo "\n" . '<meta name="robots" content="nofollow" />';

		// Add favicons.
		echo "\n" . '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( Get_Options::LUM_PICS_URL . 'favicon/apple-touch-icon.png' ) . '" />';
		echo "\n" . '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( Get_Options::LUM_PICS_URL . 'favicon/favicon-32x32.png' ) . '" />';
		echo "\n" . '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url( Get_Options::LUM_PICS_URL . 'favicon/favicon-16x16.png' ) . '" />';
		echo "\n" . '<link rel="manifest" href="' . esc_url( Get_Options::LUM_PICS_URL . 'favicon/site.webmanifest' ) . '" />';

		// Add canonical.
		// Canonical for search popup.
		if ( 0 === stripos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), site_url( '', 'relative' ) . Get_Options::get_popup_url( 'movie_search' ) ) && $sanitized_film !== null ) {

			$my_canon = Get_Options::get_popup_url( 'movie_search', site_url() ) . '?film=' . $sanitized_film;
			echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';
		}

		// Canonical for movies popups.
		if ( str_contains( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), Get_Options::get_popup_url( 'film' ) ) && is_string( $sanitized_mid ) ) {

			$url_str_info = $sanitized_info === null ? '' : '&info=' . $sanitized_info;
			$url_str_film = $sanitized_film === null ? '' : '&film=' . $sanitized_film;
			$str_film = $sanitized_film === null ? '' : $sanitized_film . '/';
			$my_canon = Get_Options::get_popup_url( 'film', site_url() ) . $str_film . '?mid=' . $sanitized_mid . $url_str_film . $url_str_info;

			echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';

			if ( strlen( $str_film ) > 0 ) {
				echo "\n" . '<meta property="article:tag" content="' . esc_attr( $str_film ) . '" />';
			}
		}

		// Canonical for people popups.
		if ( str_contains( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), Get_Options::get_popup_url( 'person' ) ) && is_string( $sanitized_mid ) ) {

			$url_str_info = $sanitized_info === null ? '' : '&info=' . $sanitized_info;
			$my_canon = Get_Options::get_popup_url( 'person', site_url() ) . $sanitized_mid . '/?mid=' . $sanitized_mid . $url_str_info;

			echo "\n" . '<link rel="canonical" href="' . esc_url_raw( $my_canon ) . '" />';

			$person = $this->plugins_classes_active['imdbphp']->get_name_class( $sanitized_mid, $this->logger->log );
			$name = $person->name();
			if ( $name !== null && strlen( $name ) > 0 ) {
				echo "\n" . '<meta property="article:tag" content="' . esc_attr( $name ) . '" />';
			}
		}

		echo "\n\t\t" . '<!-- /Lumière! Movies -->' . "\n";

		$this->logger->log->debug( '[Head_Popups] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_classes_active ) ) . ']' );
	}

}
