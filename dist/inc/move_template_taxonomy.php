<?php

/*  Move automatically the Lumière! template for taxonomy (theme/taxonomy-imdblt_standard.php) 
 *  to the current user template folder
 */

// prevent direct call
if ( (empty(wp_get_referer()) && (0 !== stripos( wp_get_referer(), admin_url() . 'admin.php?page=imdblt_options&subsection=dataoption&widgetoption=taxo' )) ) || ( ! defined( 'ABSPATH' ) ) )
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));

/************* Vars **************/
global $imdb_admin_values, $imdb_widget_values;

$lumiere_taxo_title = esc_html( $_GET['taxotype'] );

$lumiere_taxo_file_tocopy = "taxonomy-imdblt_standard.php";
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
			$content = str_replace( "director", $lumiere_taxo_title, $content);
			$content = str_replace( "imdblt_", $imdb_admin_values['imdburlstringtaxo'], $content);
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
