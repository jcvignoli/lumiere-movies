<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to test the banning of bots (a WebDriver is needed for JS execution)

use \PHPUnit\Framework\Assert;

class BanBotCest {

	/**
	 * Text displayed on a banned page
	 */
	private const BAN_TEXT = '~(' . BAN_BOTS_MSG . ')~'; // Different message for remote and local, one in French the other one in English

	public function _before(AcceptanceTester $I){
		$I->comment(Helper\Color::set("#Code _before#", "italic+bold+cyan"));
	}

	public function _after(AcceptanceTester $I){
		$I->comment(Helper\Color::set("#Code _after#", "italic+bold+cyan"));
	}

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Use curl to ping the URL, return the text
	 * Curl can be used more specifically than $I->amOnPage() with user agents and referer
	 * 
	 * @param string $url
	 * @param string $user_agent
	 * @param string|null $referer A referer to tests popups (null by default)
	 */
	private function curlSpecialHeader( $url, $user_agent, $referer = null ): string {
		/* tests WP login 
		$I->getCustomBaseUrl() . '/wp-login.php?
		$username = $_ENV[ 'TEST_WP_ADMIN_USERNAME' ];
		$password = $_ENV[ 'TEST_WP_ADMIN_PASSWORD' ];
    		$post_data = 'log='. $username .'&pwd='. $password .'&wp-submit=Log%20In&redirect_to='. $url .'/&testcookie=1';
		$cookie = '/tmp/wpcookie.txt';
   		codecept_debug($login_url);
    
		//visit the wp-login.php and set the cookie.
		$login = curl_init();
		curl_setopt ($login, CURLOPT_REFERER, $I->getCustomBaseUrl() . '/wp-admin/');
		curl_setopt($login, CURLOPT_URL, $login_url );
		curl_setopt($login, CURLOPT_POSTFIELDS,  );
		curl_setopt ($login, CURLOPT_COOKIEJAR, $cookie); 
		curl_setopt ($login, CURLOPT_RETURNTRANSFER, 1 );
		$output = curl_exec ($login);
		*/

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CAINFO, '/etc/ssl/certs/apache-local.lumiere.crt' );
		
