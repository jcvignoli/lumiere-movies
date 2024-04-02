<?php
/**
 * Class meant to test wordpress install (a WebDriver is needed for JS execution)
 */
class InstallCest {

	/* Stock the base remote URL
	 *
	 */
	var $base_url = "";

	/* Stock the root remote path
	 *
	 */
	var $base_path = "";

	public function __construct(){

		// Build vars
		$remote_or_local = defined( 'DEVELOPMENT_ENVIR' ) ? DEVELOPMENT_ENVIR : '';
		$final_var_url = 'TEST_' . strtoupper( $remote_or_local ) . '_WP_URL';
		$final_var_root_folder = 'WP_ROOT_' . strtoupper( $remote_or_local ) . '_FOLDER';

		// Build properties
		$this->base_url = $_ENV[ $final_var_url ];
		$this->base_path = $_ENV[$final_var_root_folder];

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

	/**
	 * Check if plugin activation set up crons
	 *
	 * @before login
	 *
	 */
	public function checkInstallSetupCron(AcceptanceRemoteTester $I) {

		$I->comment('Check if Lumière plugin set up crons');

		$I->amOnPluginsPage();
		$I->maybeDeactivatePlugin('lumiere-movies');
		$I->wait(2);
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('lumiere-movies');
		$I->wait(2);

		// Check if cron has been installed
		$I->maybeActivatePlugin('wp-crontrol');
		$I->amOnPage( AcceptanceSettings::ADMIN_POST_CRON_MANAGE );
		$I->wait(2);
		$I->see('lumiere_exec_once_update');
	}


	/**
	 * Check if popup when keep settings are unselected is displayed upon plugin deactivation
	 *
	 * @before login
	 *
	 */
	public function checkKeepsettingsPopupDeactivation(AcceptanceRemoteTester $I) {

		$I->comment('Check if keep settings option is followed on deactivation');

		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('lumiere-movies');

		// Disable keep settings option, so get a confirmation popup
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbkeepsettings_yes', 'lumiere_update_general_settings');
		$I->amOnPluginsPage();
		$I->scrollTo('#deactivate-lumiere-movies');
		$I->executeJS("return jQuery('#deactivate-lumiere-movies').get(0).click()");
		$I->wait(2);
		$I->seeInPopup('You have selected to not keep your settings upon uninstall');
		$I->acceptPopup();
		$I->wait(2);

		$I->amOnPluginsPage();
		$I->wait(2);
		$I->scrollTo('#activate-lumiere-movies');
		$I->executeJS("return jQuery('#activate-lumiere-movies').get(0).click()");
		$I->wait(10);
		$I->see('Plugin activated');

		// Enable keep settings option, so no popup
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbkeepsettings_yes', 'lumiere_update_general_settings');
		$I->amOnPluginsPage();
		$I->wait(10);
		$I->executeJS("return jQuery('#deactivate-lumiere-movies').get(0).click()");
		$I->wait(5);
		$I->see('Plugin deactivated');
		$I->wait(2);
		$I->amOnPluginsPage();
		$I->wait(2);
		$I->scrollTo('#activate-lumiere-movies');
		$I->executeJS("return jQuery('#activate-lumiere-movies').get(0).click()");
		$I->wait(5);
		$I->see('Plugin activated');
	}
}



