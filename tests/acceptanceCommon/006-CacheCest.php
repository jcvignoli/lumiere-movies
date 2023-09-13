<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class CacheCest {

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

	/**
	 * Make sure cache is created, login to visit an admin page
	 */
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
	 * @example ["Barry_Lyndon", "title.tt0072684"]
	 *
	 */
	public function checkCacheIsCreatedForMovies(AcceptanceRemoteTester $I, \Codeception\Example $example, \Codeception\Module\Cli $shell) {

		/* Vars */

		$js_element_delete = 'a[data-confirm="Delete *'.str_replace('_', ' ', $example[0]).'* from cache?"]';

		$I->wantTo('check that cache is created');

		// Make sure cache folders are properly created by visiting any admin page
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_GENERAL_OPTIONS_URL );
		$I->see("Layout");
		$I->wait(2);
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );

		// Make local connexion
		$I->activateLocalMount( $this->base_path, $shell );

		// Make sure cache is created
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->seeFileFound($this->base_path.'/wp-content/cache/lumiere/' . $example[1]);

		// Delete cache file using local path
		$I->deleteFile($this->base_path.'/wp-content/cache/lumiere/'.$example[1]);

		// Make sure cache is created
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->seeFileFound( $this->base_path.'/wp-content/cache/lumiere/' . $example[1]);

		// Delete cache file using interface
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_CACHE_OPTIONS_MANAGE_URL );
		$I->scrollTo('#imdb_cachedeletefor_movies_'.$example[0]);
		$I->executeJS( "return jQuery('" . $js_element_delete . "').get(0).click()");
		$I->acceptPopup();

		// Make sure cache is created
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->seeFileFound($example[1], $this->base_path.'/wp-content/cache/lumiere/');

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
		$I->activateLocalMount( $this->base_path, $shell );

		// Make sure cache is created
		$I->amOnPage("/lumiere/person/?mid=".$example[2]);
		$I->wait(4);
		$I->seeFileFound($example[1], $this->base_path.'/wp-content/cache/lumiere/');

		// Delete cache file using local path
		$I->deleteFile($this->base_path.'/wp-content/cache/lumiere/'.$example[1]);

		// Make sure cache is created
		$I->amOnPage("/lumiere/person/?mid=".$example[2]);
		$I->wait(4);
		$I->seeFileFound($example[1], $this->base_path.'/wp-content/cache/lumiere/');

		// Delete cache file using interface
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&subsection=cache&cacheoption=manage");
		$I->scrollTo('#imdb_cachedeletefor_people_'.$example[0]);
		$I->executeJS( "return jQuery('" . $js_element_delete . "').get(0).click()");
		$I->acceptPopup();
		$I->wait(2);

		// Make sure cache is created
		$I->amOnPage("/lumiere/person/?mid=".$example[2]);
		$I->wait(4);
		$I->seeFileFound($example[1], $this->base_path.'/wp-content/cache/lumiere/');

	}

}
