<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 4.6.2, update 24
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
 * @copyright (c) 2025, Lost Highway
 * @package       lumieremovies
 */

namespace Lumiere\Updates;

use Lumiere\Config\Get_Options;

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
final class Lumiere_Update_File_24 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '4.6.2';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 24;

	/**
	 * Run the local update if lumiere_check_if_run_update() was successful
	 * Everytime an update is processed, imdbHowManyUpdates is increased by 1
	 */
	protected function lumiere_run_local_update(): void {

		/**
		 * Execute the check in Updates parent class, passing the constants.
		 * The validating function makes sure that this update has to be run.
		 * If not, exit.
		 */
		if ( $this->lumiere_check_if_run_update( self::LUMIERE_VERSION_UPDATE, self::LUMIERE_NUMBER_UPDATE ) === false ) {
			return;
		}

		/**
		 * Update the number of updates already processed in Lumière options.
		 * This is executed at the beggining, so if there is an issue, it's not repeated
		 */
		$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . (string) self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) + 1 );
		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', $nb_of_updates );

		/** ------------------------- Editing part (beginning) --------------
		 */

		$imdb_admin_options = get_option( Get_Options::get_admin_tablename() );

		/**
		 * Change the language format, get rid of the "US" which is wrong
		 */
		$data_lang = $imdb_admin_options['imdblanguage'] ?? false;
		if ( $data_lang === false || $data_lang === 'US' ) {
			$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdblanguage', 'en_GB' );
			$text = 'Lumière option imdblanguage did not exist or was set on "US", successfully updated to "en_GB".';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} elseif ( $data_lang === 'ES' ) {
			$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdblanguage', 'es_ES' );
			$text = 'Lumière option imdblanguage was set on "ES", successfully updated to "es_ES".';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} elseif ( $data_lang === 'FR' ) {
			$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdblanguage', 'fr_FR' );
			$text = 'Lumière option imdblanguage was set on "FR", successfully updated to "fr_FR".';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} elseif ( $data_lang === 'DE' ) {
			$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdblanguage', 'de_DE' );
			$text = 'Lumière option imdblanguage was set on "DE", successfully updated to "de_DE".';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdblanguage was not updated, it is already set to *' . $data_lang . '*';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/** ------------------------- Editing part (end) --------------
		 */
	}
}

