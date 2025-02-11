<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 3.4, update 4
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
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @package lumiere-movies
 */

namespace Lumiere\Updates;

use Lumiere\Config\Get_Options;

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
class Lumiere_Update_File_04 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '3.4';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 4;

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

		// Simplify the coding.
		$logger = $this->logger->log;

		// Update the number of updates already processed in Lumière options.
		$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) + 1 );

		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', $nb_of_updates );

		/** ------------------------- Editing part (beginning) --------------
		 */

		// Add 'imdbSerieMovies'
		// New option to select to search for movies, series, or both
		if ( true === $this->lumiere_add_options( Get_Options::get_admin_tablename(), 'imdbseriemovies', 'movies+series' ) ) {

			$text = 'Lumière option imdbSerieMovies successfully added.';
			$logger->debug( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbSerieMovies could not be added.';
			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Add 'imdbHowManyUpdates'
		// New option to manage the number of updates made
		// Without such an option, all updates are went through
		if ( true === $this->lumiere_add_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', 1 ) ) {

			$text = 'Lumière option imdbHowManyUpdates successfully added.';
			$logger->debug( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbHowManyUpdates could not be added.';
			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		/** ------------------------- Editing part (end) --------------
		 */

	}

}
