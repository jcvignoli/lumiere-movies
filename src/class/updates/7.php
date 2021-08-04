<?php

/************************************************** Lumière version 3.5, update 7 */

$configClass->lumiere_maybe_log('info', "[Lumiere][updater] Starting update 7");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Replace 'imdbwidgetcomments' by 'imdbwidgetcomment'
// Singularizing items
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetcomments' ) ) {

	$text = "Lumière option imdbwidgetcomments successfully removed.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetcomments could not be removed.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetcomment', false ) ) {

	$text = "Lumière option imdbwidgetcomment successfully added.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetcomment could not be added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Replace 'imdbwidgetcolors' by 'imdbwidgetcolor'
// Singularizing items
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetcolors' ) ) {

	$text = "Lumière option imdbwidgetcolors successfully removed.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetcolors could not be removed.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetcolor', false ) ) {

	$text = "Lumière option imdbwidgetcolor successfully added.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetcolor could not be added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Replace 'imdbwidgettaglines' by 'imdbwidgettagline'
// Singularizing items
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgettaglines' ) ) {

	$text = "Lumière option imdbwidgettaglines successfully removed.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgettaglines could not be removed.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgettagline', false ) ) {

	$text = "Lumière option imdbwidgettagline successfully added.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgettagline could not be added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Replace 'imdbwidgetquotes' by 'imdbwidgetquote'
// Singularizing items
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetquotes' ) ) {

	$text = "Lumière option imdbwidgetquotes successfully removed.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetquotes could not be removed.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetquote', false ) ) {

	$text = "Lumière option imdbwidgetquote successfully added.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetquote could not be added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Replace 'imdbwidgetgoofs' by 'imdbwidgetgoof'
// Singularizing items
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetgoofs' ) ) {

	$text = "Lumière option imdbwidgetgoofs successfully removed.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetgoofs could not be removed.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetgoof', false ) ) {

	$text = "Lumière option imdbwidgetgoof successfully added.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetgoof could not be added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}

// Replace 'imdbwidgetkeywords' by 'imdbwidgetkeyword'
// Singularizing items
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetkeywords' ) ) {

	$text = "Lumière option imdbwidgetkeywords successfully removed.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetkeywords could not be removed.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbwidgetkeyword', false ) ) {

	$text = "Lumière option imdbwidgetkeyword successfully added.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbwidgetkeyword could not be added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
 Replace 'imdbtaxonomykeywords' by 'imdbtaxonomykeyword'
// Singularizing items
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbtaxonomykeywords' ) ) {

	$text = "Lumière option imdbtaxonomykeywords successfully removed.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbtaxonomykeywords could not be removed.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbtaxonomykeyword', false ) ) {

	$text = "Lumière option imdbtaxonomykeyword successfully added.";

	$configClass->lumiere_maybe_log('info', "[Lumiere][updater] $text");

} else {

	$text = "Lumière option imdbtaxonomykeyword could not be added.";

	$configClass->lumiere_maybe_log('error', "[Lumiere][updater] $text");

}
?>
