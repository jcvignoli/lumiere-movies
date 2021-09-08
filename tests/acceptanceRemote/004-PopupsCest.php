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
		AcceptanceTrait::login_universal($I);
	}

	/** Is popup movie functional?
	 *
	 */
	public function checkPopupMovie(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {

		// popup link movie interstellar
		$element = 'a[data-highslidefilm="interstellar"]';
		$sub_url = '/lumiere/film/interstellar/?film=interstellar';

		$I->wantTo('Check if popup movie can be open');
		$I->amOnPage('/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(7);
		$I->switchToIFrame("//iframe[@src='$this->url_base_remote$sub_url']");
		$I->see('Christopher Nolan');
	}

	/** Is popup person functional?
	 ** (also tested with checkTaxonomyOptionAndPage() 
	 *
	 */
	public function checkPopupPerson(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {

		// popup link actor Jorge Rivero
		$element = 'a[data-highslidepeople="0729473"]';
		$sub_url = '/lumiere/person/0729473/?mid=0729473';

		$I->wantTo('Check if popup person can be open');
		$I->amOnPage('/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(7);
		$I->switchToIFrame("//iframe[@src='$this->url_base_remote$sub_url']");
		$I->see('Pajarero');
	}

}
