<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to test Cache (a WebDriver is needed for JS execution)

class CacheCest {

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
	 * Check if website is online, otherwise exit
	 * Internet connection is needed if executed locally but to create the cache will need to fetch IMDb website
	 */
	public function checkCanAccessIMDB( AcceptanceTester $I, \Codeception\Module\Cli $shell ) {
		// Make local connexion
		$I->activateLocalMount( $I->getCustomBasePath(), $shell );
		$I->deleteTestFileMount( $I->getCustomBasePath(), $shell );
	}

	/**
	 * Check if cache is created for movies
	 *
	 * @before login
	 * @example ["Werewolf", "gql.TitleYear.{.id...tt0118137.}"]
	 * @example ["Barry_Lyndon", "gql.TitleYear.{.id...tt0072684.}"]
	 */
	public function checkCacheIsCreatedForMovies(AcceptanceTester $I, \Codeception\Example $example, \Codeception\Module\Cli $shell) {

		/* Vars */
		$js_element_delete = 'a[data-confirm="Delete *'.str_replace('_', ' ', $example[0]).'* from cache?"]';
		$file_current = $I->customFindFileWildcard( $I->getCustomBasePath() . '/wp-content/cache/lumiere/' . $example[1] . '*' );

		$I->comment( '-> Check that cache is created for ' . $example[0] );

		// Make sure Highslide is active, following tests are run with Highslide
		$I->SwitchModalWindow('Highslide');
		
		// Make sure cache folders are properly created by visiting any admin page
		$I->amOnPage( AcceptanceSettings::LUMIERE_GENERAL_OPTIONS_URL );
		$I->see( 'Layout' );
		$I->wait(2);
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );

		// Make local connexion
		$I->activateLocalMount( $I->getCustomBasePath(), $shell );

		// Make sure cache is created
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->seeFileFound( $file_current );

		// Delete cache file using local path
		$I->deleteFile( $file_current );

		// Make sure cache is created
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->wait(3);
		$I->seeFileFound( $file_current );

		// Delete cache file using interface
		$this->customDeleteCache( $I, $js_element_delete, '#imdb_cachedeletefor_movies_' . $example[0] );

		// Make sure cache is created
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->wait(2);
		$I->seeFileFound( $file_current );
	}

	/**
	 * Check if cache is created for people
	 *
	 * @before login
	 * @example ["Jorge_Rivero", "gql.Name.{.id...nm0729473.}", "0729473", "Distrito Federal, Mexico"]
	 * @example ["Stanley_Kubrick", "gql.Name.{.id...nm0000040.}", "0000040", "Hertfordshire, England" ]
	 */
	public function checkCacheIsCreatedForPeople(AcceptanceTester $I, \Codeception\Example $example, \Codeception\Module\Cli $shell) {

		/* Vars */
		$js_element_delete = 'a[data-confirm="You are about to delete *'.str_replace('_', ' ', $example[0]).'* from cache. Click Cancel to stop or OK to continue."]';
		$file_current = $I->customFindFileWildcard( $I->getCustomBasePath() . '/wp-content/cache/lumiere/' . $example[1] . '*' );

		$I->comment( '-> Check that cache is created for ' . $example[0] );

		// Make local connexion
		$I->activateLocalMount( $I->getCustomBasePath(), $shell );

		// Make sure cache is created
		$I->amOnPage( '/lumiere/person/?mid=' . $example[2] );
		$I->waitForText( $example[3], 15 ); // wait up to 15 seconds
		$I->seeFileFound( $file_current );

		// Delete cache file using local path
		$I->deleteFile( $file_current );

		// Make sure cache is created
		$I->amOnPage( "/lumiere/person/?mid=" . $example[2] );
		$I->waitForText( $example[3], 15); // wait up to 15 seconds
		$I->seeFileFound( $file_current );

		$this->customDeleteCache( $I, $js_element_delete, '#imdb_cachedeletefor_people_' . $example[0] );

		// Make sure cache is created
		$I->amOnPage("/lumiere/person/?mid=".$example[2]);
		$I->waitForText( $example[3], 15 ); // wait up to 15 seconds
		$I->seeFileFound( $file_current );

	}

	/**
	 * Private (invisible) function to delete cache
	 *
	 * @param string $element_to_delete The element in HTML code that helps build the javascript for deletion
	 * @param string $name Is used to know which cache to delete
	 */
	private function customDeleteCache( AcceptanceTester $I, string $element_to_delete, string $name_id ) {
		// Delete cache file using interface
		$I->amOnPage( AcceptanceSettings::LUMIERE_CACHE_OPTIONS_MANAGE_URL );
		$I->scrollTo( $name_id );
		$I->executeJS( "return jQuery('" . $element_to_delete . "').get(0).click()");
		$I->acceptPopup();
	}
}
