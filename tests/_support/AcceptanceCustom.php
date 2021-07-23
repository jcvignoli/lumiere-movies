<?php

/** Trait for common functions used by both remote and local Acceptance tests
 * 
 */
trait AcceptanceCustom {

	/** Check if a checkbox is disabled, if activated uncheck it and then submit a form
	 * 
	 */
	function CustomDisableCheckbox($element, $submit){
		try {
			$this->seeCheckboxIsChecked("$element");
			$this->uncheckOption($element);
			$this->click($submit);
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
			$this->checkOption($element);
			$this->click($submit);
			$this->comment("[Action] Checkbox $element was disabled, it has been activated");
		} 
	}
}
