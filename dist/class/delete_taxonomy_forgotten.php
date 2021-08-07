<?php
// Since version 3.5, options have been streamlined, which lead to a change in taxonomy categories (available in "posts"
// Those should be deleted

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
}


// List of potentials terms having created a taxonomy category
$category_list = array('keywords', 'goofs', 'colors');
// Delete unused terms


foreach ($category_list as $category_item){

	$taxonomy_category = $category_item;
	$taxonomy_category_full = $configClass->imdb_admin_values['imdburlstringtaxo'] . $taxonomy_category;

	$terms = get_term_by('taxonomy', $taxonomy_term, $taxonomy_category_full );

print_r($terms);

}

/* delete 
foreach ($category_list as $category_item){

	$taxonomy_term = esc_attr( 'Stanley Kubrick );
	$taxonomy_category = esc_attr($category_item);
	$taxonomy_category_full = $this->configClass->imdb_admin_values['imdburlstringtaxo'] . $taxonomy_category;


	if ( $term_already = get_term_by('name', $taxonomy_term, $taxonomy_category_full ) )
	wp_delete_term( $term_already->term_id, $taxonomy_category_full);

}

*/
