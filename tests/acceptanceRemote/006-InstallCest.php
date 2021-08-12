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

		AcceptanceTrait::login_universal($I);

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
		$I->amOnPage('/wp-admin/plugins.php');
		$I->maybeDeactivatePlugin('lumiere-movies');
		$I->wait(2);
		$I->amOnPage('/wp-admin/plugins.php');
		$I->maybeActivatePlugin('lumiere-movies');
		$I->wait(2);

		// Check if cron has been installed
		$I->amOnPage("/wp-admin/tools.php?page=crontrol_admin_manage_page");
		$I->wait(2);
		$I->see('lumiere_cron_hook');

	}


	/** Check if plugin activation set up crons
	 *
	 * @before login
	 *
	 */
	public function checkDeactivationFollowsKeepSettingsOption(AcceptanceRemoteTester $I) {

		$I->wantTo('Check if keep settings option is followed on deactivation');

		$I->amOnPage('/wp-admin/plugins.php');
		$I->maybeActivatePlugin('lumiere-movies');

		// Disable keep settings option, so get a confirmation popup
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbkeepsettings_yes', 'update_imdbSettings');
		$I->amOnPage('/wp-admin/plugins.php');
		$I->scrollTo('#deactivate-lumiere-movies');
		$I->executeJS("return jQuery('#deactivate-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->seeInPopup('You have selected to not keep your settings upon deactivation.');
		$I->acceptPopup();
		$I->wait(2);

		$I->amOnPage('/wp-admin/plugins.php');
		$I->wait(2);
		$I->scrollTo('#activate-lumiere-movies');
		$I->executeJS("return jQuery('#activate-lumiere-movies').get(0).click()");
		$I->wait(5);
		$I->see('Plugin activated.');

		// Enable keep settings option, so no popup
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbkeepsettings_yes', 'update_imdbSettings');
		$I->amOnPage('/wp-admin/plugins.php');
		$I->wait(5);
		$I->executeJS("return jQuery('#deactivate-lumiere-movies').get(0).click()");
		$I->wait(5);
		$I->see('Plugin deactivated.');
		$I->wait(2);
		$I->amOnPage('/wp-admin/plugins.php');
		$I->executeJS("return jQuery('#activate-lumiere-movies').get(0).click()");
		$I->wait(5);
		$I->see('Plugin activated.');
	}
}



