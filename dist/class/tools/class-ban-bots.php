<?php declare( strict_types = 1 );
/**
 * Detect and ban bots
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2023, Lost Highway
 *
 * @version       1.0
 * @since 3.11.4
 * @package lumiere-movies
 */

namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

/**
 * Class that detects IPs, User agent and bans those who are declared as bots
 * Is usefull to prevent the access to popups that create a lot of cache files
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
	 */
	public function __construct() {

		add_action( 'lumiere_maybe_ban_bots', [ $this, 'maybe_ban_bots' ] );
		add_action( 'lumiere_ban_bots_now', [ $this, 'lumiere_banishment' ] );
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
	 * This is an action meant to be called with do_action( 'lumiere_maybe_ban_bots' ) that will assess whether to ban the user
	* Not putting the no HTTP_REFERER condition here, since do_action( 'lumiere_maybe_ban_bots' ) can be called i.e. by taxonomy pages and they must be accessible even if there is no HTTP_REFERER
	 */
	public function maybe_ban_bots(): void {
		$this->maybe_ban_ip( self::BLACK_LIST_IP );
		$this->maybe_ban_useragent( self::BLACK_LIST_AGENT );
	}

	/**
	 * Process list of bots registered in BLACK_LIST_AGENT, exit if it one of the bad bots
	 * @param array<string> $banned_recipients The list of the banned recipients (USER_AGENT)
	 * @return void The user is banned if found in any of those lists
	 */
	private function maybe_ban_useragent( array $banned_recipients ): void {
		$agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		foreach ( $banned_recipients as $bot ) {
			if ( preg_match( "~$bot~i", $agent ) === 1 ) {
				do_action( 'lumiere_ban_bots_now' );
			}
		}
	}

	/**
	 * Detect Client IP
	 */
	private function get_user_ip(): string {
		$ip = null;
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && strlen( $_SERVER['HTTP_CLIENT_IP'] ) > 0 ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && strlen( $_SERVER['HTTP_X_FORWARDED_FOR'] ) > 0 ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		}
		return $ip;
	}

	/**
	 * Process list of IPs registered in BLACK_LIST_IP, exit if it one of the bad ips
	 * @param array<string> $banned_recipients The list of the banned recipients (HTTP_CLIENT_IP, HTTP_X_FORWARDED_FOR or REMOTE_ADDR)
	 * @return void The user is banned if found in any of those lists
	 */
	private function maybe_ban_ip( array $banned_recipients ): void {
		$ip = $this->get_user_ip();
		foreach ( $banned_recipients as $bot ) {
			if ( $ip === $bot ) {
				do_action( 'lumiere_ban_bots_now' );
			}
		}
	}

	/**
	 * Display a 403 error
	 * This is an action meant to be called with do_action( 'lumiere_ban_bots_now' ) that immediately ban the user
	 */
	public function lumiere_banishment(): void {

		$text_ban = sprintf( 
			/* translators: %1$s and %2$s are HTML tags */
			__( '%1$sYou have been banned from this site%2$s', 'lumiere-movies' ),
			'<h1>',
			'</h1>'
		);

		$text_ban .= sprintf(
			/* translators: %1$s, %2$s are HTML tags */
			__( '%1$sIf you think it\'s a mistake, please contact the administrator via a proxy server.%2$s', 'lumiere-movies' ),
			'<p>',
			'</p>'
		);

		wp_die(
			wp_kses( $text_ban, [ 'p' => [], 'h1' => [], 'a' => [ 'href' => [] ] ] ),
			esc_html__( 'Lumière Popups Access Error', 'lumiere-movies' ),
			[
				'response' => 403,
				'link_url' => esc_url( site_url() ),
				'link_text' => esc_html__( 'Back home', 'lumiere-movies' ),
			]
		);
	}
}

