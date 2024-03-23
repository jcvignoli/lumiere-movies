<?php

# Class meant to reinitialize the settings (a WebDriver is needed for JS execution)

class EndCest {

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
	 * Enable defaults Plugins
	 *
	 * @before login
	 */
	public function enablePlugins(AcceptanceRemoteTester $I) {

		$I->comment('Reset plugins to their normal state');

		// Lumière on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('lumiere-movies');

		// Polylang on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('polylang');
	}


	/**
	 * Enable Admin option
	 *
	 * @before login
	 */
	public function enableAdminGeneralOptions(AcceptanceRemoteTester $I) {

		$I->comment('Reset Admin General Options to their normal state');

		// Big menu on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#miscpart');
		$I->CustomActivateCheckbox('#imdb_imdbwordpress_bigmenu_yes', 'lumiere_update_general_settings');

		// Left menu on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_bigmenu');
		$I->CustomActivateCheckbox('#imdb_imdbwordpress_tooladminmenu_yes', 'lumiere_update_general_settings');

		// Taxonomy on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#lumiere_update_general_settings' );

		// Remove all links off
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbtaxonomy');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#lumiere_update_general_settings' );

		// Auto widget off
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdblinkingkill');
		$I->CustomDisableCheckbox('#imdb_imdbautopostwidget_yes', '#lumiere_update_general_settings' );

		// Keep settings on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbkeepsettings_yes', 'lumiere_update_general_settings');

		// Debug on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbdebug_yes', '#lumiere_update_general_settings');

		// Display one screen on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbdebugscreen_yes', '#lumiere_update_general_settings');

		// Save log off
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbdebuglog_yes', '#lumiere_update_general_settings');

		// Switch back To Highslide
		$I->SwitchModalWindow('Highslide');

		// Disable No Links
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#miscpart');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#lumiere_update_general_settings' );
	}
}


