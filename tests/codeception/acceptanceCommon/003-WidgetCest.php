<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class WidgetCest {

	public function _before(AcceptanceRemoteTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceRemoteTester $I){
		$I->comment('#Code _after#');
	}

	/** Login to Wordpress
	 *  Trait function to keep the cookie active
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {

		$I->login_universal($I);

	}

	/** Check if auto widget option display a widget based on the title of the page
	 *
	 * @before login
	 *
	 */
	public function checkAutoTitleWidget(AcceptanceRemoteTester $I) {
		$I->wantTo('check auto title widget option');

		// Activate Auto Widget
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdblinkingkill');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid throwing error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbautopostwidget_yes', '#update_imdbSettings' );
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_AUTOWIDGET_URL );
		$I->see( AcceptanceRemoteSettings::TESTING_PAGE_AUTOWIDGET_TITLE );

		// Disable Auto Widget
		$I->amOnPage( AcceptanceRemoteSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdblinkingkill');
		/*	Conditional checkbox unactivation (in _support/AcceptanceTrait.php)
			Avoid throwing error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomDisableCheckbox('#imdb_imdbautopostwidget_yes', '#update_imdbSettings' );
		$I->amOnPage( AcceptanceRemoteSettings::TESTING_PAGE_AUTOWIDGET_URL );
		$I->dontSee( AcceptanceRemoteSettings::TESTING_PAGE_AUTOWIDGET_TITLE );

	}

}
