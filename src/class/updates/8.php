<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.6, update 8
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */
$logger->info( '[Lumiere][updateVersion] Starting update 8' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbHowManyUpdates', $nb_of_updates );

/**
 * Update 'imdbautopostwidget'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbautopostwidget', '0' ) ) {

	$text = 'Lumière option imdbautopostwidget successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbautopostwidget could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdbdebuglog'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbdebuglog', '0' ) ) {

	$text = 'Lumière option imdbdebuglog successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbdebuglog could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdbcoversize'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbcoversize', '0' ) ) {

	$text = 'Lumière option imdbcoversize successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbcoversize could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdblinkingkill'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdblinkingkill', '0' ) ) {

	$text = 'Lumière option imdblinkingkill successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdblinkingkill could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdbdebug'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbdebug', '0' ) ) {

	$text = 'Lumière option imdbdebug successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbdebug could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdbwordpress_bigmenu'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbwordpress_bigmenu', '0' ) ) {

	$text = 'Lumière option imdbwordpress_bigmenu successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwordpress_bigmenu could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdbtaxonomy'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbtaxonomy', '1' ) ) {

	$text = 'Lumière option imdbtaxonomy successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbtaxonomy could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdbwordpress_tooladminmenu'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbwordpress_tooladminmenu', '1' ) ) {

	$text = 'Lumière option imdbwordpress_tooladminmenu successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwordpress_tooladminmenu could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdbdebugscreen'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbdebugscreen', '1' ) ) {

	$text = 'Lumière option imdbdebugscreen successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbdebugscreen could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdbkeepsettings'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbkeepsettings', '1' ) ) {

	$text = 'Lumière option imdbkeepsettings successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbkeepsettings could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdbpopup_highslide'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbpopup_highslide', '1' ) ) {

	$text = 'Lumière option imdbpopup_highslide successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbpopup_highslide could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Update 'imdbusecache'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_CACHE_OPTIONS, 'imdbusecache', '1' ) ) {

	$text = 'Lumière option imdbusecache successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbusecache could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Replace 'popupLarg' with 'imdbpopuplarg'
 * Option name missing 'imdb' prefix and should not be with capital case
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'popupLarg' ) ) {

	$text = 'Lumière option popupLarg successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option popupLarg could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbpopuplarg', '540' ) ) {

	$text = 'Lumière option imdbpopuplarg successfully added.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbpopuplarg could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}
/**
 * Replace 'popupLong' with 'imdbpopupLong'
 * Option name missing 'imdb' prefix
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'popupLong' ) ) {

	$text = 'Lumière option popupLong successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option popupLong could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbpopuplong', '350' ) ) {

	$text = 'Lumière option imdbpopuplong successfully added.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbpopuplong could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Remove 'imdbcachedetails'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_CACHE_OPTIONS, 'imdbcachedetails' ) ) {

	$text = 'Lumière option imdbcachedetails successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbcachedetails could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Remove 'blog_adress'
 * Obsolete and bad spelling
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'blog_adress' ) ) {

	$text = 'Lumière option blog_adress successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option blog_adress could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Remove 'imdbwidgetonpage'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetonpage' ) ) {

	$text = 'Lumière option imdbwidgetonpage successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetonpage could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Remove 'imdbwidgetonpost'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetonpost' ) ) {

	$text = 'Lumière option imdbwidgetonpost successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetonpost could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Remove 'imdbimgdir'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbimgdir' ) ) {

	$text = 'Lumière option imdbimgdir successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbimgdir could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Remove 'imdb_utf8recode'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdb_utf8recode' ) ) {

	$text = 'Lumière option imdb_utf8recode successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdb_utf8recode could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Remove 'imdbwebsite'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbwebsite' ) ) {

	$text = 'Lumière option imdbwebsite successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwebsite could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Replace 'imdbwidgetgoofsnumber' by 'imdbwidgetgoofnumber'
 * Singularizing items
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetgoofsnumber' ) ) {

	$text = 'Lumière option imdbwidgetgoofsnumber successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetgoofsnumber could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetgoofnumber', false ) ) {

	$text = 'Lumière option imdbwidgetgoofnumber successfully added.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetgoofnumber could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/*
 * Replace 'imdbwidgetquotesnumber' by 'imdbwidgetquotenumber'
 * Singularizing items
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetquotesnumber' ) ) {

	$text = 'Lumière option imdbwidgetquotesnumber successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetquotesnumber could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetquotenumber', false ) ) {

	$text = 'Lumière option imdbwidgetquotenumber successfully added.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetquotenumber could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/*
 * Replace 'imdbwidgettaglines' by 'imdbwidgettagline'
 * Singularizing items
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgettaglinesnumber' ) ) {

	$text = 'Lumière option imdbwidgettaglinesnumber successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgettaglinesnumber could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}
if ( true === $this->lumiere_add_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgettaglinenumber', false ) ) {

	$text = 'Lumière option imdbwidgettaglinenumber successfully added.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgettaglinenumber could not be added.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/*
 * Replace plural values in 'imdbwidgetorder' by their singular counterparts
 * Singularizing items
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
		'comment' => '17',
		'quote' => '18',
		'tagline' => '19',
		'color' => '20',
		'alsoknow' => '21',
		'composer' => '22',
		'soundtrack' => '23',
		'trailer' => '24',
		'officialsites' => '25',
		'source' => '26',
	]
) ) {

	$text = 'Lumière option imdbwidgetorder successfully updated.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbwidgetorder could not be updated.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Remove 'imdbtaxonomytitle'
 * Obsolete value, no taxonomy built according to the title
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbtaxonomytitle' ) ) {

	$text = 'Lumière option imdbtaxonomytitle successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbtaxonomytitle could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * Remove 'imdbdirectsearch'
 * Obsolete value
 */
if ( true === $this->lumiere_remove_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbdirectsearch' ) ) {

	$text = 'Lumière option imdbdirectsearch successfully removed.';
	$logger->info( "[Lumiere][updateVersion] $text" );

} else {

	$text = 'Lumière option imdbdirectsearch could not be removed.';
	$logger->error( "[Lumiere][updateVersion] $text" );

}

/**
 * imdbwidget values are not bool anymore, so they're set within apostrophes
 * Don't get any confirmation in the following updates
 */
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgettitle', '1' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetpic', '1' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetruntime', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetdirector', '1' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetcountry', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetactor', '1' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetcreator', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetrating', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetlanguage', '1' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetgenre', '1' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetwriter', '1' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetproducer', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetkeyword', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetprodcompany', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetplot', '1' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetgoof', '1' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetcomment', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetquote', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgettagline', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetcolor', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetalsoknow', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetcomposer', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetsoundtrack', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetofficialsites', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetsource', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgetyear', '0' );
$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, 'imdbwidgettrailer', '0' );
$logger->debug( '[Lumiere][updateVersion] Maybe updated imdbwidget* vars to be strings instead of bools' );

