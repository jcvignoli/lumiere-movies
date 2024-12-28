<?php

/** Trait for common functions used by both remote and local Acceptance tests
 * 
 */
trait AcceptanceTrait {

	/** Login to Wordpress
	 *  Save the cookies so no need to log again
	 */
	function login_universal(AcceptanceRemoteTester $I) {
		$I->comment('Start an admin session');
		if ($I->loadSessionSnapshot('login')) return;
		$I->loginAsAdmin();
		$I->saveSessionSnapshot('login');
	}

	/** Check if a checkbox is disabled, if activated uncheck it and then submit a form
	 * 
	 */
	function CustomDisableCheckbox($element, $submit){
		try {
			$this->seeCheckboxIsChecked("$element");
			$this->uncheckOption("$element");
			$this->click("$submit");
			$this->comment("[Action] Checkbox $element was activated, it has been disabled");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("[No Action] Checkbox $element was already disabled");
		} 
	}

	/** Check if a checkbox is activated, if disabled check it and then submit a form
	 * 
	 */
	function CustomActivateCheckbox($element, $submit){
		try {
			$this->seeCheckboxIsChecked("$element");
			$this->comment("[No Action] Checkbox $element was already activated");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->checkOption("$element");
			$this->click("$submit");
			$this->comment("[Action] Checkbox $element was disabled, it has been activated");
		} 
	}

	/**
	 * If text searched is not found, exit
	 */
	function CustomSeeExit($txt){
		try {
			$this->see("$txt");
			$this->comment("$txt is online, continuing...");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("$txt is offline, aborting the scenario...");
			exit();
		} 
	}

	/**
	 * Select a value in dropdown select, then submit
	 */
	function customSelectOption($element, $option, $submit){

		try {
			$this->selectOption($element, $option);
			$this->click("$submit");
			$this->comment("[Action] Selection has been switched to '$option");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("[No Action] $option was already selected.");
		} 

	}

	/**
	 * Delete theme file if it exists
	 */
	function customThemeFileExistsDelete($file){

		try {
			$this->seeThemeFileFound("$file");
			$this->deleteThemeFile("$file");
			$this->comment("[Action] File $file successfully deleted.");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("[No Action] No file $file was found.");
		} 

	}

	/**
	 * Copy theme file if it doesn't exists
	 */
	function maybeCopyThemeFile($item){

		try {
			$this->seeElement("#lumiere_copy_".$item);
			$this->click("#lumiere_copy_".$item." a");
			$this->comment("[Action] Template $item was successfully copied to theme folder.");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("[No Action] Template $item was already available in theme folder.");
		} 

	}

	/**
	 * Activate plugin if it is deactivated
	 * @param string $plugin Plugin's name as in WordPress plugin's folder (ie: lumiere-movies)
	 */
	function maybeActivatePlugin( $plugin ) {

		try {
			$this->executeJS("return jQuery('#activate-".$plugin."').get(0).click()");
			$this->comment("[Action] Plugin $plugin was unactive and has been successfully activated.");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("[No action] Plugin $plugin was already activated.");
		} 

	}

	/**
	 * Deactivate plugin if it is activated
	 * @param string $plugin Plugin's name as in WordPress plugin's folder (ie: lumiere-movies)
	 */
	function maybeDeactivatePlugin( $plugin ) {

		try {
			$this->executeJS("return jQuery('#deactivate-".$plugin."').get(0).click()");
			$this->comment("[Action] Plugin $plugin was active and has been successfully deactivated.");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("[No action] Plugin $plugin was already deactivated.");
		} 

	}


	/** Wait for JS to consider page has been loaded
	 * 
	 */
	function waitPageLoad( $timeout = 10 ) {

		$this->waitForJS('return document.readyState == "complete"', $timeout);
		$this->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', $timeout);
		$this->wait(1);
		
	}

