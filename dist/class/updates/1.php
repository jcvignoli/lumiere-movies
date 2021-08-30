<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.3.1, update 1
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */
$logger->info( '[Lumiere][updateVersion] Starting update 1' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Remove 'imdbwidgetcommentsnumber'
// Deprecated: only one comment is returned by imdbphp libraries
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetcommentsnumber' ) ) {

	$text = 'Lumière option imdbwidgetcommentsnumber successfully removed.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetcommentsnumber not removed.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Add 'imdbintotheposttheme'
// New option to manage theme colors for into the post/widget
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbintotheposttheme', 'grey' ) ) {

	$text = 'Lumière option imdbintotheposttheme successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbintotheposttheme not added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

