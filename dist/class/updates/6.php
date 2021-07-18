<?php

/************************************************** Lumière version 3.4.3, update 6 */

if($logger !== NULL)
	$logger->debug("[Lumiere][updater] Starting update 6");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Add 'imdbdebugscreen'
// New option to show the debug on screen
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbdebugscreen', true) ) {

	$text = "Lumière option imdbdebugscreen successfully added.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbdebugscreen could not be added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Add 'imdbdebuglog'
// New option to select if to write a debug log
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbdebuglog', false) ) {

	$text = "Lumière option imdbdebuglog successfully added.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbdebuglog could not be added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Add 'imdbdebuglogpath'
// New option to enter a path for the log
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbdebuglogpath', WP_CONTENT_DIR . '/debug.log' ) ) {

	$text = "Lumière option imdbdebuglogpath successfully added.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbdebuglogpath could not be added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

?>
