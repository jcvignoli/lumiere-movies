<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class LumiereCest {

	public function _before(AcceptanceRemoteTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceRemoteTester $I){
		$I->comment('#Code _after#');
	}

	/** Disable debug functions
	 *
	 */
	private function disableDebug(AcceptanceRemoteTester $I) {
		$I->wantTo('Unactive debug');
		$I->loginAsAdmin();
		$I->scrollTo('#imdblinkingkill');
		$I->CustomCanUncheckOptionThenSubmit('#imdb_imdbkeepsettings_yes', '#update_imdbSettings');
	}

	/** Login to Wordpress
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->wantTo('Start an admin session');
		$I->loginAsAdmin();
	}

	/** Is frontpage available?
	 *
	 */
	public function checkFrontpage(AcceptanceRemoteTester $I) {
		$I->wantTo('check frontpage');
		$I->amOnPage('/');
		$I->see('Blog ext');	
	}

	/** Check if auto widget option display a widget based on the title of the page
	 *
	 * @before login
	 *
	 */
	public function checkTaxonomyOptionAndPage(AcceptanceRemoteTester $I) {

		/* const */
		$url_base = $_ENV['TEST_REMOTE_WP_URL'];
		// popup link person Tony Zarindast
		$element = 'a[data-highslidepeople="0953494"]';
		$sub_url = '/imdblt/person/0953494/?mid=0953494';

		$I->wantTo('Check if taxonomy option works');

		// Enable taxonomy
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		/*	Conditional checkbox activation (in _support/AcceptanceRemoteTester.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );
		$I->amOnPage('/2021/test-codeception/');
		$I->click( "Tony Zarindast");
		$I->see('Tehran');

		// Disable taxonomy
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		/*	Conditional checkbox unactivation (in _support/AcceptanceRemoteTester.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is activated, uncheck it and then click $submit (form) */
		$I->CustomDisableCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );
		$I->amOnPage('/2021/test-codeception/');
		$I->click( "Tony Zarindast");
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(7);
		$I->switchToIFrame("//iframe[@src='$url_base$sub_url']");
		$I->see('Golden Cage');

		// Re-enable taxonomy
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

	}

	/** Check if auto widget option display a widget based on the title of the page
	 *
	 * @before login
	 *
	 */
	public function checkAutoTitleWidget(AcceptanceRemoteTester $I) {
		$I->wantTo('check auto title widget option');

		// Activate Auto Widget
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdblinkingkill');
		/*	Conditional checkbox activation (in _support/AcceptanceRemoteTester.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbautopostwidget_yes', '#update_imdbSettings' );
		$I->amOnPage("/2021/y-tu-mama-tambien/");
		$I->see('Y tu mamá también');

		// Disable Auto Widget
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdblinkingkill');
		/*	Conditional checkbox unactivation (in _support/AcceptanceRemoteTester.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomDisableCheckbox('#imdb_imdbautopostwidget_yes', '#update_imdbSettings' );
		$I->amOnPage("/2021/y-tu-mama-tambien/");
		$I->dontSee('Y tu mamá también');

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
