<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class PopupsCest {

	public function _before(AcceptanceRemoteTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceRemoteTester $I){
		$I->comment('#Code _after#');
	}

	/** Is popup movie functional?
	 *
	 */
	public function checkPopupMovie(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {
		/* const */
/*		// Get url depending on the environment called in acceptance.suite.yml
		$current_env = $scenario->current('env');
		$config_base = \Codeception\Configuration::config(); # config in codeception.yml
   		$config = \Codeception\Configuration::suiteSettings("acceptanceRemote", $config_base);
		$url_base = $config['env'][$current_env]['modules']['enabled']['config']['WPWebDriver']['url'];
*/
		$url_base = $_ENV['TEST_REMOTE_WP_URL'];
		// popup link movie interstellar
		$element = 'a[data-highslidefilm="interstellar"]';
		$sub_url = '/imdblt/film/interstellar/?film=interstellar';

		$I->wantTo('Check if popup movie can be open');
		$I->amOnPage('/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(7);
		$I->switchToIFrame("//iframe[@src='$url_base$sub_url']");
		$I->see('Christopher Nolan');
	}

	/** Is popup person functional?
	 ** (also tested with checkTaxonomyOptionAndPage() 
	 *
	 */
	public function checkPopupPerson(AcceptanceRemoteTester $I, \Codeception\Scenario $scenario) {
		/* const */
/*		// Get url depending on the environment called in acceptance.suite.yml
		$current_env = $scenario->current('env');
		$config_base = \Codeception\Configuration::config(); # config in codeception.yml
   		$config = \Codeception\Configuration::suiteSettings("acceptanceRemote", $config_base);
		$url_base = $config['env'][$current_env]['modules']['enabled']['config']['WPWebDriver']['url'];
*/
		$url_base = $_ENV['TEST_REMOTE_WP_URL'];
		// popup link actor Jorge Rivero
		$element = 'a[data-highslidepeople="0729473"]';
		$sub_url = '/imdblt/person/0729473/?mid=0729473';

		$I->wantTo('Check if popup person can be open');
		$I->amOnPage('/2021/test-codeception/');
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(7);
		$I->switchToIFrame("//iframe[@src='$url_base$sub_url']");
		$I->see('Pajarero');
	}

}
