<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class AMPCest {

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

		$I->amOnPage('/2021/test-codeception/');
		$I->seeInPageSource("lumiere_highslide_core-css");		# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 			# Lumière main css
		$I->seeInPageSource("lumiere_highslide_core-js");		# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");		# Highslide Lumière Options
		$I->seeInPageSource('<a class="linkincmovie modal_window_people highslide" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a></div>');		
		$I->amOnPage('/lumiere-director/stanley-kubrick/');
		$I->seeInPageSource("lumiere_highslide_core-css");		# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 			# Lumière main css
		$I->seeInPageSource("lumiere_highslide_core-js");		# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");		# Highslide Lumière Options
		$I->seeInPageSource('his uncle, <a class="modal_window_people highslide" data-modal_window_people="0675788" title="open a new window with IMDb informations">Martin Perveler</a>. Returning');		
		$I->amOnPage('/lumiere/film/?mid=&film=interstellar');
		$I->seeInPageSource("lumiere_highslide_core-css");		# Highslide CSS
		$I->seeInPageSource("lumiere_style_main-css"); 			# Lumière main css
		$I->seeInPageSource("lumiere_highslide_core-js");		# Highslide JS
		$I->seeInPageSource("lumiere_highslide_options-js");		
		$I->seeInPageSource('Ellen Burstyn</a>, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/0000190/?mid=0000190" title="internal link">');		

		// Switch To Bootstrap
		$I->wantTo(\Helper\Color::set('Check if Bootstrap modal window works', "italic+bold+cyan"));
		$I->SwitchModalWindow('Bootstrap');

		$I->amOnPage('/2021/test-codeception/');				# Check regular page
		$I->seeInPageSource("lumiere_bootstrap_custom-css");		
		$I->seeInPageSource('<a class="linkincmovie modal_window_people" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a>
			<span class="modal fade" id="theModal0227759">');
		$I->amOnPage('/lumiere-director/stanley-kubrick/');		# Check taxonomy
		$I->seeInPageSource("lumiere_bootstrap_custom-css");			
		$I->seeInPageSource('<a class="linkpopup" data-modal_window_people="0675788" data-target="#theModal0675788" title="open a new window with IMDb informations">Martin Perveler</a>
			<span class="modal fade" id="theModal0675788">');	
		$I->seeInPageSource("lumiere_bootstrap_core-js");		
		$I->amOnPage('/lumiere/film/?mid=&film=interstellar');		# Check popup movie
		$I->seeInPageSource("lumiere_bootstrap_custom-css");			
		$I->seeInPageSource("lumiere_bootstrap_core-js");
		$I->seeInPageSource('Ellen Burstyn</a>, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/0000190/?mid=0000190" title="internal link">');

		// Switch To Classic modal
		$I->wantTo(\Helper\Color::set('Check if Classic modal window works', "italic+bold+cyan"));
		$I->SwitchModalWindow('Classic');

		$I->amOnPage('/2021/test-codeception/');				# Check regular page
		$I->seeInPageSource("lumiere_classic_links-js");		
		$I->seeInPageSource('<a class="linkincmovie modal_window_people" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a></div>');
		$I->amOnPage('/lumiere-director/stanley-kubrick/');		# Check taxonomy
		$I->seeInPageSource("lumiere_classic_links-js");			
		$I->seeInPageSource("lumiere_style_main-css"); 			
		$I->seeInPageSource('<a class="modal_window_people " data-modal_window_people="0675788" title="open a new window with IMDb informations">Martin Perveler</a>');	
		$I->amOnPage('/lumiere/film/?mid=&film=interstellar');		# Check popup movie
		$I->seeInPageSource("lumiere_classic_links-js");			
		$I->seeInPageSource("lumiere_style_main-css"); 			
		$I->seeInPageSource('Ellen Burstyn</a>, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/0000190/?mid=0000190" title="internal link">');

		// Check AMP
		$I->wantTo(\Helper\Color::set('Check if AMP class works', "italic+bold+cyan"));

		$I->amOnPage('/2021/test-codeception/?amp');			# Check regular page
		$I->seeInPageSource("sourceURL=amp-custom.css");
		$I->seeInPageSource('<a class="linkpopup" id="link-0227759" data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations" href="https://www.jcvignoli.com/blogpourext/lumiere/person/?mid=0227759&amp;amp">Peter Dinklage</a></div>');
		$I->amOnPage('/lumiere-director/stanley-kubrick/?amp');		# Check taxonomy
		$I->seeInPageSource("sourceURL=amp-custom.css");
		$I->seeInPageSource('his uncle, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/?mid=0675788&amp;amp" title="internal link to">Martin Perveler</a>. Returning');		
		$I->amOnPage('/lumiere/film/?mid=&film=interstellar&amp');	# Check popup movie
		$I->seeInPageSource("sourceURL=amp-custom.css");	
		$I->seeInPageSource('Ellen Burstyn</a>, <a class="linkpopup" href="https://www.jcvignoli.com/blogpourext/lumiere/person/0000190/?mid=0000190&amp;amp" title="internal link">');

		// TODO: check NoLinks class


		// End, Switch back To Highslide
		$I->SwitchModalWindow('Highslide');

	}

}
