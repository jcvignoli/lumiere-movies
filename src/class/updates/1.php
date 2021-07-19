<?php

/************************************************** Lumière version 3.3.1, update 1 */

$configClass->lumiere_maybe_log('info', "[Lumiere][updater] Starting update 1");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Remove 'imdbwidgetcommentsnumber'
// Deprecated: only one comment is returned by imdbphp libraries
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetcommentsnumber') ){

	$text = "Lumière option imdbwidgetcommentsnumber successfully removed.";

	$configClass->lumiere_maybe_log('debug', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetcommentsnumber not removed.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Add 'imdbintotheposttheme'
// New option to manage theme colors for into the post/widget
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbintotheposttheme', 'grey') ) {

	$text = "Lumière option imdbintotheposttheme successfully added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbintotheposttheme not added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

?>
