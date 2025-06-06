<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 4.0, update 14
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
final class Lumiere_Update_File_14 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '4.0';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 14;

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

		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', strval( $nb_of_updates ) );

		/** ------------------------- Editing part (beginning) --------------
		 */

		/**
		 * Add 'imdbdelayimdbrequest' to LUMIERE_ADMIN_OPTIONS
		 * New var to allow delay the number of requests to IMDb -> avoid 504 HTTP error when querying IMDb website
		 */
		if ( true === $this->lumiere_add_options( Get_Options::get_admin_tablename(), 'imdbdelayimdbrequest', 0 ) ) {

			$text = 'Lumière option imdbdelayimdbrequest successfully added.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbdelayimdbrequest could not be added.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		/**
		 * Add 'imdbdelayimdbrequest' to LUMIERE_ADMIN_OPTIONS
		 * New var to allow delay the number of requests to IMDb -> avoid 504 HTTP error when querying IMDb website
		 */
		if ( true === $this->lumiere_add_options( Get_Options::get_cache_tablename(), 'imdbcacheautorefreshcron', 0 ) ) {

			$text = 'Lumière option imdbcacheautorefreshcron successfully added.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbcacheautorefreshcron could not be added.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		/**
		 * Remove 'imdbusezip' from LUMIERE_CACHE_OPTIONS
		 * The var is obsolete and not used. Automatically sending config info in the class Imdbphp
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_cache_tablename(), 'imdbusezip' ) ) {

			$text = 'Lumière option imdbusezip successfully removed.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbusezip could not be removed.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		/**
		 * Remove 'imdbconverttozip' from LUMIERE_CACHE_OPTIONS
		 * The var is obsolete and has no use in IMDBPHP
		 */
		if ( true === $this->lumiere_remove_options( Get_Options::get_cache_tablename(), 'imdbconverttozip' ) ) {

			$text = 'Lumière option imdbconverttozip successfully removed.';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbconverttozip could not be removed.';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		/** ------------------------- Editing part (end) --------------
		 */

	}

}
