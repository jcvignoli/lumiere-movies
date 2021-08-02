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
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->wantTo('Start an admin session');
		$I->loginAsAdmin();
	}

	/** Enable debug functions
	 *
	 */
	private function enableDebug(AcceptanceRemoteTester $I) {
		$I->wantTo('Activate debug option');
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbdebug_yes', '#update_imdbSettings');
	}

	/** Reset options to what they should be
	 *
	 * @before login
	 * @after enableDebug
	 *
	 */
	public function resetOptions(AcceptanceRemoteTester $I) {

		/* const */
		$url_base = $_ENV['TEST_REMOTE_WP_URL'];

		$I->wantTo('Reset options to their normal state');

		// Re-enable taxonomy
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

		// Re-enable classic editor plugni
		$I->amOnPluginsPage();
		/*	Conditional plugin activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $plugin is disabled, activate it */
		$I->CustomActivatePlugin('classic-editor');
	}

}
