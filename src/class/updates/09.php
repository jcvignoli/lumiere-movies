<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 3.7, update 9
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
class Lumiere_Update_File_09 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '3.7';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 9;

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
		 * Remove 'imdbwidgetcomment'
		 * Obsolete
		 */
		if ( true === $this->lumiere_remove_options( Get_Options_Movie::get_data_tablename(), 'imdbwidgetcomment' ) ) {
			$text = 'Lumière option imdbwidgetcomment successfully removed.';
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetcomment could not be removed.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/*
		 * Remove 'comment' in 'imdbwidgetorder'
		 * Obsolete
		 */
		if ( true === $this->lumiere_update_options(
			Get_Options_Movie::get_data_tablename(),
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
			$this->logger->log?->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetorder could not be updated.';
			$this->logger->log?->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/** ------------------------- Editing part (end) --------------
		 */
	}
}