		/*
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Cookie: wordpress_test_cookie=WP+Cookie+check" ] );
		curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie );
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie );
		*/
		if ( isset( $referer ) && strlen($referer) > 0 ) {
			curl_setopt( $ch, CURLOPT_REFERER, $referer );
		}
		$result = curl_exec( $ch ); 
		curl_close( $ch );
		/*
		unlink( $cookie );
		*/
		return $result;
	}

	/** 
	 * Check if calling popups pages is banned
	 * Popups should ban if not logged in and using a banned useragent
	 * (Only normal posts and taxonomy pages never ban)
	 *
	 * @example ["/lumiere/person/?mid=0248281"]
	 * @example ["/lumiere/film/?film=interstellar"]
	 * @example ["/lumiere/movie_search/?film=interstellar"]
	 */
	public function userAgentShouldBan(AcceptanceTester $I, \Codeception\Example $example) {

		$base_url = $I->getCustomBaseUrl();
		$result = $this->curlSpecialHeader( $base_url . $example[0], 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)' );
$I->comment($result);
		// Activate debug
		// codecept_debug($result);
	
		if ( preg_match( self::BAN_TEXT, $result ) > 0 ) {
			$I->comment ( '-> The page correctly banned access to ' . $I->getCustomBaseUrl() . $example[0] );
		} else {
			Assert::fail('!! Not banned, error!');
		}
	}

	/** 
	 * Check if calling posts or taxonomy pages are banned
	 * Only normal posts and taxonomy pages should never be banned
	 * (Popups should ban if not logged in and using a banned useragent )
	 *
	 * @example ["/en/2021/test-codeception/"]
	 * @example ["/2021/y-tu-mama-tambien/"]
	 * @example ["/lumiere-director/stanley-kubrick/"]
	 * @example ["/lumiere-genre/sci-fi/"]
	 */
	public function postsShouldNotBanBots(AcceptanceTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $I->getCustomBaseUrl() . $example[0], 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)' );

		if ( preg_match( self::BAN_TEXT, $result ) === 0 ) {
			$I->comment ( '-> The page correctly did not ban access to ' . $I->getCustomBaseUrl() . $example[0] );
		} else {
			Assert::fail('!! Banned although logged in, error!');
		}
	}

	/** 
	 * DEACTIVATED, loggin with CurL doesn't work
	 *
	 * Check if calling a page is banned when logged in
	 * Popups should NOT ban if logged in and using a banned useragent
	 * (Only normal posts and taxonomy pages never ban)
	 *
	 * @before login
	 *
	 * @example ["/lumiere/person/?mid=0248281"]
	 * @example ["/lumiere/film/?film=interstellar"]
	 * @example ["/lumiere/movie_search/?film=interstellar"]
	 */
	private function popupsShouldNotBanLoggedin(AcceptanceTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $I->getCustomBaseUrl() . $example[0], 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)' );

		if ( preg_match( self::BAN_TEXT, $result ) === 0 ) {
			$I->comment ( '-> The popups and taxo page correctly did not ban access to ' . $I->getCustomBaseUrl() . $example[0] );
		} else {
			Assert::fail('!! Banned although logged in, error!');
		}
	}
	
	/** 
	 * Check if taxo and posts are not banned if using a random user agent, even with no referer
	 *
	 * @example ["/lumiere-director/stanley-kubrick/"]
	 * @example ["/lumiere-genre/sci-fi/"]
	 * @example ["/en/2021/test-codeception/"]
	 * @example ["/2021/y-tu-mama-tambien/"]
	 */
	public function userAgentShouldNotBan(AcceptanceTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $I->getCustomBaseUrl() . $example[0], 'random not banned user agent' );

		//codecept_debug($result);

		if ( preg_match( self::BAN_TEXT, $result ) === 0 ) {
			$I->comment ( '-> The pages and taxo correctly did not ban access to ' . $I->getCustomBaseUrl() . $example[0] );
		} else {
			Assert::fail('!! Banned although logged in, error!');
		}
	}
	
	/** 
	 * Check if popups do not ban when using a random user agent WITH a referer
	 *
	 * @example ["/lumiere/person/?mid=0248281"]
	 * @example ["/lumiere/film/?film=interstellar"]
	 * @example ["/lumiere/movie_search/?film=interstellar"]
	 */
	public function popupsWithRefererShouldNotBan(AcceptanceTester $I, \Codeception\Example $example) {

		$url_nonce_valid = $I->CustomGenerateNonce( $I->getCustomBaseUrl() . $example[0] );

		$result = $this->curlSpecialHeader( $url_nonce_valid, 'random not banned user agent', 'http://localhost' );

		if ( preg_match( self::BAN_TEXT, $result ) === 0 ) {
			$I->comment ( '-> The popups correctly did not ban access to ' . $I->getCustomBaseUrl() . $example[0] );
		} else {
			Assert::fail('!! Banned although referer, error!');
		}
	}
	
	/** 
	 * Check if popups correctly ban when using a random user agent WITHOUT a referer
	 *
	 * @example ["/lumiere/person/?mid=0248281"]
	 * @example ["/lumiere/film/?film=interstellar"]
	 * @example ["/lumiere/movie_search/?film=interstellar"]
	 */
	public function popupsWithoutRefererShouldBan(AcceptanceTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $I->getCustomBaseUrl() . $example[0], 'random not banned user agent', null );

		//codecept_debug($result);

		if ( preg_match( self::BAN_TEXT, $result ) > 0 ) {
			$I->comment ( '-> The page correctly ban access to ' . $I->getCustomBaseUrl() . $example[0] );
		} else {
			Assert::fail('!! Not banned although without referer, error!');
		}
	}
}