/*
 * Remove obsolete terms linked to imdblt_keywords taxonomy (using now imdblt_keyword)
 */
$filter_taxonomy = 'imdblt_keywords';

$logger->debug( "[Lumiere][updateVersion] Process of deleting taxonomy $filter_taxonomy started" );

// Taxonomy must be registered in order to delete its terms.
register_taxonomy(
	$filter_taxonomy,
	[ 'page', 'post' ],
	[
		'label' => false,
		'public' => false,
		'query_var' => false,
		'rewrite' => false,
	]
);

// Get all terms, even if empty.
$taxo_terms = get_terms(
	[
		'taxonomy' => $filter_taxonomy,
		'hide_empty' => false,
	]
);

// Delete taxonomy terms and unregister taxonomy.
if ( is_wp_error( $taxo_terms ) === true || is_string( $taxo_terms ) === true ) {
	return;
}

foreach ( $taxo_terms as $taxo_term ) {

	if ( is_int( $taxo_term ) === true || is_string( $taxo_term ) === true ) {
		continue;
	}

	$term_id = intval( $taxo_term->term_id );
	$term_name = sanitize_text_field( $taxo_term->name );
	$term_taxonomy = sanitize_text_field( $taxo_term->taxonomy );

	if ( $term_id > 0 ) {

		wp_delete_term( $term_id, $filter_taxonomy );
		$logger->debug( '[Lumiere][updateVersion] Taxonomy: term ' . $term_name . ' in ' . $term_taxonomy . ' deleted.' );

	}

}

unregister_taxonomy( $filter_taxonomy );

$logger->debug( "[Lumiere][updateVersion] Taxonomy $filter_taxonomy processed." );
