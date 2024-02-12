<?php

# Class meant to test AMP (a WebDriver is needed for JS execution)

class AMPCest {

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
	 * Helper: Select AMP plugin
	 * Make sure that AMP plugin is active
	 * @before login
	 */
	private function prepareForAmp(AcceptanceRemoteTester $I) {
		// Activate AMP
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->maybeActivatePlugin('amp');
		// Deactivate Query Monitor, it bugs
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->maybeDeactivatePlugin('query-monitor');
		// Make sure actor data is enabled
		$I->amOnPage('/wp-admin/admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what');
		$I->scrollTo('#taxodetails');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetactor_yes', '#update_imdbwidgetSettings' );
	}

	/** 
	 * Helper: Select AMP plugin
	 * Make sure that AMP plugin is active
	 * @before login
	 */
	private function lastActions(AcceptanceRemoteTester $I) {
		// Reactivate Query Monitor, it bugs
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->maybeActivatePlugin('query-monitor');
	}
	
	/** 
	 * Check if taxonomy works with AMP
	 *
	 * @before prepareForAmp
	 * @after lastActions
	 */
	public function checkIfAMPworks(AcceptanceRemoteTester $I) {

		$I->wantTo(\Helper\Color::set('Check if AMP page differenciation works', "italic+bold+cyan"));

		// Check if AMP is functional and remove links
		$I->amOnPage( $this->base_url . AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL . '?amp' );
		$I->seeInPageSource('<a class="linkpopup" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations" href="http://local.lumiere/blogpourext/lumiere/person/?mid=0227759&amp;amp">Peter Dinklage</a></div>');
		$I->seeInPageSource( '/lumiere/person/?mid=0227759&amp;amp">Peter Dinklage</a></div>');

		// Check if without AMP it is functional
		$I->amOnPage( $this->base_url . AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );
		$I->seeInPageSource( '<a class="linkincmovie modal_window_people highslide" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a></div>' );
	}

}
