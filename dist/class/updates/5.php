<?php

/************************************************** Lumière version 3.4.2, update 5 */

if($logger !== NULL)
	$logger->debug("[Lumiere][updater] Starting update 5");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Fix 'imdblanguage'
// Correct language extensions should take two letters only to include all dialects
if ( TRUE === $this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdblanguage', 'en') ) {

	$text = "Lumière option imdblanguage successfully added.";
	$configClass->lumiere_maybe_log('debug', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdblanguage could not be added.";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Add 'imdbwidgetalsoknownumber'
// New option the number of akas displayed
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetalsoknownumber', false) ) {

	$text = "Lumière option imdbwidgetalsoknownumber successfully added..";
	$configClass->lumiere_maybe_log('debug', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetalsoknownumber could not be added..";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Add 'imdbwidgetproducernumber'
// New option to limit the number of producers displayed
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetproducernumber', false) ) {

	$text = "Lumière option imdbwidgetproducernumber successfully added.";
	$configClass->lumiere_maybe_log('debug', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetproducernumber could not be added..";
	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

?>
