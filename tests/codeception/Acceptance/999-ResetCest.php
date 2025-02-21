<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

# Class meant to reinitialize the settings (a WebDriver is needed for JS execution)

class EndCest {

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
	 * Enable defaults Plugins
	 *
	 * @before login
	 */
	public function enablePlugins(AcceptanceTester $I) {

		$I->comment('Reset plugins to their normal state');

		// Lumière on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('lumiere-movies');

		// Polylang on
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('polylang');
		
		// Classic widgets off
		$I->amOnPluginsPage();
		$I->maybeDeactivatePlugin('classic-widgets');
		
		// Classic editor off
		$I->amOnPluginsPage();
		$I->maybeDeactivatePlugin('classic-editor');
	}


	/**
	 * Enable Admin option
	 *
	 * @before login
	 */
	public function enableAdminMainOptions(AcceptanceTester $I) {

		$I->comment('Reset Admin Main Options to their normal state');

		// Big menu on
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#miscpart');
		$I->CustomActivateCheckbox('#imdb_imdbwordpress_bigmenu_yes', 'lumiere_update_main_settings');

		// Left menu on
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_bigmenu');
		$I->CustomActivateCheckbox('#imdb_imdbwordpress_tooladminmenu_yes', 'lumiere_update_main_settings');

		// Taxonomy on
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbwordpress_tooladminmenu');
		$I->CustomActivateCheckbox('#imdb_imdbtaxonomy_yes', '#lumiere_update_main_settings' );

		// Remove all links off
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbtaxonomy');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#lumiere_update_main_settings' );

		// Auto title widget off
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdblinkingkill');
		$I->CustomDisableCheckbox('#imdb_imdbautopostwidget_yes', '#lumiere_update_main_settings' );

		// Keep settings on
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbkeepsettings_yes', 'lumiere_update_main_settings');

		// Debug on
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbkeepsettings');
		$I->CustomActivateCheckbox('#imdb_imdbdebug_yes', '#lumiere_update_main_settings');

		// Display one screen on
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomActivateCheckbox('#imdb_imdbdebugscreen_yes', '#lumiere_update_main_settings');

		// Save log off
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#imdbautopostwidget');
		$I->CustomDisableCheckbox('#imdb_imdbdebuglog_yes', '#lumiere_update_main_settings');

		// Switch back To Highslide
		$I->SwitchModalWindow('Bootstrap');

		// Disable No Links
		$I->amOnPage( AcceptanceSettings::LUMIERE_ADVANCED_OPTIONS_URL );
		$I->scrollTo('#miscpart');
		$I->CustomDisableCheckbox('#imdb_imdblinkingkill_yes', '#lumiere_update_main_settings' );
		
		// Disable cron keep size under cache
		$I->amOnPage( AcceptanceSettings::LUMIERE_CACHE_OPTIONS_URL );
		$I->scrollTo('#imdb_imdbcachekeepsizeunder_id');
		$I->CustomDisableCheckbox('#imdb_imdbcachekeepsizeunder_yes', '#lumiere_update_cache_settings' );
		
		// Disable cron auto refresh cache
		$I->amOnPage( AcceptanceSettings::LUMIERE_CACHE_OPTIONS_URL );
		$I->scrollTo('#imdb_imdbcachekeepsizeunder_id');
		$I->CustomDisableCheckbox('#imdb_imdbcacheautorefreshcron_yes', '#lumiere_update_cache_settings' );
	}
	
	/**
	 * Remove auto title widget exclusion in a post
	 *
	 * @before login
	 */
	public function removeAutotitlewidgetPostExclusion(AcceptanceTester $I) {
		$I->amOnPage( ADMIN_POST_AUTOTITLEWIDGET_ID );
		$I->CustomDisableCheckbox('#lumiere_autotitlewidget_perpost', 'input[id=publish]' );
	}
	
	/**
	 * Copy cached save to cache directory
	 */
	public function copyFullCache(AcceptanceTester $I) {
		$I->cleanDir( $I->getCustomBasePath() .  '/wp-content/cache/lumiere');
		$I->copyDir( $I->getCustomBasePath() . '/wp-content/cache/lumiere_save', $I->getCustomBasePath() . '/wp-content/cache/lumiere/' );
	}
}
