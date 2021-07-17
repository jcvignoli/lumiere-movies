<?php

/************************************************** Lumière version 3.4, update 4 */

if($logger !== NULL)
	$logger->debug("[Lumiere][updater] Starting update 4");

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
$this->lumiere_update_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Add 'imdbSerieMovies'
// New option to select to search for movies, series, or both
if ( TRUE === $this->lumiere_add_options($configClass->imdbAdminOptionsName, 'imdbseriemovies', 'movies+series') ) {
	$output .= $this->print_debug(1, '<strong>Lumière option imdbSerieMovies successfully added.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbSerieMovies could not be added.</strong>');
}

// Add 'imdbHowManyUpdates'
// New option to manage the number of updates made
// Without such an option, all updates are went through
if ( TRUE === $this->lumiere_add_options($configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', 1 ) ) {
	$output .= $this->print_debug(1, '<strong>Lumière option imdbHowManyUpdates successfully added.</strong>');
} else {
	$output .= $this->print_debug(2, '<strong>Lumière option imdbHowManyUpdates could not be added.</strong>');
}

?>
