<?php declare( strict_types = 1 );

namespace Tests\Unit;

use Lumiere\Admin\Crons\Cron;
use Lumiere\Config\Get_Options;

class CronTest extends \Codeception\Test\Unit {

	protected function _before(): void {
		$GLOBALS['wp_options'] = [];
		$GLOBALS['wp_scheduled'] = [];
		$GLOBALS['wp_transients'] = [];
		
		$GLOBALS['wp_options'][ Get_Options::get_cache_tablename() ] = [
			'imdbcachekeepsizeunder' => '0',
			'imdbcachekeepsizeunder_sizelimit' => '100',
			'imdbcacheautorefreshcron' => '0',
		];
	}

	public function test_add_custom_job_recurrence(): void {
		$cron = new Cron();
		$schedules = $cron->add_custom_job_recurrence( [] );
		$this->assertArrayHasKey( 'everytwoweeks', $schedules );
	}

	public function test_cron_add_delete_oversize_adds_cron(): void {
		$GLOBALS['wp_options'][ Get_Options::get_cache_tablename() ]['imdbcachekeepsizeunder'] = '1';
		$cron = new Cron();
		
		$cron->cron_add_delete_oversize();
		$this->assertArrayHasKey( 'lumiere_cron_deletecacheoversized', $GLOBALS['wp_scheduled'] );
	}

	public function test_cron_add_delete_oversize_removes_cron(): void {
		$GLOBALS['wp_scheduled']['lumiere_cron_deletecacheoversized'] = time();
		$GLOBALS['wp_options'][ Get_Options::get_cache_tablename() ]['imdbcachekeepsizeunder'] = '0';
		$cron = new Cron();
		
		$cron->cron_add_delete_oversize();
		$this->assertArrayNotHasKey( 'lumiere_cron_deletecacheoversized', $GLOBALS['wp_scheduled'] );
	}

	public function test_cron_add_delete_cache_adds_cron(): void {
		$GLOBALS['wp_options'][ Get_Options::get_cache_tablename() ]['imdbcacheautorefreshcron'] = '1';
		$cron = new Cron();
		
		$cron->cron_add_delete_cache();
		$this->assertArrayHasKey( 'lumiere_cron_autofreshcache', $GLOBALS['wp_scheduled'] );
	}

	public function test_add_remove_crons_cache_triggers_on_transient(): void {
		$GLOBALS['wp_transients']['cron_settings_imdbcachekeepsizeunder_updated'] = 'imdbcachekeepsizeunder';
		$GLOBALS['wp_options'][ Get_Options::get_cache_tablename() ]['imdbcachekeepsizeunder'] = '1';
		
		$cron = new Cron();
		$cron->add_remove_crons_cache();
		
		$this->assertArrayHasKey( 'lumiere_cron_deletecacheoversized', $GLOBALS['wp_scheduled'] );
		$this->assertArrayNotHasKey( 'cron_settings_imdbcachekeepsizeunder_updated', $GLOBALS['wp_transients'] );
	}
}
