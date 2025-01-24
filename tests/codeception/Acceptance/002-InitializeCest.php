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
		$I->see('Options reset.');
		
		$I->comment('Reset cache settings');
		$I->amOnPage( AcceptanceSettings::LUMIERE_CACHE_OPTIONS_URL );
		$I->click("lumiere_reset_cache_settings");
		$I->see('Options reset.');
		
		$I->comment('Reset data settings');
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->click("lumiere_reset_data_settings");
		$I->see('Options reset.');
	}
	
	/** 
	 * Make sure that taxonomies and their URL are created first in French, so they take the extension "-en" which is needed for the tests
	 * Taxonomy URLs depends on the first time they're called: if in French, the english's ones will take "-en" once they're created.
	 *
	 * @before login
	 */
	public function ensureTaxoLinksGetCorrectExt(AcceptanceTester $I) {

		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL_FR );
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL_FR_TWO );
	}
}
