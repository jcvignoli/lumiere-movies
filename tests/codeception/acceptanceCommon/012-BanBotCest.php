<?php

# Class meant to test the banning of bots (a WebDriver is needed for JS execution)

use \PHPUnit\Framework\Assert;

class BanBotCest {

	/**
	 * Stock the base remote URL
	 */
	var $base_url = "";

	/**
	 * Stock the root remote path
	 */
	var $base_path = "";

	public function __construct(){

		// Build vars
		$remote_or_local = defined( 'DEVELOPMENT_ENVIR' ) ? DEVELOPMENT_ENVIR : '';
		$final_var_url = 'TEST_' . strtoupper( $remote_or_local ) . '_WP_URL';
		$final_var_root_folder = 'WP_ROOT_' . strtoupper( $remote_or_local ) . '_FOLDER';

		// Build properties
		$this->base_url = $_ENV[ $final_var_url ];
		$this->base_path = $_ENV[$final_var_root_folder];

	}


	public function _before(AcceptanceRemoteTester $I){
		$I->comment(\Helper\Color::set("#Code _before#", "italic+bold+cyan"));
	}

	public function _after(AcceptanceRemoteTester $I){
		$I->comment(\Helper\Color::set("#Code _after#", "italic+bold+cyan"));

	}

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Use curl to ping the URL, return the text
	 */
	private function curlSpecialHeader( $url ): string {

		$banned_user = 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $banned_user );
		curl_setopt($ch, CURLOPT_URL, $url ); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec( $ch ); 
		curl_close($ch);
		return $result;
	}

	/** 
	 * Check if calling a page is banned
	 *
	 * @example ["/lumiere/person/?mid=0248281"]
	 * @example ["/lumiere/film/?film=interstellar"]
	 * @example ["/lumiere/search/?film=interstellar&norecursive=yes"]
	 * @example ["/en/2021/test-codeception/"]
	 * @example ["/2021/y-tu-mama-tambien/"]
	 * @example ["/lumiere-director/stanley-kubrick/"]
	 */
	public function banBots(AcceptanceLocalTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $this->base_url . $example[0] );

		if ( !preg_match( '~You have been banned from this site~', $result ) > 0 ) {
			Assert::fail('!! Not banned, error!');
		} else {
			$I->comment ( '-> The page correctly banned access to ' . $example[0] );
		}
	}

}
