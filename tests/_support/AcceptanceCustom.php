<?php

/** Trait for common functions used by both remote and local Acceptance tests
 * 
 */
trait AcceptanceCustom {


	/** If can *check* a checkbox, then submit a form
	 * 
	 */
	function CustomCanCheckOptionThenSubmit($element, $submit) {
		try {
			$this->checkOption("$element");
			$this->click($submit);
			$this->comment("[Action] Checkbox $element was disabled, it has been activated");
		} catch (\PHPUnit_Framework_AssertionFailedError $f) {
			$this->comment("[No action] Checkbox $element was already activated");
			return false;
		}
		return true;
	}

	/** If can *uncheck* a checkbox, then submit a form
	 * 
	 */
	function CustomCanUncheckOptionThenSubmit($element, $submit) {
		try {
			$this->uncheckOption("$element");
			$this->click($submit);
			$this->comment("[Action] Checkbox $element was activated, it has been disabled");
		} catch (\PHPUnit_Framework_AssertionFailedError $f) {
			$this->comment("[No action] Checkbox $element was already disabled");
			return false;
		}
		return true;
	}

	/** If can *check* a checkbox, then
	 * 
	 */
	function CustomCanCheckOption($element){
		try {
			$this->checkOption("$element");
		} catch (\PHPUnit_Framework_AssertionFailedError $f) {
			return false;
		}
		return true;
	}


	/** If can *uncheck* a checkbox, then
	 * 
	 */
	function CustomCanUncheckOption($element){
		try {
			$this->uncheckOption("$element");
		} catch (\PHPUnit_Framework_AssertionFailedError $f) {
			return false;
		}
		return true;
	}

}
