<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 3.3.3, update 2
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

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
class Lumiere_Update_File_02 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '3.3.3';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 2;

	/**
	 * Constructor
	 *
	 */
	public function __construct () {

		// Construct parent class
		parent::__construct();

		// Run the update
		$this->lumiere_run_local_update();

	}

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
		$logger = $this->logger->log();

		// Update the number of updates already processed in Lumière options.
		$logger->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) + 1 );

		$this->lumiere_update_options( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, 'imdbHowManyUpdates', $nb_of_updates );

		/** ------------------------- Editing part (beginning) --------------
		 */

		// Update 'imdbwidgetsource'
		// No need to display the source by default
		if ( true === $this->lumiere_update_options( \Lumiere\Settings::LUMIERE_DATA_OPTIONS, 'imdbwidgetsource', '0' ) ) {

			$text = 'Lumière option imdbwidgetsource successfully updated.';

			$logger->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetsource could not be updated.';

			$logger->error( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		/** ------------------------- Editing part (end) --------------
		 */

	}

}
