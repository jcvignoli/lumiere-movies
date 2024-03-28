<?php

# Class meant to test the Bootstrap Popups (a WebDriver is needed for JS execution)

class PopupsBootstrapCest {

	/**
	 * Stock the base remote URL
	 */
	var $base_url = "";

	/**
	 * Stock the root remote path
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

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->login_universal($I);
	}

	/** 
	 * Select Bootstrap
	 * Make sure that Bootstrap modal window is selected
	 *
	 * @before login
	 */
	private function bootstrap(AcceptanceRemoteTester $I) {

		// Make sure Bootstrap is active, following tests are run with Bootstrap
		$I->SwitchModalWindow('Bootstrap');
	}
	
	/**
	 * Is popup movie functional?
	 *
	 * @before bootstrap
	 */
	public function checkPopupMovie(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {

		// popup link movie interstellar
		$element = 'a[data-modal_window_film="interstellar"]';
		$sub_url = '/en/lumiere/film/?film=interstellar';
		$xpath = '//html/body/div[2]/div/main/div/div/div/article/div/p/span[2]/span/span/span[2]/object'; // found with chrome tools

		$I->comment('-> Check if popup movie can be open');
		$I->amOnPage('/en/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(4);

		$I->seeElement('object', ["name" => "interstellar"]);
		$I->switchToFrame( $xpath );
		$I->see( 'Mankind was born on Earth');
	}

	/**
	 * Is popup person functional?
	 * (also tested with checkTaxonomyOptionAndPage() 
	 * This one doesn't work, the path is not found, can't switch to the frame, set the visibility to private so it is not executed
	 *
	 * @before bootstrap
	 */
	private function checkPopupPerson(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {

		// popup link actor Jorge Rivero
		$element = 'a[data-modal_window_people="0729473"]';
		$sub_url = '/en/lumiere/person/?mid=0729473';
		$xpath = '/html/body/div[2]/div/main/div/div/div/article/div/div[1]/div/div[6]/div[1]/div[1]/span/span/span/span[2]/object'; // found with chrome tools

		$I->comment('-> Check if popup person can be open');
		$I->amOnPage('/en/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");

		$I->wait(8);

		$I->seeElement('object', ["data" => "$this->base_url$sub_url"]);
		$I->switchToFrame( $xpath );
		$I->see( 'Pajarero');
	}
}
