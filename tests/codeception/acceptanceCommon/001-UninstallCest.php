<?php
// Class meant to test WordPress install (a WebDriver is needed for JS execution)

use \PHPUnit\Framework\Assert;

class UninstallCest {

	/**
	 * Properties
	 */
	private string $base_url;
	private string $base_path;
	private string $real_path;
	private string $host;
	private string $user_name;

	/**
	 * Build the properties
	 */
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

	/**
	 * Executed before all methods, at the beginning of the class
	 */
	public function _before(AcceptanceRemoteTester $I){
		$I->comment(\Helper\Color::set("#Code _before#", "italic+bold+cyan"));
	}

	/**
	 * Executed after all methods, at the end of the class
	 */
	public function _after(AcceptanceRemoteTester $I){
		$I->comment(\Helper\Color::set("#Code _after#", "italic+bold+cyan"));
	}

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Uninstall the plugin to do the tests on a fresh install
	 * Select either the remote or local method based on a var in _bootstrap.*.php
	 *
	 * @before login
	 */
	public function pluginUninstall(AcceptanceRemoteTester $I, \Codeception\Module\Cli $shell) {
		if ( DEVELOPMENT_ENVIR === 'remote' ) {
			$this->remoteUninstall( $I, $shell );
		} elseif ( DEVELOPMENT_ENVIR === 'local' ) {
			$this->localUninstall( $I, $shell );
		} else {
			Assert::fail('!!Neither local nor remote environment, something strange happened!!');
		}
	}

	/**
	 * Uninstall the plugin to do the tests on a fresh install -LOCAL
	 * Take into account that a symbolic link is used
	 */
	private function localUninstall(AcceptanceRemoteTester $I, \Codeception\Module\Cli $shell) {

		// Build the path vars
		$wpcontent = $this->base_path . '/wp-content';
		$wpplugins = $wpcontent . '/plugins';
		$dir_plugin_lumiere = $wpplugins . '/lumiere-movies';
		
		$I->comment('Do Lumière *LOCAL* plugin uninstall for a fresh start');

		// Disable keep settings options and deactivate Lumière
		$this->disable_keepsettings_and_deactivate( $I );
		
		// Save plugin directory
		$I->comment( \Helper\Color::set( "**See if Lumière directory exists and copy**", "italic+bold+cyan" ) );
		$I->customSeeFile( $dir_plugin_lumiere . '/lumiere-movies.php' );
		$I->comment( \Helper\Color::set('Saving plugin directory...', 'yellow+blink') );
		
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
		$I->comment(\Helper\Color::set("**Copy back to the saved plugin directory**", 'italic+bold+cyan'));
		
		// Make sure Lumière! directory has been deleted
		// need to refresh the page after plugin deletion, so jumping from nonsense pages to others until checking if file, otherwise the file is FOUND!
		$I->amOnPluginsPage();
		$I->wait(1);
		$I->click( "Blog ext (codeception)" );
		$I->wait(1);
		$I->reloadPage();
		$I->wait(10);
		$I->customDontSeeFile( $dir_plugin_lumiere . '/lumiere-movies.php' );
		
		// Restore the symbolic link
		$I->comment( 'Move back the symbolic link' );		
		$shell->runShellCommand('mv ' . $wpcontent . '/lumiere-save ' . $dir_plugin_lumiere );

		$I->customSeeFile( $dir_plugin_lumiere . '/lumiere-movies.php' ); // both seeFile of customSeeFile, don't work...
		$I->comment( \Helper\Color::set('Deleting temporary plugin directory...', 'yellow') );
		
		$shell->runShellCommand( ' rm -R ' . $wpcontent . '/lumiere-movies' );
		
		$I->seeFileFound( $dir_plugin_lumiere . '/lumiere-movies.php' ); // both seeFile of customSeeFile, don't work... 

		// Activate Lumière
		$this->activate_plugin( $I );
	}
	
