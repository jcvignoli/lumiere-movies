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

	/** Check if plugin can be installed
	 *
	 * @before login
	 *
	 */
	public function checkCanInstallLumiere(AcceptanceRemoteTester $I, \Codeception\Module\Cli $shell) {

		$I->wantTo('Check if Lumière plugin can be installed');

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

}
