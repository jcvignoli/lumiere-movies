<?php

/** Trait for common functions used by both remote and local Acceptance tests
 * 
 */
trait AcceptanceTrait {

	/** Login to Wordpress
	 *
	 */
	function login_universal() {
		$this->wantTo('Start an admin session');
		$this->loginAsAdmin();
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

	/** Activate plugin if not yet active
	 * 
	 */
	function CustomActivatePlugin($plugin){
		try {
			$this->seePluginDeactivated("$plugin");
			$this->activatePlugin("$plugin");
			$this->comment("[Action] Plugin $plugin successfully activated.");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("[No Action] Plugin $plugin was already activated.");
		} 
	}

	/** Disable plugin if active
	 * 
	 */
	function CustomDisablePlugin($plugin){
		try {
			$this->seePluginActivated("$plugin");
			$this->deactivatePlugin("$plugin");
			$this->comment("[Action] Plugin $plugin successfully deactivated.");
		} catch (\PHPUnit_Framework_AssertionFailedError | \NoSuchElementException | \Exception $f) {
			$this->comment("[No Action] Plugin $plugin was already deactivated.");
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
}





