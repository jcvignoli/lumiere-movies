<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 3.6, update 8
 * Child of Updates class
 *
 * This class updates data for a new Lumière version
 * When writing a new update class, make sure to update only:
 * 1/ const LUMIERE_VERSION_UPDATE
 * 2/ const LUMIERE_NUMBER_UPDATE
 * 3/ function lumiere_run_local_update()
 * 4/ the classname
 *
 * This file is automatically registered and run in class-updates.php
 * @phpcs:disable WordPress.Files.FileName
 *
 * @copyright (c) 2022, Lost Highway
 * @package       lumieremovies
 */

namespace Lumiere\Updates;

use Lumiere\Config\Get_Options;

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
class Lumiere_Update_File_08 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '3.6';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 8;

	/**
	 * Run the local update if lumiere_check_if_run_update() was successful
	 * Everytime an update is processed, imdbHowManyUpdates is increased by 1
	 */
	protected function lumiere_run_local_update (): void {

		// Execute the check in Updates parent class, passing the constants.
		// The validating function makes sure that this update has to be run.
		// If not, exit.
		if ( $this->lumiere_check_if_run_update( self::LUMIERE_VERSION_UPDATE, self::LUMIERE_NUMBER_UPDATE ) === false ) {
			return;
		}

		// Update the number of updates already processed in Lumière options.
		$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) + 1 );

		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', $nb_of_updates );

		/** ------------------------- Editing part (beginning) --------------
		 */

		/**
		 * Update 'imdbautopostwidget'
		 * From "false" to '0'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbautopostwidget', '0' ) ) {
			$text = 'Lumière option imdbautopostwidget successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbautopostwidget could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdbdebuglog'
		 * From "false" to '0'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbdebuglog', '0' ) ) {

			$text = 'Lumière option imdbdebuglog successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbdebuglog could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdbcoversize'
		 * From "false" to '0'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbcoversize', '0' ) ) {

			$text = 'Lumière option imdbcoversize successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbcoversize could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdblinkingkill'
		 * From "false" to '0'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdblinkingkill', '0' ) ) {

			$text = 'Lumière option imdblinkingkill successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdblinkingkill could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdbdebug'
		 * From "false" to '0'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbdebug', '0' ) ) {

			$text = 'Lumière option imdbdebug successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbdebug could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdbwordpress_bigmenu'
		 * From "false" to '0'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbwordpress_bigmenu', '0' ) ) {

			$text = 'Lumière option imdbwordpress_bigmenu successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbwordpress_bigmenu could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdbtaxonomy'
		 * From "true" to '1'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbtaxonomy', '1' ) ) {

			$text = 'Lumière option imdbtaxonomy successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbtaxonomy could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdbwordpress_tooladminmenu'
		 * From "true" to '1'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbwordpress_tooladminmenu', '1' ) ) {

			$text = 'Lumière option imdbwordpress_tooladminmenu successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbwordpress_tooladminmenu could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdbdebugscreen'
		 * From "true" to '1'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbdebugscreen', '1' ) ) {

			$text = 'Lumière option imdbdebugscreen successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbdebugscreen could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdbkeepsettings'
		 * From "true" to '1'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbkeepsettings', '1' ) ) {

			$text = 'Lumière option imdbkeepsettings successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbkeepsettings could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdbpopup_highslide'
		 * From "true" to '1'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbpopup_highslide', '1' ) ) {

			$text = 'Lumière option imdbpopup_highslide successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbpopup_highslide could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update 'imdbusecache'
		 * From "true" to '1'
		 */
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbusecache', '1' ) ) {

			$text = 'Lumière option imdbusecache successfully updated.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbusecache could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Replace 'popupLarg' with 'imdbpopuplarg'
		 * Option name missing 'imdb' prefix and should not be with capital case
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_admin_tablename(), 'popupLarg' ) ) {

			$text = 'Lumière option popupLarg successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option popupLarg could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		if ( true === $this->lumiere_add_options( Get_Options::get_admin_tablename(), 'imdbpopuplarg', '540' ) ) {

			$text = 'Lumière option imdbpopuplarg successfully added.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbpopuplarg could not be added.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Replace 'popupLong' with 'imdbpopupLong'
		 * Option name missing 'imdb' prefix
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_admin_tablename(), 'popupLong' ) ) {

			$text = 'Lumière option popupLong successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option popupLong could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		if ( true === $this->lumiere_add_options( Get_Options::get_admin_tablename(), 'imdbpopuplong', '350' ) ) {

			$text = 'Lumière option imdbpopuplong successfully added.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {

			$text = 'Lumière option imdbpopuplong could not be added.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove 'imdbcachedetails'
		 * Obsolete
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_cache_tablename(), 'imdbcachedetails' ) ) {
			$text = 'Lumière option imdbcachedetails successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbcachedetails could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove 'blog_adress'
		 * Obsolete and bad spelling
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_admin_tablename(), 'blog_adress' ) ) {
			$text = 'Lumière option blog_adress successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option blog_adress could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove 'imdbwidgetonpage'
		 * Obsolete
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetonpage' ) ) {
			$text = 'Lumière option imdbwidgetonpage successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {
			$text = 'Lumière option imdbwidgetonpage could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove 'imdbwidgetonpost'
		 * Obsolete
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetonpost' ) ) {
			$text = 'Lumière option imdbwidgetonpost successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetonpost could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove 'imdbimgdir'
		 * Obsolete
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_admin_tablename(), 'imdbimgdir' ) ) {
			$text = 'Lumière option imdbimgdir successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbimgdir could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove 'imdb_utf8recode'
		 * Obsolete
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_admin_tablename(), 'imdb_utf8recode' ) ) {
			$text = 'Lumière option imdb_utf8recode successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdb_utf8recode could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove 'imdbwebsite'
		 * Obsolete
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_admin_tablename(), 'imdbwebsite' ) ) {
			$text = 'Lumière option imdbwebsite successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwebsite could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Replace 'imdbwidgetgoofsnumber' by 'imdbwidgetgoofnumber'
		 * Singularizing items
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetgoofsnumber' ) ) {
			$text = 'Lumière option imdbwidgetgoofsnumber successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetgoofsnumber could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgetgoofnumber', false ) ) {
			$text = 'Lumière option imdbwidgetgoofnumber successfully added.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetgoofnumber could not be added.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/*
		 * Replace 'imdbwidgetquotesnumber' by 'imdbwidgetquotenumber'
		 * Singularizing items
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetquotesnumber' ) ) {
			$text = 'Lumière option imdbwidgetquotesnumber successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetquotesnumber could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgetquotenumber', false ) ) {
			$text = 'Lumière option imdbwidgetquotenumber successfully added.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetquotenumber could not be added.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/*
		 * Replace 'imdbwidgettaglines' by 'imdbwidgettagline'
		 * Singularizing items
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgettaglinesnumber' ) ) {
			$text = 'Lumière option imdbwidgettaglinesnumber successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgettaglinesnumber could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgettaglinenumber', false ) ) {
			$text = 'Lumière option imdbwidgettaglinenumber successfully added.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgettaglinenumber could not be added.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/*
		 * Replace plural values in 'imdbwidgetorder' by their singular counterparts
		 * Singularizing items
		 */
		if ( true === $this->lumiere_update_options(
			Get_Options::get_data_tablename(),
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
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetorder could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove 'imdbtaxonomytitle'
		 * Obsolete value, no taxonomy built according to the title
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbtaxonomytitle' ) ) {
			$text = 'Lumière option imdbtaxonomytitle successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbtaxonomytitle could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove 'imdbdirectsearch'
		 * Obsolete value
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_admin_tablename(), 'imdbdirectsearch' ) ) {
			$text = 'Lumière option imdbdirectsearch successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbdirectsearch could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * imdbwidget values are not bool anymore, so they're set within apostrophes
		 * Don't get any confirmation in the following updates
		 */
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgettitle', '1' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetpic', '1' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetruntime', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetdirector', '1' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetcountry', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetactor', '1' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetcreator', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetrating', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetlanguage', '1' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetgenre', '1' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetwriter', '1' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetproducer', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetkeyword', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetprodcompany', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetplot', '1' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetgoof', '1' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetcomment', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetquote', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgettagline', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetcolor', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetalsoknow', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetcomposer', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetsoundtrack', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetofficialsites', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetsource', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetyear', '0' );
		$this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgettrailer', '0' );
		$this->logger->log?->debug( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Maybe updated imdbwidget* vars to be strings instead of bools' );

		/*
		 * Remove obsolete terms linked to imdblt_keywords taxonomy (using now imdblt_keyword)
		 */
		$filter_taxonomy = 'imdblt_keywords';

		$this->logger->log?->debug( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] Process of deleting taxonomy $filter_taxonomy started" );

		// Taxonomy must be registered in order to delete its terms.
		register_taxonomy(
			$filter_taxonomy,
			[ 'page', 'post' ],
			[
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
		if ( $taxo_terms instanceof \WP_Error ) {
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Invalid terms: ' . $taxo_terms->get_error_message() );
			return;
		}

		/** @psalm-suppress PossiblyInvalidIterator -- Cannot iterate over string -- this is the old WordPress way to have get_terms() return strings */
		foreach ( $taxo_terms as $taxo_term ) {

			$term_id = intval( $taxo_term->term_id );
			$term_name = sanitize_text_field( $taxo_term->name );
			$term_taxonomy = sanitize_text_field( $taxo_term->taxonomy );

			if ( $term_id > 0 ) {

				wp_delete_term( $term_id, $filter_taxonomy );
				$this->logger->log?->debug( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Taxonomy: term ' . $term_name . ' in ' . $term_taxonomy . ' deleted.' );

			}

		}

		unregister_taxonomy( $filter_taxonomy );

		$this->logger->log?->debug( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] Taxonomy $filter_taxonomy processed." );

		/** ------------------------- Editing part (end) --------------
		 */
	}
}
