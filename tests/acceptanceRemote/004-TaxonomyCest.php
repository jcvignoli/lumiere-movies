<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class TaxonomyCest {

	var $url_base = "";

	public function __construct(){

		$this->url_base = $_ENV['TEST_REMOTE_WP_URL'];

	}

	/** Run needed actions BEFORE each function
	 *
	 *
	 */
	public function _before(AcceptanceRemoteTester $I){
		$I->comment('#Code _before#');
	}

	/** Run needed actions AFTER each function
	 *
	 *
	 */
	public function _after(AcceptanceRemoteTester $I){
		$I->comment('#Code _after#');
	}

	/** Helper: Login to Wordpress
	 *  Trait function to keep the cookie active
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {
		AcceptanceTrait::login_universal($I);
	}

	/** Helper: Enable taxonomy
	 *
	 * @before login
	 *
	 */
	private function enableTaxonomy(AcceptanceRemoteTester $I) {

		$I->wantTo('Activate taxonomy if disabled');

		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

	}

	/** Helper: Disable taxonomy
	 *
	 * @before login
	 *
	 */
	private function disableTaxonomy(AcceptanceRemoteTester $I) {

		$I->wantTo('Activate taxonomy if disabled');

		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		/*	Conditional checkbox unactivation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is activated, uncheck it and then click $submit (form) */
		$I->CustomDisableCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

	}
	/** Run needed actions BEFORE starting the class
	 *
	 * @before login
	 *
	 */
	public function startingCest(AcceptanceRemoteTester $I){

		$this->enableTaxonomy($I);
	}

	/** Check if auto widget option display a widget based on the title of the page
	 *
	 * @before login
	 *
	 */
	public function checkTaxonomyActivation(AcceptanceRemoteTester $I) {

		/* VARS */
		// popup link person Tony Zarindast
		$element = 'a[data-highslidepeople="0953494"]';
		$sub_url = '/imdblt/person/0953494/?mid=0953494';

		$I->wantTo('Check if auto widget taxonomy option works');

		$I->amOnPage('/2021/test-codeception/');
		$I->click( "Tony Zarindast");
		$I->see('Tehran');

		// Disable taxonomy
		$this->disableTaxonomy($I);

		$I->amOnPage('/2021/test-codeception/');
		$I->click( "Tony Zarindast");
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait(7);
		$I->switchToIFrame("//iframe[@src='$this->url_base$sub_url']");
		$I->see('Golden Cage');

	}

	/** Check if auto widget option display a widget based on the title of the page
	 *
	 * @before login
	 */
	public function checkTaxonomyTemplateSystem(AcceptanceRemoteTester $I) {

		$who = 'director';

		$I->wantTo("Check if Taxonomy template system works, using $who example");

		// R
		$I->deleteThemeFile('oceanwp/taxonomy-imdblt_' . $who . '.php');

		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=taxo');
		$I->scrollTo('#imdb_imdbtaxonomy' . $who .'_yes');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy'.$who.'_yes', '#imdbconfig_save' );


	}


	/** Run needed actions AFTER closing the class
	 *
	 * @before login
	 *
	 */
	public function closingCest(AcceptanceRemoteTester $I){

		$this->enableTaxonomy($I);

	}
}


