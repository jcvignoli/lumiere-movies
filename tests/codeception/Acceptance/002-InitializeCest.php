<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to initialize the settings (a WebDriver is needed for JS execution)

class InitializeCest {


	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/** 
	 * Disable defaults Plugins
	 *
	 * @before login
	 */
	public function enablePlugins(AcceptanceTester $I) {

		$I->comment('Set the wordpress install to its normal state');

		// Lumière on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('lumiere-movies');
		
		// Query monitor destroys the layout
		$I->amOnPluginsPage();
		$I->maybeDeactivatePlugin('query-monitor');
	}


	/**
	 * Reset options, test that reseting works
	 *
	 * @before login
	 */
	public function resetOptions(AcceptanceTester $I) {

		$I->comment('If reset works');
		
		$I->comment('Reset main settings');
		$I->amOnPage( AcceptanceSettings::LUMIERE_MAIN_OPTIONS_URL );
		$I->click("lumiere_reset_main_settings");
		$I->wait(1);
		$I->see('Options reset.');
		
		$I->comment('Reset cache settings');
		$I->amOnPage( AcceptanceSettings::LUMIERE_CACHE_OPTIONS_URL );
		$I->click("lumiere_reset_cache_settings");
		$I->wait(1);
		$I->see('Options reset.');
		
		$I->comment('Reset data settings');
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_DATA_MOVIE_URL );
		$I->click("lumiere_reset_data_movie_settings");
		$I->wait(1);
		$I->see('Options reset.');
		
		$I->comment('Reset data settings');
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_DATA_PERSON_URL );
		$I->click("lumiere_reset_data_person_settings");
		$I->wait(1);
		$I->see('Options reset.');
	}
	
	/** 
	 * Polylang compatibility
	 * Make sure that taxonomies and their URL are created first in French, so they take the extension "-en" which is needed for the tests
	 * Taxonomy URLs depends on the first time they're called: if in French, the english's ones will take "-en" once they're created.
	 *
	 * @before login
	 */
	public function ensureTaxoLinksGetCorrectExt(AcceptanceTester $I) {
		$I->maybeActivatePlugin('polylang');
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL_FR );
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL_FR_TWO );
	}
	
	
	/** 
	 * Set Polylang language default to English
	 * Needed to ensure that all popups and frontend are in English/French as it should be
	 *
	 * @before login

	public function setEnglishDefaultPolylang(AcceptanceTester $I) {
		$I->amOnPage( 'wp-admin/admin.php?page=mlang' );
		$I->click( ['xpath'=> '//*[@id="the-list"]/tr[1]/td[4]/div/span/a']); => couldn't find a way to click on default lang ENG
		$I->waitPageLoad();
	}
	 */
	 
	/** 
	 * Update the WordPress translation, Lumière translations actually
	 * Needed to ensure that all popups and frontend are in English/French as it should be
	 *
	 * @before login
	 */
	public function updateTranslation(AcceptanceTester $I) {
		$I->amOnPage( 'wp-admin/update-core.php' );
		$I->tryToClick( 'Update Translations' );	
	}
}
