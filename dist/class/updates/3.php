<?php

/************************************************** Lumière version 3.3.4, update 3 */

if($logger !== NULL)
	$logger->debug("[Lumiere][updater] Starting update 3");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Remove 'imdbdisplaylinktoimdb'
// Deprecated: removed links to IMDb in popup search and movie
if ( TRUE === $this->lumiere_remove_options($configClass->imdbAdminOptionsName, 'imdbdisplaylinktoimdb') ){
	$output .= $this->print_debug(1, '<strong>Lumière option imdbdisplaylinktoimdb successfully removed.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbdisplaylinktoimdb not removed.</strong>');
}

// Remove 'imdbpicsize'
// Deprecated: removed links to IMDb in popup search and movie
if ( TRUE === $this->lumiere_remove_options($configClass->imdbAdminOptionsName, 'imdbpicsize') ){
	$output .= $this->print_debug(1, '<strong>Lumière option imdbpicsize successfully removed.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbpicsize not removed.</strong>');
}

// Remove 'imdbpicurl'
// Deprecated: removed links to IMDb in popup search and movie
if ( TRUE === $this->lumiere_remove_options($configClass->imdbAdminOptionsName, 'imdbpicurl') ){
	$output .= $this->print_debug(1, '<strong>Lumière option imdbpicurl successfully removed.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbpicurl not removed.</strong>');
}

// Move 'imdblinkingkill'
// Variable moved from widget options to admin
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdblinkingkill') ){
	$output .= $this->print_debug(1, '<strong>Lumière option imdblinkingkill successfully removed.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdblinkingkill not removed.</strong>');
}
if ( TRUE === $this->lumiere_add_options($configClass->imdbAdminOptionsName, 'imdblinkingkill', 'false') ){
	$output .= $this->print_debug(1, '<strong>Lumière option imdblinkingkill successfully added.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdblinkingkill not added.</strong>');
}

// Move 'imdbautopostwidget'
// Variable moved from widget options to admin
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbautopostwidget') ){
	$output .= $this->print_debug(1, '<strong>Lumière option imdbautopostwidget successfully removed.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbautopostwidget not removed.</strong>');
}

if ( TRUE === $this->lumiere_add_options($configClass->imdbAdminOptionsName, 'imdbautopostwidget', 'false') ){
	$output .= $this->print_debug(1, '<strong>Lumière option imdbautopostwidget successfully added.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbautopostwidget not added.</strong>');
}

// Move 'imdbintotheposttheme'
// Variable moved from widget options to admin
if ( TRUE === $this->lumiere_remove_options($configClass->imdbWidgetOptionsName, 'imdbintotheposttheme') ) {
	$output .= $this->print_debug(1, '<strong>Lumière option imdbintotheposttheme successfully removed.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbintotheposttheme not removed.</strong>');
}
if ( TRUE === $this->lumiere_add_options($configClass->imdbAdminOptionsName, 'imdbintotheposttheme', 'grey') ) {
	$output .= $this->print_debug(1, '<strong>Lumière option imdbintotheposttheme successfully added.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbintotheposttheme not added.</strong>');
}

?>
