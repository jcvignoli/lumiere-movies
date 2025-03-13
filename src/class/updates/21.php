<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 4.5, update 21
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
class Lumiere_Update_File_21 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '4.5';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 21;

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
		$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) + 1 );
		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', $nb_of_updates );

		/** ------------------------- Editing part (beginning) --------------
		 */

		$imdb_data_options = get_option( Get_Options::get_data_tablename() );

		/**
		 * Change 'prodcompany' to 'prodCompany' to LUM_DATA_OPTIONS
		 * Change the name var to match imdbphp library method names, retrieving the saved value and setting the new value with it
		 */
		$prodcompany_value = $imdb_data_options['imdbwidgetprodcompany'] ?? false;
		if ( $prodcompany_value !== false && true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetprodcompany' ) ) {
			$text = 'Lumière option imdbwidgetprodcompany successfully removed.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetprodcompany could not be removed.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgetprodCompany', $prodcompany_value ) ) {
			$text = 'Lumière option prodCompany successfully added.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option prodCompany could not be added.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		// Update imdbwidgetorder prodcompany
		$order_value = $imdb_data_options['imdbwidgetorder'] ?? false;
		$order_value['prodCompany'] = $order_value !== false && isset( $order_value['prodcompany'] ) ? $order_value['prodcompany'] : false;
		unset( $order_value['prodcompany'] );
		if ( $order_value['prodCompany'] !== false && true === $this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetorder', $order_value ) ) {
			$text = 'Lumière option imdbwidgetorder successfully updated.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetorder could not be updated.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Change 'officialsites' to 'extSites' to LUM_DATA_OPTIONS
		 * Change the name var to match imdbphp library method names, retrieving the saved value and setting the new value with it
		 */
		$extsites_value = $imdb_data_options['imdbwidgetofficialsites'] ?? false;
		if ( $extsites_value !== false && true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetofficialsites' ) ) {
			$text = 'Lumière option imdbwidgetofficialsites successfully removed.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetofficialsites could not be removed.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgetextSites', $extsites_value ) ) {
			$text = 'Lumière option imdbwidgetextSites successfully added.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option officialsites could not be added.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		// Update imdbwidgetorder officialsites to extSites
		$order_value = $imdb_data_options['imdbwidgetorder'] ?? false;
		$order_value['extSites'] = $order_value !== false && isset( $order_value['officialsites'] ) ? $order_value['officialsites'] : false;
		unset( $order_value['officialsites'] );
		if ( true === $this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetorder', $order_value ) ) {
			$text = 'Lumière option imdbwidgetorder successfully updated.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetorder could not be updated.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Change 'imdbwidgetcreator' to 'imdbwidgetcinematographer' to LUM_DATA_OPTIONS
		 * Change the name var to match imdbphp library method names, retrieving the saved value and setting the new value with it
		 */
		$cinematographer_value = $imdb_data_options['imdbwidgetcreator'] ?? false;
		if ( $cinematographer_value !== false && true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetcreator' ) ) {
			$text = 'Lumière option imdbwidgetcreator successfully removed.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetcreator could not be removed.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgetcinematographer', $cinematographer_value ) ) {
			$text = 'Lumière option imdbwidgetcinematographer successfully added.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetcinematographer could not be added.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		// Update imdbwidgetorder creator to cinematographer
		$order_value = $imdb_data_options['imdbwidgetorder'] ?? false;
		$order_value['cinematographer'] = $order_value !== false && isset( $order_value['creator'] ) ? $order_value['creator'] : '18';
		unset( $order_value['creator'] );
		if ( $order_value !== false && true === $this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetorder', $order_value ) ) {
			$text = 'Lumière option imdbwidgetorder successfully updated.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetorder could not be updated.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		// Update imdbtaxonomycinematographer
		$cinematographertaxo_value = $imdb_data_options['imdbtaxonomycreator'] ?? false;
		if ( $cinematographertaxo_value !== false && true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbtaxonomycreator' ) ) {
			$text = 'Lumière option imdbtaxonomycreator successfully removed.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbtaxonomycreator could not be removed.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbtaxonomycinematographer', $cinematographertaxo_value ) ) {
			$text = 'Lumière option imdbtaxonomycinematographer successfully added.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbtaxonomycinematographer could not be added.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * Add 'imdbwidgettrivia' to LUM_DATA_OPTIONS
		 */
		// Trivia widget
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgettrivia', '0' ) ) {
			$text = 'Lumière option imdbwidgettrivia successfully added.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgettrivia could not be added.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		// Trivia number
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgettrivianumber', '3' ) ) {
			$text = 'Lumière option imdbwidgettrivianumber successfully added.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgettrivianumber could not be added.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		// Create imdbwidgetorder with trivia if doesn't exist (it shouldn't)
		$order_value = $imdb_data_options['imdbwidgetorder'] ?? false;
		$order_value['cinematographer'] = $order_value !== false && isset( $order_value['trivia'] ) ? $order_value['trivia'] : '27';
		if ( $order_value !== false && true === $this->lumiere_update_options( Get_Options::get_data_tablename(), 'imdbwidgetorder', $order_value ) ) {
			$text = 'Lumière option imdbwidgetorder (trivia) successfully updated.';
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = 'Lumière option imdbwidgetorder (trivia) could not be updated.';
			$this->logger->log->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/** ------------------------- Editing part (end) --------------
		 */
	}
}

