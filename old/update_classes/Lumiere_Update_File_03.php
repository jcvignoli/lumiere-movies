<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 3.3.4, update 3
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
use Lumiere\Config\Get_Options_Movie;

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
final class Lumiere_Update_File_03 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '3.3.4';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 3;

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
		$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . (string) self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) + 1 );

		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', $nb_of_updates );

		/** ------------------------- Editing part (beginning) --------------
		 */

		// Remove 'imdbdisplaylinktoimdb'
		// Deprecated: removed links to IMDb in popup search and movie
		if ( true === $this->lumiere_remove_options( Get_Options::get_admin_tablename(), 'imdbdisplaylinktoimdb' ) ) {

			$text = 'Lumière option imdbdisplaylinktoimdb successfully removed.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbdisplaylinktoimdb could not be removed.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Remove 'imdbpicsize'
		// Deprecated: removed links to IMDb in popup search and movie
		if ( true === $this->lumiere_remove_options( Get_Options::get_admin_tablename(), 'imdbpicsize' ) ) {

			$text = 'Lumière option imdbpicsize successfully removed.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbpicsize could not be removed.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Remove 'imdbpicurl'
		// Deprecated: removed links to IMDb in popup search and movie
		if ( true === $this->lumiere_remove_options( Get_Options::get_admin_tablename(), 'imdbpicurl' ) ) {

			$text = 'Lumière option imdbpicurl successfully removed.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbpicurl could not be removed.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Move 'imdblinkingkill'
		// Variable moved from widget options to admin
		if ( true === $this->lumiere_remove_options( Get_Options_Movie::get_data_tablename(), 'imdblinkingkill' ) ) {

			$text = 'Lumière option imdblinkingkill successfully removed.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdblinkingkill could not be removed.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		if ( true === $this->lumiere_add_options( Get_Options::get_admin_tablename(), 'imdblinkingkill', 'false' ) ) {

			$text = 'Lumière option imdblinkingkill successfully added.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdblinkingkill could not be added.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Move 'imdbautopostwidget'
		// Variable moved from widget options to admin
		if ( true === $this->lumiere_remove_options( Get_Options_Movie::get_data_tablename(), 'imdbautopostwidget' ) ) {

			$text = 'Lumière option imdbautopostwidget successfully removed.';
			$this->logger->log?->info( "[updateVersion] $text" );

		} else {

			$text = 'Lumière option imdbautopostwidget could not be removed.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		if ( true === $this->lumiere_add_options( Get_Options::get_admin_tablename(), 'imdbautopostwidget', 'false' ) ) {

			$text = 'Lumière option imdbautopostwidget successfully added.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbautopostwidget could not be added.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Move 'imdbintotheposttheme'
		// Variable moved from widget options to admin
		if ( true === $this->lumiere_remove_options( Get_Options_Movie::get_data_tablename(), 'imdbintotheposttheme' ) ) {
			$text = 'Lumière option imdbintotheposttheme successfully removed.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbintotheposttheme could not be removed.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		if ( true === $this->lumiere_add_options( Get_Options::get_admin_tablename(), 'imdbintotheposttheme', 'grey' ) ) {
			$text = 'Lumière option imdbintotheposttheme successfully added.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbintotheposttheme could not be added.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/** ------------------------- Editing part (end) --------------
		 */
	}
}
