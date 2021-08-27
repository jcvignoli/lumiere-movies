<?php declare( strict_types = 1 );
/************************************************** Lumière version 3.6, update 8
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
 */
$logger->info( '[Lumiere][updater] Starting update 8' );

$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 );
$this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

/**
 * Update 'imdbautopostwidget'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbautopostwidget', '0' ) ) {

	$text = 'Lumière option imdbautopostwidget successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbautopostwidget could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdbdebuglog'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbdebuglog', '0' ) ) {

	$text = 'Lumière option imdbdebuglog successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbdebuglog could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdbcoversize'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbcoversize', '0' ) ) {

	$text = 'Lumière option imdbcoversize successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbcoversize could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdblinkingkill'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdblinkingkill', '0' ) ) {

	$text = 'Lumière option imdblinkingkill successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdblinkingkill could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdbdebug'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbdebug', '0' ) ) {

	$text = 'Lumière option imdbdebug successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbdebug could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdbwordpress_bigmenu'
 * From "false" to '0'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbwordpress_bigmenu', '0' ) ) {

	$text = 'Lumière option imdbwordpress_bigmenu successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwordpress_bigmenu could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdbtaxonomy'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbtaxonomy', '1' ) ) {

	$text = 'Lumière option imdbtaxonomy successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbtaxonomy could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdbwordpress_tooladminmenu'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbwordpress_tooladminmenu', '1' ) ) {

	$text = 'Lumière option imdbwordpress_tooladminmenu successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwordpress_tooladminmenu could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdbdebugscreen'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbdebugscreen', '1' ) ) {

	$text = 'Lumière option imdbdebugscreen successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbdebugscreen could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdbkeepsettings'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbkeepsettings', '1' ) ) {

	$text = 'Lumière option imdbkeepsettings successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbkeepsettings could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdbpopup_highslide'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbAdminOptionsName, 'imdbpopup_highslide', '1' ) ) {

	$text = 'Lumière option imdbpopup_highslide successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbpopup_highslide could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Update 'imdbusecache'
 * From "true" to '1'
 */
