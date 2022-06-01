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

	/** 	Check if website is online, otherwise exit
	 *
	 */
	public function checkBlogActive(AcceptanceRemoteTester $I) {
		$I->wantTo('Check if the blog is online');
		$I->amOnPage('/');
		//$I->see('Blog ext'); # can also use _failed() and _passed() instead
		$I->CustomSeeExit('Blog ext');
	}

	/** Activate plugin
	 *
	 * @before login
	 *
	 */
	public function activateLumiere(AcceptanceRemoteTester $I) {
		$I->amOnPage('/wp-admin/plugins.php');
		$I->maybeActivatePlugin('lumiere-movies');
	}

	/** Disable debug functions
	 *
	 * @before login
	 *
	 */
	public function disableDebug(AcceptanceRemoteTester $I) {
		$I->wantTo('Disable debug');
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbdebug_yes', '#update_imdbSettings');
	}

	/** Activate Highslide modal window, most of the tests are run with Highslide
	 *
	 * @before login
	 *
	 */
	public function enableHighslide(AcceptanceRemoteTester $I) {
		$I->wantTo('Enable Highslide');
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options');
		$I->customSelectOption( "select[name=imdbpopup_modal_window]", "highslide", "update_imdbSettings" );

	}

}




