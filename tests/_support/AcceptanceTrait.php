<?php

/** Trait for common functions used by both remote and local Acceptance tests
 * 
 */
trait AcceptanceTrait {

	/** Login to Wordpress
	 *  Save the cookies so no need to log again
	 */
	function login_universal(AcceptanceRemoteTester $I) {
		if ($I->loadSessionSnapshot('login')) return;
		$I->wantTo('Start an admin session');
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

	/** If text searched is not found, exit
	 * 
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

	/** Select a value in dropdown select, then submit
	 * 
	 */
	function customSelectOption($element, $option, $submit){

		$this->selectOption($element, $option);
		$this->click("$submit");
		$this->comment("[Action] Selection has been switch to '$option");

	}

	/** Delete theme file if it exists
	 * 
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

	/** Copy theme file if it doesn't exists
	 * 
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

	/** Activate plugin if it is deactivated
	 * 
	 */
	function maybeActivatePlugin($plugin){

		try {
			$this->executeJS("return jQuery('#activate-".$plugin."').get(0).click()");
			$this->comment("[Action] Plugin $plugin was unactive and has been successfully activated.");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("[No action] Plugin $plugin was already activated.");
		} 

	}

	/** Deactivate plugin if it is activated
	 * 
	 */
	function maybeDeactivatePlugin($plugin){

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
	function waitPageLoad($timeout = 10){

		$this->waitForJS('return document.readyState == "complete"', $timeout);
		$this->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', $timeout);
		$this->wait(1);
		
	}

}




/*		// Get url depending on the environment called in acceptance.suite.yml
		$current_env = $scenario->current('env');
		$config_base = \Codeception\Configuration::config(); # config in codeception.yml
   		$config = \Codeception\Configuration::suiteSettings("acceptanceRemote", $config_base);
		$url_base = $config['env'][$current_env]['modules']['enabled']['config']['WPWebDriver']['url'];
*/

