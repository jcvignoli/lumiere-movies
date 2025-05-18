<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

/**
 * Class meant to test third party plugins
 */
class IrpCest {

	public function _before(AcceptanceTester $I){
		$I->comment('#Code _before#');
	}

	public function _after(AcceptanceTester $I){
		$I->comment('#Code _after#');
	}

	/**
	 * Login to Wordpress
	 * Trait function to keep the cookie active
	 */
	private function login(AcceptanceTester $I) {
		$I->login_universal($I);
	}

	/**
	 * Check the integration with Intelly Related Post
	 * @before login
	 */
	public function checkIRPworks(AcceptanceTester $I){
		// Activate IRP
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('intelly-related-posts');

		// Enable Always Display IRP in posts Lumière
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#otherpluginspart');
		/*	Conditional checkbox unactivation (in Support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is activated, uncheck it and then click $submit (form) */
		$I->CustomActivateCheckbox('input[id="imdb_imdbirpdisplays_yes"]', '#lumiere_update_main_settings' );
		$I->waitPageLoad();
		
		// Enable Always Display IRP in posts
		$I->amOnPage( 'wp-admin/options-general.php?page=intelly-related-posts' );
		/*	Conditional checkbox unactivation (in Support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is activated, uncheck it and then click $submit (form) */
		$I->waitPageLoad();
		
		/** Another element may be shown, couldn't catch it, use this if it happens again (wrong values for now) */
		if ($I->tryToSeeElement('.alert')) {
		    $I->waitForText('Do you accept cookies?');
		    $I->click('Yes');
		}

		$I->CustomActivateCheckbox('input[name="irpActive"]', '/html/body/div[1]/div[2]/div[2]/div[1]/div[2]/form/input[4]' );
		$I->waitPageLoad();
		
		// Check if IRP can be seen in posts
		$I->amOnPage( AcceptanceSettings::TESTING_PAGE_BASE_URL_FR ); // In English TESTING_PAGE_BASE_URL, IRP doesn't show up
		$I->seeInPageSource( '<!-- INLINE RELATED POSTS' );

		// Disable Always Display IRP in posts Lumière
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#otherpluginspart');
		/*	Conditional checkbox unactivation (in Support/AcceptanceTrait.php)
			Avoid to throw error if untrue, normal behaviour of codeception 
			If $element is activated, uncheck it and then click $submit (form) */
		$I->CustomDisableCheckbox('#imdb_imdbirpdisplays_yes', '#lumiere_update_main_settings' );
		
		// Check if IRP is not seen in posts
		$I->amOnPage( $I->getCustomBaseUrl() . AcceptanceSettings::TESTING_PAGE_BASE_URL );
		$I->dontSeeInPageSource( '<!-- INLINE RELATED POSTS' );
	}
}
