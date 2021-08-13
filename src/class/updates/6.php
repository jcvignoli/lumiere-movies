<?php

/************************************************** Lumière version 3.4.3, update 6 */

$this->configClass->loggerclass->info("[Lumiere][updater] Starting update 6");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Add 'imdbdebuglevel'
// New option to select the level of verbosity
if ( TRUE === $this->lumiere_add_options($configClass->imdbAdminOptionsName, 'imdbdebuglevel', 'DEBUG' ) ) {

	$text = "Lumière option imdbdebuglevel successfully added.";

	$this->configClass->loggerclass->info("[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbdebuglevel could not be added.";

	$this->configClass->loggerclass->error("[Lumiere][updater] $text");

}

// Add 'imdbdebugscreen'
// New option to show the debug on screen
if ( TRUE === $this->lumiere_add_options($configClass->imdbAdminOptionsName, 'imdbdebugscreen', true) ) {

	$text = "Lumière option imdbdebugscreen successfully added.";

	$this->configClass->loggerclass->info("[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbdebugscreen could not be added.";

	$this->configClass->loggerclass->error("[Lumiere][updater] $text");

}

// Add 'imdbdebuglog'
// New option to select if to write a debug log
if ( TRUE === $this->lumiere_add_options($configClass->imdbAdminOptionsName, 'imdbdebuglog', false) ) {

	$text = "Lumière option imdbdebuglog successfully added.";

	$this->configClass->loggerclass->info("[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbdebuglog could not be added.";

	$this->configClass->loggerclass->error("[Lumiere][updater] $text");

}

// Add 'imdbdebuglogpath'
// New option to enter a path for the log
if ( TRUE === $this->lumiere_add_options($configClass->imdbAdminOptionsName, 'imdbdebuglogpath', WP_CONTENT_DIR . '/debug.log' ) ) {

	$text = "Lumière option imdbdebuglogpath successfully added.";

	$this->configClass->loggerclass->info("[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbdebuglogpath could not be added.";

	$this->configClass->loggerclass->error("[Lumiere][updater] $text");

}


