<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.4.2, update 5
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */

$logger->info( '[Lumiere][updateVersion] Starting update 5' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbHowManyUpdates', $nb_of_updates );

// Fix 'imdblanguage'
// Correct language extensions should take two letters only to include all dialects
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdblanguage', 'en' ) ) {

	$text = 'Lumière option imdblanguage successfully added.';
	$logger->debug( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdblanguage could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Add 'imdbwidgetalsoknownumber'
// New option the number of akas displayed
if ( true === $this->lumiere_add_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetalsoknownumber', false ) ) {

	$text = 'Lumière option imdbwidgetalsoknownumber successfully added.';
	$logger->debug( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetalsoknownumber could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Add 'imdbwidgetproducernumber'
// New option to limit the number of producers displayed
if ( true === $this->lumiere_add_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetproducernumber', false ) ) {

	$text = 'Lumière option imdbwidgetproducernumber successfully added.';
	$logger->debug( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetproducernumber could not be added..';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

