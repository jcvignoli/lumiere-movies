<?php declare( strict_types = 1 );
/**
 * Class of update : Option updates to make according to the current plugin version
 *
 * Use Child classes to process the updates
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 3.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Plugins\Logger;
use FilesystemIterator;

/**
 * Parent class Updates
 * The logic is in the parent class, the data in child classes
 *
 *  -> Checks the current Lumière version against the updates and uses $config_class->imdb_admin_values['imdbHowManyUpdates'] var to know if new updates have to be made in lumiere_check_if_run_update()
 *  -> Everytime an update is processed, imdbHowManyUpdates is increased by 1 (in child class)
 */
class Updates {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * \Lumiere\Logger class
	 *
	 */
	protected Logger $logger;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

		// Start Logger class.
		$this->logger = new Logger( 'updateClass' );

	}

	/**
	 * Main function: Run updates of options
	 *
	 * Use the files in folder class/updates/ to proceed with the update
	 */
	public function run_update_options(): void {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		// Debug info
		$logger->debug( '[Lumiere][updateClass] Running updates...' );

		// Count the number of files in class/updates/
		$files = new FilesystemIterator( plugin_dir_path( __DIR__ ) . 'class/updates/', FilesystemIterator::SKIP_DOTS );
		$nb_of_files_in_updates_folder = intval( iterator_count( $files ) );

		$logger->debug( '[Lumiere][updateClass] Number of updates found: ' . $nb_of_files_in_updates_folder );

		// Iteration for each class in class/updates/
		for ( $i = 1; $i <= $nb_of_files_in_updates_folder; $i++ ) {

			// If number has less than two digits, add a leading zero.
			$iterative_number_with_leading_zero = sprintf( '%02d', $i );

			// Build the class name.
			$class_name_iterative = "Lumiere\Updates\Lumiere_Update_File_{$iterative_number_with_leading_zero}";

			// Execute if class exists.
			if ( true === class_exists( $class_name_iterative ) ) {
				$fake_var_for_execute_class = new $class_name_iterative();
			}
		}

	}

	/**
	 * Add option in array of WordPress options
	 * WordPress doesn't know how to handle adding a specific key in a array of options
	 *
	 * @param null|string $option_array : the name of the array of options, such as $config_class->imdbWidgetOptionsName
	 * @param null|string $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 * @param mixed $option_value : the value to add to the key, can be bool, int, array, string
	 *
	 * @return bool true if successful, a notice if missing mandatory parameters; false if option already exists
	 */
	protected function lumiere_add_options( ?string $option_array = null, ?string $option_key = null, mixed $option_value = null ): bool {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		if ( is_null( $option_array ) === true ) {
			$logger->error( '[Lumiere][updateClass][lumiere_add_options] Cannot update Lumière options, var array is undefined.' );
			return false;
		}

		if ( is_null( $option_key ) === true ) {
			$logger->error( '[Lumiere][updateClass][lumiere_add_options] Cannot update Lumière options, var key is undefined.' );
			return false;
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( false === $check_if_exists ) {

			$option_array_search[ $option_key ] = $option_value;
			update_option( $option_array, $option_array_search );

			$logger->info( "[Lumiere][updateClass][lumiere_add_options] Lumière option ($option_key) added." );

			return true;

		}

		$logger->error( "[Lumiere][updateClass][lumiere_add_options] Lumière option ($option_key) already exists." );

		return false;

	}

	/**
	 * Update option in array of WordPress options
	 * WordPress doesn't know how to handle updating a specific key in a array of options
	 *
	 * @param null|string $option_array : the array of options, such as $config_class->imdbWidgetOptionsName
	 * @param null|string $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 * @param mixed $option_value : the value to add to the key, can be bool, int or string
	 *
	 * @return bool true if successful, a notice if missing mandatory parameters; false if option already exists
	 */
	protected function lumiere_update_options( ?string $option_array = null, ?string $option_key = null, mixed $option_value = null ): bool {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		if ( is_null( $option_array ) === true ) {
			$logger->error( '[Lumiere][updateClass][lumiere_update_options] Cannot update Lumière options, var array is undefined.' );
			return false;
		}

		if ( is_null( $option_key ) === true ) {
			$logger->error( '[Lumiere][updateClass][lumiere_update_options] Cannot update Lumière options, var key is undefined.' );
			return false;
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( true === $check_if_exists ) {
			$option_array_search[ $option_key ] = $option_value;
			update_option( $option_array, $option_array_search );

			$logger->info( "[Lumiere][updateClass][lumiere_update_options] Lumière option ($option_key) was successfully updated." );

			return true;

		}

		$logger->error( "[Lumiere][updateClass][lumiere_update_options] Lumière option ($option_key) was not found." );

		return false;

	}

	/**
	 * Remove option in array of WordPress options
	 * WordPress doesn't know how to handle removing a specific key in a array of options
	 *
	 * @param null|string $option_array : the array of options, such as $config_class->imdbWidgetOptionsName
	 * @param null|string $option_key : the key in the array of options to be removed, such as 'imdbintotheposttheme'
	 *
	 * @return bool true if successful, a notice if missing mandatory parameters; false if option already exists
	 */
	protected function lumiere_remove_options( ?string $option_array = null, ?string $option_key = null ): bool {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		if ( is_null( $option_array ) === true ) {
			$logger->error( '[Lumiere][updateClass][lumiere_remove_options] Cannot remove Lumière options, var array is undefined.' );
			return false;
		}

		if ( is_null( $option_key ) === true ) {
			$logger->error( '[Lumiere][updateClass][lumiere_remove_options] Cannot remove Lumière options, var key is undefined.' );
			return false;
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( true === $check_if_exists ) {

			unset( $option_array_search[ $option_key ] );
			update_option( $option_array, $option_array_search );

			$logger->info( "[Lumiere][updateClass][lumiere_remove_options] Lumière options ($option_key) successfully removed." );

			return true;

		}

		$logger->error( "[Lumiere][updateClass][lumiere_remove_options] Cannot remove Lumière options, ($option_key) does not exist." );

		return false;

	}

	/**
	 * Check if we have to actually run the update
	 * Called from updates child class (located in class/updates)
	 * Triggered if conditions specified in constants in child class are met
	 *
	 * @param string $version_update i.e. 3.7
	 * @param int $number_of_updates i.e. 9
	 * @return bool True if the update should happen, false otherwhise
	 */
	protected function lumiere_check_if_run_update( string $version_update = '', int $number_of_updates = 0 ): bool {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );

		// Check if the update should happen
		if (
			/**
			 * Check if the current Lumière version is greater or equal to Lumière version impacted by the child's update
			 */
			version_compare( $this->config_class->lumiere_version, $version_update ) >= 0
			/**
			 * Check if the number of updates already run (saved in database) is equal to child's class update number
			 * The child's class update number will make sure that a sequencial update order is respected when parsing "updates/*.php" files
			 */
			&& ( $this->imdb_admin_values['imdbHowManyUpdates'] === strval( $number_of_updates ) )
		) {

			$this->logger->log()->debug( "[Lumiere][updateClass] Update $number_of_updates has started" );
			return true;
		}

		$this->logger->log()->debug( "[Lumiere][updateClass] Update $number_of_updates not needed." );
		return false;
	}
}

