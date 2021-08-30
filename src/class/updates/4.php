<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.4, update 4
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */
$logger->info( '[Lumiere][updateVersion] Starting update 4' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Add 'imdbSerieMovies'
// New option to select to search for movies, series, or both
if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdbseriemovies', 'movies+series' ) ) {

	$text = 'Lumière option imdbSerieMovies successfully added.';
	$logger->debug( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbSerieMovies could not be added..';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Add 'imdbHowManyUpdates'
// New option to manage the number of updates made
// Without such an option, all updates are went through
if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', 1 ) ) {

	$text = 'Lumière option imdbHowManyUpdates successfully added.';
	$logger->debug( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbHowManyUpdates could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

