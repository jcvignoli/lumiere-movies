<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class EndCest {

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
		AcceptanceTrait::login_universal($I);
	}

	/** Enable defaults Plugins
	 *
	 * @before login
	 *
	 */
	public function enablePlugins(AcceptanceRemoteTester $I) {

		$I->wantTo('Enable taxonomy (normal state)');

		// Lumière on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('lumiere-movies');

		// Classic editor on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('classic-editor');

		// Polylang on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('polylang');
	}


	/** Enable Admin option
	 *
	 * @before login
	 *
	 */
	public function enableAdminGeneralOptions(AcceptanceRemoteTester $I) {

		$I->wantTo('Enable Admin General Options (normal state)');

		// Big menu on
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#miscpart');
		$I->CustomActivateCheckbox('#imdb_imdbwordpress_bigmenu_yes', 'update_imdbSettings');

		// Left menu on
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_bigmenu');
		$I->CustomActivateCheckbox('#imdb_imdbwordpress_tooladminmenu_yes', 'update_imdbSettings');

		// Taxonomy on
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

		// Remove all links off
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbtaxonomy');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#update_imdbSettings' );

		// Auto widget off
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdblinkingkill');
		$I->CustomDisableCheckbox('#imdb_imdbautopostwidget_yes', '#update_imdbSettings' );

		// Keep settings on
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbkeepsettings_yes', 'update_imdbSettings');

		// Debug on
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbdebug_yes', '#update_imdbSettings');

		// Display one screen on
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbdebugscreen_yes', '#update_imdbSettings');

		// Save log off
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbdebuglog_yes', '#update_imdbSettings');
	}
}


