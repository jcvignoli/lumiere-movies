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

	/**
	 * Text displayed on a banned page
	 */
	var $ban_text = "";

	public function __construct(){

		// Build vars
		$remote_or_local = defined( 'DEVELOPMENT_ENVIR' ) ? DEVELOPMENT_ENVIR : '';
		$final_var_url = 'TEST_' . strtoupper( $remote_or_local ) . '_WP_URL';
		$final_var_root_folder = 'WP_ROOT_' . strtoupper( $remote_or_local ) . '_FOLDER';

		// Build properties
		$this->base_url = $_ENV[ $final_var_url ];
		$this->base_path = $_ENV[$final_var_root_folder];
		
		$this->ban_text = '~(Prevented a bad request)~';

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
	 * Curl can be used more specifically than $I->amOnPage() with user agents and referer
	 * 
	 * @param string $url
	 * @param string $user_agent
	 * @param string|null $referer A referer to tests popups (null by default)
	 */
	private function curlSpecialHeader( $url, $user_agent, $referer = null ): string {
		/* tests WP login 
		$this->base_url . '/wp-login.php?
		$username = $_ENV[ 'TEST_WP_ADMIN_USERNAME' ];
		$password = $_ENV[ 'TEST_WP_ADMIN_PASSWORD' ];
    		$post_data = 'log='. $username .'&pwd='. $password .'&wp-submit=Log%20In&redirect_to='. $url .'/&testcookie=1';
		$cookie = '/tmp/wpcookie.txt';
   		codecept_debug($login_url);
    
		//visit the wp-login.php and set the cookie.
		$login = curl_init();
		curl_setopt ($login, CURLOPT_REFERER, $this->base_url . '/wp-admin/');
		curl_setopt($login, CURLOPT_URL, $login_url );
		curl_setopt($login, CURLOPT_POSTFIELDS,  );
		curl_setopt ($login, CURLOPT_COOKIEJAR, $cookie); 
		curl_setopt ($login, CURLOPT_RETURNTRANSFER, 1 );
		$output = curl_exec ($login);
		*/

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		/*
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Cookie: wordpress_test_cookie=WP+Cookie+check" ] );
		curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie );
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie );
		*/
		if ( isset( $referer ) && strlen($referer) > 0 ) {
			curl_setopt($ch, CURLOPT_REFERER, $referer );
		}
		$result = curl_exec( $ch ); 
		curl_close($ch);
		/*
		unlink( $cookie );
		*/
		return $result;
	}

	/** 
	 * Check if calling popups and taxo pages are banned
	 * Popups should ban if not logged in and using a banned useragent
	 * Taxonomy pages should ban if not logged in and using a banned useragent
	 * (Only normal posts never ban)
	 *
	 * @example ["/lumiere/person/?mid=0248281"]
	 * @example ["/lumiere/film/?film=interstellar"]
	 * @example ["/lumiere/search/?film=interstellar&norecursive=yes"]
	 * @example ["/lumiere-director/stanley-kubrick/"]
	 * @example ["/lumiere-genre/sci-fi/"]
	 */
	public function userAgentShouldtBan(AcceptanceLocalTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $this->base_url . $example[0], 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)' );

		if ( preg_match( $this->ban_text, $result ) > 0 ) {
			$I->comment ( '-> The page correctly banned access to ' . $example[0] );
		} else {
			Assert::fail('!! Not banned, error!');
		}
	}

	/** 
	 * Check if calling posts are banned
	 * Only normal posts should never be banned
	 * ( Popups should ban if not logged in and using a banned useragent )
	 * ( Taxonomy pages should ban if not logged in and using a banned useragent )
	 *
	 * @example ["/en/2021/test-codeception/"]
	 * @example ["/2021/y-tu-mama-tambien/"]
	 */
	public function postsShouldNotBanBots(AcceptanceLocalTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $this->base_url . $example[0], 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)' );

		if ( preg_match( $this->ban_text, $result ) === 0 ) {
			$I->comment ( '-> The page correctly did not ban access to ' . $example[0] );
		} else {
			Assert::fail('!! Banned although logged in, error!');
		}
	}

	/** 
	 * DEACTIVATED, loging with CurL doesn't work
	 *
	 * Check if calling a page is banned when logged in
	 * Popups should NOT ban if logged in and using a banned useragent
	 * Taxonomy pages should NOT ban if logged in and using a banned useragent
	 * (Only normal posts never ban)
	 *
	 * @before login
	 *
	 * @example ["/lumiere/person/?mid=0248281"]
	 * @example ["/lumiere/film/?film=interstellar"]
	 * @example ["/lumiere/search/?film=interstellar&norecursive=yes"]
	 * @example ["/lumiere-director/stanley-kubrick/"]
	 * @example ["/lumiere-genre/sci-fi/"]
	 */
	private function popupsShouldNotBanLoggedin(AcceptanceLocalTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $this->base_url . $example[0], 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)' );

		codecept_debug($result);

		if ( preg_match( $this->ban_text, $result ) === 0 ) {
			$I->comment ( '-> The popups and taxo page correctly did not ban access to ' . $example[0] );
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
	public function userAgentShouldNotBan(AcceptanceLocalTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $this->base_url . $example[0], 'random not banned user agent' );

		//codecept_debug($result);

		if ( preg_match( $this->ban_text, $result ) === 0 ) {
			$I->comment ( '-> The pages and taxo correctly did not ban access to ' . $example[0] );
		} else {
			Assert::fail('!! Banned although logged in, error!');
		}
	}
	
	/** 
	 * Check if popups do not ban when using a random user agent WITH a referer
	 *
	 * @example ["/lumiere/person/?mid=0248281"]
	 * @example ["/lumiere/film/?film=interstellar"]
	 * @example ["/lumiere/search/?film=interstellar&norecursive=yes"]
	 */
	public function popupsWithRefererShouldNotBan(AcceptanceLocalTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $this->base_url . $example[0], 'random not banned user agent', 'http://localhost' );

		//codecept_debug($result);

		if ( preg_match( $this->ban_text, $result ) === 0 ) {
			$I->comment ( '-> The popups correctly did not ban access to ' . $example[0] );
		} else {
			Assert::fail('!! Banned although referer, error!');
		}
	}
	
	/** 
	 * Check if popups correctly ban when using a random user agent WITHOUT a referer
	 *
	 * @example ["/lumiere/person/?mid=0248281"]
	 * @example ["/lumiere/film/?film=interstellar"]
	 * @example ["/lumiere/search/?film=interstellar&norecursive=yes"]
	 */
	public function popupsWithoutRefererShouldBan(AcceptanceLocalTester $I, \Codeception\Example $example) {

		$result = $this->curlSpecialHeader( $this->base_url . $example[0], 'random not banned user agent', null );

		//codecept_debug($result);

		if ( preg_match( $this->ban_text, $result ) > 0 ) {
			$I->comment ( '-> The page correctly ban access to ' . $example[0] );
		} else {
			Assert::fail('!! Not banned although without referer, error!');
		}
	}
}