	/**
	 * Uninstall the plugin to do the tests on a fresh install - REMOTE
	 */
	private function remoteUninstall(AcceptanceRemoteTester $I, \Codeception\Module\Cli $shell) {
	
		// Build the path vars
		$wpcontent = $this->base_path . '/wp-content';
		$wpplugins = $wpcontent . '/plugins';
		$dir_plugin_lumiere = $wpplugins . '/lumiere-movies';
		$remote_cred = $this->user_name.'@'.$this->host;
		$remote_plugin_path = $this->real_path.'/wp-content/plugins';
		$remote_plugin_path_lumiere = $this->real_path.'/wp-content/plugins/lumiere-movies';
		$remote_wpcontent_path = $this->real_path.'/wp-content';

		$I->comment('Do Lumière *REMOTE* plugin uninstall for a fresh start');

		// Make local connexion
		// Not needed anymore, using scp and ssh
		// $I->activateLocalMount( $this->base_path, $shell );

		// Disable keep settings options and deactivate Lumière
		$this->disable_keepsettings_and_deactivate( $I );
		$I->wait(4);

		// Save plugin directory
		$I->comment( \Helper\Color::set( "**See if Lumière directory exists and copy**", "italic+bold+cyan" ) );

		$I->customSeeFile( $dir_plugin_lumiere . '/lumiere-movies.php' );
		$I->comment( \Helper\Color::set('Saving plugin directory...', 'yellow+blink') );
		
		$shell->runShellCommand('scp -r '.$remote_cred.':'.$remote_plugin_path_lumiere.' '.$remote_cred.':'.$remote_wpcontent_path.'/');

		// Uninstall plugin
		$this->uninstall_plugin( $I );

		// Revert back the saved plugin directory
		$I->comment(\Helper\Color::set("**Copy back to the saved plugin directory**", 'italic+bold+cyan'));
		$I->comment( \Helper\Color::set('Restoring plugin directory...', 'yellow+blink') );
		
		$shell->runShellCommand('scp -r ' . $remote_cred.':'.$remote_wpcontent_path.'/lumiere-movies'.' '.$remote_cred.':'.$remote_plugin_path.'/');

		$I->customSeeFile( $dir_plugin_lumiere . '/lumiere-movies.php' ); // both seeFile of customSeeFile, don't work...

		$I->comment( \Helper\Color::set('Deleting temporary plugin directory...', 'yellow+blink') );
		
		$shell->runShellCommand("ssh ".$remote_cred." 'rm -R ".$remote_wpcontent_path."/lumiere-movies'");
		
		$I->customSeeFile( $dir_plugin_lumiere . '/lumiere-movies.php' ); // both seeFile of customSeeFile, don't work... 

		// Activate Lumière
		$this->activate_plugin( $I );
	}

	/**
	 * Disable Lumière keeps settings and Uninstall the plugin
	 */
	private function disable_keepsettings_and_deactivate( AcceptanceRemoteTester $I ) {
		// Disable keep settings option to get rid of all options
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('lumiere-movies');
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->wait(2);
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbkeepsettings_yes', 'lumiere_update_general_settings');

		// Deactivate plugin
		$I->amOnPluginsPage();
		$I->scrollTo('#deactivate-lumiere-movies');
		$I->executeJS("return jQuery('#deactivate-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->acceptPopup(); # Are you sure you want to remove the plugin?
	}

	/**
	 * Uninstall (delete) the plugin
	 */
	private function uninstall_plugin( AcceptanceRemoteTester $I ) {
		$I->amOnPluginsPage();
		$I->wait(2);
		$I->scrollTo('#delete-lumiere-movies');
		$I->executeJS("return jQuery('#delete-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->acceptPopup(); # Are you sure you want to remove the plugin?
		$I->comment(\Helper\Color::set("**Lumière plugin deleted**", "italic+bold+cyan"));
	}

	/**
	 * Reactivate the plugsin
	 */
	private function activate_plugin( AcceptanceRemoteTester $I ) {
		$I->amOnPluginsPage();
		$I->wait(2);
		$I->scrollTo('#activate-lumiere-movies');
		$I->executeJS("return jQuery('#activate-lumiere-movies').get(0).click()");
	}
}