if ( true === $this->lumiere_update_options( $configClass->imdbCacheOptionsName, 'imdbusecache', '1' ) ) {

	$text = 'Lumière option imdbusecache successfully updated.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbusecache could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Replace 'popupLarg' with 'imdbpopuplarg'
 * Option name missing 'imdb' prefix and should not be with capital case
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbAdminOptionsName, 'popupLarg' ) ) {

	$text = 'Lumière option popupLarg successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option popupLarg could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdbpopuplarg', '540' ) ) {

	$text = 'Lumière option imdbpopuplarg successfully added.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbpopuplarg could not be added.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}
/**
 * Replace 'popupLong' with 'imdbpopupLong'
 * Option name missing 'imdb' prefix
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbAdminOptionsName, 'popupLong' ) ) {

	$text = 'Lumière option popupLong successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option popupLong could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbAdminOptionsName, 'imdbpopuplong', '350' ) ) {

	$text = 'Lumière option imdbpopuplong successfully added.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbpopuplong could not be added.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Remove 'imdbcachedetails'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbCacheOptionsName, 'imdbcachedetails' ) ) {

	$text = 'Lumière option imdbcachedetails successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbcachedetails could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Remove 'blog_adress'
 * Obsolete and bad spelling
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbAdminOptionsName, 'blog_adress' ) ) {

	$text = 'Lumière option blog_adress successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option blog_adress could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Remove 'imdbwidgetonpage'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetonpage' ) ) {

	$text = 'Lumière option imdbwidgetonpage successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwidgetonpage could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Remove 'imdbwidgetonpost'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetonpost' ) ) {

	$text = 'Lumière option imdbwidgetonpost successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwidgetonpost could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Remove 'imdbimgdir'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbAdminOptionsName, 'imdbimgdir' ) ) {

	$text = 'Lumière option imdbimgdir successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbimgdir could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Remove 'imdb_utf8recode'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbAdminOptionsName, 'imdb_utf8recode' ) ) {

	$text = 'Lumière option imdb_utf8recode successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdb_utf8recode could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Remove 'imdbwebsite'
 * Obsolete
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbAdminOptionsName, 'imdbwebsite' ) ) {

	$text = 'Lumière option imdbwebsite successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwebsite could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/**
 * Replace 'imdbwidgetgoofsnumber' by 'imdbwidgetgoofnumber'
 * Singularizing items
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetgoofsnumber' ) ) {

	$text = 'Lumière option imdbwidgetgoofsnumber successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwidgetgoofsnumber could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetgoofnumber', false ) ) {

	$text = 'Lumière option imdbwidgetgoofnumber successfully added.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwidgetgoofnumber could not be added.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/*
 * Replace 'imdbwidgetquotesnumber' by 'imdbwidgetquotenumber'
 * Singularizing items
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetquotesnumber' ) ) {

	$text = 'Lumière option imdbwidgetquotesnumber successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwidgetquotesnumber could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetquotenumber', false ) ) {

	$text = 'Lumière option imdbwidgetquotenumber successfully added.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwidgetquotenumber could not be added.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/*
 * Replace 'imdbwidgettaglines' by 'imdbwidgettagline'
 * Singularizing items
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbwidgettaglinesnumber' ) ) {

	$text = 'Lumière option imdbwidgettaglinesnumber successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwidgettaglinesnumber could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}
if ( true === $this->lumiere_add_options( $configClass->imdbWidgetOptionsName, 'imdbwidgettaglinenumber', false ) ) {

	$text = 'Lumière option imdbwidgettaglinenumber successfully added.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwidgettaglinenumber could not be added.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/*
 * Replace plural values in 'imdbwidgetorder' by their singular counterparts
 * Singularizing items
 */
if ( true === $this->lumiere_update_options(
	$configClass->imdbWidgetOptionsName,
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
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbwidgetorder could not be updated.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/*
 * Remove 'imdbtaxonomytitle'
 * Obsolete value, no taxonomy built according to the title
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbWidgetOptionsName, 'imdbtaxonomytitle' ) ) {

	$text = 'Lumière option imdbtaxonomytitle successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbtaxonomytitle could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/*
 * Remove 'imdbdirectsearch'
 * Obsolete value
 */
if ( true === $this->lumiere_remove_options( $configClass->imdbAdminOptionsName, 'imdbdirectsearch' ) ) {

	$text = 'Lumière option imdbdirectsearch successfully removed.';
	$logger->info( "[Lumiere][updateOptions] $text" );

} else {

	$text = 'Lumière option imdbdirectsearch could not be removed.';
	$logger->error( "[Lumiere][updateOptions] $text" );

}

/*
 * imdbwidget values are not bool anymore, so they're set within apostrophes
 * Don't get any confirmation in the following updates
 */
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgettitle', '1' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetpic', '1' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetruntime', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetdirector', '1' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetcountry', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetactor', '1' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetcreator', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetrating', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetlanguage', '1' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetgenre', '1' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetwriter', '1' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetproducer', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetkeyword', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetprodcompany', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetplot', '1' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetgoof', '1' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetcomment', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetquote', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgettagline', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetcolor', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetalsoknow', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetcomposer', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetsoundtrack', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetofficialsites', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetsource', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgetyear', '0' );
$this->lumiere_update_options( $configClass->imdbWidgetOptionsName, 'imdbwidgettrailer', '0' );
$logger->debug( '[Lumiere][updateOptions] Maybe updated imdbwidget* vars to be strings instead of bools' );

/*
 * Remove obsolete terms linked to imdblt_keywords taxonomy (using now imdblt_keyword)
 */
$filter_taxonomy = 'imdblt_keywords';

$logger->debug( "[Lumiere][updateOptions] Process of deleting taxonomy $filter_taxonomy started" );

// Taxonomy must be registered in order to delete its terms
register_taxonomy(
	$filter_taxonomy,
	null,
	[
		'label' => false,
		'public' => false,
		'query_var' => false,
		'rewrite' => false,
	]
);

# Get all terms, even if empty
$taxo_terms = get_terms(
	[
		'taxonomy' => $filter_taxonomy,
		'hide_empty' => false,
	]
);

# Delete taxonomy terms and unregister taxonomy
foreach ( $taxo_terms as $taxo_term ) {

	$term_id = (int) $taxo_term->term_id;
	$term_name = (string) sanitize_text_field( $taxo_term->name );
	$term_taxonomy = (string) sanitize_text_field( $taxo_term->taxonomy );

	if ( ! empty( $term_id ) ) {

		wp_delete_term( $term_id, $filter_taxonomy );
		$logger->debug( '[Lumiere][updateOptions] Taxonomy: term ' . $term_name . ' in ' . $term_taxonomy . ' deleted.' );

	}

}

unregister_taxonomy( $filter_taxonomy );

$logger->debug( "[Lumiere][updateOptions] Taxonomy $filter_taxonomy deleted." );
