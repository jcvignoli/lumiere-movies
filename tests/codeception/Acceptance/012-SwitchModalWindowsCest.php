<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to test Modal Windows (a WebDriver is needed for JS execution)

class ModalWindowsCest {

	public function _before(AcceptanceTester $I){
		$I->comment(Helper\Color::set("#Code _before#", "italic+bold+cyan"));
	}

	public function _after(AcceptanceTester $I){
		$I->comment(Helper\Color::set("#Code _after#", "italic+bold+cyan"));
	}

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/** 
	 * Check if switching modal/non-modal windows works
	 *
	 * @before login
	 */
	public function checkModalWindows(AcceptanceTester $I) {

		// Make sure kill imdb links is not active
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#behaviourpart');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#lumiere_update_main_settings' );

		// Switch To Highslide
		$I->comment(Helper\Color::set('Check if Highslide modal window works', "italic+bold+cyan"));
		$I->SwitchModalWindow('Highslide');
		$I->waitPageLoad();
		
		// Make sure a term for English taxo exists
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );

		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );# Check regular page
		$I->seeInPageSource("lumiere_highslide_core_style-css");
		$I->seeInPageSource("lumiere_style_main-css"); 	
		$I->seeInPageSource("lumiere_highslide_core-js");
		$I->seeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('<a class="add_cursor lum_link_make_popup lum_link_with_people highslide" id="link-0227759" data-modal_window_nonce="' );
		$I->seeInPageSource( 'data-modal_window_people="0227759" data-target="#theModal0227759" title="Open a new window with IMDb informations for Peter Dinklage">Peter Dinklage</a>' );
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_TAXONOMY_URL );# Check taxonomy page
		$I->seeInPageSource("lumiere_highslide_core_style-css");
		$I->seeInPageSource("lumiere_style_main-css"); 	
		$I->seeInPageSource("lumiere_highslide_core-js");
		$I->seeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource(' to stay with his uncle, Martin Perveler. Returning to the Bronx in 1941');		
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->seeInPageSource("lumiere_highslide_core_style-css");
		$I->seeInPageSource("lumiere_style_main-css"); 	
		$I->seeInPageSource("lumiere_highslide_core-js");
		$I->seeInPageSource("lumiere_highslide_options-js");

		$I->seeInPageSource( 'Ellen Burstyn</a>, 
						<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" title="internal link Elyes Gabel" href="' . $I->getCustomBaseUrl() . '/lumiere/person/?mid=1175468' );

		// Switch To Bootstrap
		$I->comment(Helper\Color::set('Check if Bootstrap modal window works', "italic+bold+cyan"));
		$I->SwitchModalWindow('Bootstrap');
		$I->waitPageLoad();
		
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );# Check regular page
		$I->seeInPageSource("lumiere_bootstrap_custom-css");		
		$I->seeInPageSource( '<a class="add_cursor lum_link_make_popup lum_link_with_people" id="link-0227759" data-modal_window_nonce="' );
		$I->seeInPageSource( 'data-modal_window_people="0227759" data-target="#theModal0227759" title="Open a new window with IMDb informations for Peter Dinklage">Peter Dinklage</a>
			<span class="modal fade" id="theModal0227759">');
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_TAXONOMY_URL );# Check taxonomy page
		$I->seeInPageSource("lumiere_bootstrap_custom-css");			
		$I->seeInPageSource(' to stay with his uncle, Martin Perveler. Returning to the Bronx in 1941');	
		$I->seeInPageSource("lumiere_bootstrap_core-js");		
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->seeInPageSource("lumiere_bootstrap_custom-css");			
		$I->seeInPageSource("lumiere_bootstrap_core-js");
		$I->seeInPageSource('Ellen Burstyn</a>, 
					<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" title="internal link Elyes Gabel" href="' . $I->getCustomBaseUrl() . '/lumiere/person/?mid=1175468' );

		// Switch To Classic modal
		$I->comment(Helper\Color::set('Check if Classic modal window works', "italic+bold+cyan"));
		$I->SwitchModalWindow('Classic');
		$I->waitPageLoad();
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );# Check regular page
		$I->seeInPageSource("lumiere_classic_links-js");		
		$I->seeInPageSource( '<a class="add_cursor lum_link_make_popup lum_link_with_people" id="link-0227759" data-modal_window_nonce="' );
		$I->seeInPageSource( 'data-modal_window_people="0227759" data-target="#theModal0227759" title="Open a new window with IMDb informations for Peter Dinklage">Peter Dinklage</a>');
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_TAXONOMY_URL );# Check taxonomy page
		$I->seeInPageSource("lumiere_classic_links-js");
		$I->seeInPageSource("lumiere_style_main-css"); 			
		$I->seeInPageSource(' to stay with his uncle, Martin Perveler. Returning to the Bronx in 1941');	
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_POPUP_FILM_URL );# Check popup movie
		$I->seeInPageSource("lumiere_classic_links-js");			
		$I->seeInPageSource("lumiere_style_main-css"); 			
		$I->seeInPageSource('Ellen Burstyn</a>, 
						<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" title="internal link Elyes Gabel" href="' . $I->getCustomBaseUrl() . '/lumiere/person/?mid=1175468');

		// Check AMP
		$I->comment(Helper\Color::set('Check if AMP class works', "italic+bold+cyan"));

		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL . '?amp' );# Check regular page
		$I->waitForText( 'test codeception', 15 ); // wait up to 15 seconds
		$I->seeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");
		$I->seeInPageSource('<a class="add_cursor lum_link_no_popup" id="link-0227759" data-modal_window_nonce="');
		$I->seeInPageSource('data-modal_window_people="0227759" data-target="#theModal0227759" title="Open a new window with IMDb informations for Peter Dinklage" href="' . $I->getCustomBaseUrl() . '/lumiere/person/?mid=0227759&amp;');
		$I->seeInPageSource( '&amp;amp">Peter Dinklage</a>');
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_TAXONOMY_URL . '?amp' );# Check taxonomy page
		$I->seeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");
		$I->seeInPageSource(' to stay with his uncle, Martin Perveler. Returning to the Bronx in 1941');		
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_POPUP_FILM_URL . '&amp' );# Check popup movie
		$I->seeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");	
		$I->seeInPageSource('Ellen Burstyn</a>, 
						<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" title="internal link Elyes Gabel" href="' . $I->getCustomBaseUrl() . '/lumiere/person/?mid=1175468');

		// Check NoLinks class
		$I->comment(Helper\Color::set('Check if No Links works', "italic+bold+cyan"));
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#behaviourpart');
		$I->CustomActivateCheckbox('#imdb_imdblinkingkill_yes', '#lumiere_update_main_settings' );
		$I->waitPageLoad();
		
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL ); // Check regular page
		$I->dontSeeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");
		$I->dontSeeInPageSource("lumiere_classic_links-js");		
		$I->dontSeeInPageSource("lumiere_bootstrap_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_options-js");

		$I->seeInPageSource('<div class="lumiere_align_left lumiere_flex_auto">Peter Dinklage
				</div>');
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_TAXONOMY_URL ); // Check taxonomy page
		$I->dontSeeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");
		$I->dontSeeInPageSource("lumiere_classic_links-js");		
		$I->dontSeeInPageSource("lumiere_bootstrap_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('his uncle, Martin Perveler. Returning');

		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_POPUP_FILM_URL ); // Check popup movie
		$I->dontSeeInPageSource("<link rel=\"preconnect\" href=\"https://cdn.ampproject.org\">");	
		$I->dontSeeInPageSource("lumiere_classic_links-js");		
		$I->dontSeeInPageSource("lumiere_bootstrap_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_core-js");
		$I->dontSeeInPageSource("lumiere_highslide_options-js");
		$I->seeInPageSource('Ellen Burstyn</a>, 
						<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" title="internal link Elyes Gabel" href="' . $I->getCustomBaseUrl() . '/lumiere/person/?mid=1175468'); // There are links in no class popups

		// End, Switch back To Highslide, remove kill imdb links
		$I->SwitchModalWindow('Highslide');
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#behaviourpart');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#lumiere_update_main_settings' );

	}

}
