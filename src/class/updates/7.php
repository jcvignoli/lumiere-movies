<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.5, update 7
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */
$logger->info( '[Lumiere][updateVersion] Starting update 7' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

// Replace 'imdbwidgetcomments' by 'imdbwidgetcomment'
// Singularizing items
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetcomments' ) ) {

	$text = 'Lumière option imdbwidgetcomments successfully removed.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetcomments could not be removed.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetcomment', false ) ) {

	$text = 'Lumière option imdbwidgetcomment successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetcomment could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Replace 'imdbwidgetcolors' by 'imdbwidgetcolor'
// Singularizing items
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetcolors' ) ) {

	$text = 'Lumière option imdbwidgetcolors successfully removed.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetcolors could not be removed.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetcolor', false ) ) {

	$text = 'Lumière option imdbwidgetcolor successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetcolor could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Replace 'imdbwidgettaglines' by 'imdbwidgettagline'
// Singularizing items
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgettaglines' ) ) {

	$text = 'Lumière option imdbwidgettaglines successfully removed.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgettaglines could not be removed.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgettagline', false ) ) {

	$text = 'Lumière option imdbwidgettagline successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgettagline could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Replace 'imdbwidgetquotes' by 'imdbwidgetquote'
// Singularizing items
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetquotes' ) ) {

	$text = 'Lumière option imdbwidgetquotes successfully removed.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetquotes could not be removed.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetquote', false ) ) {

	$text = 'Lumière option imdbwidgetquote successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetquote could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Replace 'imdbwidgetgoofs' by 'imdbwidgetgoof'
// Singularizing items
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetgoofs' ) ) {

	$text = 'Lumière option imdbwidgetgoofs successfully removed.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetgoofs could not be removed.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetgoof', false ) ) {

	$text = 'Lumière option imdbwidgetgoof successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetgoof could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

// Replace 'imdbwidgetkeywords' by 'imdbwidgetkeyword'
// Singularizing items
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetkeywords' ) ) {

	$text = 'Lumière option imdbwidgetkeywords successfully removed.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetkeywords could not be removed.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetkeyword', false ) ) {

	$text = 'Lumière option imdbwidgetkeyword successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetkeyword could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}
// Replace 'imdbtaxonomykeywords' by 'imdbtaxonomykeyword'
// Singularizing items
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbtaxonomykeywords' ) ) {

	$text = 'Lumière option imdbtaxonomykeywords successfully removed.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbtaxonomykeywords could not be removed.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbtaxonomykeyword', false ) ) {

	$text = 'Lumière option imdbtaxonomykeyword successfully added.';

	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbtaxonomykeyword could not be added.';

	$logger->error( "[Lumiere][updateVersion] $text" );

}

