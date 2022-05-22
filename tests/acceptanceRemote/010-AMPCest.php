<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class AMPCest {

	/* Stock the base remote URL
	 *
	 */
	var $url_base_remote = "";

	/* Stock the root remote path
	 *
	 */
	var $root_remote = "";

	public function __construct(){

		$this->url_base_remote = $_ENV['TEST_REMOTE_WP_URL'];
		$this->root_remote = $_ENV['WP_ROOT_REMOTE_FOLDER'];

	}


	public function _before(AcceptanceRemoteTester $I){
		$I->comment(\Helper\Color::set("#Code _before#", "italic+bold+cyan"));
	}

	public function _after(AcceptanceRemoteTester $I){

		$I->comment(\Helper\Color::set("#Code _after#", "italic+bold+cyan"));

	}

	/**
	 *  Login to Wordpress
	 *  Trait function to keep the cookie active
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {

		$I->login_universal($I);

	}

	/** 
	 * Check if taxonomy works with AMP
	 *
	 * @before login
	 *
	 */
	public function checkIfAMPworks(AcceptanceRemoteTester $I) {

		$I->wantTo(\Helper\Color::set('Check if AMP page differenciation works', "italic+bold+cyan"));

		// Activate AMP
		$I->amOnPage('/wp-admin/plugins.php');
		$I->maybeActivatePlugin('amp');

		// Check if AMP is functional and remove links
		$I->amOnPage('/2021/test-codeception/?amp');
		$I->seeInPageSource('<div class="lumiere_align_left lumiere_flex_auto">Peter Dinklage</div>');

		// Check if without AMP it is functional
		$I->amOnPage('/2021/test-codeception/');
		$I->seeInPageSource('<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="0227759" title="open a new window with IMDb informations">Peter Dinklage</a>');

	}

}



