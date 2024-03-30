<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class PolylangCest {

	/* Stock the base remote URL
	 *
	 */
	var $base_url = "";

	/* Stock the root remote path
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

	/**
	 * Helper: Select Highslide
	 * Make sure that Highslide modal window is selected
	 *
	 */
	private function highslide(AcceptanceRemoteTester $I) {

		// Make sure Highslide is active, following tests are run with Highslide
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_GENERAL_OPTIONS_URL );
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
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('polylang');

		// Activate taxonomy
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

		// Activate director data detail
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetdirector_yes', '#update_imdbwidgetSettings' );

		// Activate director taxonomy
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_DATA_OPTIONS_TAXO_URL );
		$I->scrollTo('#imdb_imdbtaxonomycomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomydirector_yes', '#update_imdbwidgetSettings' );

		// Check if polylang options are available
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->click( "Tony Zarindast");
		$I->scrollTo('.imdbelementPIC');
		$I->seeInPageSource('<form method="get" id="lang_form" name="lang_form" action="#lang_form">');
		$I->seeInPageSource('Español');
		$I->seeInPageSource('English');
		$I->seeInPageSource('Français');

		// Deactivate Polylang plugin 
		$I->amOnPluginsPage();
		$I->maybeDeactivatePlugin('polylang');

		// Check if polylang options are available
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->click( "Tony Zarindast");
		$I->scrollTo('.imdbelementPIC');
		$I->dontSeeInPageSource('<form method="get" id="lang_form" name="lang_form" action="#lang_form">');
		$I->dontSeeInPageSource('Español');
		$I->dontSeeInPageSource('Français');

		// Reactivate Polylang
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('polylang');
	}
}

