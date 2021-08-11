<?php

/************************************************** Lumière version 3.5, update 8 */

$configClass->lumiere_maybe_log('info', "[Lumiere][updater] Starting update 8");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

/*
 * Replace 'imdbwidgetgoofsnumber' by 'imdbwidgetgoofnumber'
 * Singularizing items
 */
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetgoofsnumber' ) ) {

	$text = "Lumière option imdbwidgetgoofsnumber successfully removed.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updateOptions] $text");

} else {

	$text = "Lumière option imdbwidgetgoofsnumber could not be removed.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updateOptions] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetgoofnumber', false ) ) {

	$text = "Lumière option imdbwidgetgoofnumber successfully added.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updateOptions] $text");

} else {

	$text = "Lumière option imdbwidgetgoofnumber could not be added.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updateOptions] $text");

}

/*
 * Replace 'imdbwidgetquotesnumber' by 'imdbwidgetquotenumber'
 * Singularizing items
 */
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetquotesnumber' ) ) {

	$text = "Lumière option imdbwidgetquotesnumber successfully removed.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updateOptions] $text");

} else {

	$text = "Lumière option imdbwidgetquotesnumber could not be removed.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updateOptions] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetquotenumber', false ) ) {

	$text = "Lumière option imdbwidgetquotenumber successfully added.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updateOptions] $text");

} else {

	$text = "Lumière option imdbwidgetquotenumber could not be added.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updateOptions] $text");

}

/*
 * Replace 'imdbwidgettaglines' by 'imdbwidgettagline'
 * Singularizing items
 */
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgettaglinesnumber' ) ) {

	$text = "Lumière option imdbwidgettaglinesnumber successfully removed.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updateOptions] $text");

} else {

	$text = "Lumière option imdbwidgettaglinesnumber could not be removed.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updateOptions] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgettaglinenumber', false ) ) {

	$text = "Lumière option imdbwidgettaglinenumber successfully added.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updateOptions] $text");

} else {

	$text = "Lumière option imdbwidgettaglinenumber could not be added.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updateOptions] $text");

}

/*
 * Replace plural values in 'imdbwidgetorder' by their singular counterparts
 * Singularizing items
 */
if ( TRUE === $this->lumiere_update_options($configClass->imdbWidgetOptionsName, 'imdbwidgetorder', 
	array("title" => "1", "pic" => "2","runtime" => "3", "director" => "4", "country" => "5", "actor" => "6", "creator" => "7", "rating" => "8", "language" => "9","genre" => "10","writer" => "11","producer" => "12", "keyword" => "13", "prodcompany" => "14", "plot" => "15", "goof" => "16", "comment" => "17", "quote" => "18", "tagline" => "19", "color" => "20", "alsoknow" => "21", "composer" => "22", "soundtrack" => "23", "trailer" => "24", "officialsites" => "25", "source" => "26" 
	) 
) ) {

	$text = "Lumière option imdbwidgetorder successfully updated.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updateOptions] $text");

} else {

	$text = "Lumière option imdbwidgetorder could not be updated.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updateOptions] $text");

}

/*
 * Remove 'imdbtaxonomytitle'
 * Obsolete value, no taxonomy built according to the title
 */
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbtaxonomytitle' ) ) {

	$text = "Lumière option imdbtaxonomytitle successfully removed.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updateOptions] $text");

} else {

	$text = "Lumière option imdbtaxonomytitle could not be removed.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updateOptions] $text");

}

/*
 * Remove obsolete terms linked to imdblt_keywords taxonomy (using now imdblt_keyword)
 */
$filter_taxonomy = 'imdblt_keywords';

$configClass->lumiere_maybe_log('debug', "[Lumiere][updateOptions] Process of deleting taxonomy $filter_taxonomy started");

// Taxonomy must be registered in order to delete its terms
register_taxonomy( $filter_taxonomy, null, array( 'label' => false, 'public' => false, 'query_var' => false, 'rewrite' => false ) );

# Get all terms, even if empty
$terms = get_terms( array(
	'taxonomy' => $filter_taxonomy,
	'hide_empty' => false
) );

# Delete taxonomy terms and unregister taxonomy
foreach ( $terms as $term ) {

	$term_id = (int) $term->term_id;
	$term_name = (string) sanitize_text_field($term->name);
	$term_taxonomy = (string) sanitize_text_field($term->taxonomy);

	if ( ! empty( $term_id ) ) {

		wp_delete_term( $term_id, $filter_taxonomy );
		$configClass->lumiere_maybe_log('debug', "[Lumiere][updateOptions] Taxonomy: term " . $term_name . " in " . $term_taxonomy . " deleted.");

	}

	unregister_taxonomy( $filter_taxonomy );

	$configClass->lumiere_maybe_log('debug', "[Lumiere][updateOptions] Taxonomy $filter_taxonomy deleted.");

}
