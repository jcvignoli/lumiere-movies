<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class ModalWindowsCest {

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

	/** 
	 * Check if taxonomy works with AMP
	 *
	 * @before login
	 *
	 */
	public function checkModalWindows(AcceptanceRemoteTester $I) {

		// Switch To Highslide
		$I->wantTo(\Helper\Color::set('Check if Highslide modal window works', "italic+bold+cyan"));
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
		$I->seeInPageSource('his uncle, <a class="modal_window_people highslide" data-modal_window_people="0675788" title="open a new window with IMDb informations">Martin Perveler</a>. Returning');		
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->seeInPageSource("lumiere_highslide_core-css");
		$I->seeInPageSource("lumiere_style_main-css"); 	
		$I->seeInPageSource("lumiere_highslide_core-js");
		$I->seeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('Ellen Burstyn</a>, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/0000190/?mid=0000190" title="internal link">');

		// Switch To Bootstrap
		$I->wantTo(\Helper\Color::set('Check if Bootstrap modal window works', "italic+bold+cyan"));
		$I->SwitchModalWindow('Bootstrap');

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );# Check regular page
		$I->seeInPageSource("lumiere_bootstrap_custom-css");		
		$I->seeInPageSource('<a class="linkincmovie modal_window_people" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a>
			<span class="modal fade" id="theModal0227759">');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_TAXONOMY_URL );# Check taxonomy page
		$I->seeInPageSource("lumiere_bootstrap_custom-css");			
		$I->seeInPageSource('<a class="linkpopup" data-modal_window_people="0675788" data-target="#theModal0675788" title="open a new window with IMDb informations">Martin Perveler</a>
			<span class="modal fade" id="theModal0675788">');	
		$I->seeInPageSource("lumiere_bootstrap_core-js");		
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->seeInPageSource("lumiere_bootstrap_custom-css");			
		$I->seeInPageSource("lumiere_bootstrap_core-js");
		$I->seeInPageSource('Ellen Burstyn</a>, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/0000190/?mid=0000190" title="internal link">');

		// Switch To Classic modal
		$I->wantTo(\Helper\Color::set('Check if Classic modal window works', "italic+bold+cyan"));
		$I->SwitchModalWindow('Classic');

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );# Check regular page
		$I->seeInPageSource("lumiere_classic_links-js");		
		$I->seeInPageSource('<a class="linkincmovie modal_window_people" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a></div>');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_TAXONOMY_URL );# Check taxonomy page
		$I->seeInPageSource("lumiere_classic_links-js");
		$I->seeInPageSource("lumiere_style_main-css"); 			
		$I->seeInPageSource('<a class="modal_window_people " data-modal_window_people="0675788" title="open a new window with IMDb informations">Martin Perveler</a>');	
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->seeInPageSource("lumiere_classic_links-js");			
		$I->seeInPageSource("lumiere_style_main-css"); 			
		$I->seeInPageSource('Ellen Burstyn</a>, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/0000190/?mid=0000190" title="internal link">');

		// Check AMP
		$I->wantTo(\Helper\Color::set('Check if AMP class works', "italic+bold+cyan"));

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL . '?amp' );# Check regular page
		$I->seeInPageSource("sourceURL=amp-custom.css");
		$I->seeInPageSource('<a class="linkpopup" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations" href="https://www.jcvignoli.com/blogpourext/lumiere/person/?mid=0227759&amp;amp">Peter Dinklage</a></div>');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_TAXONOMY_URL . '?amp' );# Check taxonomy page
		$I->seeInPageSource("sourceURL=amp-custom.css");
		$I->seeInPageSource('his uncle, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/?mid=0675788&amp;amp" title="internal link to">Martin Perveler</a>. Returning');		
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL . '&amp' );# Check popup movie
		$I->seeInPageSource("sourceURL=amp-custom.css");	
		$I->seeInPageSource('Ellen Burstyn</a>, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/0000190/?mid=0000190&amp;amp" title="internal link">');

		// Check NoLinks class
		$I->wantTo(\Helper\Color::set('Check if No Links works', "italic+bold+cyan"));
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#miscpart');
		$I->CustomActivateCheckbox('#imdb_imdblinkingkill_yes', '#update_imdbSettings' );

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_BASE_URL );# Check regular page
		$I->dontSeeInPageSource("sourceURL=amp-custom.css");
		$I->dontSeeInPageSource("lumiere_classic_links-js");		
		$I->dontSeeInPageSource("lumiere_bootstrap_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_options-js");

		$I->seeInPageSource('<div class="lumiere_align_left lumiere_flex_auto">Peter Dinklage</div>');
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_TAXONOMY_URL );# Check taxonomy page
		$I->dontSeeInPageSource("sourceURL=amp-custom.css");
		$I->dontSeeInPageSource("lumiere_classic_links-js");		
		$I->dontSeeInPageSource("lumiere_bootstrap_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('his uncle, Martin Perveler. Returning');

		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->dontSeeInPageSource("sourceURL=amp-custom.css");	
		$I->dontSeeInPageSource("lumiere_classic_links-js");		
		$I->dontSeeInPageSource("lumiere_bootstrap_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('Ellen Burstyn</a>, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/0000190/?mid=0000190" title="internal link">'); // There are links in no class popups

		// End, Switch back To Highslide, remove kill imdb links
		$I->SwitchModalWindow('Highslide');
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#miscpart');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#update_imdbSettings' );

	}

}
