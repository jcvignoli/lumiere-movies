<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class SearchCest {

	/** Stock the base remote URL
	 *
	 */
	var $base_url = "";

	/** Stock the root remote path
	 *
	 */
	var $base_path = "";

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
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceRemoteTester $I){

		$I->comment('#Code _after#');

	}

	/** Login to Wordpress
	 *  Trait function to keep the cookie active
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {

		$I->login_universal($I);

	}

	/** Check if search page works
	 *
	 * @before login
	 *
	 */
	public function checkSearchPageWorks(AcceptanceRemoteTester $I) {

		$I->wantTo('check that search page is working');

		// Welcome page is up
		$I->amOnPage("/wp-admin/lumiere/search/");
		$I->fillField('#moviesearched', '2001');
		$I->click('Search');

		// Check if search function is working
		$I->seeInCurrentUrl( "/wp-admin/lumiere/search/?moviesearched=2001" );
		$I->see('2001: A Space Odyssey (1968)');
		$I->see('0062622');
		$I->click("#imdbid_0062622");

		// Has the JS window popped up?		
		$I->seeInPopup('Successfully copied 0062622');
		$I->acceptPopup();

	}

	/** Check if search page can be displayed from edit page (metabox)
	 *
	 * @before login
	 *
	 */
	public function checkSearchCanPopupFromMetabox(AcceptanceRemoteTester $I) {

		$I->wantTo('check that search page is working');

		// Open the window
		$I->amOnPage( ADMIN_POST_ID_TESTS );
		$I->click('a[data-lumiere_admin_popup="no data"]');
		$I->wait(5);

		// Search in the window
		$I->switchToNewWindow();
		$I->fillField('#moviesearched', '2001');
		$I->click('Search');
		$I->see('2001: A Space Odyssey (1968)');
		$I->see('0062622');

		$I->click("#imdbid_0062622");

	}
}
