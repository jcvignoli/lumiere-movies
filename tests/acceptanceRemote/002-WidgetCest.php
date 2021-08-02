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
	 *
	 */
	private function login(AcceptanceRemoteTester $I) {
		$I->wantTo('Start an admin session');
		$I->loginAsAdmin();
	}

	/** Check if auto widget option display a widget based on the title of the page
	 *
	 * @before login
	 *
	 */
	public function checkAutoTitleWidget(AcceptanceRemoteTester $I) {
		$I->wantTo('check auto title widget option');

		// Activate Auto Widget
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdblinkingkill');
		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbautopostwidget_yes', '#update_imdbSettings' );
		$I->amOnPage("/2021/y-tu-mama-tambien/");
		$I->see('Y tu mamá también');

		// Disable Auto Widget
		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdblinkingkill');
		/*	Conditional checkbox unactivation (in _support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomDisableCheckbox('#imdb_imdbautopostwidget_yes', '#update_imdbSettings' );
		$I->amOnPage("/2021/y-tu-mama-tambien/");
		$I->dontSee('Y tu mamá también');

	}

}
