<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 3.4.2, update 5
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
final class Lumiere_Update_File_05 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '3.4.2';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 5;

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

		// Fix 'imdblanguage'
		// Correct language extensions should take two letters only to include all dialects
		if ( true === $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdblanguage', 'en' ) ) {
			$text = 'Lumière option imdblanguage successfully added.';
			$this->logger->log?->debug( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdblanguage could not be added.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		// Add 'imdbwidgetalsoknownumber'
		// New option the number of akas displayed
		if ( true === $this->lumiere_add_options( Get_Options_Movie::get_data_tablename(), 'imdbwidgetalsoknownumber', false ) ) {
			$text = 'Lumière option imdbwidgetalsoknownumber successfully added.';
			$this->logger->log?->debug( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetalsoknownumber could not be added.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		// Add 'imdbwidgetproducernumber'
		// New option to limit the number of producers displayed
		if ( true === $this->lumiere_add_options( Get_Options_Movie::get_data_tablename(), 'imdbwidgetproducernumber', false ) ) {
			$text = 'Lumière option imdbwidgetproducernumber successfully added.';
			$this->logger->log?->debug( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetproducernumber could not be added..';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		/** ------------------------- Editing part (end) --------------
		 */
	}
}
