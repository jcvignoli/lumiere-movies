<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class SearchCest {

	/* Stock the base remote URL
	 *
	 */
	var $url_base_remote = "";

	/* Stock the root remote path
	 *
	 */
	var $root_remote = "";

	public function __construct(){

		$this->url_base_remote = $_ENV['TEST_REMOTE_WP_URL'];
		$this->root_remote = $_ENV['WP_ROOT_REMOTE_FOLDER'];

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

		AcceptanceTrait::login_universal($I);

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
		$I->amOnPage("/wp-admin/post.php?post=4715&action=edit");
		$I->click('a[data-lumiere_admin_popup="no data"]');

		// Search in the window
/*		$I->switchToWindow("popup");
		$I->fillField('#moviesearched', '2001');
		$I->click('Search');
		$I->see('2001: A Space Odyssey (1968)');
		$I->see('0062622');
		$I->click("#imdbid_0062622");
can't get this work
*/
	}
}
