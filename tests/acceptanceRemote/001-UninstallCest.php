<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class UninstallCest {

	/** Stock the base remote URL
	 *
	 */
	var $url_base_remote = "";

	/** Stock mounted the root remote path
	 *
	 */
	var $root_remote = "";

	/** Stock the root remote path
	 *
	 */
	var $root_remote_path = "";

	/** Stock the root remote path
	 *
	 */
	var $host = "";

	/** Stock the root remote path
	 *
	 */
	var $user_name = "";

	public function __construct(){

		$this->url_base_remote = $_ENV['TEST_REMOTE_WP_URL'];
		$this->root_remote = $_ENV['WP_ROOT_REMOTE_FOLDER'];
		$this->root_remote_path = $_ENV['TEST_REMOTE_FTP_PATH'];

		$this->host = $_ENV['TEST_REMOTE_FTP_HOST'];
		$this->user_name = $_ENV['TEST_REMOTE_FTP_USERNAME'];

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

		$I->login_universal($I);

	}

	/** Uninstall the plugin to do the tests on a fresh install
	 *
	 * @before login
	 * 
	 */
	public function pluginUninstall(AcceptanceRemoteTester $I, \Codeception\Module\Cli $shell) {

		$wpcontent = $this->root_remote . '/wp-content/';
		$dir_plugin_lumiere = $wpcontent . 'plugins/lumiere-movies/';
		$remote_cred = $this->user_name.'@'.$this->host;
		$remote_plugin_path = $this->root_remote_path.'/wp-content/plugins';
		$remote_plugin_path_lumiere = $this->root_remote_path.'/wp-content/plugins/lumiere-movies';
		$remote_wpcontent_path = $this->root_remote_path.'/wp-content';

		$I->wantTo('Do Lumière plugin uninstall for a fresh start');

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
		$I->comment( \Helper\Color::set('Saving plugin folder...', 'magenta+blink') );
		$shell->runShellCommand('scp -r '.$remote_cred.':'.$remote_plugin_path_lumiere.' '.$remote_cred.':'.$remote_wpcontent_path.'/');

		// Delete plugin
		$I->amOnPage('/wp-admin/plugins.php');
		$I->wait(2);
		$I->scrollTo('#delete-lumiere-movies');
		$I->executeJS("return jQuery('#delete-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->acceptPopup(); # Are you sure you want to remove the plugin?
		$I->comment(\Helper\Color::set("**Lumière plugin deleted**", "italic+bold+cyan"));

		// Revert back the saved plugin folder
		$I->comment(\Helper\Color::set("**Copy back to the saved plugin folder**", 'italic+bold+cyan'));
		$I->comment( \Helper\Color::set('Restoring plugin folder...', 'magenta+blink') );
		$shell->runShellCommand('scp -r '.$remote_cred.':'.$remote_wpcontent_path.'/lumiere-movies'.' '.$remote_cred.':'.$remote_plugin_path.'/');
		$I->seeFileFound( 'lumiere-movies.php', $wpcontent . 'lumiere-movies/' );
		$I->comment( \Helper\Color::set('Deleting temporary plugin folder...', 'magenta+blink') );
		$shell->runShellCommand("ssh ".$remote_cred." 'rm -R ".$remote_wpcontent_path."/lumiere-movies"."'");
		$I->seeFileFound( 'lumiere-movies.php', $dir_plugin_lumiere );

		// Activate plugin
		$I->amOnPage('/wp-admin/plugins.php');
		$I->wait(2);
		$I->scrollTo('#activate-lumiere-movies');
		$I->executeJS("return jQuery('#activate-lumiere-movies').get(0).click()");

	}


}



