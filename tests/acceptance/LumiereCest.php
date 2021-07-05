<?php
use \Codeception\Util\Locator; 

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

		$I->click('//a[@data-highslidefilm="fight+club"]');
		$I->see('David Fincher');
	}


}
