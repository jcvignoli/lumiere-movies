<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.4.2, update 5
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */
$logger->info( '[Lumiere][updater] Starting update 5' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Fix 'imdblanguage'
// Correct language extensions should take two letters only to include all dialects
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdblanguage', 'en' ) ) {

	$text = 'Lumière option imdblanguage successfully added.';
	$this->configClass->loggerclass->debug( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdblanguage could not be added.';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}

// Add 'imdbwidgetalsoknownumber'
// New option the number of akas displayed
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetalsoknownumber', false ) ) {

	$text = 'Lumière option imdbwidgetalsoknownumber successfully added..';
	$this->configClass->loggerclass->debug( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdbwidgetalsoknownumber could not be added..';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}

// Add 'imdbwidgetproducernumber'
// New option to limit the number of producers displayed
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetproducernumber', false ) ) {

	$text = 'Lumière option imdbwidgetproducernumber successfully added.';
	$this->configClass->loggerclass->debug( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdbwidgetproducernumber could not be added..';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}

