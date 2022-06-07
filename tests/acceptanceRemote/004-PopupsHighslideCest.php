<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class PopupsCest {

	/** Stock the base remote URL
	 *
	 */
	var $url_base_remote = "";

	/** Stock the root remote path
	 *
	 */
	var $root_remote = "";

	public function __construct(){

		$this->url_base_remote = $_ENV['TEST_REMOTE_WP_URL'];
		$this->root_remote = $_ENV['WP_ROOT_REMOTE_FOLDER'];

	}

	public function _before(AcceptanceRemoteTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceRemoteTester $I){
		$I->comment('#Code _after#');
	}

	/** Login to Wordpress
	 *  Trait function to keep the cookie active
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {

		$I->login_universal($I);

	}

	/** Select Highslide
	 * Make sure that Highslide modal window is selected
	 *
	 */
	private function highslide(AcceptanceRemoteTester $I) {

		// Make sure Highslide is active, following tests are run with Highslide
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_GENERAL_OPTIONS_URL );
		$I->customSelectOption( "select[name=imdbpopup_modal_window]", "Highslide", "update_imdbSettings" );

	}

	/** Is popup movie functional?
	 *
	 * @before login
	 * @before highslide
	 *
	 */
	public function checkPopupMovie(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {

		// popup link movie interstellar
		$element = 'a[data-modal_window_film="' . AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_TITLE . '"]';
		$sub_url = AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL;

		$I->wantTo('Check if popup movie can be open');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(7);
		$I->switchToIFrame("//iframe[@src='$this->url_base_remote$sub_url']");
		$I->see( AcceptanceRemoteSettings::TESTING_PAGE_BASE_A_DIRECTOR );
	}

	/** Is popup person functional?
	 ** (also tested with checkTaxonomyOptionAndPage() 
	 *
	 */
	public function checkPopupPerson(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {

		// popup link actor Jorge Rivero
		$element = 'a[data-modal_window_people="' . AcceptanceRemoteSettings::TESTING_PAGE_POPUP_PERSON_MID . '"]';
		$sub_url = AcceptanceRemoteSettings::TESTING_PAGE_POPUP_PERSON_URL;

		$I->wantTo('Check if popup person can be open');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(7);
		$I->switchToIFrame("//iframe[@src='$this->url_base_remote$sub_url']");
		$I->see( AcceptanceRemoteSettings::TESTING_PAGE_BASE_ELEMENT );

	}

}
