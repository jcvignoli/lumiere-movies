<?php

/************************************************** Lumière version 3.3.3, update 2 */

$configClass->lumiere_maybe_log('info', "[Lumiere][updater] Starting update 2");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Update 'imdbwidgetsource'
// No need to display the source by default
if ( TRUE === $this->lumiere_update_options($configClass->imdbWidgetOptionsName, 'imdbwidgetsource', '0') ) {

	$text = "Lumière option imdbwidgetsource successfully updated.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetsource could not be updated.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

