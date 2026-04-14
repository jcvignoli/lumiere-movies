<?php declare( strict_types = 1 );

namespace Tests\Unit;

use Lumiere\Config\Get_Options_Movie;

class GetOptionsMovieTest extends \Codeception\Test\Unit {

	public function test_get_all_fields(): void {
		$fields = Get_Options_Movie::get_all_fields();
		$this->assertIsArray( $fields );
		$this->assertArrayHasKey( 'director', $fields );
		$this->assertArrayHasKey( 'actor', $fields );
	}

	public function test_get_list_items_taxo(): void {
		$fields = Get_Options_Movie::get_list_items_taxo();
		$this->assertIsArray( $fields );
		$this->assertArrayHasKey( 'genre', $fields );
	}

	public function test_get_items_with_numbers(): void {
		$fields = Get_Options_Movie::get_items_with_numbers();
		$this->assertIsArray( $fields );
		// According to phpstan-return in Get_Options_Movie, 'actor' is one of them
		$this->assertArrayHasKey( 'actor', $fields );
	}
}
