<?php

# Class meant to test local wordpress install  (a WebDriver is needed for JS execution)

use AcceptanceTester;

class LumiereLocalCest {

	public function frontpageWorks(AcceptanceTester $I) {
		$I->wantTo('check frontpage');
		$I->amOnPage('/');
		$I->see('Here you are');	
	}


	public function popupMoviesCanOpen(AcceptanceTester $I, \Codeception\Scenario $scenario) {
		/* vars */
		// Get url depending on the environment called in codeception.yml
/*		$current_env = $scenario->current('env');
		$config = \Codeception\Configuration::config();
		$url_base = $config['env'][$current_env]['modules']['enabled'][0]['WebDriver']['url'];
*/		$url_base = $_ENV['TEST_LOCAL_SITE_WP_URL']; # more direct, but can be wrong
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
		/* vars */
		// Get url depending on the environment called in codeception.yml
/*		$current_env = $scenario->current('env');
		$config = \Codeception\Configuration::config();
		$url_base = $config['env'][$current_env]['modules']['enabled'][0]['WebDriver']['url'];
*/		$url_base = $_ENV['TEST_LOCAL_SITE_WP_URL']; # more direct, but can be wrong
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
		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$I->seePluginActivated('lumiere-movies');
		$I->amOnPage('/2021/test-codeception/');
		$I->click( "Tony Zarindast");
		$I->see('Tehran');
	}

}