	/**
	 * Copy files with Rsync (faster than native copy)
	 * 
	 * param $source the folder source path to be copied
	 * param $dest the folder destination path to be receive the source
	 * param $shell the class \Codeception\Module\Cli
	 */
	function copyWithRsync( $source, $dest, $shell ) {
		$this->comment("Copying files to $dest...");
		$shell->runShellCommand( 'rsync -rv ' . $source . ' '. $dest );
		$this->comment("Lumiere plugin folder (into $dest) saved");
	}

	/**
	 * Activate local mount, as it goes sleep
	 * 
	 * param $path the full path of WordPress, /home/../(wp-content)
	 * param $shell the class \Codeception\Module\Cli
	 */
	function activateLocalMount( $path, $shell ) {
		$this->comment("Activating local mount in $path...");
		$shell->runShellCommand( "touch $path/wp-content/cache/testcodeception.txt" );
		$this->comment( "Local mount activated, saved testcodeception.txt in $path/wp-content/cache/" );
	}

	/**
	 * Delete testcodeception.txt file created in $this->activateLocalMount()
	 * 
	 * param $path the full path of WordPress, /home/../(wp-content)
	 * param $shell the class \Codeception\Module\Cli
	 */
	function deleteTestFileMount( $path, $shell ) {
		$this->comment( "Deleting $path/wp-content/cache/testcodeception.txt" );
		$shell->runShellCommand( "rm $path/wp-content/cache/testcodeception.txt" );
	}

	/** Activate local mount, as it goes sleep
	 * 
	 * param $modal the Modal window to activate (ie: Bootstrap, Highslide, ...)
	 */
	function SwitchModalWindow( $modal ) {
		try {
			$this->amOnPage( AcceptanceSettings::LUMIERE_GENERAL_OPTIONS_URL );
			$this->customSelectOption( "select[name=imdbpopup_modal_window]", $modal, "lumiere_update_general_settings" );
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("[No action] Couldn't switch to $modal modal window.");
		} 
	}

	/**
	 * Switch to new window
	 * Function switchToWindow() needs a name that we usually don't know
	 * With switchToNewWindow(), we detect we just open
	 * Doesn't work
	 */
	function switchToNewWindow() {
		$this->executeInSelenium( function ( \Facebook\WebDriver\Remote\RemoteWebDriver $webdriver ) {
			$handles = $webdriver->getWindowHandles();
			$lastWindow = end($handles);
			$webdriver->switchTo()->window($lastWindow);
		} );
	}
	
	/**
	 * Custom function to check if a file is available, exit otherwhise
	 * Filesystem SeeFile is not reliable
	 */
	function customSeeFile( $file ) {
		if ( is_file( $file ) === false ) {
			$this->comment("[CustomSeeFile] File was not found, exiting.");
			exit;
		} 
		$this->comment("[CustomSeeFile] File was found, continuing.");
	}
	
	/**
	 * Custom function to check if a file is not available, exit otherwhise
	 * Filesystem dontSeeFile is not reliable
	 */
	function customDontSeeFile( $file ) {
		if ( file_exists( $file ) === true ) {
			$this->comment("[CustomDontSeeFile] File was found, exiting.");
			exit;
		} 
		$this->comment("[CustomDontSeeFile] File doesn't exist, continuing.");
	}
	
	/**
	 * Custom function to check if a file is not available, exit otherwhise
	 * Filesystem dontSeeFile is not reliable
	 */
	function customFindFileWildcard( $file ) {
		$file_search = glob( $file );
		$final_file = $file_search[0];
		if ( is_file( $final_file ) === true ) {
			$this->comment("[customFindFileWildcard] File $final_file was found.");
			return $final_file;
		} 
		$this->comment("[customFindFileWildcard] File $final_file was not found.");
		exit;
	}
}




/*		// Get url depending on the environment called in acceptance.suite.yml
		$current_env = $scenario->current('env');
		$config_base = \Codeception\Configuration::config(); # config in codeception.yml
   		$config = \Codeception\Configuration::suiteSettings("acceptanceRemote", $config_base);
		$url_base = $config['env'][$current_env]['modules']['enabled']['config']['WPWebDriver']['url'];
*/

