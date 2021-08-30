<?php declare( strict_types = 1 );
/**
 * Class of update : Option updates to make according to the current plugin version
 *  -> Always put a version earlier for updates,
 *  as WordPress checks with previous version.
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
use \Lumiere\Utils;
use \Lumiere\Logger;

/**
 * Uses the files in /updates/ to updates the database
 * Checks the current Lumière version against the updates and uses $configClass->imdb_admin_values['imdbHowManyUpdates'] var to know if new updates have to be made
 * Everytime an update is processed, imdbHowManyUpdates increases of 1
 *
 * Main external vars/functions:
 * @$configClass->lumiere_version: get extracted from class.config.php
 * @$utilsClass->lumiere_activate_debug(): activate the debugging options
 * @$configClass->lumiere_start_logger(): run the logger class
 */
class Update_Options {

	/* \Lumiere\Settings class
	 *
	 */
	private Settings $configClass;

	/**
	 * \Lumiere\Logger class
	 *
	 */
	private Logger $logger;

	/**
	 * Admin options from database
	 * @var array<string> $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 * \Lumiere\Utils class
	 *
	 */
	private Utils $utilsClass;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Load all classes in class/updates folder, will be loaded when needed
		// @TODO: make this operational
		// spl_autoload_register( [ 'Lumiere\UpdateOptions', 'updates_files_loader' ] );

