<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.7, update 9
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */
$logger->info( '[Lumiere][updateVersion] Starting update 9' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbHowManyUpdates', $nb_of_updates );

/**
 * Remove 'imdbwidgetcomment'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetcomment' ) ) {

	$text = 'Lumière option imdbwidgetcomment successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetcomment could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/*
 * Remove 'comment' in 'imdbwidgetorder'
 * Obsolete
 */
if ( true === $this->lumiere_update_options(
	\Lumiere\Settings::LUMIERE_WIDGET_OPTIONS,
	'imdbwidgetorder',
	[
		'title' => '1',
		'pic' => '2',
		'runtime' => '3',
		'director' => '4',
		'country' => '5',
		'actor' => '6',
		'creator' => '7',
		'rating' => '8',
		'language' => '9',
		'genre' => '10',
		'writer' => '11',
		'producer' => '12',
		'keyword' => '13',
		'prodcompany' => '14',
		'plot' => '15',
		'goof' => '16',
		'quote' => '17',
		'tagline' => '18',
		'color' => '19',
		'alsoknow' => '20',
		'composer' => '21',
		'soundtrack' => '22',
		'trailer' => '23',
		'officialsites' => '24',
		'source' => '25',
	]
) ) {

	$text = 'Lumière option imdbwidgetorder successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetorder could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}
