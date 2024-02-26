<?php

# THIS never restore the lumiere-movies folder in wp-content/plugins

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class UninstallCest {

	/**
	 * Vars
	 */
	private string $base_url;
	private string $base_path;
	private string $real_path;
	private string $host;
	private string $user_name;

	public function __construct(){

		// Build vars
		$remote_or_local = defined( 'DEVELOPMENT_ENVIR' ) ? DEVELOPMENT_ENVIR : '';
		$final_var_url = 'TEST_' . strtoupper( $remote_or_local ) . '_WP_URL';
		$final_var_root_folder = 'WP_ROOT_' . strtoupper( $remote_or_local ) . '_FOLDER';

		// Build properties
		$this->base_url = $_ENV[ $final_var_url ];
		$this->base_path = $_ENV[$final_var_root_folder];
		
		$this->real_path = '';
		$this->host = '';
		$this->user_name = '';
		
		if ( DEVELOPMENT_ENVIR === 'remote' ) {
			$this->real_path = $_ENV['TEST_REMOTE_FTP_PATH'];
			$this->host = $_ENV['TEST_REMOTE_FTP_HOST'];
			$this->user_name = $_ENV['TEST_REMOTE_FTP_USERNAME'];
		} elseif ( DEVELOPMENT_ENVIR === 'local' ) {
			$this->real_path = $_ENV['WP_ROOT_LOCAL_FOLDER'];
		}			
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

	/**
	 * Uninstall the plugin to do the tests on a fresh install
	 *
	 * @before login
	 */
	public function pluginUninstall(AcceptanceRemoteTester $I, \Codeception\Module\Cli $shell) {

		// Build the path vars
		$wpcontent = $this->base_path . '/wp-content/';
		$dir_plugin_lumiere = $wpcontent . 'plugins/lumiere-movies/';
		$remote_cred = $this->user_name.'@'.$this->host;
		$remote_plugin_path = $this->real_path.'/wp-content/plugins';
		$remote_plugin_path_lumiere = $this->real_path.'/wp-content/plugins/lumiere-movies';
		$remote_wpcontent_path = $this->real_path.'/wp-content';

		$I->comment('Do Lumière plugin uninstall for a fresh start');

		// Make local connexion
		$I->activateLocalMount( $this->base_path, $shell );

		// Disable keep settings option to get rid of all options
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->maybeActivatePlugin('lumiere-movies');
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->wait(2);
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbkeepsettings_yes', 'update_imdbSettings');

		// Deactivate plugin
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->scrollTo('#deactivate-lumiere-movies');
		$I->executeJS("return jQuery('#deactivate-lumiere-movies').get(0).click()");
		$I->wait(4);
		$I->acceptPopup(); # Are you sure you want to remove the plugin?
		$I->wait(4);

		// Save plugin directory
		$I->comment( \Helper\Color::set( "**See if Lumière directory exists and copy**", "italic+bold+cyan" ) );
		$I->customSeeFile( $dir_plugin_lumiere . 'lumiere-movies.php' );
		$I->comment( \Helper\Color::set('Saving plugin directory...', 'yellow+blink') );
		
		if ( DEVELOPMENT_ENVIR === 'remote' ) {
			$shell->runShellCommand('scp -r '.$remote_cred.':'.$remote_plugin_path_lumiere.' '.$remote_cred.':'.$remote_wpcontent_path.'/');
		} elseif ( DEVELOPMENT_ENVIR === 'local' ) {
			// Copy with removing the symbolic link property from the origin, making a regular directory
			$shell->runShellCommand('cp -Ra -L ' . $remote_plugin_path_lumiere . ' ' . $remote_wpcontent_path.'/');
			// Save the symbolic link rather that deleting it, have to rebuild it otherwhise and I don't have the full working-env/dist as envir var
			$shell->runShellCommand('mv ' . $remote_plugin_path_lumiere . ' ' . $remote_wpcontent_path . '/lumiere-save' );
			// Copy back the regular directory into plugins/
			$shell->runShellCommand('cp -Ra ' . $remote_wpcontent_path . '/lumiere-movies ' . $remote_plugin_path.'/');
			// Make sure we can delete plugins/lumiere-movies, give full rights
			$shell->runShellCommand('chmod -R 777 ' . $remote_plugin_path_lumiere );
		}

		// Delete plugin
		$I->amOnPage('/wp-admin/plugins.php');
		$I->wait(2);
		$I->scrollTo('#delete-lumiere-movies');
		$I->executeJS("return jQuery('#delete-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->acceptPopup(); # Are you sure you want to remove the plugin?
		$I->comment(\Helper\Color::set("**Lumière plugin deleted**", "italic+bold+cyan"));

		// Revert back the saved plugin directory
		$I->comment(\Helper\Color::set("**Copy back to the saved plugin directory**", 'italic+bold+cyan'));
		$I->comment( \Helper\Color::set('Restoring plugin directory...', 'yellow+blink') );
		
		if ( DEVELOPMENT_ENVIR === 'remote' ) {
			$shell->runShellCommand('scp -r ' . $remote_cred.':'.$remote_wpcontent_path.'/lumiere-movies'.' '.$remote_cred.':'.$remote_plugin_path.'/');
		} elseif ( DEVELOPMENT_ENVIR === 'local' ) {
			// Make sure Lumière! directory has been deleted
//			$I->customDontSeeFile( $dir_plugin_lumiere . 'lumiere-movies.php' ); // both seeFile of customSeeFile, don't work...
			// Restore the symbolic link
			$shell->runShellCommand('mv ' . $remote_wpcontent_path . '/lumiere-save ' . $remote_plugin_path_lumiere  );	
		}

//		$I->customSeeFile( $dir_plugin_lumiere . 'lumiere-movies.php' ); // both seeFile of customSeeFile, don't work...
		$I->comment( \Helper\Color::set('Deleting temporary plugin directory...', 'yellow+blink') );
		
		if ( DEVELOPMENT_ENVIR === 'remote' ) {
			$shell->runShellCommand("ssh ".$remote_cred." 'rm -R ".$remote_wpcontent_path."/lumiere-movies"."'");
		} elseif ( DEVELOPMENT_ENVIR === 'local' ) {
			$shell->runShellCommand( ' rm -R ' . $remote_wpcontent_path.'/lumiere-movies' );
		}
		
//		$I->customSeeFile( $dir_plugin_lumiere . 'lumiere-movies.php' ); // both seeFile of customSeeFile, don't work... 

		// Activate plugin
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->wait(2);
		$I->scrollTo('#activate-lumiere-movies');
		$I->executeJS("return jQuery('#activate-lumiere-movies').get(0).click()");

	}


}
