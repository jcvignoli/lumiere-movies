<?php declare( strict_types = 1 );

namespace Tests\Unit;

use Lumiere\Config\Settings_Service;

class SettingsServiceTest extends \Codeception\Test\Unit {

	protected function _before(): void {
		$GLOBALS['wp_options'] = [];
	}

	public function test_constructor_refreshes_options(): void {
		$admin_options = [ 'imdburlstringtaxo' => 'custom-' ];
		$GLOBALS['wp_options']['lumiere_admin_options'] = $admin_options;

		$service = new Settings_Service();
		$this->assertEquals( $admin_options, $service->get_admin_options() );
	}

	public function test_get_admin_option_returns_default(): void {
		$service = new Settings_Service();
		$this->assertEquals( 'default_val', $service->get_admin_option( 'non_existent', 'default_val' ) );
	}

	public function test_get_admin_option_returns_value(): void {
		$GLOBALS['wp_options']['lumiere_admin_options'] = [ 'key' => 'value' ];
		$service = new Settings_Service();
		$this->assertEquals( 'value', $service->get_admin_option( 'key' ) );
	}

	public function test_is_installed_returns_false_when_empty(): void {
		$service = new Settings_Service();
		$this->assertFalse( $service->is_installed() );
	}

	public function test_is_installed_returns_true_when_not_empty(): void {
		$GLOBALS['wp_options']['lumiere_admin_options'] = [ 'installed' => '1' ];
		$service = new Settings_Service();
		$this->assertTrue( $service->is_installed() );
	}

	public function test_update_admin_options_updates_db_and_local(): void {
		$service = new Settings_Service();
		$new_options = [ 'new_key' => 'new_value' ];
		
		$service->update_admin_options( $new_options );
		
		$this->assertEquals( $new_options, $service->get_admin_options() );
		$this->assertEquals( $new_options, $GLOBALS['wp_options']['lumiere_admin_options'] );
	}
	
	public function test_get_movie_options(): void {
		$movie_options = [ 'imdbwidgetdirector' => '1' ];
		$GLOBALS['wp_options']['lumiere_data_options'] = $movie_options;
		
		$service = new Settings_Service();
		$this->assertEquals( $movie_options, $service->get_movie_options() );
		$this->assertEquals( '1', $service->get_movie_option('imdbwidgetdirector') );
	}
}
