<?php declare( strict_types = 1 );
/**
 * Class of update : Option updates to make according to the current plugin version
 *  -> Always put a version earlier for updates,
 *  as WordPress checks against previous version.
 *  -> progressive increment of the updates,
 *  using $imdb_admin_values['imdbHowManyUpdates']
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 2.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Settings;
use \Lumiere\Logger;

/**
 *
 * Uses the files in /updates/ to updates the database
 * Checks the current Lumière version against the updates and uses $config_class->imdb_admin_values['imdbHowManyUpdates'] var to know if new updates have to be made
 * Everytime an update is processed, imdbHowManyUpdates increases of 1
 *
 */
class Update_Options {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * \Lumiere\Logger class
	 *
	 */
	private Logger $logger;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Load all classes in class/updates folder, will be loaded when needed
		// @TODO: make this operational
		// spl_autoload_register( [ 'Lumiere\UpdateOptions', 'updates_files_loader' ] );

		// Construct Global Settings trait.
		$this->settings_open();

		// Start Logger class.
		$this->logger = new Logger( 'updateClass' );

		// Execute the options update
		$this->run_update_options();

	}

	/**
	 * Main function: Run updates of options
	 * add/remove/update options
	 * Uses the files in folder updates to proceed with the update
	 *
	 * logger feedback if debugging is activated
	 */
	private function run_update_options(): void {

		/* VARS */
		$output = '';
		$imdb_admin_values = $this->imdb_admin_values;
		$config_class = $this->config_class;

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		// Debug info
		$logger->debug( '[Lumiere][updateOptions] Running updates...' );

		// @TODO: transform those files into classes and use spl_autoload_register to load them all

		/************************************************** 3.6 */
		if ( ( version_compare( $this->config_class->lumiere_version, '3.6' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 8 ) ) {              # update 8

			require_once 'updates/8.php';

			$logger->debug( '[Lumiere][updateOptions] Update 8 has been run.' );

		}

		/************************************************** 3.5 */
		if ( ( version_compare( $this->config_class->lumiere_version, '3.5' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 7 ) ) {              # update 7

			require_once 'updates/7.php';

			$logger->debug( '[Lumiere][updateOptions] Update 7 has been run.' );

		}

		/************************************************** 3.4.3 */
		if ( ( version_compare( $this->config_class->lumiere_version, '3.4.3' ) >= 0 )
			&& ( $this->imdb_admin_values['imdbHowManyUpdates'] === 6 ) ) {                # update 6

			require_once 'updates/6.php';

			$logger->debug( '[Lumiere][updateOptions] Update 6 has been run.' );

		}
		/************************************************** 3.4.2 */
		if ( ( version_compare( $this->config_class->lumiere_version, '3.4.2' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 5 ) ) {              # update 5

			require_once 'updates/5.php';

			$logger->debug( '[Lumiere][updateOptions] Update 5 has been run.' );

		}
		/************************************************** 3.4 */
		if ( ( version_compare( $config_class->lumiere_version, '3.4' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 4 ) ) {              # update 4

			require_once 'updates/4.php';

			$logger->debug( '[Lumiere][updateOptions] Update 4 has been run.' );

		}

		/************************************************** 3.3.4 */

		if ( ( version_compare( $this->config_class->lumiere_version, '3.3.4' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 3 ) ) {              # update 3

			require_once 'updates/3.php';

			$logger->debug( '[Lumiere][updateOptions] Update 3 has been run.' );

		}

		/************************************************** 3.3.3 */

		if ( ( version_compare( $config_class->lumiere_version, '3.3.3' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 2 ) ) {              # update 2

			require_once 'updates/2.php';

			$logger->debug( '[Lumiere][updateOptions] Update 2 has been run.' );

		}

		/************************************************** 3.3.1 */

		if ( ( version_compare( $config_class->lumiere_version, '3.3.1' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 1 ) ) {              # update 1

			require_once 'updates/1.php';

			$logger->debug( '[Lumiere][updateOptions] Update 1 has been run.' );

		}

	}

	/**
	 * Add option in array of WordPress options
	 * WordPress doesn't know how to handle adding a specific key in a array of options
	 *
	 * @param string $option_array : the name of the array of options, such as $config_class->imdbWidgetOptionsName
	 * @param string $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 * @param mixed[] $option_value : the value to add to the key, can be bool, int or string
	 *
	 * @return bool true if successful, a notice if missing mandatory parameters; false if option already exists
	 */
	public function lumiere_add_options( string $option_array = null, string $option_key = null, $option_value = null ): bool {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		if ( is_null( $option_array ) === true ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_add_options] Cannot update Lumière options, ($option_array) is undefined." );
			return false;
		}

		if ( is_null( $option_key ) === true ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_add_options] Cannot update Lumière options, ($option_key) is undefined." );
			return false;
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( false === $check_if_exists ) {

			$option_array_search[ $option_key ] = $option_value;
			update_option( $option_array, $option_array_search );

			$logger->info( "[Lumiere][updateOptions][lumiere_add_options] Lumière option ($option_key) added." );

			return true;

		}

		$logger->error( "[Lumiere][updateOptions][lumiere_add_options] Lumière option ($option_key) already exists." );

		return false;

	}

	/**
	 * Update option in array of WordPress options
	 * WordPress doesn't know how to handle updating a specific key in a array of options
	 *
	 * @param string $option_array : the array of options, such as $config_class->imdbWidgetOptionsName
	 * @param string $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 * @param bool|string|int|array<int|string, string> $option_value : the value to add to the key, can be bool, int or string
	 *
	 * @return bool true if successful, a notice if missing mandatory parameters; false if option already exists
	 */
	public function lumiere_update_options( string $option_array = null, string $option_key = null, $option_value = null ): bool {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		if ( is_null( $option_array ) === true ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_update_options] Cannot update Lumière options, ($option_array) is undefined." );
			return false;
		}

		if ( is_null( $option_key ) === true ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_update_options] Cannot update Lumière options, ($option_key) is undefined." );
			return false;
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( true === $check_if_exists ) {
			$option_array_search[ $option_key ] = $option_value;
			update_option( $option_array, $option_array_search );

			$logger->info( "[Lumiere][updateOptions][lumiere_update_options] Lumière option ($option_key) was successfully updated." );

			return true;

		}

		$logger->error( "[Lumiere][updateOptions][lumiere_update_options] Lumière option ($option_key) was not found." );

		return false;

	}

	/**
	 * Remove option in array of WordPress options
	 * WordPress doesn't know how to handle removing a specific key in a array of options
	 *
	 * @param string $option_array : the array of options, such as $config_class->imdbWidgetOptionsName
	 * @param string $option_key : the key in the array of options to be removed, such as 'imdbintotheposttheme'
	 *
	 * @return bool true if successful, a notice if missing mandatory parameters; false if option already exists
	 */
	public function lumiere_remove_options( string $option_array = null, string $option_key = null ): bool {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		if ( is_null( $option_array ) === true ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_remove_options] Cannot remove Lumière options, ($option_array) is undefined." );
			return false;
		}

		if ( is_null( $option_key ) === true ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_remove_options] Cannot remove Lumière options, ($option_key) is undefined." );
			return false;
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( true === $check_if_exists ) {

			unset( $option_array_search[ $option_key ] );
			update_option( $option_array, $option_array_search );

			$logger->info( "[Lumiere][updateOptions][lumiere_remove_options] Lumière options ($option_key) successfully removed." );

			return true;

		}

		$logger->error( "[Lumiere][updateOptions][lumiere_remove_options] Cannot remove Lumière options, ($option_key) does not exist." );

		return false;

	}

	/**
	 * Loads all files included in updates/
	 * Loaded in spl_autoload_register()
	 *
	 * @param string $class_name Class name automagically retrieved from spl_autoload_register()
	 *
	 * @TODO: Not yet operational, files must be transformed into classes
	 */
	public static function updates_files_loader ( string $class_name ): void {

		$parts = explode( '\\', $class_name );
		$class = 'class-' . strtolower( array_pop( $parts ) );
		$folder = strtolower( implode( DIRECTORY_SEPARATOR, $parts ) );
		$folder_cleaned = str_replace( 'lumiere/', '', $folder );

		// Final path for inclusion
		$classpath = plugin_dir_path( __DIR__ ) . 'class' . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . $folder_cleaned . DIRECTORY_SEPARATOR . $class . '.php';

		if ( file_exists( $classpath ) ) {

			require $classpath;

		}

	}
}

