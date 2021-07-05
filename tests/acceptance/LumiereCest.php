<?php
class LumiereCest {
/*
	public function frontpageWorks(AcceptanceTester $I) {
		$I->amOnPage('/');
		$I->see('Here you are');	
	}
*/
	public function popupmoviesWorks(AcceptanceTester $I) {
		$I->wantTo('Test if popup movies works');
		$I->amOnPage('/2021/test-imdblt/');
			$element = 'a[data-highslidefilm="fight+club"]';
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->see('David Fincher');
	}
/* check
	public function popupmoviesWorks(AcceptanceTester $I) {
		$I->wantTo('Test if popup movies works');
		$I->amOnPage('/2021/test-codeception/');
			$element = 'a[data-highslidefilm="interstellar"]';
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->see('Christopher Nolan');
	}
*/
}
