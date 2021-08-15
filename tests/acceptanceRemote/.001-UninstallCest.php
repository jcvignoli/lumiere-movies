<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class UninstallCest {

	/** Stock the base remote URL
	 *
	 */
	var $url_base_remote = "";

	/** Stock the root remote path
	 *
	 */
	var $root_remote = "";

	public function __construct(){

		$this->url_base_remote = $_ENV['TEST_REMOTE_WP_URL'];
		$this->root_remote = $_ENV['WP_ROOT_REMOTE_FOLDER'];

	}


	public function _before(AcceptanceRemoteTester $I){

		$I->comment(\Helper\Color::set("#Code _before#", "italic+bold+cyan"));

	}

	public function _after(AcceptanceRemoteTester $I){

		$I->comment(\Helper\Color::set("#Code _after#", "italic+bold+cyan"));

	}

	/** Login to Wordpress
	 *  Trait function to keep the cookie active
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {

		AcceptanceTrait::login_universal($I);

	}

	/** Uninstall the plugin to do the tests on a fresh install
	 *
	 * @ before login
	 * Can't use universal login due to plugin activation/deactivation
	 */
	public function pluginUninstall(AcceptanceRemoteTester $I, \Codeception\Module\Cli $shell) {

		$wpcontent = $this->root_remote . '/wp-content/';
		$dir_plugin_lumiere = $wpcontent . 'plugins/lumiere-movies/';

		$I->wantTo('Do Lumière plugin uninstall for a fresh start');

		$I->loginAsAdmin();

		// Make local connexion
		$I->activateLocalMount( $this->root_remote, $shell );

		// Disable keep settings option to get rid of all options
		$I->amOnPage('/wp-admin/plugins.php');
		$I->maybeActivatePlugin('lumiere-movies');
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->wait(2);
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbkeepsettings_yes', 'update_imdbSettings');

		// Deactivate plugin
		$I->amOnPage('/wp-admin/plugins.php');
		$I->scrollTo('#deactivate-lumiere-movies');
		$I->executeJS("return jQuery('#deactivate-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->acceptPopup(); # Are you sure you want to remove the plugin?
		$I->wait(2);

		// Save plugin folder
		$I->comment(\Helper\Color::set("**See if Lumière folder exists and copy**", "italic+bold+cyan"));
		$I->seeFileFound( 'lumiere-movies.php', $dir_plugin_lumiere );
//		$I->copyDir( $dir_plugin_lumiere , $wpcontent . 'lumiere-movies'); # too slow! use rsync instead
		$I->copyWithRsync($dir_plugin_lumiere, $wpcontent . 'lumiere-movies/', $shell );

		// Delete plugin
		$I->amOnPage('/wp-admin/plugins.php');
		$I->wait(2);
		$I->scrollTo('#delete-lumiere-movies');
		$I->executeJS("return jQuery('#delete-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->acceptPopup(); # Are you sure you want to remove the plugin?
		$I->comment(\Helper\Color::set("**Lumière plugin deleted**", "italic+bold+cyan"));

		// Revert back the saved plugin folder
		$I->comment(\Helper\Color::set("**Copy back to the saved plugin folder**", "italic+bold+cyan"));
		$I->copyWithRsync($wpcontent . 'lumiere-movies/', $dir_plugin_lumiere, $shell );
		$I->seeFileFound( 'lumiere-movies.php', $wpcontent . 'lumiere-movies/' );
		$I->deleteDir( $wpcontent . 'lumiere-movies/' );
		$I->seeFileFound( 'lumiere-movies.php', $dir_plugin_lumiere );

		// Activate plugin
		$I->amOnPage('/wp-admin/plugins.php');
		$I->wait(2);
		$I->scrollTo('#activate-lumiere-movies');
		$I->executeJS("return jQuery('#activate-lumiere-movies').get(0).click()");

	}


}



