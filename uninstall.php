<?php

 #############################################################################
 # IMDb Link transformer                                                     #
 # written by Prometheus group                                               #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #       			                                             #
 #  Function : Uninstall completely IMDb LT when deleting the plugin	     #
 #       	  			                                     #
 #############################################################################


if(!defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) 
	exit();

delete_option( 'imdbAdminOptions' ); 
delete_option( 'imdbWidgetOptions' );
delete_option( 'imdbCacheOptions' );

add_action( 'admin_init', 'imdblt_unregister_taxonomy' );

echo "IMDbLT options deleted.";


function imdblt_unregister_taxonomy() {

	$taxonomy_name = 'imdblt_genre';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_genre' ); 
		unregister_taxonomy('imdblt_genre');
	}

	$taxonomy_name = 'imdblt_title';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_title' ); 
		unregister_taxonomy('imdblt_title');
	}

	$taxonomy_name = 'imdblt_keywords';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_keywords' );
		unregister_taxonomy('imdblt_keywords');
	}
 
	$taxonomy_name = 'imdblt_country';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_country' ); 
		unregister_taxonomy('imdblt_country');
	}

	$taxonomy_name = 'imdblt_language';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_language' ); 
		unregister_taxonomy('imdblt_language');
	}

	$taxonomy_name = 'imdblt_composer';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_composer' ); 
		unregister_taxonomy('imdblt_composer');
	}

	$taxonomy_name = 'imdblt_color';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_color' ); 
		unregister_taxonomy('imdblt_color');
	}

	$taxonomy_name = 'imdblt_director';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_director' ); 
		unregister_taxonomy('imdblt_director');
	}

	$taxonomy_name = 'imdblt_creator';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_creator' ); 
		unregister_taxonomy('imdblt_creator');
	}

	$taxonomy_name = 'imdblt_producer';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_producer' ); 
		unregister_taxonomy('imdblt_producer');
	}

	$taxonomy_name = 'imdblt_actor';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_actor' ); 
		unregister_taxonomy('imdblt_actor');
	}

	$taxonomy_name = 'imdblt_writer';
	$terms = get_terms( array(
		'taxonomy' => $taxonomy_name,
		'hide_empty' => false
	) );
	//$terms = get_terms( 'genre'); ##delete all terms added for genre
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'imdblt_writer' ); 
		unregister_taxonomy('imdblt_writer');
	}
}

?>
