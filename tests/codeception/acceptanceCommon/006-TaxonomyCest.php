<?php

# Class meant to test Taxonomy (a WebDriver is needed for JS execution)

class TaxonomyCest {

	/**
	 * Theme name
	 */
	const theme_name = 'oceanwp';

	/**
	 * Stock the base remote URL
	 */
	var $base_url = "";

	/**
	 * Stock the root remote path
	 */
	var $base_path = "";

	public function __construct(){

		$remote_or_local = defined( 'DEVELOPMENT_ENVIR' ) ? DEVELOPMENT_ENVIR : '';
		$final_var_url = 'TEST_' . strtoupper( $remote_or_local ) . '_WP_URL';
		$final_var_root_folder = 'WP_ROOT_' . strtoupper( $remote_or_local ) . '_FOLDER';
		
		$this->base_url = $_ENV[$final_var_url];
		$this->base_path = $_ENV[$final_var_root_folder];
		
	}

	/**
	 * Run needed actions BEFORE each function
	 */
	public function _before(AcceptanceRemoteTester $I){
		$I->comment('#Code _before#');
	}

	/**
	 * Run needed actions AFTER each function
	 */
	public function _after(AcceptanceRemoteTester $I){
		$I->comment('#Code _after#');
	}

	/**
	 * Helper: Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceRemoteTester $I) {

		$I->login_universal($I);

	}

	/**
	 * Helper: Select Highslide
	 * Make sure that Highslide modal window is selected
	 */
	private function highslide(AcceptanceRemoteTester $I) {
		// Make sure Highslide is active, following tests are run with Highslide
		$I->SwitchModalWindow('Highslide');

	}
	/**
	 * Helper: Enable taxonomy
	 * @before login
	 */
	private function maybeEnableTaxonomy(AcceptanceRemoteTester $I) {

		$I->wantTo('Activate taxonomy if disabled');

		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#lumiere_update_general_settings' );

	}

	/**
	 * Helper: Disable taxonomy
	 * @before login
	 */
	private function maybeDisableTaxonomy(AcceptanceRemoteTester $I) {

		$I->wantTo('Disable taxonomy if active');

		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		/*	Conditional checkbox unactivation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is activated, uncheck it and then click $submit (form) */
		$I->CustomDisableCheckbox('#imdb_imdbtaxonomy_yes', '#lumiere_update_general_settings' );

	}

	/**
	 * Run needed actions BEFORE starting the class
	 * @before login
	 */
	public function startingCest(AcceptanceRemoteTester $I){
		$this->maybeEnableTaxonomy($I);
	}

	/**
	 * Check if Taxonomy system works
	 * @before login
	 * @example ["director", "composer"]
	 */
	public function checkTaxonomyPeopleTemplateSystem(AcceptanceRemoteTester $I, \Codeception\Example $example, \Codeception\Module\Cli $shell) {

		// Make local connexion
		$shell->runShellCommand( 'touch ' . $this->base_path . '/wp-content/cache/testcodeception.txt' );

		$I->wantTo("Check if Taxonomy template system works");

		$this->maybeEnableTaxonomy($I);

		// Delete Lumière taxonomy template in theme folder if it exists
		$I->customThemeFileExistsDelete( self::theme_name . '/taxonomy-lumiere-' . $example[0] . '.php');

		// Activate $item in 'what to display'
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidget' . $example[1] .'_yes');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbwidget'.$example[0].'_yes', '#lumiere_update_data_settings' );

		// Activate $item in 'Taxonomy'
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_TAXO_URL );
		$I->scrollTo('#imdb_imdbtaxonomy' . $example[1] .'_yes');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy'.$example[0].'_yes', '#lumiere_update_data_settings' );
		/*	Conditional click to copy if the theme is found (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */

		// Copy Lumière taxonomy template to theme folder
		$I->maybeCopyThemeFile($example[0]);
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PERMALINK_URL );
		$I->wait(2);
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PERMALINK_URL );
		$I->wait(2);

		// Check that the template has been successfully implemented
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->scrollTo( '#title_Werewolf' );
		$I->click( "Tony Zarindast");
		$I->wait(2);
		$I->see('Tehran');

		// Disable $item in 'what to display'
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidget' . $example[1] .'_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidget'.$example[0].'_yes', '#lumiere_update_data_settings' );
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#taxodetails');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetactor_yes', '#lumiere_update_data_settings' );
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetwriter_yes', '#lumiere_update_data_settings' );

		// Check that the template has been successfully removed
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->dontSee('Director');

		// Re-activate $item in 'what to display'
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidget' . $example[1] .'_yes');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbwidget'.$example[0].'_yes', '#lumiere_update_data_settings' );
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#taxodetails');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetactor_yes', '#lumiere_update_data_settings' );
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetwriter_yes', '#lumiere_update_data_settings' );

	}

	/**
	 * Make sure we have a template for genre
	 *
	 * @before login
	 */
	public function checkTaxonomyItemTemplateSystem(AcceptanceRemoteTester $I) {
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_TAXO_URL );
		$I->scrollTo('#imdb_imdbtaxonomygenre_yes');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomygenre_yes', '#lumiere_update_data_settings' );
		
		// Copy Lumière taxonomy template to theme folder
		$I->maybeCopyThemeFile( 'genre' );
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PERMALINK_URL );
		$I->wait(2);
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PERMALINK_URL );
		$I->wait(2);
		// Check that the template has been successfully implemented
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->scrollTo( '#title_Werewolf' );
		$I->click( "Horror");
		$I->wait(2);
		$I->see('List of posts tagged Horror');
	}

	/**
	 * Check if taxonomy deactivation/activation produce expected results
	 * @before login
	 * @before highslide
	 */
	public function checkTaxonomyActivation(AcceptanceRemoteTester $I) {

		/* VARS */
		// popup link person Tony Zarindast
		$element = 'a[data-modal_window_people="0953494"]';
		$sub_url = '/en/lumiere/person/?mid=0953494';
		$text_zarindast = '1934, in Tabriz, Iran';

		$I->wantTo('Check if auto widget taxonomy option works');

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->scrollTo( '#title_Werewolf' );
		$I->click( "Tony Zarindast");
		$I->see( $text_zarindast );

		// Disable taxonomy
		$this->maybeDisableTaxonomy($I);

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->click( "Tony Zarindast");
		$I->executeJS( "return jQuery('" . $element . "').get(0).click()");
		$I->wait( 4 );
		$iframe_find_name = $I->grabAttributeFrom('//iframe', 'name');
		$I->switchToIframe( "$iframe_find_name" );
		$I->see( $text_zarindast );

	}


	/**
	 * Check if the link "click to expand" works on taxo
	 * @before login
	 */
	public function taxoClickMore(AcceptanceRemoteTester $I) {
		$this->maybeEnableTaxonomy($I);
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->click( "Stanley Kubrick");
		$I->waitForText('Stanley Kubrick was born in Manhattan');
		$I->scrollTo(['css' => '.activatehidesection' ]);
		$I->executeJS( "return jQuery('span.activatehidesection').get(0).click()");
		$I->see('the next few years, Kubrick had regular assignments for "Look",');
	}
	
	/**
	 * Run needed actions AFTER closing the class
	 * @before login
	 */
	public function closingCest(AcceptanceRemoteTester $I){

		$this->maybeEnableTaxonomy($I);

	}
}


