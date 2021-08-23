<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.3.4, update 3
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */
$logger->info( '[Lumiere][updater] Starting update 3' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Remove 'imdbdisplaylinktoimdb'
// Deprecated: removed links to IMDb in popup search and movie
if ( true === $this->lumiere_remove_options( $configClass->imdbAdminOptionsName, 'imdbdisplaylinktoimdb' ) ) {

	$text = 'Lumière option imdbdisplaylinktoimdb successfully removed.';
	$this->configClass->loggerclass->info( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdbdisplaylinktoimdb could not be removed.';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}

// Remove 'imdbpicsize'
// Deprecated: removed links to IMDb in popup search and movie
if ( true === $this->lumiere_remove_options( $configClass->imdbAdminOptionsName, 'imdbpicsize' ) ) {

	$text = 'Lumière option imdbpicsize successfully removed.';
	$this->configClass->loggerclass->info( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdbpicsize could not be removed.';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}

// Remove 'imdbpicurl'
// Deprecated: removed links to IMDb in popup search and movie
if ( true === $this->lumiere_remove_options( $configClass->imdbAdminOptionsName, 'imdbpicurl' ) ) {

	$text = 'Lumière option imdbpicurl successfully removed.';
	$this->configClass->loggerclass->info( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdbpicurl could not be removed.';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}

// Move 'imdblinkingkill'
// Variable moved from widget options to admin
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdblinkingkill' ) ) {

	$text = 'Lumière option imdblinkingkill successfully removed.';
	$this->configClass->loggerclass->info( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdblinkingkill could not be removed.';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdblinkingkill', 'false' ) ) {

	$text = 'Lumière option imdblinkingkill successfully added.';
	$this->configClass->loggerclass->info( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdblinkingkill could not be added.';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}

// Move 'imdbautopostwidget'
// Variable moved from widget options to admin
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbautopostwidget' ) ) {

	$text = 'Lumière option imdbautopostwidget successfully removed.';
	$this->configClass->loggerclass->info( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdbautopostwidget could not be removed.';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}

if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdbautopostwidget', 'false' ) ) {

	$text = 'Lumière option imdbautopostwidget successfully added.';
	$this->configClass->loggerclass->info( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdbautopostwidget could not be added.';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}

// Move 'imdbintotheposttheme'
// Variable moved from widget options to admin
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbintotheposttheme' ) ) {

	$text = 'Lumière option imdbintotheposttheme successfully removed.';
	$this->configClass->loggerclass->info( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdbintotheposttheme could not be removed.';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdbintotheposttheme', 'grey' ) ) {

	$text = 'Lumière option imdbintotheposttheme successfully added.';
	$this->configClass->loggerclass->info( "[Lumiere][updater] $text" );

} else {

	$text = 'Lumière option imdbintotheposttheme could not be added.';
	$this->configClass->loggerclass->error( "[Lumiere][updater] $text" );

}

