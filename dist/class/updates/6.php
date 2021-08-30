<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.4.3, update 6
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */

$logger->info( '[Lumiere][updateVersion] Starting update 6' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Add 'imdbdebuglevel'
// New option to select the level of verbosity
if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdbdebuglevel', 'DEBUG' ) ) {

	$text = 'Lumière option imdbdebuglevel successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbdebuglevel could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Add 'imdbdebugscreen'
// New option to show the debug on screen
if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdbdebugscreen', true ) ) {

	$text = 'Lumière option imdbdebugscreen successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbdebugscreen could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Add 'imdbdebuglog'
// New option to select if to write a debug log
if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdbdebuglog', false ) ) {

	$text = 'Lumière option imdbdebuglog successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbdebuglog could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Add 'imdbdebuglogpath'
// New option to enter a path for the log
if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdbdebuglogpath', WP_CONTENT_DIR . '/debug.log' ) ) {

	$text = 'Lumière option imdbdebuglogpath successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbdebuglogpath could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

