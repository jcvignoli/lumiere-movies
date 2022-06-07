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

	/** Helper: Select Highslide
	 * Make sure that Highslide modal window is selected
	 *
	 */
	private function highslide(AcceptanceRemoteTester $I) {

		// Make sure Highslide is active, following tests are run with Highslide
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_GENERAL_OPTIONS_URL );
		$I->customSelectOption( "select[name=imdbpopup_modal_window]", "Highslide", "update_imdbSettings" );

	}

	/** 
	 * Check if taxonomy works with AMP
	 *
	 * @before login
	 * @before highslide
	 *
	 */
	public function checkIfAMPworks(AcceptanceRemoteTester $I) {

		$I->wantTo(\Helper\Color::set('Check if AMP page differenciation works', "italic+bold+cyan"));

		// Activate AMP
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->maybeActivatePlugin('amp');

		// Check if AMP is functional and remove links
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL . '?amp' );
		$I->seeInPageSource('<a class="linkpopup" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations" href="https://www.jcvignoli.com/blogpourext/lumiere/person/?mid=0227759&amp;amp">Peter Dinklage</a></div>');

		// Check if without AMP it is functional
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->seeInPageSource('<a class="linkincmovie modal_window_people highslide" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a></div>');

	}

}
