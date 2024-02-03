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

namespace Lumiere\Frontend\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'Lumière Movies: You can not call directly this page', 'lumiere-movies' ) );
}

class Ban_Bots {

	/**
	 * List of bots not respecting the rules
	 */
	private const BLACK_LIST = [
		/**
		 * Those bots do not respect the rules
		 */
		'(b|B)ytespider|(b|B)ytedance|YandexBot|GPTBot|bingbot',
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

		add_action( 'lumiere_ban_bots', [ $this, 'ban_bots' ] );

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
	 * Return the black list
	 * @return array<int, string>
	 */
	private function get_black_list(): array {
		return self::BLACK_LIST;
	}

	/**
	 * Process list of bots registered in BLACK_LIST, exit if it one of the bad bots
	 *
	 */
	public function ban_bots(): void {
		$this->ban_user_agent();
	}

	/**
	 * Process list of bots registered in BLACK_LIST, exit if it one of the bad bots
	 *
	 */
	private function ban_user_agent(): void {

		$agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$array_black_lists = $this->get_black_list();

		foreach ( $array_black_lists as $bot ) {
			if ( preg_match( "~$bot~", $agent ) === 1 ) {
				$this->banishment();
			}
		}
	}

	/**
	 * Exclude function
	 */
	private function banishment(): void {
		$message  = '<meta name="robots" content="noindex,nofollow,noarchive,nosnippet,noodp,noydir">';
		$message .= '<h1>You have been banned from this site.</h1>';
		$message .= '<p>If you think there has been a mistake, please contact the administrator via proxy server.</p>';
		$block_status     = '403';
		$block_protocol   = 'HTTP/1.1';
		$block_connection = 'Connection: Close';

		header( $block_protocol . ' ' . $block_status );
		header( $block_connection );
		wp_die(
			wp_kses(
				$message,
				[
					'h1' => [],
					'p' => [],
					'meta' => [
						'name' => [],
						'content' => [],
					],
				]
			)
		);
	}
}

