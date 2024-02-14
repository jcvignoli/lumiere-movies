<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 3.11.6, update 14
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

use Lumiere\Settings;

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
class Lumiere_Update_File_14 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '3.12';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 14;

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
		$nb_of_updates = ( $this->imdb_admin_values['imdbHowManyUpdates'] + 1 );

		$this->lumiere_update_options( Settings::LUMIERE_ADMIN_OPTIONS, 'imdbHowManyUpdates', $nb_of_updates );

		/** ------------------------- Editing part (beginning) --------------
		 */

		/**
		 * Add 'imdbdelayimdbrequest' to LUMIERE_ADMIN_OPTIONS
		 * New var to allow delay the number of requests to IMDb -> avoid 504 HTTP error when querying IMDb website
		 */
		if ( true === $this->lumiere_add_options( Settings::LUMIERE_ADMIN_OPTIONS, 'imdbdelayimdbrequest', 0 ) ) {

			$text = 'Lumière option imdbdelayimdbrequest successfully added.';
			$logger->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbdelayimdbrequest could not be added.';
			$logger->error( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		/**
		 * Remove 'imdbusezip' from LUMIERE_CACHE_OPTIONS
		 * The var is obsolete and not used. Automatically sending config info in the class Imdbphp
		 */
		if ( true === $this->lumiere_remove_options( Settings::LUMIERE_CACHE_OPTIONS, 'imdbusezip' ) ) {

			$text = 'Lumière option imdbusezip successfully removed.';
			$logger->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbusezip could not be removed.';
			$logger->error( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		
		/**
		 * Remove 'imdbconverttozip' from LUMIERE_CACHE_OPTIONS
		 * The var is obsolete and has no use in IMDBPHP
		 */
		if ( true === $this->lumiere_remove_options( Settings::LUMIERE_CACHE_OPTIONS, 'imdbconverttozip' ) ) {

			$text = 'Lumière option imdbconverttozip successfully removed.';
			$logger->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbconverttozip could not be removed.';
			$logger->error( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		/** ------------------------- Editing part (end) --------------
		 */

	}

}
