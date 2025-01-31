<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 4.2.1, update 18
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
 * @copyright (c) 2024, Lost Highway
 *
 * @package lumiere-movies
 */

namespace Lumiere\Updates;

use Lumiere\Tools\Get_Options;

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
class Lumiere_Update_File_18 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '4.2.1';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 18;

	/**
	 * Run the local update if lumiere_check_if_run_update() was successful
	 * Everytime an update is processed, imdbHowManyUpdates is increased by 1
	 */
	protected function lumiere_run_local_update(): void {

		// Execute the check in Updates parent class, passing the constants.
		// The validating function makes sure that this update has to be run.
		// If not, exit.
		if ( $this->lumiere_check_if_run_update( self::LUMIERE_VERSION_UPDATE, self::LUMIERE_NUMBER_UPDATE ) === false ) {
			return;
		}

		// Update the number of updates already processed in Lumière options.
		$this->logger->log->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) + 1 );

		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', strval( $nb_of_updates ) );

		/** ------------------------- Editing part (beginning) --------------
		 */

		/**
		 * ---------------- Rename imdb* options rows to lumiere_*
		 */
		global $wpdb;

		/**
		 * 1. Rename ADMIN row in wp_options table
		 */
		$old_admin_table = 'imdbAdminOptions';
		$new_admin_table = 'lumiere_admin_options';
		$execute_sql = $wpdb->update( $wpdb->options, [ 'option_name' => $new_admin_table ], [ 'option_name' => $old_admin_table ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( $execute_sql ) {
			$this->logger->log->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Successfully renamed table ' . $old_admin_table . ' to ' . $new_admin_table );
		} elseif ( $execute_sql === 0 && count( get_option( $new_admin_table ) ) > 0 ) {
			$this->logger->log->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Update row ' . $new_admin_table . ' not needed, row already exists.' );
		} else {
			$this->logger->log->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Failed to rename table ' . $old_admin_table . ' to ' . $new_admin_table . ' Last DB error: ' . $wpdb->last_error );
		}

		/**
		 * 2. Rename Data/Widget row in wp_options table
		 */
		$old_data_table = 'imdbWidgetOptions';
		$new_data_table = 'lumiere_data_options';
		$execute_sql = $wpdb->update( $wpdb->options, [ 'option_name' => $new_data_table ], [ 'option_name' => $old_data_table ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( $execute_sql ) {
			$this->logger->log->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Successfully renamed table ' . $old_data_table . ' to ' . $new_data_table );
		} elseif ( $execute_sql === 0 && count( get_option( $new_data_table ) ) > 0 ) {
			$this->logger->log->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Update row ' . $new_data_table . ' not needed, row already exists.' );
		} else {
			$this->logger->log->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Failed to rename table ' . $old_data_table . ' to ' . $new_data_table . ' Last DB error: ' . $wpdb->last_error );

		}

		/**
		 * 3. Rename Cache row in wp_options table
		 */
		$old_cache_table = 'imdbCacheOptions';
		$new_cache_table = 'lumiere_cache_options';
		$execute_sql = $wpdb->update( $wpdb->options, [ 'option_name' => $new_cache_table ], [ 'option_name' => $old_cache_table ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( $execute_sql ) {
			$this->logger->log->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Successfully renamed table ' . $old_cache_table . ' to ' . $new_cache_table );
		} elseif ( $execute_sql === 0 && count( get_option( $new_cache_table ) ) > 0 ) {
			$this->logger->log->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Update row ' . $new_cache_table . ' not needed, row already exists.' );
		} else {
			$this->logger->log->info( '[Lumiere][updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Failed to rename table ' . $old_cache_table . ' to ' . $new_cache_table . ' Last DB error: ' . $wpdb->last_error );
		}

		/** ------------------------- Editing part (end) --------------
		 */

	}

}
