<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)
# Doesn't work, doesn't find the iframe

class PopupsBootstrapCest {

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

	/** Is popup movie functional?
	 *
	 * @before login
	 *
	 */
	public function checkPopupMovie(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {

		// Make sure Highslide is active, following tests are run with Highslide
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options');
		$I->customSelectOption( "form select[name=imdbpopup_modal_window]", "Bootstrap", "update_imdbSettings" );

		// popup link movie interstellar
		$element = 'a[data-modal_window_film="interstellar"]';
		$sub_url = '/lumiere/film/?film=interstellar';

		$I->wantTo('Check if popup movie can be open');
		$I->amOnPage('/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(4);
		$I->seeElement('object', ["data" => "$this->url_base_remote$sub_url"]);
		
		/** Cannot access the HTML iframe (<object>) with switchToIframe or anything else */
#		$I->switchToIFrame("//iframe[@src='$this->url_base_remote$sub_url']");
#		$I->see('Christopher Nolan');
	}

	/** Is popup person functional?
	 ** (also tested with checkTaxonomyOptionAndPage() 
	 *
	 */
	public function checkPopupPerson(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {

		// popup link actor Jorge Rivero
		$element = 'a[data-modal_window_people="0729473"]';
		$sub_url = '/lumiere/person/?mid=0729473';

		$I->wantTo('Check if popup person can be open');
		$I->amOnPage('/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");

		$I->wait(4);
		$I->seeElement('object', ["data" => "$this->url_base_remote$sub_url"]);
		
		/** Cannot access the HTML iframe (<object>) with switchToIframe or anything else */
#		$I->switchToIFrame("//iframe[@src='$this->url_base_remote$sub_url']");
#		$I->see('Pajarero');
	}

}
