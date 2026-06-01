<?php declare( strict_types = 1 );

namespace Tests\Unit;

use Lumiere\Frontend\Module\Parent_Module;
use Lumiere\Config\Settings_Service;
use Lumiere\Frontend\Link_Maker\No_Links;
use Lumiere\Config\Get_Options;

class ParentModuleNonceTest extends \Codeception\Test\Unit {

	protected function _before(): void {
		$GLOBALS['last_nonce_action'] = null;
		$GLOBALS['wp_options'][Get_Options::get_admin_tablename()] = [
			'imdburlpopups' => 'lumiere-popup/',
		];
		$GLOBALS['wp_options']['permalink_structure'] = '/%postname%/';
	}

	public function test_get_person_url_uses_correct_nonce_action(): void {
		$settings = new Settings_Service();
		$link_maker = new No_Links();
		$module = new class( $settings, $link_maker ) extends Parent_Module {
			public function call_get_person_url( $id, $name ) {
				return $this->get_person_url( $id, $name );
			}
		};

		$module->call_get_person_url( 'nm0000110', 'Stanley Kubrick' );

		$this->assertEquals( 'popup_nonce', $GLOBALS['last_nonce_action'] );
	}

	public function test_get_film_url_uses_correct_nonce_action(): void {
		$settings = new Settings_Service();
		$link_maker = new No_Links();
		$module = new class( $settings, $link_maker ) extends Parent_Module {
			public function call_get_film_url( $id, $title ) {
				return $this->get_film_url( $id, $title );
			}
		};

		$module->call_get_film_url( 'tt0062866', '2001: A Space Odyssey' );

		$this->assertEquals( 'popup_nonce', $GLOBALS['last_nonce_action'] );
	}
}
