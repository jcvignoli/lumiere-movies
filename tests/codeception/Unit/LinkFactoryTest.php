<?php declare( strict_types = 1 );

namespace Tests\Unit;

use Lumiere\Frontend\Link_Maker\Link_Factory;
use Lumiere\Config\Settings_Service;
use Lumiere\Frontend\Link_Maker\AMP_Links;
use Lumiere\Frontend\Link_Maker\No_Links;
use Lumiere\Frontend\Link_Maker\Bootstrap_Links;
use Lumiere\Frontend\Link_Maker\Highslide_Links;
use Lumiere\Frontend\Link_Maker\Classic_Links;
use Exception;

class LinkFactoryTest extends \Codeception\Test\Unit {

	protected function _before(): void {
		$GLOBALS['wp_options'] = [];
		$GLOBALS['did_actions'] = [];
		$GLOBALS['is_admin'] = false;
		$GLOBALS['amp_is_request'] = false;
		$_SERVER['REQUEST_URI'] = '/';
		$_GET = [];
	}

	public function test_select_link_maker_returns_amp_links_on_amp_page(): void {
		$GLOBALS['amp_is_request'] = true;
		$GLOBALS['did_actions']['wp'] = 1;
		
		$settings = new Settings_Service();
		$factory = new Link_Factory( $settings );
		
		$link_maker = $factory->select_link_maker();
		$this->assertInstanceOf( AMP_Links::class, $link_maker );
	}

	public function test_select_link_maker_returns_no_links_when_disabled(): void {
		$GLOBALS['wp_options']['lumiere_admin_options'] = [ 'imdblinkingkill' => '1' ];
		
		$settings = new Settings_Service();
		$factory = new Link_Factory( $settings );
		
		$link_maker = $factory->select_link_maker();
		$this->assertInstanceOf( No_Links::class, $link_maker );
	}

	public function test_select_link_maker_returns_bootstrap_links(): void {
		$GLOBALS['wp_options']['lumiere_admin_options'] = [ 
			'imdblinkingkill' => '0',
			'imdbpopup_modal_window' => 'bootstrap'
		];
		
		$settings = new Settings_Service();
		$factory = new Link_Factory( $settings );
		
		$link_maker = $factory->select_link_maker();
		$this->assertInstanceOf( Bootstrap_Links::class, $link_maker );
	}

	public function test_select_link_maker_returns_highslide_links(): void {
		$GLOBALS['wp_options']['lumiere_admin_options'] = [ 
			'imdblinkingkill' => '0',
			'imdbpopup_modal_window' => 'highslide'
		];
		
		$settings = new Settings_Service();
		$factory = new Link_Factory( $settings );
		
		$link_maker = $factory->select_link_maker();
		$this->assertInstanceOf( Highslide_Links::class, $link_maker );
	}

	public function test_select_link_maker_returns_classic_links(): void {
		$GLOBALS['wp_options']['lumiere_admin_options'] = [ 
			'imdblinkingkill' => '0',
			'imdbpopup_modal_window' => 'classic'
		];
		
		$settings = new Settings_Service();
		$factory = new Link_Factory( $settings );
		
		$link_maker = $factory->select_link_maker();
		$this->assertInstanceOf( Classic_Links::class, $link_maker );
	}

	public function test_select_link_maker_throws_exception_on_invalid_setting(): void {
		$GLOBALS['wp_options']['lumiere_admin_options'] = [ 
			'imdblinkingkill' => '0',
			'imdbpopup_modal_window' => 'invalid'
		];
		
		$settings = new Settings_Service();
		$factory = new Link_Factory( $settings );
		
		$this->tester->expectThrowable( Exception::class, function() use ($factory) {
			$factory->select_link_maker();
		});
	}
}
