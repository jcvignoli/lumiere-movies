<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.3.3, update 2
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */
$logger->info( '[Lumiere][updateVersion] Starting update 2' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbHowManyUpdates', $nb_of_updates );

// Update 'imdbwidgetsource'
// No need to display the source by default
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetsource', '0' ) ) {

	$text = 'Lumière option imdbwidgetsource successfully updated.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetsource could not be updated.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