		// Get database options.
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );

		// Start the settings class
		$this->configClass = new Settings();

		// Start Logger class.
		$this->logger = new Logger( 'updateClass' );

		// Start the Utils class
		$this->utilsClass = new Utils();

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
	private function run_update_options() {

		/* VARS */
		$output = '';
		$imdb_admin_values = $this->imdb_admin_values;
		$configClass = $this->configClass;

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		// Debug info
		$logger->debug( '[Lumiere][updateOptions] Running updates...' );

		// @TODO: transform those files into classes and use spl_autoload_register to load them all

		/************************************************** 3.6 */
		if ( ( version_compare( $this->configClass->lumiere_version, '3.6' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 8 ) ) {              # update 8

			require_once 'updates/8.php';

			$logger->debug( '[Lumiere][updateOptions] Update 8 has been run.' );

		}

		/************************************************** 3.5 */
		if ( ( version_compare( $this->configClass->lumiere_version, '3.5' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 7 ) ) {              # update 7

			require_once 'updates/7.php';

			$logger->debug( '[Lumiere][updateOptions] Update 7 has been run.' );

		}

		/************************************************** 3.4.3 */
		if ( ( version_compare( $this->configClass->lumiere_version, '3.4.3' ) >= 0 )
			&& ( $this->imdb_admin_values['imdbHowManyUpdates'] === 6 ) ) {                # update 6

			require_once 'updates/6.php';

			$logger->debug( '[Lumiere][updateOptions] Update 6 has been run.' );

		}
		/************************************************** 3.4.2 */
		if ( ( version_compare( $this->configClass->lumiere_version, '3.4.2' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 5 ) ) {              # update 5

			require_once 'updates/5.php';

			$logger->debug( '[Lumiere][updateOptions] Update 5 has been run.' );

		}
		/************************************************** 3.4 */
		if ( ( version_compare( $configClass->lumiere_version, '3.4' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 4 ) ) {              # update 4

			require_once 'updates/4.php';

			$logger->debug( '[Lumiere][updateOptions] Update 4 has been run.' );

		}

		/************************************************** 3.3.4 */

		if ( ( version_compare( $this->configClass->lumiere_version, '3.3.4' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 3 ) ) {              # update 3

			require_once 'updates/3.php';

			$logger->debug( '[Lumiere][updateOptions] Update 3 has been run.' );

		}

		/************************************************** 3.3.3 */

		if ( ( version_compare( $configClass->lumiere_version, '3.3.3' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 2 ) ) {              # update 2

			require_once 'updates/2.php';

			$logger->debug( '[Lumiere][updateOptions] Update 2 has been run.' );

		}

		/************************************************** 3.3.1 */

		if ( ( version_compare( $configClass->lumiere_version, '3.3.1' ) >= 0 )
			&& ( $imdb_admin_values['imdbHowManyUpdates'] === 1 ) ) {              # update 1

			require_once 'updates/1.php';

			$logger->debug( '[Lumiere][updateOptions] Update 1 has been run.' );

		}

	}

	/**
	 * Add option in array of WordPress options
	 * WordPress doesn't know how to handle adding a specific key in a array of options
	 *
	 * @parameter mandatory $option_array : the array of options, such as $configClass->imdbWidgetOptionsName
	 * @parameter mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 * @parameter optional $option_key : the value to add to the key, NULL if not specified
	 *
	 * returns text if successful, a notice if missing mandatory parameters,FALSE if option already exists
	 */
	public function lumiere_add_options( $option_array = null, $option_key = null, $option_value = null ) {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		if ( ! isset( $option_array ) ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_add_options] Cannot update Lumière options, ($option_array) is undefined." );
		}

		if ( ! isset( $option_key ) ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_add_options] Cannot update Lumière options, ($option_key) is undefined." );
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( false === $check_if_exists ) {
			$option_array_search[ $option_key ] = $option_value;
			update_option( $option_array, $option_array_search );

			$logger->info( "[Lumiere][updateOptions][lumiere_add_options] Lumière option ($option_key) added." );

			return true;

		} else {

			$logger->error( "[Lumiere][updateOptions][lumiere_add_options] Lumière option ($option_key) already exists." );

		}

		return false;

	}

	/**
	 * Update option in array of WordPress options
	 * WordPress doesn't know how to handle updating a specific key in a array of options
	 *
	 * @parameter mandatory $option_array : the array of options, such as $configClass->imdbWidgetOptionsName
	 * @parameter mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 * @parameter optional $option_key : the value to add to the key, NULL if not specified
	 *
	 * returns text if successful, a notice if missing mandatory parameters, FALSE if option already exists
	 */
	public function lumiere_update_options( $option_array = null, $option_key = null, $option_value = null ) {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		if ( ! isset( $option_array ) ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_update_options] Cannot update Lumière options, ($option_array) is undefined." );
		}

		if ( ! isset( $option_key ) ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_update_options] Cannot update Lumière options, ($option_key) is undefined." );
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( true === $check_if_exists ) {
			$option_array_search[ $option_key ] = $option_value;
			update_option( $option_array, $option_array_search );

			$logger->info( "[Lumiere][updateOptions][lumiere_update_options] Lumière option ($option_key) was successfully updated." );

			return true;

		} else {

			$logger->error( "[Lumiere][updateOptions][lumiere_update_options] Lumière option ($option_key) was not found." );

		}

		return false;

	}

	/**
	 * Remove option in array of WordPress options
	 * WordPress doesn't know how to handle removing a specific key in a array of options
	 *
	 * @param mandatory $option_array : the array of options, such as $configClass->imdbWidgetOptionsName
	 * @param mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 *
	 * @return bool TRUE if successful, a notice if missing mandatory parameters, FALSE if option is not found
	 */
	public function lumiere_remove_options( $option_array = null, $option_key = null ) {

		// Manually Activate logging, since current function is run before WP init
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		if ( ! isset( $option_array ) ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_remove_options] Cannot remove Lumière options, ($option_array) is undefined." );
		}

		if ( ! isset( $option_key ) ) {
			$logger->error( "[Lumiere][updateOptions][lumiere_remove_options] Cannot remove Lumière options, ($option_key) is undefined." );
		}

		$option_array_search = get_option( $option_array );
		$check_if_exists = array_key_exists( $option_key, $option_array_search );

		if ( true === $check_if_exists ) {

			unset( $option_array_search[ $option_key ] );
			update_option( $option_array, $option_array_search );

			$logger->info( "[Lumiere][updateOptions][lumiere_remove_options] Lumière options ($option_key) successfully removed." );

			return true;

		} else {

			$logger->error( "[Lumiere][updateOptions][lumiere_remove_options] Cannot remove Lumière options, ($option_key) does not exist." );

		}

		return false;

	}

	/**
	 * Loads all files included in updates/
	 * Loaded in spl_autoload_register()
	 * @TODO: Not yet operational, files must be transformed into classes
	 *
	 */
	public static function updates_files_loader ( $class_name ) {

		// Remove 'Lumiere' and transforms '\' into '/'
		$class_name = str_replace( 'Lumiere/', '', str_replace( '\\', '/', ltrim( $class_name, '\\' ) ) );

		// Path for inclusion
		$path_to_file = plugin_dir_path( __DIR__ ) . 'class/updates/' . $class_name . '.php';

		if ( file_exists( $path_to_file ) ) {

			require $path_to_file;

		}

	}
}

