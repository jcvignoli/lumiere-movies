<?php

# Class meant to test Modal Windows (a WebDriver is needed for JS execution)

class ModalWindowsCest {

	/**
	 * Stock the base remote URL
	 */
	var $base_url = "";

	/**
	 * Stock the root remote path
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
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->login_universal($I);
	}

	/** 
	 * Check if switching modal/non-modal windows works
	 *
	 * @before login
	 */
	public function checkModalWindows(AcceptanceRemoteTester $I) {

		// Switch To Highslide
		$I->comment(\Helper\Color::set('Check if Highslide modal window works', "italic+bold+cyan"));
		$I->SwitchModalWindow('Highslide');

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );# Check regular page
		$I->seeInPageSource("lumiere_highslide_core-css");
		$I->seeInPageSource("lumiere_style_main-css"); 	
		$I->seeInPageSource("lumiere_highslide_core-js");
		$I->seeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('<a class="linkincmovie modal_window_people highslide" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a></div>');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_TAXONOMY_URL );# Check taxonomy page
		$I->seeInPageSource("lumiere_highslide_core-css");
		$I->seeInPageSource("lumiere_style_main-css"); 	
		$I->seeInPageSource("lumiere_highslide_core-js");
		$I->seeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('to stay with his
uncle, <a class="modal_window_people highslide" data-modal_window_people="0675788" title="open a new window with IMDb informations">Martin Perveler</a>. Returning');		
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->seeInPageSource("lumiere_highslide_core-css");
		$I->seeInPageSource("lumiere_style_main-css"); 	
		$I->seeInPageSource("lumiere_highslide_core-js");
		$I->seeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('Ellen Burstyn</a>, <a rel="nofollow" class="linkpopup" href="' . $this->base_url . '/lumiere/person/0000190/?mid=0000190" title="');

		// Switch To Bootstrap
		$I->comment(\Helper\Color::set('Check if Bootstrap modal window works', "italic+bold+cyan"));
		$I->SwitchModalWindow('Bootstrap');

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );# Check regular page
		$I->seeInPageSource("lumiere_bootstrap_custom-css");		
		$I->seeInPageSource('<a class="linkincmovie modal_window_people" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a>
			<span class="modal fade" id="theModal0227759">');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_TAXONOMY_URL );# Check taxonomy page
		$I->seeInPageSource("lumiere_bootstrap_custom-css");			
		$I->seeInPageSource('California, to stay with his
uncle, <a class="linkpopup" data-modal_window_people="0675788" data-target="#theModal0675788" title="');	
		$I->seeInPageSource("lumiere_bootstrap_core-js");		
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->seeInPageSource("lumiere_bootstrap_custom-css");			
		$I->seeInPageSource("lumiere_bootstrap_core-js");
		$I->seeInPageSource('Ellen Burstyn</a>, <a rel="nofollow" class="linkpopup" href="' . $this->base_url . '/lumiere/person/0000190/?mid=0000190" title="');

		// Switch To Classic modal
		$I->comment(\Helper\Color::set('Check if Classic modal window works', "italic+bold+cyan"));
		$I->SwitchModalWindow('Classic');

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );# Check regular page
		$I->seeInPageSource("lumiere_classic_links-js");		
		$I->seeInPageSource('<a class="linkincmovie modal_window_people" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a></div>');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_TAXONOMY_URL );# Check taxonomy page
		$I->seeInPageSource("lumiere_classic_links-js");
		$I->seeInPageSource("lumiere_style_main-css"); 			
		$I->seeInPageSource('California, to stay with his
uncle, <a class="modal_window_people " data-modal_window_people="0675788" title="');	
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->seeInPageSource("lumiere_classic_links-js");			
		$I->seeInPageSource("lumiere_style_main-css"); 			
		$I->seeInPageSource('Ellen Burstyn</a>, <a rel="nofollow" class="linkpopup" href="' . $this->base_url . '/lumiere/person/0000190/?mid=0000190" title="');

		// Check AMP
		$I->comment(\Helper\Color::set('Check if AMP class works', "italic+bold+cyan"));

		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->maybeDeactivatePlugin('query-monitor');

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL . '?amp' );# Check regular page
		$I->waitForText( 'test codeception', 15 ); // wait up to 15 seconds
		$I->seeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");
		$I->seeInPageSource('<a class="linkpopup" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations" href="' . $this->base_url . '/lumiere/person/?mid=0227759&amp;amp">Peter Dinklage</a></div>');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_TAXONOMY_URL . '?amp' );# Check taxonomy page
		$I->seeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");
		$I->seeInPageSource('to stay with his
uncle, <a class="linkpopup" href="' . $this->base_url . '/lumiere/person/?mid=0675788&amp;amp" title="');		
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL . '&amp' );# Check popup movie
		$I->seeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");	
		$I->seeInPageSource('Ellen Burstyn</a>, <a rel="nofollow" class="linkpopup" href="' . $this->base_url . '/lumiere/person/0000190/?mid=0000190&amp;amp" title="');

		// Reactivate Query Monitor, it bugs
		$I->amOnPage( AcceptanceRemoteSettings::ADMIN_PLUGINS_URL );
		$I->maybeActivatePlugin('query-monitor');

		// Check NoLinks class
		$I->comment(\Helper\Color::set('Check if No Links works', "italic+bold+cyan"));
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#miscpart');
		$I->CustomActivateCheckbox('#imdb_imdblinkingkill_yes', '#lumiere_update_general_settings' );

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );# Check regular page
		$I->dontSeeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");
		$I->dontSeeInPageSource("lumiere_classic_links-js");		
		$I->dontSeeInPageSource("lumiere_bootstrap_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_options-js");

		$I->seeInPageSource('<div class="lumiere_align_left lumiere_flex_auto">Peter Dinklage</div>');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_TAXONOMY_URL );# Check taxonomy page
		$I->dontSeeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");
		$I->dontSeeInPageSource("lumiere_classic_links-js");		
		$I->dontSeeInPageSource("lumiere_bootstrap_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('his uncle, Martin Perveler. Returning');

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->dontSeeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");	
		$I->dontSeeInPageSource("lumiere_classic_links-js");		
		$I->dontSeeInPageSource("lumiere_bootstrap_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('Ellen Burstyn</a>, <a rel="nofollow" class="linkpopup" href="' . $this->base_url . '/lumiere/person/0000190/?mid=0000190" title="'); // There are links in no class popups

		// End, Switch back To Highslide, remove kill imdb links
		$I->SwitchModalWindow('Highslide');
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#miscpart');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#lumiere_update_general_settings' );

	}

}
