<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

/**
 * Class meant to test wordpress install (a WebDriver is needed for JS execution)
 * Data for person, new 4.6 addition, is tested
 */
class CronsCest {

	public function _before(AcceptanceTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceTester $I){
		$I->comment('#Code _after#');
	}

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}
	
	/**
	 * Prepare wordpress
	 * @before login
	 */
	public function prepare( AcceptanceTester $I ) {
		// Disable debug if it exists, debug displays some words we don't want
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->reloadPage();
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->waitForElement('#imdbkeepsettings', 30);
		$I->scrollTo('#imdbkeepsettings');
		$I->CustomDisableCheckbox('#imdb_imdbdebug_yes', '#lumiere_update_main_settings');
	}

	/**
	 * Check if data details deactivation works
	 *
	 * @before login
	 */
	public function checkDataDeactivation( AcceptanceTester $I ) {
		$I->comment(Helper\Color::set("Check that deactivated data details are not seen", "italic+bold+cyan"));

		// Activate $item in 'what to display'
		# first row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_DATA_PERSON_URL);
		$I->scrollTo('#person_display');
		$I->CustomDisableCheckbox('#bio_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#person_display');
		$I->CustomDisableCheckbox('#nickname_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#person_display');
		$I->CustomDisableCheckbox('#spouse_active_yes', '#lumiere_update_data_person_settings' );
		# second row
		$I->waitPageLoad();
		$I->scrollTo('#bio_active_yes');
		$I->CustomDisableCheckbox('#children_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#bio_active_yes');
		$I->CustomDisableCheckbox('#credit_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#bio_active_yes');
		$I->CustomDisableCheckbox('#news_active_yes', '#lumiere_update_data_person_settings' );		
		# third row
		$I->waitPageLoad();
		$I->scrollTo('#children_active_yes');
		$I->CustomDisableCheckbox('#pubinterview_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#children_active_yes');
		$I->CustomDisableCheckbox('#pubmovies_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#children_active_yes');
		$I->CustomDisableCheckbox('#pubportrayal_active_yes', '#lumiere_update_data_person_settings' );
		# fourth row
		$I->waitPageLoad();
		$I->scrollTo('#pubinterview_active_yes');
		$I->CustomDisableCheckbox('#pubprints_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#pubinterview_active_yes');
		$I->CustomDisableCheckbox('#quotes_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#pubinterview_active_yes');
		$I->CustomDisableCheckbox('#trivia_active_yes', '#lumiere_update_data_person_settings' );
		# Fifth row
		$I->waitPageLoad();
		$I->scrollTo('#pubprints_active_yes');
		$I->CustomDisableCheckbox('#trademark_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		
		// See if data is not available
		$I->comment( Helper\Color::set("Check if data is hidden", "italic+bold+cyan") );
		$I->waitPageLoad();
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_PERSON );
		$I->waitPageLoad();
		$I->dontSee('Marques de fabrique:');
		$I->dontSee('Nouvelles:');
		$I->dontSee('Films biographiques:');
		$I->dontSee('Christiane Kubrick');
		$I->dontSee('Stanley Kubrick was born in Manhattan, New York City,');
		
		// Titles are displayed even if everything is deactivated.
		$I->see('Stanley Kubrick');
		$I->see( 'Chuck Palahniuk' );
		$I->see( 'David Fincher' );
		$I->see( 'Notation:' );
	}
	
	/**
	 * Check if data details deactivation works
	 *
	 * @before login
	 */
	public function checkDataActivation( AcceptanceTester $I ) {
		$I->comment(Helper\Color::set("Check that activate data details are displayed", "italic+bold+cyan"));

		// Activate $item in 'what to display'
		# first row
		$I->amOnPage(AcceptanceSettings::LUMIERE_DATA_OPTIONS_DATA_PERSON_URL);
		$I->scrollTo('#person_display');
		$I->CustomActivateCheckbox('#bio_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#person_display');
		$I->CustomActivateCheckbox('#nickname_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#person_display');
		$I->CustomActivateCheckbox('#spouse_active_yes', '#lumiere_update_data_person_settings' );
		# second row
		$I->waitPageLoad();
		$I->scrollTo('#bio_active_yes');
		$I->CustomActivateCheckbox('#children_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#bio_active_yes');
		$I->CustomActivateCheckbox('#credit_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#bio_active_yes');
		$I->CustomActivateCheckbox('#news_active_yes', '#lumiere_update_data_person_settings' );		
		# third row
		$I->waitPageLoad();
		$I->scrollTo('#children_active_yes');
		$I->CustomActivateCheckbox('#pubinterview_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#children_active_yes');
		$I->CustomActivateCheckbox('#pubmovies_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#children_active_yes');
		$I->CustomActivateCheckbox('#pubportrayal_active_yes', '#lumiere_update_data_person_settings' );
		# fourth row
		$I->waitPageLoad();
		$I->scrollTo('#pubinterview_active_yes');
		$I->CustomActivateCheckbox('#pubprints_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#pubinterview_active_yes');
		$I->CustomActivateCheckbox('#quotes_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		$I->scrollTo('#pubinterview_active_yes');
		$I->CustomActivateCheckbox('#trivia_active_yes', '#lumiere_update_data_person_settings' );
		# Fifth row
		$I->waitPageLoad();
		$I->scrollTo('#pubprints_active_yes');
		$I->CustomActivateCheckbox('#trademark_active_yes', '#lumiere_update_data_person_settings' );
		$I->waitPageLoad();
		
		// See if data is not available
		$I->comment( Helper\Color::set("Check if data is hidden", "italic+bold+cyan") );
		$I->waitPageLoad();
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_PERSON );
		$I->waitPageLoad();
		$I->see('Anecdotes:');
		$I->see('Marques de fabrique:');
		$I->see('Nouvelles:');
		$I->see('Christiane Kubrick');
		$I->see('Stanley Kubrick was born in Manhattan, New York City,');
		$I->see('Stanley Kubrick');
		$I->see( 'Chuck Palahniuk' );
		$I->see( 'David Fincher' );

	}
}
