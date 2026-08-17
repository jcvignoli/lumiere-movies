<?php
/**
 * Specific Class for updating
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
 * @copyright (c) 2026, Lost Highway
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Updates;

use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
final class Lumiere_Update_File_26 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '4.8.1';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 26;

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
		$this->logger->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . (string) self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->settings->get_admin_option( 'imdbHowManyUpdates' ) ) + 1 );
		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', $nb_of_updates );

		// Update the number of updates already processed in Lumière options.
		$this->logger->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . (string) self::LUMIERE_NUMBER_UPDATE );

		/** ------------------------- Editing part (beginning) --------------
		 */

		/**
		 * Edit 'imdbdelayimdbrequest' in LUMIERE_ADMIN_OPTIONS
		 * Var was not meant to be 0 (infinite) => avoid fatal error
		 */
		if ( $this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbdelayimdbrequest', '20' ) === true ) {
			$text = 'Lumière option imdbdelayimdbrequest successfully updated to 20.';
			$this->logger->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbdelayimdbrequest could not be update.';
			$this->logger->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove obsolete 'imdbwidgetcolor' in LUMIERE_DATA_OPTIONS
		 */
		if ( $this->lumiere_remove_options( Get_Options_Movie::get_data_tablename(), 'imdbwidgetcolor' ) ) {
			$text = 'Lumière option imdbwidgetcolor successfully deleted.';
			$this->logger->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetcolor could not be removed.';
			$this->logger->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove obsolete 'imdbtaxonomycolor' in LUMIERE_DATA_OPTIONS
		 */
		if ( $this->lumiere_remove_options( Get_Options_Movie::get_data_tablename(), 'imdbtaxonomycolor' ) ) {
			$text = 'Lumière option imdbtaxonomycolor successfully deleted.';
			$this->logger->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbtaxonomycolor could not be removed.';
			$this->logger->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Remove obsolete imdbwidgetorder['color'] in LUMIERE_DATA_OPTIONS
		 */
		/** @var array<string, string> $imdb_data_options */
		$imdb_data_options = get_option( Get_Options_Movie::get_data_tablename() );
		$order_value = $imdb_data_options['imdbwidgetorder'] ?? false;
		if ( isset( $order_value['color'] ) ) {
			unset( $order_value['color'] );
			if ( $this->lumiere_update_options( Get_Options_Movie::get_data_tablename(), 'imdbwidgetorder', $order_value ) ) {
				$text = 'Lumière option imdbwidgetorder[color] successfully deleted.';
				$this->logger->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
			} else {
				$text = 'Lumière option imdbwidgetorder[color] could not be removed.';
				$this->logger->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
			}
		} else {
			$text = 'Lumière option $imdbwidgetorder["color"] does not exist.';
			$this->logger->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update imdbwidgetorder'prodcompany' to imdbwidgetorder'prodCompany' if it exists
		 * This is not needed on recent installations, but don't know why mine got stuck with lowercase
		 */
		/** @var array<string, string> $imdb_data_options Reinitialize the var so the previous get updated */
		$imdb_data_options = get_option( Get_Options_Movie::get_data_tablename() );
		$order_value = $imdb_data_options['imdbwidgetorder'] ?? false;
		$prodcompany_value = $order_value['prodcompany'] ?? false;
		if ( isset( $order_value['prodcompany'] ) && $prodcompany_value !== false ) {
			unset( $order_value['prodcompany'] );
			$order_value['prodCompany'] = $prodcompany_value;
			if ( $this->lumiere_update_options( Get_Options_Movie::get_data_tablename(), 'imdbwidgetorder', $order_value ) === true ) {
				$text = 'Lumière option $imdbwidgetorder["prodCompany"] successfully updated.';
				$this->logger->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
			}
		} else {
			$text = 'Lumière option $imdbwidgetorder["prodCompany"] already exists so no need to be updated.';
			$this->logger->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Update imdbwidgetorder'extsites' to imdbwidgetorder'extSites' if it exists
		 * This is not needed on recent installations, but don't know why mine got stuck with lowercase
		 */
		/** @var array<string, string> $imdb_data_options Reinitialize the var so the previous get updated */
		$imdb_data_options = get_option( Get_Options_Movie::get_data_tablename() );
		$order_value = $imdb_data_options['imdbwidgetorder'] ?? false;
		$extsites_value = $order_value['extsites'] ?? false;
		if ( isset( $order_value['extsites'] ) && $extsites_value !== false ) {
			unset( $order_value['extsites'] );
			$order_value['extSites'] = $extsites_value;
			if ( $this->lumiere_update_options( Get_Options_Movie::get_data_tablename(), 'imdbwidgetorder', $order_value ) === true ) {
				$text = 'Lumière option $imdbwidgetorder["extSites"] successfully updated.';
				$this->logger->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
			}
		} else {
			$text = 'Lumière option $imdbwidgetorder["extSites"] already exists so no need to be updated.';
			$this->logger->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/** ------------------------- Editing part (end) --------------
		 */
	}
}
