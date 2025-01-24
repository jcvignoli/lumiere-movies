<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;
use \PHPUnit\Framework\Assert;

/**
 * Class meant to test WordPress install
 */
class UninstallCest {

	/**
	 * Executed before all methods, at the beginning of the class
	 */
	public function _before(AcceptanceTester $I){
		$I->comment(Helper\Color::set("#Code _before#", "italic+bold+cyan"));
	}

	/**
	 * Executed after all methods, at the end of the class
	 */
	public function _after(AcceptanceTester $I){
		$I->comment(Helper\Color::set("#Code _after#", "italic+bold+cyan"));
	}
	
	/**
	 * Stop the test if anything fails here, we many not have lumière installed
	 */
	public function _failed(AcceptanceTester $I) {
		$I->comment( "\n\n" . Helper\Color::set('!!!!! Cannot process the suite if the uninstall fails, exit', "bold+red") );
		exit(1);
	}

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Uninstall the plugin to do the tests on a fresh install
	 * Select either the remote or local method based on a var in _bootstrap.*.php
	 *
	 * @before login
	 */
	public function pluginUninstall(AcceptanceTester $I, \Codeception\Module\Cli $shell) {
		if ( $I->getRemoteOrLocal() === 'remote' ) {
			$this->remoteUninstall( $I, $shell );
		} elseif ( $I->getRemoteOrLocal() === 'local' ) {
			$this->localUninstall( $I, $shell );
		} else {
			Assert::fail('!!Neither local nor remote environment, something strange happened!!');
		}
	}

	/**
	 * Uninstall the plugin to do the tests on a fresh install -LOCAL
	 * Take into account that a symbolic link is used
	 */
	private function localUninstall(AcceptanceTester $I, \Codeception\Module\Cli $shell) {

		// Build the path vars
		$wpcontent = $I->getCustomBasePath() . '/wp-content'; // In acceptance helper
		$wpplugins = $wpcontent . '/plugins';
		$dir_plugin_lumiere = $wpplugins . '/lumiere-movies';
		
		$I->comment('Do Lumière *LOCAL* plugin uninstall for a fresh start');

		// Disable keep settings options and deactivate Lumière
		$this->disable_keepsettings_and_deactivate( $I );
		
		// Save plugin directory
		$I->comment( Helper\Color::set( "**See if Lumière directory exists and copy**", "italic+bold+cyan" ) );
		$I->amOnPluginsPage();
		$I->seePluginInstalled('lumiere-movies');
		$I->comment( Helper\Color::set('Saving plugin directory...', 'yellow+blink') );
		
		// Copy with removing the symbolic link property from the origin, making a regular directory
		$I->comment('Copy the plugin as a regular folder');
		$shell->runShellCommand( 'cp -Ra -L ' . $dir_plugin_lumiere . ' ' . $wpcontent . '/' );
		// Move the plugin
		$I->comment('Move and rename the symbolic link');
		$shell->runShellCommand( 'mv ' . $dir_plugin_lumiere . ' ' . $wpcontent . '/lumiere-save' );
		// Copy back the regular directory into plugins/
		$I->comment('Copy the plugin saved in wp-content back to plugins');
		$shell->runShellCommand( 'cp -Ra ' . $wpcontent . '/lumiere-movies ' . $wpplugins . '/' );
		// Make sure we can delete plugins/lumiere-movies, give full rights
		$I->comment('Give the permissions to the lumiere-movies');
		$shell->runShellCommand( 'chmod -R 777 ' . $dir_plugin_lumiere );

		// Uninstall plugin
		$this->uninstall_plugin( $I );
		
		// Revert back the saved plugin directory
		$I->comment(Helper\Color::set("**Copy back to the saved plugin directory**", 'italic+bold+cyan'));
		
		// Restore the symbolic link
		// These tricks are meant to ensure we really don't see the plugin anymore, it fails quite often
		$I->amOnPluginsPage();
		$I->reloadPage();
		$I->scrollTo('#deactivate-lost-highway-extra-functions');
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL_FR );
		$I->amOnPluginsPage();
		$I->reloadPage();
		$I->scrollTo('#deactivate-lost-highway-extra-functions');
		$I->dontSeePluginInstalled('lumiere-movies');
		$I->comment( 'Move back the symbolic link' );		
		$shell->runShellCommand('mv ' . $wpcontent . '/lumiere-save ' . $dir_plugin_lumiere );

