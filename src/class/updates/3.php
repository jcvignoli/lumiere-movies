<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.3.4, update 3
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */
$logger->info( '[Lumiere][updateVersion] Starting update 3' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbHowManyUpdates', $nb_of_updates );

// Remove 'imdbdisplaylinktoimdb'
// Deprecated: removed links to IMDb in popup search and movie
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbdisplaylinktoimdb' ) ) {

	$text = 'Lumière option imdbdisplaylinktoimdb successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbdisplaylinktoimdb could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Remove 'imdbpicsize'
// Deprecated: removed links to IMDb in popup search and movie
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbpicsize' ) ) {

	$text = 'Lumière option imdbpicsize successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbpicsize could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Remove 'imdbpicurl'
// Deprecated: removed links to IMDb in popup search and movie
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbpicurl' ) ) {

	$text = 'Lumière option imdbpicurl successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbpicurl could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Move 'imdblinkingkill'
// Variable moved from widget options to admin
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdblinkingkill' ) ) {

	$text = 'Lumière option imdblinkingkill successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdblinkingkill could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdblinkingkill', 'false' ) ) {

	$text = 'Lumière option imdblinkingkill successfully added.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdblinkingkill could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Move 'imdbautopostwidget'
// Variable moved from widget options to admin
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbautopostwidget' ) ) {

	$text = 'Lumière option imdbautopostwidget successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbautopostwidget could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

if ( true === $this->lumiere_add_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbautopostwidget', 'false' ) ) {

	$text = 'Lumière option imdbautopostwidget successfully added.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbautopostwidget could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Move 'imdbintotheposttheme'
// Variable moved from widget options to admin
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbintotheposttheme' ) ) {

	$text = 'Lumière option imdbintotheposttheme successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbintotheposttheme could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbintotheposttheme', 'grey' ) ) {

	$text = 'Lumière option imdbintotheposttheme successfully added.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbintotheposttheme could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

