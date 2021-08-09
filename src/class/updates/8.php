<?php

/************************************************** Lumière version 3.5, update 8 */

$configClass->lumiere_maybe_log('info', "[Lumiere][updater] Starting update 8");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Replace 'imdbwidgetgoofsnumber' by 'imdbwidgetgoofnumber'
// Singularizing items
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetgoofsnumber' ) ) {

	$text = "Lumière option imdbwidgetgoofsnumber successfully removed.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetgoofsnumber could not be removed.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetgoofnumber', false ) ) {

	$text = "Lumière option imdbwidgetgoofnumber successfully added.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetgoofnumber could not be added.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Replace 'imdbwidgetquotesnumber' by 'imdbwidgetquotenumber'
// Singularizing items
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetquotesnumber' ) ) {

	$text = "Lumière option imdbwidgetquotesnumber successfully removed.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetquotesnumber could not be removed.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetquotenumber', false ) ) {

	$text = "Lumière option imdbwidgetquotenumber successfully added.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetquotenumber could not be added.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Replace 'imdbwidgettaglines' by 'imdbwidgettagline'
// Singularizing items
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgettaglinesnumber' ) ) {

	$text = "Lumière option imdbwidgettaglinesnumber successfully removed.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgettaglinesnumber could not be removed.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgettaglinenumber', false ) ) {

	$text = "Lumière option imdbwidgettaglinenumber successfully added.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgettaglinenumber could not be added.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Replace plural values in 'imdbwidgetorder' by their singular counterparts
// Singularizing items
if ( TRUE === $this->lumiere_update_options($configClass->imdbWidgetOptionsName, 'imdbwidgetorder', 
	array("title" => "1", "pic" => "2","runtime" => "3", "director" => "4", "country" => "5", "actor" => "6", "creator" => "7", "rating" => "8", "language" => "9","genre" => "10","writer" => "11","producer" => "12", "keyword" => "13", "prodcompany" => "14", "plot" => "15", "goof" => "16", "comment" => "17", "quote" => "18", "tagline" => "19", "color" => "20", "alsoknow" => "21", "composer" => "22", "soundtrack" => "23", "trailer" => "24", "officialsites" => "25", "source" => "26" 
	) 
) ) {

	$text = "Lumière option imdbwidgetorder successfully updated.";
	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetorder could not be updated.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
