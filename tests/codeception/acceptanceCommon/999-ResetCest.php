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

		// Classic editor on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('classic-editor');

		// Polylang on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('polylang');

		// Query Monitor on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('query-monitor');
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
		$I->CustomActivateCheckbox('#imdb_imdbwordpress_bigmenu_yes', 'update_imdbSettings');

		// Left menu on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_bigmenu');
		$I->CustomActivateCheckbox('#imdb_imdbwordpress_tooladminmenu_yes', 'update_imdbSettings');

		// Taxonomy on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

		// Remove all links off
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbtaxonomy');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#update_imdbSettings' );

		// Auto widget off
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdblinkingkill');
		$I->CustomDisableCheckbox('#imdb_imdbautopostwidget_yes', '#update_imdbSettings' );

		// Keep settings on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbkeepsettings_yes', 'update_imdbSettings');

		// Debug on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbdebug_yes', '#update_imdbSettings');

		// Display one screen on
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbdebugscreen_yes', '#update_imdbSettings');

		// Save log off
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbdebuglog_yes', '#update_imdbSettings');

		// Switch back To Highslide
		$I->SwitchModalWindow('Highslide');

		// Disable No Links
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#miscpart');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#update_imdbSettings' );
	}
}


