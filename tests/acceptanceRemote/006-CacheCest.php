<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class CacheCest {

	/** Stock the base remote URL
	 *
	 */
	var $url_base_remote = "";

	/** Stock the root remote path
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

		$I->login_universal($I);

	}

	/** Check if auto widget option display a widget based on the title of the page
	 *
	 * @before login
	 * @example ["Werewolf", "title.tt0118137"]
	 * @example ["Game_of_Thrones", "title.tt0944947"]
	 *
	 */
	public function checkCacheIsCreatedForMovies(AcceptanceRemoteTester $I, \Codeception\Example $example, \Codeception\Module\Cli $shell) {

		/* Vars */

		$js_element_delete = 'a[data-confirm="Delete *'.str_replace('_', ' ', $example[0]).'* from cache?"]';

		$I->wantTo('check that cache is created');

		// Make local connexion
		$I->activateLocalMount( $this->root_remote, $shell );

		// Make sure cache is created
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->seeFileFound($example[1], $this->root_remote.'/wp-content/cache/lumiere/');

		// Delete cache file using local path
		$I->deleteFile($this->root_remote.'/wp-content/cache/lumiere/'.$example[1]);

		// Make sure cache is created
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->seeFileFound($example[1], $this->root_remote.'/wp-content/cache/lumiere/');

		// Delete cache file using interface
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_CACHE_OPTIONS_MANAGE_URL );
		$I->scrollTo('#imdb_cachedeletefor_movies_'.$example[0]);
		$I->executeJS( "return jQuery('" . $js_element_delete . "').get(0).click()");
		$I->acceptPopup();

		// Make sure cache is created
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->seeFileFound($example[1], $this->root_remote.'/wp-content/cache/lumiere/');

	}

	/** Check if auto widget option display a widget based on the title of the page
	 *
	 * @before login
	 * @example ["Jorge_Rivero", "name.nm0729473", "0729473"]
	 * @example ["Stanley_Kubrick", "name.nm0000040", "0000040"]
	 *
	 */
	public function checkCacheIsCreatedForPeople(AcceptanceRemoteTester $I, \Codeception\Example $example, \Codeception\Module\Cli $shell) {

		/* Vars */

		$js_element_delete = 'a[data-confirm="You are about to delete *'.str_replace('_', ' ', $example[0]).'* from cache. Click Cancel to stop or OK to continue."]';

		$I->wantTo('check that cache is created');

		// Make local connexion
		$I->activateLocalMount( $this->root_remote, $shell );

		// Make sure cache is created
		$I->amOnPage("/lumiere/person/?mid=".$example[2]);
		$I->wait(7);
		$I->seeFileFound($example[1], $this->root_remote.'/wp-content/cache/lumiere/');

		// Delete cache file using local path
		$I->deleteFile($this->root_remote.'/wp-content/cache/lumiere/'.$example[1]);

		// Make sure cache is created
		$I->amOnPage("/lumiere/person/?mid=".$example[2]);
		$I->wait(6);
		$I->seeFileFound($example[1], $this->root_remote.'/wp-content/cache/lumiere/');

		// Delete cache file using interface
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&subsection=cache&cacheoption=manage");
		$I->scrollTo('#imdb_cachedeletefor_people_'.$example[0]);
		$I->executeJS( "return jQuery('" . $js_element_delete . "').get(0).click()");
		$I->acceptPopup();
		$I->wait(2);

		// Make sure cache is created
		$I->amOnPage("/lumiere/person/?mid=".$example[2]);
		$I->wait(6);
		$I->seeFileFound($example[1], $this->root_remote.'/wp-content/cache/lumiere/');

	}

}
