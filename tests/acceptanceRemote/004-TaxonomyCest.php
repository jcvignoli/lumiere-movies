<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class TaxonomyCest {

	public function _before(AcceptanceRemoteTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceRemoteTester $I){
		$I->comment('#Code _after#');
	}

	/** Login to Wordpress
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->wantTo('Start an admin session');
		$I->loginAsAdmin();
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
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );
		$I->amOnPage('/2021/test-codeception/');
		$I->click( "Tony Zarindast");
		$I->see('Tehran');

		// Disable taxonomy
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		/*	Conditional checkbox unactivation (in _support/AcceptanceTrait.php)
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

}
