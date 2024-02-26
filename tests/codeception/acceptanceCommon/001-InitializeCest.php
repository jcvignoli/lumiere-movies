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

		// Deactivate Query Monitor, it bugs
		$I->amOnPluginsPage();
		$I->maybeDeactivatePlugin('query-monitor');
	}


	/**
	 * Enable Admin option
	 *
	 * @before login
	 */
	public function enableAdminGeneralOptions(AcceptanceRemoteTester $I) {

		$I->comment('Reset Admin General Options to their normal state');
	}
}


