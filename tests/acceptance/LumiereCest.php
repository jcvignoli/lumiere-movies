<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class LumiereCest {

	public function frontpageWorks(AcceptanceTester $I) {
		$I->wantTo('check frontpage');
		$I->amOnPage('/');
		$I->see('Here you are');	
	}

	public function popupMoviesCanOpen(AcceptanceTester $I, \Codeception\Scenario $scenario) {
		/* const */
		// Get url depending on the environment called in acceptance.suite.yml
		$current_env = $scenario->current('env');
		$config_base = \Codeception\Configuration::config(); # config in codeception.yml
   		$config = \Codeception\Configuration::suiteSettings("acceptance", $config_base);
		$url_base = $config['env'][$current_env]['modules']['enabled']['config']['WebDriver']['url'];
		// $url = $_ENV['TEST_SITE_WP_URL']; # more direct, but can be wrong

		/* settings */
		// popup link movie interstellar
		$element = 'a[data-highslidefilm="interstellar"]';
		$sub_url = '/imdblt/film/interstellar/?film=interstellar';

		$I->wantTo('popup movies can open');
		$I->amOnPage('/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(5);
		$I->switchToIFrame("//iframe[@src='$url_base$sub_url']");
		$I->see('Christopher Nolan');
	}

	public function popupPersonCanOpen(AcceptanceTester $I, \Codeception\Scenario $scenario) {
		/* const */
		// Get url depending on the environment called in acceptance.suite.yml
		$current_env = $scenario->current('env');
		$config_base = \Codeception\Configuration::config(); # config in codeception.yml
   		$config = \Codeception\Configuration::suiteSettings("acceptance", $config_base);
		$url_base = $config['env'][$current_env]['modules']['enabled']['config']['WebDriver']['url'];

		/* settings */
		// popup link actor Jorge Rivero
		$element = 'a[data-highslidepeople="0729473"]';
		$sub_url = '/imdblt/person/0729473/?mid=0729473';

		$I->wantTo('popup person can open');
		$I->amOnPage('/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(5);
		$I->switchToIFrame("//iframe[@src='$url_base$sub_url']");
		$I->see('Pajarero');
	}

	public function taxonomyPage(AcceptanceTester $I) {
		$I->wantTo('taxonomy person page');
		$I->amOnPage('/2021/test-codeception/');
		$I->click( "Tony Zarindast");
		$I->see('Tehran');
	}

}
