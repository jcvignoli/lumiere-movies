<?php

# Class meant to initialize the settings (a WebDriver is needed for JS execution)

class InitializeCest {


	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->login_universal($I);
	}

	/** 
	 * Disable defaults Plugins
	 *
	 * @before login
	 */
	public function enablePlugins(AcceptanceRemoteTester $I) {

		$I->comment('Set the wordpress install to its normal state');

		// Lumière on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('lumiere-movies');
	}


	/**
	 * Enable Admin option
	 *
	 * @before login
	 */
	public function enableAdminGeneralOptions(AcceptanceRemoteTester $I) {

		$I->comment('Reset Admin General Options to their normal state');
	}
	
	/** 
	 * Make sure that taxonomies and their URL are created first in french, so they take the extension "-en" which is needed for the tests
	 * Taxonomy URLs depends on the first time they're called: if in french, the english's ones will take "-en" once they're created.
	 *
	 * @before login
	 */
	public function ensureTaxoLinksGetCorrectExt(AcceptanceRemoteTester $I) {

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL_FR );
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL_FR_TWO );
	}
}


