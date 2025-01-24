<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to test Taxonomy (a WebDriver is needed for JS execution)

class TaxonomyCest {

	/**
	 * Theme name
	 */
	const THEME_NAME = 'oceanwp';

	/**
	 * Run needed actions BEFORE each function
	 */
	public function _before(AcceptanceTester $I){
		$I->comment('#Code _before#');
	}

	/**
	 * Run needed actions AFTER each function
	 */
	public function _after(AcceptanceTester $I){
		$I->comment('#Code _after#');
	}

	/**
	 * Helper: Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Helper: Select Highslide
	 * Make sure that Highslide modal window is selected
	 */
	private function highslide(AcceptanceTester $I) {
		// Make sure Highslide is active, following tests are run with Highslide
		$I->SwitchModalWindow('Highslide');

	}
	/**
	 * Helper: Enable taxonomy
	 * @before login
	 */
	private function maybeEnableTaxonomy(AcceptanceTester $I) {

		$I->wantTo('Activate taxonomy if disabled');

		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#behaviourpart');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#lumiere_update_main_settings' );

	}

	/**
	 * Helper: Disable taxonomy
	 * @before login
	 */
	private function maybeDisableTaxonomy(AcceptanceTester $I) {

		$I->wantTo('Disable taxonomy if active');

		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#behaviourpart');
		/*	Conditional checkbox unactivation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is activated, uncheck it and then click $submit (form) */
		$I->CustomDisableCheckbox('#imdb_imdbtaxonomy_yes', '#lumiere_update_main_settings' );

	}

	/**
	 * Run needed actions BEFORE starting the class
	 * @before login
	 */
	public function startingCest(AcceptanceTester $I){
		$this->maybeEnableTaxonomy($I);
	}

	/**
	 * Check if Taxonomy system works
	 * @before login
	 * @example ["director", "composer"]
	 */
	public function checkTaxonomyPeopleTemplateSystem(AcceptanceTester $I, \Codeception\Example $example, \Codeception\Module\Cli $shell) {

		// Make local connexion
		$shell->runShellCommand( 'touch ' . $I->getCustomBasePath() . '/wp-content/cache/testcodeception.txt' );

		$I->wantTo("Check if Taxonomy template system works");

		$this->maybeEnableTaxonomy($I);

		// Delete Lumière taxonomy template in theme folder if it exists
		$I->customThemeFileExistsDelete( self::THEME_NAME . '/taxonomy-lumiere-' . $example[0] . '.php');

		// Activate $item in 'what to display'
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidget' . $example[1] .'_yes');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbwidget'.$example[0].'_yes', '#lumiere_update_data_settings' );

		// Activate $item in 'Taxonomy'
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_TAXO_URL );
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
		$I->amOnPage( AcceptanceSettings::ADMIN_PERMALINK_URL );
		$I->wait(2);
		$I->amOnPage( AcceptanceSettings::ADMIN_PERMALINK_URL );
		$I->wait(2);

		// Check that the template has been successfully implemented
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->scrollTo( [ 'id' => 'title_Werewolf'] );
		$I->wait(1);
		$I->click( "Tony Zarindast");
		$I->wait(2);
		$I->see('Tehran');

		// Disable $item in 'what to display'
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidget' . $example[1] .'_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidget'.$example[0].'_yes', '#lumiere_update_data_settings' );
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#taxodetails');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetactor_yes', '#lumiere_update_data_settings' );
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomDisableCheckbox('#imdb_imdbwidgetwriter_yes', '#lumiere_update_data_settings' );

		// Check that the template has been successfully removed
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->dontSee('Director');

		// Re-activate $item in 'what to display'
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidget' . $example[1] .'_yes');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbwidget'.$example[0].'_yes', '#lumiere_update_data_settings' );
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#taxodetails');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetactor_yes', '#lumiere_update_data_settings' );
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidgetsoundtrack_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetwriter_yes', '#lumiere_update_data_settings' );

	}

	/**
	 * Make sure we have a template for genre
	 *
	 * @before login
	 */
	public function checkTaxonomyItemTemplateSystem(AcceptanceTester $I) {
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_TAXO_URL );
		$I->scrollTo('#imdb_imdbtaxonomygenre_yes');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomygenre_yes', '#lumiere_update_data_settings' );
		
		// Copy Lumière taxonomy template to theme folder
		$I->maybeCopyThemeFile( 'genre' );
		$I->amOnPage( AcceptanceSettings::ADMIN_PERMALINK_URL );
		$I->wait(2);
		$I->amOnPage( AcceptanceSettings::ADMIN_PERMALINK_URL );
		$I->wait(2);
		// Check that the template has been successfully implemented
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->scrollTo( [ 'id' => 'title_Werewolf'] );
		$I->wait(1);
		$I->click( "Horror");
		$I->wait(2);
		$I->see('List of posts tagged Horror');
	}

	/**
	 * Check if taxonomy deactivation/activation produce expected results
	 * @before login
	 * @before highslide
	 */
	public function checkTaxonomyActivation(AcceptanceTester $I) {

		/* VARS */
		// popup link person Tony Zarindast
		$element = 'a[data-modal_window_people="0953494"]';
		$sub_url = '/en/lumiere/person/?mid=0953494';
		$text_zarindast = '1934 in Tabriz, Iran';

		$I->wantTo('Check if taxonomy option works');

		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->scrollTo( [ 'id' => 'title_Werewolf'] );
		$I->wait(1);
		$I->click( "Tony Zarindast");
		$I->see( $text_zarindast );

		// Disable taxonomy
		$this->maybeDisableTaxonomy($I);

		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->scrollTo( [ 'id' => 'title_Werewolf'] );
		$I->wait(1);
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
	public function taxoClickMore(AcceptanceTester $I) {
		$this->maybeEnableTaxonomy($I);
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->scrollTo( [ 'id' => 'link_taxo_2001__a_space_odyssey_en_lumiere_director_stanley_kubrick' ], 0, -100 );
		$I->waitForText('Stanley Kubrick');
		$I->see( "Stanley Kubrick" );
		// $I->click( "Stanley Kubrick"); // doesn't work, dunno why
		$I->click( [ 'id' => 'link_taxo_2001__a_space_odyssey_en_lumiere_director_stanley_kubrick' ] );
		$I->waitForText('Stanley Kubrick was born in Manhattan');
		$I->scrollTo(['css' => '.activatehidesection' ]);
		$I->executeJS( "return jQuery('span.activatehidesection').get(0).click()");
		$I->see('the next few years, Kubrick had regular assignments for "Look",');
	}
	
	/**
	 * Run needed actions AFTER closing the class
	 * @before login
	 */
	public function closingCest(AcceptanceTester $I){

		$this->maybeEnableTaxonomy($I);

	}
}


