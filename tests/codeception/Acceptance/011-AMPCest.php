<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to test AMP (a WebDriver is needed for JS execution)

class AMPCest {

	public function _before(AcceptanceTester $I){
		$I->comment(Helper\Color::set("#Code _before#", "italic+bold+cyan"));
	}

	public function _after(AcceptanceTester $I){
		$I->comment(Helper\Color::set("#Code _after#", "italic+bold+cyan"));
	}

	/**
	 *  Login to Wordpress
	 *  Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/** 
	 * Helper: Select AMP plugin
	 * Make sure that AMP plugin is active
	 *
	 * @before login
	 */
	private function prepareForAmp(AcceptanceTester $I) {
		// Activate AMP
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('amp');
		
		// Make sure actor data is enabled
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#taxodetails');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetactor_yes', '#lumiere_update_data_settings' );
	}

	/** 
	 * Helper: Make sure that Polylang plugin is active
	 * @before login
	 */
	private function prepareForPolylang(AcceptanceTester $I) {

		// Activate Polylang.
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('polylang');

		// Activate taxonomy.
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

		// Activate director data detail.
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetdirector_yes', '#update_imdbwidgetSettings' );

		// Activate director taxonomy.
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_TAXO_URL );
		$I->scrollTo('#imdb_imdbtaxonomycomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomydirector_yes', '#update_imdbwidgetSettings' );
		
		// Make sure Bootstrap is active, the test is run with Bootstrap.
		$I->amOnPage( AcceptanceSettings::LUMIERE_GENERAL_OPTIONS_URL );
		$I->customSelectOption( "select[name=imdbpopup_modal_window]", "Bootstrap", "update_imdbSettings" );
	}
	
	/** 
	 * Check if taxonomy works with AMP
	 *
	 * @before prepareForAmp
	 */
	public function checkIfAMPworks(AcceptanceTester $I) {

		$I->comment( 'Check if AMP page differenciation works' );

		// Check if AMP is functional and remove links -- Splitted up, since the nonce can't be detected
		$I->amOnPage( $I->getCustomBaseUrl() . AcceptanceSettings::TESTING_PAGE_BASE_URL . '?amp' );
		$I->seeInPageSource('<a class="lum_link_no_popup" id="link-0227759" data-modal_window_nonce="');
		$I->seeInPageSource('data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations" href="' . $I->getCustomBaseUrl() . '/lumiere/person/?mid=0227759&amp;');
		$I->seeInPageSource( '&amp;amp">Peter Dinklage</a></div>');

		// Check if without AMP it is functional
		$I->amOnPage( $I->getCustomBaseUrl() . AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->seeInPageSource( '<a class="lum_link_make_popup lum_link_with_people highslide" id="link-0227759" data-modal_window_nonce="');
		$I->seeInPageSource( 'data-modal_window_people="0227759" data-target="#theModal0227759" title="open a new window with IMDb informations">Peter Dinklage</a></div>' );
	}

	/** 
	 * Check if AMP works with Polylang
	 *
	 * @before prepareForPolylang
	 */
	public function checkIfAMPworksWithPolylang(AcceptanceTester $I) {

		$I->comment( 'Check if AMP page works with Polylang' );

		// Check if polylang form in taxonomy page is available
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->click( "Tony Zarindast");
		$I->scrollTo('.imdbelementPIC');
		$I->seeInPageSource('<form method="get" id="lang_form" name="lang_form" action="');
		$I->seeInPageSource('Español');
		$I->seeInPageSource('English');
		$I->seeInPageSource('Français');
	}
}
