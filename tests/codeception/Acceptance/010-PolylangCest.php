<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class PolylangCest {

	public function _before( AcceptanceTester $I ){
		$I->comment(Helper\Color::set("#Code _before#", "italic+bold+cyan"));
	}

	public function _after( AcceptanceTester $I ){
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
	 * Helper: Select Highslide
	 * Make sure that Highslide modal window is selected
	 */
	private function highslide(AcceptanceTester $I) {

		// Make sure Highslide is active, following tests are run with Highslide
		$I->amOnPage( AcceptanceSettings::LUMIERE_MAIN_OPTIONS_URL );
		$I->customSelectOption( "select[name=imdbpopup_modal_window]", "Highslide", "update_imdbSettings" );
	}

	/** 
	 * Check if taxonomy works with Polylang
	 *
	 * @before login
	 * @before highslide
	 */
	public function checkTaxonomyActivationWorksWithPolylang( AcceptanceTester $I ) {

		$I->wantTo(Helper\Color::set('Check if taxonomy works with Polylang', "italic+bold+cyan"));

		// Activate Polylang
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('polylang');

		// Activate taxonomy
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

		// Activate director data detail
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_WHATDISPLAY_URL );
		$I->scrollTo('#imdb_imdbwidgetcomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbwidgetdirector_yes', '#update_imdbwidgetSettings' );

		// Activate director taxonomy
		$I->amOnPage( AcceptanceSettings::LUMIERE_DATA_OPTIONS_TAXO_URL );
		$I->scrollTo('#imdb_imdbtaxonomycomposer_yes');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomydirector_yes', '#update_imdbwidgetSettings' );

		// Check if polylang options are available
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->scrollTo( 'span[id=title_Werewolf1995]' );
		$I->wait(1);
		$I->click( "Tony Zarindast");

		$I->seeInPageSource('<form method="get" id="lang_form" name="lang_form" action="#lang_form">');
		$I->seeInPageSource('<option value="es">Español</option>');
		$I->seeInPageSource('<option value="en">English</option>');
		$I->seeInPageSource('<option value="fr">Français</option>');

		// Deactivate Polylang plugin 
		$I->amOnPluginsPage();
		$I->maybeDeactivatePlugin('polylang');

		// Check if polylang options are available
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->scrollTo( 'span[id=title_Werewolf1995]' );
		$I->wait(1);
		$I->click( "Tony Zarindast");

		$I->dontSeeInPageSource('<form method="get" id="lang_form" name="lang_form" action="#lang_form">');
		$I->dontSeeInPageSource('<option value="es">Español</option>');
		$I->dontSeeInPageSource('<option value="fr">Français</option>');
		$I->dontSeeInPageSource('<option value="en">English</option>');

		// Reactivate Polylang
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('polylang');
	}

	/** 
	 * Check if custom Polylang taxonomies are forced and activated
	 * This is a mandatory requirement to get all taxo to create taxonomy pages
	 *
	 * @before login
	 */
	public function checkCustomTaxoPolylangForcedActivated( AcceptanceTester $I ) {
		$I->maybeActivatePlugin('polylang');
		$I->amOnAdminPage('/admin.php?page=mlang_settings');
		$I->seeInPageSource('<input name="taxonomies[lumiere-director]" type="checkbox" value="1" checked="checked" disabled="disabled">' );
		$I->seeInPageSource('<input name="taxonomies[lumiere-genre]" type="checkbox" value="1" checked="checked" disabled="disabled">' );
	}
}

