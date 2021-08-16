<?php

/*  Move automatically the Lumière! template for taxonomy (theme/taxonomy-imdblt_standard.php) 
 *  to the current user template folder
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
}

// Start Lumière config class and get the vars
if (class_exists("\Lumiere\Settings")) {

	$configClass = new \Lumiere\Settings();
	$imdb_admin_values = $configClass->imdb_admin_values;
	$imdb_widget_values = $configClass->imdb_widget_values;

	// List of potential types for a person
	$array_people = $configClass->array_people; # array

	// List of potential types for an item
	$array_items = $configClass->array_items; # array
}

$lumiere_taxo_title = esc_html( $_GET['taxotype'] );
$lumiere_taxo_file_tocopy = in_array($lumiere_taxo_title, $array_people, true) ? $lumiere_taxo_file_tocopy = \Lumiere\Settings::TAXO_PEOPLE_THEME : $lumiere_taxo_file_tocopy = \Lumiere\Settings::TAXO_ITEMS_THEME;
$lumiere_taxo_file_copied = "taxonomy-" . $imdb_admin_values['imdburlstringtaxo'] . $lumiere_taxo_title . ".php";
$lumiere_current_theme_path = get_stylesheet_directory()."/";
$lumiere_current_theme_path_file = $lumiere_current_theme_path . $lumiere_taxo_file_copied ;
$lumiere_taxonomy_theme_path = $imdb_admin_values['imdbpluginpath'] . "theme/";
$lumiere_taxonomy_theme_file = $lumiere_taxonomy_theme_path . $lumiere_taxo_file_tocopy;

// Taxonomy is activated in the panel, and $_GET["taxotype"] exists as a $imdb_widget_values, and there is a referer
if ( (isset($imdb_admin_values['imdbtaxonomy'])) && (!empty($imdb_admin_values['imdbtaxonomy'])) && (isset($imdb_widget_values[ 'imdbtaxonomy'.$lumiere_taxo_title ])) && (!empty($imdb_widget_values[ 'imdbtaxonomy'.$lumiere_taxo_title ])) ){

	// $_GET["taxotype"] found, var exists, is not empty 
	if ( (isset($lumiere_taxo_title)) && (!empty($lumiere_taxo_title)) ) {

		// Copy failed
		if (!copy($lumiere_taxonomy_theme_file, $lumiere_current_theme_path_file) ) {
			wp_safe_redirect( add_query_arg( "msg", "taxotemplatecopy_failed", wp_get_referer() ) ); 
			exit();
		// Copy successful
		} else {
			// Edit the text according to the $_GET["taxotype"] passed
			$content = file_get_contents($lumiere_current_theme_path_file); 
			$content = str_replace( "standard", $lumiere_taxo_title, $content);
			file_put_contents($lumiere_current_theme_path_file, $content); 

			wp_safe_redirect( add_query_arg( "msg", "taxotemplatecopy_success", wp_get_referer() ) );
			exit();
		}

	// No $_GET["taxotype"] found or not in array
	} else {
		wp_safe_redirect( add_query_arg( "msg", "taxotemplatecopy_failed", wp_get_referer() ) ); 
		exit();
	}
// empty $_GET
} else {
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
}

?>