		// Delete the plugin
		$I->amOnPluginsPage();
		$I->wait(4);
		$I->seePluginInstalled('lumiere-movies');
		$I->comment( Helper\Color::set('Deleting temporary plugin directory...', 'yellow') );
		$shell->runShellCommand( 'rm -R ' . $wpcontent . '/lumiere-movies' );

		// End by activating the plugin		
		$I->amOnPluginsPage();
		$I->wait(2);
		$I->seePluginInstalled('lumiere-movies');
		$I->activatePlugin('lumiere-movies');
	}
	
	/**
	 * Uninstall the plugin to do the tests on a fresh install - REMOTE
	 */
	private function remoteUninstall(AcceptanceTester $I, \Codeception\Module\Cli $shell) {
	
		// Build the path vars
		$wpcontent = $I->getCustomBasePath() . '/wp-content'; // In acceptance helper
		$wpplugins = $wpcontent . '/plugins';
		$dir_plugin_lumiere = $wpplugins . '/lumiere-movies';
		$remote_cred = $_ENV['TEST_REMOTE_FTP_USERNAME'] . '@'. $_ENV['TEST_REMOTE_FTP_HOST'];
		$remote_plugin_path = $_ENV['TEST_REMOTE_FTP_PATH'] . '/wp-content/plugins';
		$remote_plugin_path_lumiere = $_ENV['TEST_REMOTE_FTP_PATH'] . '/wp-content/plugins/lumiere-movies';
		$remote_wpcontent_path = $_ENV['TEST_REMOTE_FTP_PATH'] . '/wp-content';

		$I->comment('Do Lumière *REMOTE* plugin uninstall for a fresh start');

		// Disable keep settings options and deactivate Lumière
		$this->disable_keepsettings_and_deactivate( $I );

		// Save plugin directory
		$I->comment( Helper\Color::set( "**See if Lumière directory exists and copy**", "italic+bold+cyan" ) );

		$I->amOnPluginsPage();
		$I->wait(2);
		$I->seePluginInstalled('lumiere-movies');
		$I->comment( Helper\Color::set('Saving plugin directory...', 'yellow+blink') );
		
		// Copy lumiere-movies from plugins to wp-content
		$shell->runShellCommand( 'ssh ' . $remote_cred . " 'cp -R ".$remote_plugin_path_lumiere . ' ' . $remote_wpcontent_path . "/'" );

		// Delete plugin
		$this->uninstall_plugin( $I );

		// Copy lumiere-movies from wp-content to plugins
		$I->comment(Helper\Color::set("**Copy back to the saved plugin directory**", 'italic+bold+cyan'));
		$I->comment( Helper\Color::set('Restoring plugin directory...', 'yellow+blink') );
		$shell->runShellCommand( 'ssh ' . $remote_cred." 'cp -R " . $remote_wpcontent_path . '/lumiere-movies'.' '.$remote_plugin_path."/'");

		// Delete saved lumiere-movies folder in wp-content
		$I->amOnPluginsPage();
		$I->wait(2);
		$I->seePluginInstalled('lumiere-movies');
		$I->comment( Helper\Color::set('Deleting temporary plugin directory...', 'yellow+blink') );
		$shell->runShellCommand( 'ssh ' . $remote_cred . " 'rm -R " . $remote_wpcontent_path . "/lumiere-movies'");

		// End by activating the plugin		
		$I->amOnPluginsPage();
		$I->wait(2);
		$I->seePluginInstalled('lumiere-movies');
		$I->activatePlugin('lumiere-movies');
	}

	/**
	 * Disable Lumière keeps settings and Uninstall the plugin
	 */
	private function disable_keepsettings_and_deactivate( AcceptanceTester $I ) {
		// Disable keep settings option to get rid of all options
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('lumiere-movies');
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->wait(2);
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomDisableCheckbox('#imdb_imdbkeepsettings_yes', 'lumiere_update_main_settings');

		// Deactivate plugin
		$I->amOnPluginsPage();
		$I->scrollTo('#deactivate-lumiere-movies');
		$I->executeJS("return jQuery('#deactivate-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->acceptPopup(); # Are you sure you want to remove the plugin?
	}

	/**
	 * Uninstall (delete) the plugin
	 * Haven't found any WPLoader function
	 */
	private function uninstall_plugin( AcceptanceTester $I ) {
		$I->amOnPluginsPage();
		$I->wait(2);
		$I->scrollTo('#delete-lumiere-movies');
		$I->executeJS("return jQuery('#delete-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->acceptPopup(); # Are you sure you want to remove the plugin?
		$I->comment(Helper\Color::set("**Lumière plugin deleted**", "italic+bold+cyan"));
	}
}
