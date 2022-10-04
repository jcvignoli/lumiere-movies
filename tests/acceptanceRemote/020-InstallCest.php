<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class InstallCest {

	/* Stock the base remote URL
	 *
	 */
	var $url_base_remote = "";

	/* Stock the root remote path
	 *
	 */
	var $root_remote = "";

	public function __construct(){

		$this->url_base_remote = $_ENV['TEST_REMOTE_WP_URL'];
		$this->root_remote = $_ENV['WP_ROOT_REMOTE_FOLDER'];

	}


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

		$I->login_universal($I);

	}

	/** Check if plugin activation set up crons
	 *
	 * @before login
	 *
	 */
	public function checkInstallSetupCron(AcceptanceRemoteTester $I) {

		$I->wantTo('Check if Lumière plugin set up crons');

		// Activate then deactivate plugin
/*		$I->amOnPluginsPage();
		$I->deactivatePlugin('lumiere-movies');
		$I->amOnPluginsPage();
		$I->activatePlugin('lumiere-movies');
*/
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->maybeDeactivatePlugin('lumiere-movies');
		$I->wait(2);
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->maybeActivatePlugin('lumiere-movies');
		$I->wait(2);

		// Check if cron has been installed
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_POST_CRON_MANAGE );
		$I->wait(2);
		$I->see('lumiere_cron_hook');

	}


	/** Check if popup when keep settings are unselected is displayed upon plugin deactivation
	 *
	 * @before login
	 *
	 */
	public function checkKeepsettingsPopupDeactivation(AcceptanceRemoteTester $I) {

		$I->wantTo('Check if keep settings option is followed on deactivation');

		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->maybeActivatePlugin('lumiere-movies');

		// Disable keep settings option, so get a confirmation popup
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbkeepsettings_yes', 'update_imdbSettings');
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->scrollTo('#deactivate-lumiere-movies');
		$I->executeJS("return jQuery('#deactivate-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->seeInPopup('You have selected to not keep your settings upon uninstall');
		$I->acceptPopup();
		$I->wait(2);

		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->wait(2);
		$I->scrollTo('#activate-lumiere-movies');
		$I->executeJS("return jQuery('#activate-lumiere-movies').get(0).click()");
		$I->wait(10);
		$I->see('Plugin activated');

		// Enable keep settings option, so no popup
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbkeepsettings_yes', 'update_imdbSettings');
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->wait(10);
		$I->executeJS("return jQuery('#deactivate-lumiere-movies').get(0).click()");
		$I->wait(5);
		$I->see('Plugin deactivated');
		$I->wait(2);
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->wait(2);
		$I->scrollTo('#activate-lumiere-movies');
		$I->executeJS("return jQuery('#activate-lumiere-movies').get(0).click()");
		$I->wait(5);
		$I->see('Plugin activated');
	}
}



