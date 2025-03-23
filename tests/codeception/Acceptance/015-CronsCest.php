<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support\Helper\AcceptanceSettings;

/**
 * Class meant to test wordpress install (a WebDriver is needed for JS execution)
 */
class CronsCest {

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
	 * Run needed actions BEFORE starting the class
	 * @before login
	 */
	public function startingCest(AcceptanceTester $I){
		// Wp crontrol allows to check if a cron is installed
		$I->amOnPluginsPage();
		$I->maybeActivatePlugin('wp-crontrol');
	}

	/**
	 * Check if crons are correctly set up
	 *
	 * @before login
	 */
	public function cronsExist(AcceptanceTester $I) {

		$I->comment('Check if Lumière plugin set up crons');
		
		// Activates crons
		$I->amOnPage( AcceptanceSettings::LUMIERE_CACHE_OPTIONS_URL );
		$I->scrollTo('#imdb_imdbcachekeepsizeunder_yes');
		$I->CustomActivateCheckbox('#imdb_imdbcachekeepsizeunder_yes', '#lumiere_update_cache_settings' );
		$I->amOnPage( AcceptanceSettings::LUMIERE_CACHE_OPTIONS_URL );
		$I->scrollTo('#imdb_imdbcacheautorefreshcron_yes');
		$I->CustomActivateCheckbox('#imdb_imdbcacheautorefreshcron_yes', '#lumiere_update_cache_settings' );
		
		$I->amOnPage( AcceptanceSettings::ADMIN_POST_CRON_MANAGE );
		$I->wait(2);
		$I->seeInSource('lumiere_cron_deletecacheoversized');
		$I->seeInSource('lumiere_cron_autofreshcache');
		
		// Deactivate crons
		$I->amOnPage( AcceptanceSettings::LUMIERE_CACHE_OPTIONS_URL );
		$I->scrollTo('#imdb_imdbcachekeepsizeunder_yes');
		$I->CustomDisableCheckbox('#imdb_imdbcachekeepsizeunder_yes', '#lumiere_update_cache_settings' );
		$I->scrollTo('#imdb_imdbcacheautorefreshcron_yes');
		$I->CustomDisableCheckbox('#imdb_imdbcacheautorefreshcron_yes', '#lumiere_update_cache_settings' );	
		
		$I->amOnPage( AcceptanceSettings::ADMIN_POST_CRON_MANAGE );
		$I->wait(2);
		$I->dontSeeInSource('lumiere_cron_deletecacheoversized');
		$I->dontSeeInSource('lumiere_cron_autofreshcache');
	}
}
