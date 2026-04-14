<?php declare( strict_types = 1 );

namespace Tests\Unit;

use Lumiere\Frontend\Widget\Widget_Frontpage;
use Lumiere\Config\Settings_Service;

class WidgetFrontpageTest extends \Codeception\Test\Unit {

	protected function _before(): void {
		$GLOBALS['wp_options'] = [];
		$GLOBALS['is_home'] = false;
		$GLOBALS['is_front_page'] = false;
		$GLOBALS['is_404'] = false;
		$GLOBALS['is_attachment'] = false;
		$GLOBALS['is_archive'] = false;
		$GLOBALS['is_author'] = false;
		$GLOBALS['the_ID'] = 123;
		$GLOBALS['post_meta'] = [];
		$GLOBALS['post_titles'] = [ 123 => 'Test Movie' ];
		$GLOBALS['active_widgets'] = [];
		$GLOBALS['wp_options']['widget_block'] = [];
	}

	public function test_lum_get_widget_returns_empty_in_forbidden_areas(): void {
		$GLOBALS['is_home'] = true;
		$widget = new Widget_Frontpage();
		$this->assertEquals( '', $widget->lum_get_widget( 'Title' ) );
	}

	public function test_lum_get_widget_returns_empty_when_no_data_found(): void {
		$GLOBALS['wp_options']['lumiere_admin_options'] = [ 'imdbautopostwidget' => '0' ];
		$widget = new Widget_Frontpage();
		$this->assertEquals( '', $widget->lum_get_widget( 'Title' ) );
	}

	public function test_lum_get_widget_with_auto_title(): void {
		$GLOBALS['wp_options']['lumiere_admin_options'] = [ 'imdbautopostwidget' => '1' ];
		
		$widget = new Widget_Frontpage();
		
		// In bootstrap.php:
		// apply_filters('lum_display_movies_box', ...) will return $value.
		// $value will be $get_array_imdbid.
		// $get_array_imdbid will be $values from 'lum_find_movie_id'.
		// $values will be [ 'byname' => 'Test Movie' ].
		// So $output will be a non-empty string if we make sure $get_array_imdbid is not false.
		
		$result = $widget->lum_get_widget( 'My Widget' );
		
		$this->assertStringContainsString( 'My Widget', $result );
	}
}
