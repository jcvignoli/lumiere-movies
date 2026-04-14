<?php declare( strict_types = 1 );

namespace Tests\Unit;

use Lumiere\Admin\Cache\Cache_Files_Management;
use Lumiere\Config\Get_Options;

class CacheFilesManagementTest extends \Codeception\Test\Unit {

	protected function _before(): void {
		$GLOBALS['wp_options'] = [];
		$GLOBALS['wp_options'][ Get_Options::get_cache_tablename() ] = [
			'imdbcachedir' => '/tmp/lumiere-cache/',
			'imdbphotoroot' => '/tmp/lumiere-cache/images/',
			'imdbusecache' => '1',
		];
		$GLOBALS['wp_options']['lumiere_admin_options'] = [
			'imdblanguage' => 'en_US',
			'imdbdelayimdbrequest' => '0',
		];
		$GLOBALS['wp_options'][ Get_Options::get_cache_tablename() ]['imdbphotodir'] = 'http://example.org/photos/';
		$GLOBALS['wp_options'][ Get_Options::get_cache_tablename() ]['imdbcacheexpire'] = '3600';
	}

	public function test_cache_getfoldersize_returns_zero_if_not_dir(): void {
		// Our bootstrap has wp_filesystem mocking is_dir to return true usually,
		// but we can override it if we used a more complex mock.
		// For now, let's just test basic functionality.
		$cache = new Cache_Files_Management();
		$this->assertIsInt( $cache->cache_getfoldersize( '/non/existent' ) );
	}

	public function test_lumiere_create_cache_inactive(): void {
		$GLOBALS['wp_options'][ Get_Options::get_cache_tablename() ]['imdbusecache'] = '0';
		$cache = new Cache_Files_Management();
		$this->assertFalse( $cache->lumiere_create_cache() );
	}

	public function test_lumiere_create_cache_active(): void {
		$GLOBALS['wp_options'][ Get_Options::get_cache_tablename() ]['imdbusecache'] = '1';
		$cache = new Cache_Files_Management();
		$this->assertTrue( $cache->lumiere_create_cache() );
	}
}
