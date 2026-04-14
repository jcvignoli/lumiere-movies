<?php declare( strict_types = 1 );

namespace Tests\Unit;

use Lumiere\Config\Get_Options_Person;

class GetOptionsPersonTest extends \Codeception\Test\Unit {

	public function test_get_all_person_fields(): void {
		$fields = Get_Options_Person::get_all_person_fields();
		$this->assertIsArray( $fields );
	}

	public function test_get_all_credit_role(): void {
		$roles = Get_Options_Person::get_all_credit_role();
		$this->assertIsArray( $roles );
	}
}
