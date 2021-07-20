<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class LumiereCest {

	public function activateDebug(AcceptanceLocalTester $I) {
		$I->wantTo('log in');
		$I->amOnPluginsPage();
		$I->seePluginActivated('lumiere-movies');
		$I->amOnPage("/wp-admin/admin.php?page=imdblt_options&generaloption=advanced");
		$I->selectOption('form input[id=imdb_imdbdebug_yes]');
	}

	public function frontpageWorks(AcceptanceRemoteTester $I) {
		$I->wantTo('check frontpage');
		$I->amOnPage('/');
		$I->see('Here you are');	
	}

	public function popupMoviesCanOpen(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {
		/* const */
		// Get url depending on the environment called in acceptance.suite.yml
		$current_env = $scenario->current('env');
		$config_base = \Codeception\Configuration::config(); # config in codeception.yml
   		$config = \Codeception\Configuration::suiteSettings("acceptanceRemote", $config_base);
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

	public function popupPersonCanOpen(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {
		/* const */
		// Get url depending on the environment called in acceptance.suite.yml
		$current_env = $scenario->current('env');
		$config_base = \Codeception\Configuration::config(); # config in codeception.yml
   		$config = \Codeception\Configuration::suiteSettings("acceptanceRemote", $config_base);
		$url_base = $config['env'][$current_env]['modules']['enabled']['config']['WebDriver']['url']; # must be changed if run with something different than webdriver, find a way to automatise it by getting the webdriver/WPWebDriver

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

	public function taxonomyPage(AcceptanceRemoteTester $I) {
		$I->wantTo('taxonomy person page');
		$I->amOnPage('/2021/test-codeception/');
		$I->click( "Tony Zarindast");
		$I->see('Tehran');
	}

}
