<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to test Popups with Highslide (a WebDriver is needed for JS execution)

class PopupsHighslideCest {

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
	 * Select Highslide
	 * Make sure that Highslide modal window is selected
	 *
	 * @before login
	 */
	private function highslide(AcceptanceTester $I) {
		// Make sure Highslide is active, following tests are run with Highslide
		$I->SwitchModalWindow('Highslide');
	}

	/**
	 * Is popup movie functional?
	 *
	 * @before highslide
	 */
	public function checkPopupMovie(AcceptanceTester $I, \Codeception\Scenario $scenario) {

		// popup link movie interstellar
		$element = 'a[data-modal_window_film="' . AcceptanceSettings::TESTING_PAGE_POPUP_FILM_TITLE . '"]';
		$sub_url = AcceptanceSettings::TESTING_PAGE_POPUP_FILM_URL_WITHOUTMID;

		$I->comment('Check if popup movie can be open');
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
			
		$iframe_find_name = $I->grabAttributeFrom('//iframe', 'name');
		$I->switchToIframe( $iframe_find_name );
		$I->see( AcceptanceSettings::TESTING_PAGE_BASE_A_DIRECTOR );
		
		// Test click to go to another popup
		$I->click( AcceptanceSettings::TESTING_PAGE_BASE_A_DIRECTOR );
		$I->see( 'Best known for his cerebral, often nonlinea' );
	}

	/**
	 * Is popup person functional?
	 * (also tested with checkTaxonomyOptionAndPage() 
	 *
	 * @before highslide
	 */
	public function checkPopupPerson(AcceptanceTester $I, \Codeception\Scenario $scenario) {

		// popup link actor Jorge Rivero
		$element = 'a[data-modal_window_people="' . AcceptanceSettings::TESTING_PAGE_POPUP_PERSON_MID . '"]';
		$sub_url = AcceptanceSettings::TESTING_PAGE_POPUP_PERSON_URL;

		$I->comment('Check if popup person can be open');
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		
		$iframe_find_name = $I->grabAttributeFrom('//iframe', 'name');
		$I->switchToIframe( $iframe_find_name );
		$I->see( AcceptanceSettings::TESTING_PAGE_BASE_ELEMENT );

		// Test click to go to another popup
		$I->click( 'The Popcorn Chronicles' );
		$I->see( 'Emilio Portes' );
	}

}
