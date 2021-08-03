<?php

# Class meant to test remote wordpress install (a WebDriver is needed for JS execution)

class EndCest {

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
		AcceptanceTrait::login_universal($I);
	}

	/** Enable debug function
	 *
	 * @before login
	 */
	public function enableDebug(AcceptanceRemoteTester $I) {

		$I->wantTo('Enable debug (normal state)');

		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbautopostwidget');

		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid throwing error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbdebug_yes', '#update_imdbSettings');

	}

	/** Enable Taxonomy
	 *
	 * @before login
	 *
	 */
	public function enableTaxonomy(AcceptanceRemoteTester $I) {

		$I->wantTo('Enable taxonomy (normal state)');

		$I->amOnPage("/wp-admin/admin.php?page=lumiere_options&generaloption=advanced");
		$I->scrollTo('#imdbwordpress_tooladminmenu');

		/*	Conditional checkbox activation (in _support/AcceptanceTrait.php)
			Avoid throwing error if untrue, normal behaviour of codeception 
			If $element is disabled, check it and then click $submit (form) */
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#update_imdbSettings' );

	}

	/** Enable Classic editor Plugin
	 *
	 * @before login
	 *
	 */
	public function enableClassEditor(AcceptanceRemoteTester $I) {

		$I->wantTo('Enable taxonomy (normal state)');

		$I->amOnPluginsPage();

		/*	Conditional plugin activation (in _support/AcceptanceTrait.php)
			Avoid throwing error if untrue, normal behaviour of codeception 
			If $plugin is disabled, activate it */
		$I->CustomActivatePlugin('classic-editor');

	}

}


