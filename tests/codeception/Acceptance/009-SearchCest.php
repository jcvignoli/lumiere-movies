<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to test IMDbphp Search functions (a WebDriver is needed for JS execution)

class SearchCest {

	public function _before(AcceptanceTester $I){
		$I->comment( '#Code _before#' );
	}

	public function _after(AcceptanceTester $I){
		$I->comment( '#Code _after#' );
	}

	/** 
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Prepare the options for the search
	 *
	 * @before login
	 */
	public function prepare(AcceptanceTester $I) {
		// Classic editor, so we can click
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('classic-editor');
	}

	/**
	 * Check if search page works
	 *
	 * @before login
	 */
	public function checkSearchPageWorks(AcceptanceTester $I) {

		$I->comment( 'Check that search page is working' );

		// Welcome page is up
		$I->amOnPage( "/wp-admin/lumiere/search-items/" );
		$I->fillField( '#lum_movie_input', '2001' );
		$I->click( 'Recherche' );
		$I->waitPageLoad();

		// Check if search function is working
		$I->seeInCurrentUrl( "/wp-admin/lumiere/search-items/?select_search_type=movie&itemsearched=2001" );
		$I->see( '2001: A Space Odyssey (1968)' );
		$I->see( '0062622' );
		$I->click( "#imdbid_0062622" );

		// Has the JS window popped up?		
		$I->seeInPopup( 'Successfully copied 0062622' );
		$I->acceptPopup();

	}

	/**
	 * Check if search page can be displayed from edit page (metabox)
	 *
	 * @before login
	 */
	public function checkSearchCanPopupFromMetabox(AcceptanceTester $I) {

		$I->comment( 'Check that search popup in metabox is working' );

		// Make sure Highslide is active, following tests are run with Highslide
		$I->SwitchModalWindow('Highslide');
		$I->amOnPluginsPage();

		// Open the window
		$I->amOnPage( ADMIN_POST_ID_TESTS );
		$I->scrollTo( '#lum_form_type_query' );
		
		$I->click( 'a[data-lumiere_admin_search_popup="noInfoNeeded"]' );
		$I->waitPageLoad();

		// Search in the window
		$I->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
			$handles=$webdriver->getWindowHandles();
			$last_window = end($handles);
			$webdriver->switchTo()->window($last_window);
		});
		$I->waitForElementVisible( '#lum_movie_input', 15 ); // wait up to 15 seconds
		
		$I->scrollTo('#lum_movie_input');
		$I->fillField( '#lum_movie_input', '2001' );
		$I->click( 'Recherche' );
		$I->waitPageLoad();
		$I->see( '2001: A Space Odyssey (1968)' );
		$I->see( '0062622' );

		$I->scrollTo([ 'id' => 'imdbid_0062622' ]);
		$I->click( '#imdbid_0062622' );
		
		$I->acceptPopup();
		
		// Back to the main window.
		$I->switchToWindow();
	}
}
