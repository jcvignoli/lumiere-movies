<?php declare( strict_types = 1 );
/**
 * Class of update : Option updates to make according to the current plugin version
 *
 * Use Child classes to process the updates
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       3.0
 * @package       lumieremovies
 */
namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Config\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Plugins\Logger;
use Lumiere\Config\Open_Options;
use Lumiere\Config\Get_Options;

/**
 * The updating rules are in this current parent class, the data to be updated are in child classes (in /updates folder)
 * When is the upate processed
 * (a) A manual update is run {@link Updates::run_update_options()} is triggered when needed {@link \Lumiere\Core::lum_update_needed()} on every visit of admin page
 * (b) On the Lumière plugin activation {@link \Lumiere\Core::lumiere_on_activation()} a cron with update is triggered
 * (c) On WordPress plugin autoupdate {@link \Lumiere\Core::lum_on_plugin_autoupdate()} both an update is run {@link Updates::run_update_options()} and a cron is installed
 * (d) When WordPress manual update is triggerd (by manual click) {@link \Lumiere\Core::lum_on_plugin_manualupdate()} both an update is run {@link Updates::run_update_options()} and a cron is installed
 *
 * In the cron method, it is ensured that the latest update is executed, in addition to the update of the former
 * version (WordPress update uses the replaced plugin version to execute the update, so with this system an update with the previous plugin is executed,
 * then another update with the new plugin)
 *
 * How is the update is processed
 * (a) Calculate the number of files in update folder and formula with {@link \Lumiere\Setting::imdb_admin_values['imdbHowManyUpdates']}
 * (b) Build the class name according to its number in {@link Updates::run_update_options()}
 * (c) Checks in the child class the current Lumière version against the updates and uses {@link \Lumiere\Setting::imdb_admin_values['imdbHowManyUpdates']} var
 * to check if a new updates is available in {@link \Lumiere\Updates::lumiere_check_if_run_update()) (called here from the child class)
 * (d) Everytime an update is run, {@link \Lumiere\Setting::imdb_admin_values['imdbHowManyUpdates']} is increased by 1 in the method
 * ChildClass::lumiere_run_local_update() in the child class
 *
 * @since 4.6.2 Removed some old update classes in update folder, so new mechanism to run updates uses the latest filename number in Update folder
 */
class Updates {

