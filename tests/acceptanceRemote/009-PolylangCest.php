<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class PolylangCest {

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
		$I->comment(\Helper\Color::set("#Code _before#", "italic+bold+cyan"));
	}

	public function _after(AcceptanceRemoteTester $I){

		$I->comment(\Helper\Color::set("#Code _after#", "italic+bold+cyan"));

	}

	/**
	 *  Login to Wordpress
	 *  Trait function to keep the cookie active
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {

		$I->login_universal($I);

	}

	/** Helper: Select Highslide
	 * Make sure that Highslide modal window is selected
	 *
	 */
	private function highslide(AcceptanceRemoteTester $I) {

		// Make sure Highslide is active, following tests are run with Highslide
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options');
		$I->customSelectOption( "select[name=imdbpopup_modal_window]", "Highslide", "update_imdbSettings" );

	}

	/** 
	 * Check if taxonomy works with Polylang
	 *
	 * @before login
	 * @before highslide
	 *
	 */
	public function checkTaxonomyActivationWorksWithPolylang(AcceptanceRemoteTester $I) {

		$I->wantTo(\Helper\Color::set('Check if taxonomy works with Polylang', "italic+bold+cyan"));

		// Activate Polylang
		$I->amOnPage('/wp-admin/plugins.php');
		$I->maybeActivatePlugin('polylang');

		// Activate taxonomy
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

		// Activate director data detail
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetdirector_yes', '#update_imdbwidgetSettings' );

		// Activate director taxonomy
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=taxo');
		$I->scrollTo('#imdb_imdbtaxonomycomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomydirector_yes', '#update_imdbwidgetSettings' );

		// Check if polylang options are available
		$I->amOnPage('/2021/test-codeception/');
		$I->click( "Tony Zarindast");
		$I->scrollTo('#highslide_pic');
		$I->seeInPageSource('Filter language');
		$I->seeInPageSource('Español');
		$I->seeInPageSource('English');
		$I->seeInPageSource('Français');

		// Deactivate Polylang plugin 
		$I->amOnPage('/wp-admin/plugins.php');
		$I->maybeDeactivatePlugin('polylang');

		// Check if polylang options are available
		$I->amOnPage('/2021/test-codeception/');
		$I->click( "Tony Zarindast");
		$I->scrollTo('#highslide_pic');
		$I->dontSeeInPageSource('Filter language');
		$I->dontSeeInPageSource('Español');
		$I->dontSeeInPageSource('Français');

		// Reactivate Polylang
		$I->amOnPage('/wp-admin/plugins.php');
		$I->maybeActivatePlugin('polylang');

	}

}

