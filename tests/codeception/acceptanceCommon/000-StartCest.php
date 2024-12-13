<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class StartCest {

	public function _before(AcceptanceRemoteTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceRemoteTester $I){
		$I->comment('#Code _after#');
	}
/*
	public function _failed(AcceptanceRemoteTester $I){
		$I->comment('Cannot start initialisation, exiting...');
		exit();
	}

	public function _passed(AcceptanceRemoteTester $I){
		$I->comment('Test Lumière initialisation successfully started, continuing...');
	}
*/

	/** Login to Wordpress
	 *  Trait function to keep the cookie active
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->login_universal($I);
	}

	/** 
	 * Check if website is online, otherwise exit
	 */
	public function checkBlogActive(AcceptanceRemoteTester $I) {
		$I->wantTo( 'Check if the blog is online' );
		$I->amOnPage( '/' );
		//$I->see('Blog ext'); # can also use _failed() and _passed() instead
		$I->CustomSeeExit( 'Blog ext (codeception)' );
	}

	/**
	 * Activate plugin
	 * @before login
	 */
	public function activateLumiere(AcceptanceRemoteTester $I) {
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('lumiere-movies');
	}

	/**
	 * Disable debug functions
	 * @before login
	 */
	public function disableDebug(AcceptanceRemoteTester $I) {
		$I->wantTo('Disable debug');
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbkeepsettings');
		$I->CustomDisableCheckbox('#imdb_imdbdebug_yes', '#lumiere_update_general_settings');
	}

	/**
	 * Activate Highslide modal window, most of the tests are run with Highslide
	 * @before login
	 */
	public function enableHighslide(AcceptanceRemoteTester $I) {
		$I->wantTo('Enable Highslide');
		$I->SwitchModalWindow('Highslide');
	}

	/**
	 * Create the cache folder by visiting an admin page
	 * This way permissions are correctly set from the beginning
	 * @before login
	 */
	public function createCacheFolder(AcceptanceRemoteTester $I) {
		// Make sure cache folders are properly created by visiting any admin page
		$I->amOnPage( AcceptanceSettings::LUMIERE_GENERAL_OPTIONS_URL );
		$I->see("Layout");
		$I->wait(2);
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
	}
}