	// Trait including the database settings.
	use Open_Options;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Logger $logger = new Logger( 'updateClass' ),
	) {
		// Get global settings class properties.
		$this->get_db_options(); // In Open_Options trait.
	}

	/**
	 * Main function: Run updates of options
	 *
	 * Use the files in folder class/updates/ to proceed with the update
	 * @since 4.6.1 using WP_Upgrader::create_lock() to avoid running twice, lock lasts 120 seconds or is deleted at the end of the process
	 */
	public function run_update_options(): void {

		$this->logger->log?->debug( '[updateClass] Running updates...' );

		// Make sure it doesn't run twice.
		if ( get_transient( 'lum_update_started' ) === 'locked' ) {
			$this->logger->log?->debug( '[updateClass] Update process already running, exit' );
			return;
		}

		// Count the number of files in class/updates/
		$update_files = glob( LUM_WP_PATH . Get_Options::LUM_UPDATES_PATH . '/Lumiere_Update_File_*.php' );
		$last_update_number_filename = is_array( $update_files ) && count( $update_files ) === 1 ? preg_replace( '/[^0-9]/', '', max( $update_files ) ) : 0;
		$number_updates = intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) - 1;

		if ( $number_updates >= $last_update_number_filename ) {
			$this->logger->log?->debug( '[updateClass] Already up-to-date, exit' );
			return;
		}

		set_transient( 'lum_update_started', 'locked', 240 );

		$this->logger->log?->debug( '[updateClass] Number of updates found: ' . strval( $last_update_number_filename ) );

		// Iteration for each class in class/updates/
		for ( $i = 1; $i <= $last_update_number_filename; $i++ ) {

			if ( $i < $number_updates ) {
				continue;
			}

			// If number has less than two digits, add a leading zero.
			// $iterative_number_with_leading_zero = sprintf( '%02d', $i );

			// Build the class name.
			// $class_name = "Lumiere\Updates\Lumiere_Update_File_{$iterative_number_with_leading_zero}";
			$class_name = "Lumiere\Updates\Lumiere_Update_File_{$i}";

			/**
			 * Execute if class and method exist.
			 * Do not include it into a hook, since it's executed in multiple hooks
			 */
			// @phan-suppress-next-line PhanUndeclaredClassReference (Phan lost with class name)
			if ( class_exists( $class_name ) === true && method_exists( $class_name, 'lumiere_run_local_update' ) === true ) {
				// @phan-suppress-next-line PhanTypeExpectedObjectOrClassName, PhanUndeclaredClass (Phan lost with class name)
				$child_update_class = new $class_name();
				$child_update_class->lumiere_run_local_update();
			}
		}

		delete_transient( 'lum_update_started' );

		$this->logger->log?->debug( '[updateClass] Update process finished' );
	}

	/**
	 * Add option in array of WordPress options
	 * WordPress doesn't know how to handle adding a specific key in a array of options
	 *
	 * @param null|string $option_array : the name of the array of options, such as $imdb_data_option
	 * @param null|string $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 * @param mixed $option_value : the value to add to the key, can be bool, int, array, string
	 *
	 * @return bool true if successful, a notice if missing mandatory parameters; false if option already exists
	 */
	protected function lumiere_add_options( ?string $option_array = null, ?string $option_key = null, mixed $option_value = null ): bool {

		if ( is_null( $option_array ) === true ) {
			$this->logger->log?->error( '[updateClass][lumiere_add_options] Cannot update Lumière options, var array is undefined.' );
			return false;
		}

		if ( is_null( $option_key ) === true ) {
			$this->logger->log?->error( '[updateClass][lumiere_add_options] Cannot update Lumière options, var key is undefined.' );
			return false;
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( false === $check_if_exists ) {

			$option_array_search[ $option_key ] = $option_value;
			update_option( $option_array, $option_array_search );

			$this->logger->log?->info( "[updateClass][lumiere_add_options] Lumière option ($option_key) added." );

			return true;

		}

		$this->logger->log?->error( "[updateClass][lumiere_add_options] Lumière option ($option_key) already exists." );

		return false;
	}

	/**
	 * Update option in array of WordPress options
	 * WordPress doesn't know how to handle updating a specific key in a array of options
	 *
	 * @param null|string $option_array : the array of options, such as $imdb_data_option
	 * @param null|string $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 * @param mixed $option_value : the value to add to the key, can be bool, int or string
	 *
	 * @return bool true if successful, a notice if missing mandatory parameters; false if option already exists
	 */
	protected function lumiere_update_options( ?string $option_array = null, ?string $option_key = null, mixed $option_value = null ): bool {

		if ( is_null( $option_array ) === true ) {
			$this->logger->log?->error( '[updateClass][lumiere_update_options] Cannot update Lumière options, var array is undefined.' );
			return false;
		}

		if ( is_null( $option_key ) === true ) {
			$this->logger->log?->error( '[updateClass][lumiere_update_options] Cannot update Lumière options, var key is undefined.' );
			return false;
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( true === $check_if_exists ) {
			$option_array_search[ $option_key ] = $option_value;
			update_option( $option_array, $option_array_search );

			$this->logger->log?->info( "[updateClass][lumiere_update_options] Lumière option ($option_key) was successfully updated." );

			return true;

		}

		$this->logger->log?->error( "[updateClass][lumiere_update_options] Lumière option ($option_key) was not found." );

		return false;
	}

	/**
	 * Remove option in array of WordPress options
	 * WordPress doesn't know how to handle removing a specific key in a array of options
	 *
	 * @param null|string $option_array : the array of options, such as $imdb_data_option
	 * @param null|string $option_key : the key in the array of options to be removed, such as 'imdbintotheposttheme'
	 *
	 * @return bool true if successful, a notice if missing mandatory parameters; false if option already exists
	 */
	protected function lumiere_remove_options( ?string $option_array = null, ?string $option_key = null ): bool {

		if ( is_null( $option_array ) === true ) {
			$this->logger->log?->error( '[updateClass][lumiere_remove_options] Cannot remove Lumière options, var array is undefined.' );
			return false;
		}

		if ( is_null( $option_key ) === true ) {
			$this->logger->log?->error( '[updateClass][lumiere_remove_options] Cannot remove Lumière options, var key is undefined.' );
			return false;
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( true === $check_if_exists ) {

			unset( $option_array_search[ $option_key ] );
			update_option( $option_array, $option_array_search );

			$this->logger->log?->info( "[updateClass][lumiere_remove_options] Lumière options ($option_key) successfully removed." );

			return true;

		}

		$this->logger->log?->error( "[updateClass][lumiere_remove_options] Cannot remove Lumière options, ($option_key) does not exist." );

		return false;
	}

	/**
	 * Check if we have to actually run the update
	 * Called from updates child class (located in class/updates)
	 * Triggered if conditions specified in constants in child class are met
	 *
	 * @param string $version_update i.e. 3.7
	 * @param int $update_number i.e. 9
	 * @return bool True if the update should happen, false otherwhise
	 *
	 * @since 4.1 casted $this->imdb_admin_values['imdbHowManyUpdates'] to string, which doesn't make sense, but update fails otherwise
	 */
	protected function lumiere_check_if_run_update( string $version_update = '', int $update_number = 0 ): bool {

		// Convert to string so it can be added into debug log.
		$update_number_string = (string) $update_number;

		/**
		 * Check if the update should happen
		 * @psalm-suppress RedundantCastGivenDocblockType -- Correct, imdbHowManyUpdates should be string, but for some unknown reason it is not a string...
		 */
		if (
			/**
			 * Check if the current Lumière version is greater or equal to Lumière version impacted by the child's update
			 */
			version_compare( lum_get_version(), $version_update ) >= 0
			/**
			 * Check if the number of updates already run (saved in database) is equal to child's class update number
			 * The child's class update number will make sure that a sequencial update order is respected when parsing "updates/*.php" files
			 * @phpstan-ignore-next-line -- PHPStan is correct, imdbHowManyUpdates should be string, but for some unknown reason it is not a string...
			 */
			&& ( (string) $this->imdb_admin_values['imdbHowManyUpdates'] === $update_number_string )
		) {

			$this->logger->log?->info( "[updateClass] Update $update_number_string has started" );
			return true;
		}

		//$this->logger->log?->debug( "[updateClass] Update $update_number_string not needed." );
		return false;
	}
}

