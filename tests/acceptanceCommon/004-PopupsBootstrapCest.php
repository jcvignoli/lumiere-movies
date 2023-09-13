<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)
# Doesn't fully work, doesn't find the iframe

class PopupsBootstrapCest {

	/** Stock the base remote URL
	 *
	 */
	var $base_url = "";

	/** Stock the root remote path
	 *
	 */
	var $base_path = "";

	public function __construct(){

		$remote_or_local = defined( 'DEVELOPMENT_ENVIR' ) ? DEVELOPMENT_ENVIR : '';
		$final_var_url = 'TEST_' . strtoupper( $remote_or_local ) . '_WP_URL';
		$final_var_root_folder = 'WP_ROOT_' . strtoupper( $remote_or_local ) . '_FOLDER';
		
		$this->base_url = $_ENV[$final_var_url];
		$this->base_path = $_ENV[$final_var_root_folder];

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

	/**
	 * Is popup movie functional?
	 *
	 * @before login
	 *
	 */
	public function checkPopupMovie(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {

		// Make sure Bootstrap is active, following tests are run with Bootstrap
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options');
		$I->customSelectOption( "form select[name=imdbpopup_modal_window]", "Bootstrap", "update_imdbSettings" );

		// popup link movie interstellar
		$element = 'a[data-modal_window_film="interstellar"]';
		$sub_url = '/lumiere/film/?film=interstellar';

		$I->wantTo('Check if popup movie can be open');
		$I->amOnPage('/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(4);
		$I->seeElement('object', ["data" => "$this->base_url$sub_url"]);
		
		/** Cannot access the HTML iframe (<object>) with switchToIframe or anything else */
#		$I->switchToIFrame("//iframe[@src='$this->base_url$sub_url']");
#		$I->see('Christopher Nolan');
	}

	/**
	 * Is popup person functional?
	 * (also tested with checkTaxonomyOptionAndPage() 
	 */
	public function checkPopupPerson(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {

		// popup link actor Jorge Rivero
		$element = 'a[data-modal_window_people="0729473"]';
		$sub_url = '/lumiere/person/?mid=0729473';

		$I->wantTo('Check if popup person can be open');
		$I->amOnPage('/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");

		$I->wait(4);
		$I->seeElement('object', ["data" => "$this->base_url$sub_url"]);
		
		/** Cannot access the HTML iframe (<object>) with switchToIframe or anything else */
#		$I->switchToIFrame("//iframe[@src='$this->base_url$sub_url']");
#		$I->see('Pajarero');
	}

}
