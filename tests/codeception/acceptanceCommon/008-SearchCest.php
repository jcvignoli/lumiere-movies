<?php

# Class meant to test IMDbphp Search functions (a WebDriver is needed for JS execution)

class SearchCest {

	/**
	 * Stock the base remote URL
	 */
	private string $base_url;
	private string $base_path;

	public function __construct(){

		// Build vars
		$remote_or_local = defined( 'DEVELOPMENT_ENVIR' ) ? DEVELOPMENT_ENVIR : '';
		$final_var_url = 'TEST_' . strtoupper( $remote_or_local ) . '_WP_URL';
		$final_var_root_folder = 'WP_ROOT_' . strtoupper( $remote_or_local ) . '_FOLDER';

		// Build properties
		$this->base_url = $_ENV[ $final_var_url ];
		$this->base_path = $_ENV[$final_var_root_folder];
	}


	public function _before(AcceptanceRemoteTester $I){
		$I->comment( '#Code _before#' );
	}

	public function _after(AcceptanceRemoteTester $I){

		$I->comment( '#Code _after#' );

	}

	/** 
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Prepare the options for the search
	 *
	 * @before login
	 */
	public function prepare(AcceptanceRemoteTester $I) {
		// Classic editor, so we can click
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('classic-editor');
	}

	/**
	 * Check if search page works
	 *
	 * @before login
	 */
	public function checkSearchPageWorks(AcceptanceRemoteTester $I) {

		$I->comment( 'Check that search page is working' );

		// Welcome page is up
		$I->amOnPage( "/wp-admin/lumiere/search/" );
		$I->fillField( '#moviesearched', '2001' );
		$I->click( 'Search' );

		// Check if search function is working
		$I->seeInCurrentUrl( "/wp-admin/lumiere/search/?moviesearched=2001" );
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
	public function checkSearchCanPopupFromMetabox(AcceptanceRemoteTester $I) {

		$I->comment( 'Check that search popup in metabox is working' );

		// Make sure Highslide is active, following tests are run with Highslide
		$I->SwitchModalWindow('Highslide');
		$I->amOnPluginsPage();

		// Open the window
		$I->amOnPage( ADMIN_POST_ID_TESTS );
		$I->scrollTo( '#lum_form_type_query' );
		
		$I->click( 'a[data-lumiere_admin_search_popup="noInfoNeeded"]' );
		$I->wait(5);

		// Search in the window
		$I->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
			$handles=$webdriver->getWindowHandles();
			$last_window = end($handles);
			$webdriver->switchTo()->window($last_window);
		});
		$I->waitForElementVisible( '#moviesearched', 15 ); // wait up to 15 seconds
		
		$I->scrollTo('#moviesearched');
		$I->fillField( '#moviesearched', '2001' );
		$I->click( 'Search' );
		$I->see( '2001: A Space Odyssey (1968)' );
		$I->see( '0062622' );

		$I->scrollTo('#imdbid_0062622');
		$I->click( '#imdbid_0062622' );
		
		$I->acceptPopup();
		
		// Back to the main window.
		$I->switchToWindow();
	}
}
