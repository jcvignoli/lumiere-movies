<?php declare( strict_types = 1 );
/**
 * Detect and ban bots
 *
 * @author Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2023, Lost Highway
 *
 * @version 1.0
 * @since 3.11.4
 * @package lumiere-movies
 */

namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Class that detects IPs, User agent, HTTP_REFERER and bans those found as bots
 * Is usefull to prevent the access to popups that create a lot of cache files
 *
 * @since 4.2.3 two conditional methods in __construct, no automatic ban function available to outside anymore (ban_bot_now() is now private)
 * @since 4.3 As we're using nonces for accessing to popups, there should be limited interest in the class, except in regards to self::maybe_ban_noreferrer()
 */
class Ban_Bots {

	/**
	 * List of IPs not respecting the rules
	 */
	private const BLACK_LIST_IP = [
		'47.76.35.19', // Crazy bot from Alibaba in HK.
	];

	/**
	 * List of bots not respecting the rules
	 *
	 * @since 4.0 Added Bingbot and Googlebot
	 */
	private const BLACK_LIST_AGENT = [
		/**
		 * Those bots do not respect the rules
		 */
		'bytespider|bytedance|YandexBot|GPTBot|bingbot|Googlebot',
		/**
		 * From 7G https://perishablepress.com/7g-firewall/
		 */
		'sux0r|suzukacz|suzuran|takeout|teleport|telesoft|true_robots|turingos|turnit|vampire|vikspider|voideye|webleacher|webreaper|webstripper|webvac|webviewer|webwhacker',
		'winhttp|wwwoffle|woxbot|xaldon|xxxyy|yamanalab|yioopbot|youda|zeus|zmeu|zune|zyborg',
	];

	/**
	 * Constructor
	 * Add types of conditional banning here, no automatic ban available, make a conditional function instead
	 * Actions must be executed here, popups can't since they're executed in template filter
	 */
	public function __construct() {

		add_action( 'lum_maybe_ban_bots_general', [ $this, 'maybe_ban_bots_general' ] );
		add_action( 'lum_maybe_ban_bots_noreferrer', [ $this, 'maybe_ban_noreferrer' ] );

		// Execute: conditionally ban bots from getting the page, i.e. User Agent or IP.
		do_action( 'lum_maybe_ban_bots_general' );

		// Execute: ban bots if no referer.
		do_action( 'lum_maybe_ban_bots_noreferrer' );
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
	 * Process list of bots registered in BLACK_LIST_*, exit if it one of the bad bots
	 * This is an action meant to be called with do_action( 'lum_maybe_ban_bots_general' ) that will assess whether to ban the user
	* Not putting the no HTTP_REFERER condition here, since do_action( 'lum_maybe_ban_bots_general' ) can be called i.e. by taxonomy pages and they must be accessible even if there is no HTTP_REFERER
	 */
	public function maybe_ban_bots_general(): void {
		$this->maybe_ban_ip( self::BLACK_LIST_IP );
		$this->maybe_ban_useragent( self::BLACK_LIST_AGENT );
	}

	/**
	 * Process list of bots registered in BLACK_LIST_AGENT, exit if it one of the bad bots
	 * @param array<string> $banned_recipients The list of the banned recipients (USER_AGENT)
	 * @return void The user is banned if found in any of those lists
	 */
	private function maybe_ban_useragent( array $banned_recipients ): void {
		$agent = esc_url_raw( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
		foreach ( $banned_recipients as $bot ) {
			if ( preg_match( "~$bot~i", $agent ) === 1 ) {
				$this->ban_bot_now();
			}
		}
	}

	/**
	 * Detect Client IP
	 */
	private function get_user_ip(): string {
		$ip_address = null;
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && strlen( esc_url_raw( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) ) ) > 0 ) {
			$ip_address = esc_url_raw( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && strlen( esc_url_raw( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) > 0 ) {
			$ip_address = esc_url_raw( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} else {
			$ip_address = esc_url_raw( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		}
		return $ip_address;
	}

	/**
	 * Process list of IPs registered in BLACK_LIST_IP, exit if it one of the bad ips
	 * @param array<string> $banned_recipients The list of the banned recipients (HTTP_CLIENT_IP, HTTP_X_FORWARDED_FOR or REMOTE_ADDR)
	 * @return void The user is banned if found in any of those lists
	 */
	private function maybe_ban_ip( array $banned_recipients ): void {
		$ip_address = $this->get_user_ip();
		foreach ( $banned_recipients as $bot ) {
			if ( $ip_address === $bot ) {
				$this->ban_bot_now();
			}
		}
	}

	/**
	 * If there is no referrer and the user is not logged in, ban bot
	 * Not included in maybe_ban_bots_general() as some parts of the website may want to not ban if there is no HTTP_REFERER
	 *
	 * @return void The user is banned if conditions are met
	 */
	public function maybe_ban_noreferrer(): void {
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) && ! is_user_logged_in() ) {
			$this->ban_bot_now();
		}
	}

	/**
	 * Display a 400 error
	 * This immediately bans the user
	 * @since 4.1 Status changed from 403 to 400, removed translation of the $text_ban
	 */
	private function ban_bot_now(): void {

		$text_ban = '<h1>Prevented a bad request</h1>';

		$text_ban .= '<p>If you think it\'s a mistake, please contact the administrator via a proxy server.</p>';

		wp_die(
			wp_kses(
				$text_ban,
				[
					'p' => [],
					'h1' => [],
					'a' => [ 'href' => [] ],
				]
			),
			esc_html__( 'Lumière Popups Access Error', 'lumiere-movies' ),
			[
				'response' => 400,
				'link_url' => esc_url( site_url() ),
				'link_text' => esc_html__( 'Back home', 'lumiere-movies' ),
			]
		);
	}
}

