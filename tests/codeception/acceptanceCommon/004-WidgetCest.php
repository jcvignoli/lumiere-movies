<?php

// Class meant to test the widget functions (a WebDriver is needed for JS execution)

class WidgetCest {

	public function _before(AcceptanceRemoteTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceRemoteTester $I){
		$I->comment('#Code _after#');
	}

	/**
	 * Login to Wordpress
	 *  Trait function to keep the cookie active
	 */
	private function login(AcceptanceRemoteTester $I) {

		$I->login_universal($I);

	}

	/** Check if auto title widget option display a widget based on the title of the page
	 *
	 * @before login
	 */
	public function checkAutoTitleWidget( AcceptanceRemoteTester $I ) {
		$I->wantTo('check auto title widget option');

		// Activate Auto Title Widget
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdblinkingkill');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid throwing error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbautopostwidget_yes', '#lumiere_update_general_settings' );
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_AUTOTITLEWIDGET_URL );
		$I->seeInSource( AcceptanceRemoteSettings::TESTING_PAGE_AUTOTITLEWIDGET_TITLE );

		// Disable Auto Title Widget
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdblinkingkill');
		/*	Conditional checkbox unactivation (in _support/AcceptanceTrait.php)
			Avoid throwing error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomDisableCheckbox('#imdb_imdbautopostwidget_yes', '#lumiere_update_general_settings' );
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_AUTOTITLEWIDGET_URL );
		$I->dontSeeInSource( AcceptanceRemoteSettings::TESTING_PAGE_AUTOTITLEWIDGET_TITLE );

	}

	/**
	 * Check if removing auto title widget from a post works
	 *
	 * @before login
	 */
	public function autotitlewidgetPostExclusion( AcceptanceRemoteTester $I ) {

		$I->wantTo('Test auto title widget exclusion option');

		// Activate classic editor so we can easily access to options.
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('classic-editor');

		// Activate Auto Title Widget
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdblinkingkill');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid throwing error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbautopostwidget_yes', '#lumiere_update_general_settings' );
			
		// Set auto title widget exclusion in a post and verify if the post doesn't contain it.
		$I->amOnPage( ADMIN_POST_AUTOTITLEWIDGET_ID /* in _bootstrap */ );
		$I->CustomActivateCheckbox('#lumiere_autotitlewidget_perpost', 'input[id=publish]' );
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_AUTOTITLEWIDGET_URL );
		$I->dontSeeInSource( 'Alfonso Cuarón' );

		// Remove auto title widget exclusion in a post and verify if the post doesn't contain it.
		$I->amOnPage( ADMIN_POST_AUTOTITLEWIDGET_ID /* in _bootstrap */ );
		/*	Conditional checkbox unactivation (in _support/AcceptanceTrait.php)
			Avoid throwing error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomDisableCheckbox('#lumiere_autotitlewidget_perpost', 'input[id=publish]' );
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_AUTOTITLEWIDGET_URL );
		$I->scrollTo( ".lum_results_section_subtitle" );
		$I->SeeInSource( 'Alfonso Cuarón' );
	}
	
	/**
	 * Check if removing auto title widget from a post works
	 *
	 * @before login
	 */
	public function classicWidget( AcceptanceRemoteTester $I ) {

		$I->wantTo('Test the styles if classic widget is in use');

		// Activate classic widgets
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('classic-widgets');
		$I->amOnPage( '/wp-admin/widgets.php' );
		$I->dontSeeInSource( 'lumiere-widget-editor-style-css' );
		$I->dontSeeInSource( 'lumiere-widget-editor-script-js' );
		$I->seeInSource( 'lumiere_css_admin-css' );
		$I->seeInSource( 'lum_legacy_widget_label' );
		
		// Dectivate classic widgets
		$I->amOnPluginsPage();
		$I->maybeDeactivatePlugin('classic-widgets');
		$I->amOnPage( '/wp-admin/widgets.php' );
		$I->seeInSource( 'lumiere-widget-editor-style-css' );
		$I->seeInSource( 'lumiere-widget-editor-script-js' );
		$I->seeInSource( 'lumiere_css_admin-css' );
		$I->dontSeeInSource( 'lum_legacy_widget_label' );
	}
	
	/**
	 * Revert back what was changed
	 *
	 * @before login
	 */
	public function cleanTools( AcceptanceRemoteTester $I ) {
		$I->amOnPluginsPage();
		$I->maybeDeactivatePlugin('classic-widgets');
	}
}
