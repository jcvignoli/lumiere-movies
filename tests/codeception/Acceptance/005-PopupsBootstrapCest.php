<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to test the Bootstrap Popups (a WebDriver is needed for JS execution)

class PopupsBootstrapCest {

	public function _before(AcceptanceTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceTester $I){
		$I->comment('#Code _after#');
	}

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/** 
	 * Select Bootstrap
	 * Make sure that Bootstrap modal window is selected
	 *
	 * @before login
	 */
	private function bootstrap(AcceptanceTester $I) {

		// Make sure Bootstrap is active, following tests are run with Bootstrap
		$I->SwitchModalWindow('Bootstrap');
	}
	
	/**
	 * Is popup movie functional?
	 *
	 * @before bootstrap
	 */
	public function checkPopupMovie( AcceptanceTester $I ) {

		// popup link movie interstellar
		$element = 'a[data-modal_window_film="interstellar"]';
		$sub_url = '/en/lumiere/film/?film=interstellar';
		$xpath = '//html/body/div[2]/div/main/div/div/div/article/div/p/span[2]/span/span/span[2]/object'; // found with chrome tools

		$I->comment('-> Check if popup movie can be open');
		$I->amOnPage('/en/2021/test-codeception/');
		$I->waitPageLoad();
		
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(2);
		
		$I->seeElement('object', ["name" => "interstellar"]);
		$I->switchToFrame( $xpath );
		$I->see( 'Mankind was born on Earth');
		
		// Test click to go to another popup
		$I->click( AcceptanceSettings::TESTING_PAGE_BASE_A_DIRECTOR );
		
		$I->scrollTo('.lumiere_width_20_perc');
		$I->waitForText( 'Best known for his cerebral, often nonlinea', 15 ); // wait up to 15 seconds
	}

	/**
	 * Is popup person functional?
	 * (also tested with checkTaxonomyOptionAndPage() 
	 * This one doesn't work, the path is not found, can't switch to the frame, set the visibility to private so it is not executed
	 *
	 * @before bootstrap
	 */
	private function checkPopupPerson( AcceptanceTester $I ) {

		// popup link actor Jorge Rivero
		$element = 'a[data-modal_window_people="0729473"]';
		$sub_url = '/en/lumiere/person/?mid=0729473';
		$xpath = '/html/body/div[2]/div/main/div/div/div/article/div/div[1]/div/div[6]/div[1]/div[1]/span/span/span/span[2]/object'; // found with chrome tools

		$I->comment('-> Check if popup person can be open');
		$I->amOnPage('/en/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");

		$I->wait(8);

		$I->seeElement('object', [ 'dat' => $I->getCustomBaseUrl() . $sub_url ] );
		$I->switchToFrame( $xpath );
		$I->see( 'Pajarero');
	}
}
