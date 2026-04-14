<?php declare( strict_types = 1 );

namespace Tests\Unit;

use Lumiere\Frontend\Taxonomy\Add_Taxonomy;

class AddTaxonomyTest extends \Codeception\Test\Unit {

	protected function _before(): void {
		$GLOBALS['the_ID'] = 123;
		$GLOBALS['taxonomies_exist'] = [ 'lumiere-director' => true ];
		$GLOBALS['terms_exist'] = [];
		$GLOBALS['inserted_terms'] = [];
		$GLOBALS['object_terms'] = [];
		$GLOBALS['terms'] = [];
	}

	public function test_create_taxonomy_options_inserts_term(): void {
		$taxo = new Add_Taxonomy();
		$imdb_admin_values = [ 'imdburlstringtaxo' => 'lumiere-' ];
		
		$result = $taxo->create_taxonomy_options( 'director', 'Stanley Kubrick', $imdb_admin_values );
		
		$this->assertEquals( 'lumiere-director', $result['custom_taxonomy_fullname'] );
		$this->assertEquals( 'Stanley Kubrick', $result['taxonomy_term'] );
		$this->assertContains( 'Stanley Kubrick', $GLOBALS['inserted_terms']['lumiere-director'] );
		$this->assertArrayHasKey( 123, $GLOBALS['object_terms'] );
	}

	public function test_get_taxonomy_url_href(): void {
		$taxo = new Add_Taxonomy();
		$term = new \WP_Term( 1, 'stanley-kubrick' );
		$GLOBALS['terms']['lumiere-director']['Stanley Kubrick'] = $term;
		
		$href = $taxo->get_taxonomy_url_href( 'Stanley Kubrick', 'lumiere-director' );
		
		$this->assertEquals( 'http://example.org/lumiere-director/1', $href );
	}
}
