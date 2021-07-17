<?php

/************************************************** Lumière version 3.3.1, update 1 */

if($logger !== NULL)
	$logger->debug("[Lumiere][updater] Starting update 1");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Remove 'imdbwidgetcommentsnumber'
// Deprecated: only one comment is returned by imdbphp libraries
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbwidgetcommentsnumber') ){
	$output .= $this->print_debug(1, '<strong>Lumière option imdbwidgetcommentsnumber successfully removed.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbwidgetcommentsnumber not removed.</strong>');
}

// Add 'imdbintotheposttheme'
// New option to manage theme colors for into the post/widget
if ( TRUE === $this->lumiere_add_options($configClass->imdbWidgetOptionsName, 'imdbintotheposttheme', 'grey') ) {
	$output .= $this->print_debug(1, '<strong>Lumière option imdbintotheposttheme successfully added.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbintotheposttheme not added.</strong>');
}

?>
