<?php

/**
 * Class of update : Option updates to make according to the current plugin version
 *	-> Always put a version earlier for updates,
 *	as Wordpress checks with previous version.
 *	-> progressive increment of the updates,
 *	using $imdb_admin_values['imdbHowManyUpdates'] 
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
	wp_die('You can not call directly this page');

/** Class to update Lumière options 
 ** Uses the files in /updates/ to updates the database
 ** Checks the current Lumière version against the updates and uses $configClass->imdb_admin_values['imdbHowManyUpdates'] var to know if new updates have to be made
 ** Everytime an update is processed, imdbHowManyUpdates increases of 1
 **
 ** Main external vars/functions:
 ** @$configClass->lumiere_version: get extracted from class.config.php 
 ** @$utilsClass->lumiere_activate_debug(): activate the debugging options
 ** @$configClass->lumiere_start_logger(): run the logger class
 ** @$configClass->lumiere_maybe_log(): write/display a log of events if conditions are met
 **/
class UpdateOptions {

	/* \Lumiere\Settings class
	 * 
	 */
	private $configClass;

	private $imdb_admin_values;

	/* \Lumiere\Utils class
	 * 
	 * 
	 */
	private $utilsClass;

	function __construct() {

		if (class_exists("\Lumiere\Settings")) {

			// Start the settings class
			$this->configClass = new \Lumiere\Settings('updateClass');
			$this->imdb_admin_values = $this->configClass->get_imdb_admin_option();

			// Start the Utils class 
			$this->utilsClass = new \Lumiere\Utils();

		} else {
			wp_die('Lumière files have been moved. Can not update Lumière!');
		}

		// add_filter ( 'wp_head', [ $this, 'runUpdateOptions' ], 0); # executes on every frontpage, but now uses cron instead
		$this->runUpdateOptions();

	}

	/*** Main function: Run updates of options
	 *** add/remove/update options 
	 ** Uses the files in folder updates to proceed with the update
	 **
	 ** logger feedback if debugging is activated
	 **/
	function runUpdateOptions() {

		/* VARS */
		$output = "";
		$imdb_admin_values = $this->imdb_admin_values;
		$configClass = $this->configClass;

		// Manually Activate logging, since current function is run before WP init
		$this->configClass->lumiere_start_logger('updateClass');
		$logger = $this->configClass->loggerclass;

		// Debug info
		$logger->debug("[Lumiere][updateOptions] Running updates...");

		/************************************************** 3.5 */
		if ( (version_compare( $this->configClass->lumiere_version, "3.5" ) >= 0 )
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 8 ) ){				# update 8

			require_once('updates/8.php');

			$logger->debug("[Lumiere][updateOptions] Update 8 has been run.");

		}

		/************************************************** 3.5 */
		if ( (version_compare( $this->configClass->lumiere_version, "3.5" ) >= 0 )
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 7 ) ){				# update 7

			require_once('updates/7.php');

			$logger->debug("[Lumiere][updateOptions] Update 7 has been run.");

		}

		/************************************************** 3.4.3 */
		if ( (version_compare( $this->configClass->lumiere_version, "3.4.3" ) >= 0 )
			&& ($this->imdb_admin_values['imdbHowManyUpdates'] == 6 ) ){				# update 6

			require_once('updates/6.php');

			$logger->debug("[Lumiere][updateOptions] Update 6 has been run.");

		}
		/************************************************** 3.4.2 */
		if ( (version_compare( $this->configClass->lumiere_version, "3.4.2" ) >= 0 )
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 5 ) ){				# update 5

			require_once('updates/5.php');

			$logger->debug("[Lumiere][updateOptions] Update 5 has been run.");

		}
		/************************************************** 3.4 */
		if ( (version_compare( $configClass->lumiere_version, "3.4" ) >= 0 )
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 4 ) ){				# update 4

			require_once('updates/4.php');

			$logger->debug("[Lumiere][updateOptions] Update 4 has been run.");

		}

		/************************************************** 3.3.4 */

		if ( (version_compare( $this->configClass->lumiere_version, "3.3.4" ) >= 0 )
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 3 ) ){ 				# update 3

			require_once('updates/3.php');

			$logger->debug("[Lumiere][updateOptions] Update 3 has been run.");

		}
		
		/************************************************** 3.3.3 */

		if ( (version_compare( $configClass->lumiere_version, "3.3.3" ) >= 0 ) 
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 2 ) ){				# update 2

			require_once('updates/2.php');

			$logger->debug("[Lumiere][updateOptions] Update 2 has been run.");

		}

		/************************************************** 3.3.1 */

		if ( (version_compare( $configClass->lumiere_version, "3.3.1" ) >= 0 )
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 1 ) ){				# update 1

			require_once('updates/1.php');

			$logger->debug("[Lumiere][updateOptions] Update 1 has been run.");

		}


	}

	/*** Add option in array of WordPress options
	 *** WordPress doesn't know how to handle adding a specific key in a array of options
	 **
	 ** @parameter mandatory $option_array : the array of options, such as $configClass->imdbWidgetOptionsName
	 ** @parameter mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 ** @parameter optional $option_key : the value to add to the key, NULL if not specified
	 **
	 ** returns text if successful, a notice if missing mandatory parameters,FALSE if option already exists
	 **/
	function lumiere_add_options($option_array=NULL,$option_key=NULL,$option_value=NULL) {

		// Manually Activate logging, since current function is run before WP init
		$this->configClass->lumiere_start_logger('updateClass');
		$logger = $this->configClass->loggerclass;

		if (!isset($option_array)) 
			$logger->error("[Lumiere][updateOptions][lumiere_add_options] Cannot update Lumière options, ($option_array) is undefined.");

		if (!isset($option_key)) 
			$logger->error("[Lumiere][updateOptions][lumiere_add_options] Cannot update Lumière options, ($option_key) is undefined.");

		$option_array_search = get_option($option_array);
		$check_if_exists = array_key_exists ($option_key, $option_array_search);

		if ( FALSE === $check_if_exists) {
			$option_array_search[$option_key] = $option_value;
			update_option($option_array, $option_array_search);

			$logger->info("[Lumiere][updateOptions][lumiere_add_options] Lumière option ($option_key) added.");

			return true;

		} else {

			$logger->error("[Lumiere][updateOptions][lumiere_add_options] Lumière option ($option_key) already exists.");

		}

		return false;

	}

	/*** Update option in array of WordPress options
	 *** WordPress doesn't know how to handle updating a specific key in a array of options
	 **
	 ** @parameter mandatory $option_array : the array of options, such as $configClass->imdbWidgetOptionsName
	 ** @parameter mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 ** @parameter optional $option_key : the value to add to the key, NULL if not specified
	 **
	 ** returns text if successful, a notice if missing mandatory parameters, FALSE if option already exists
	 **/
	function lumiere_update_options($option_array=NULL,$option_key=NULL,$option_value=NULL) {

		// Manually Activate logging, since current function is run before WP init
		$this->configClass->lumiere_start_logger('updateClass');
		$logger = $this->configClass->loggerclass;

		if (!isset($option_array))
			$logger->error("[Lumiere][updateOptions][lumiere_update_options] Cannot update Lumière options, ($option_array) is undefined.");

		if (!isset($option_key)) 
			$logger->error("[Lumiere][updateOptions][lumiere_update_options] Cannot update Lumière options, ($option_key) is undefined.");


		$option_array_search = get_option($option_array);
		$check_if_exists = array_key_exists ($option_key, $option_array_search);

		if ( TRUE === $check_if_exists) {
			$option_array_search[$option_key] = $option_value;
			update_option($option_array, $option_array_search);

			$logger->info("[Lumiere][updateOptions][lumiere_update_options] Lumière option ($option_key) was successfully updated.");

			return true;

		} else {

			$logger->error("[Lumiere][updateOptions][lumiere_update_options] Lumière option ($option_key) was not found.");

		}

		return false;

	}

	/*** Remove option in array of WordPress options
	 *** WordPress doesn't know how to handle removing a specific key in a array of options
	 **
	 ** @parameter mandatory $option_array : the array of options, such as $configClass->imdbWidgetOptionsName
	 ** @parameter mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 **
	 ** returns TRUE if successful, a notice if missing mandatory parameters, FALSE if option is not found
	 **/
	function lumiere_remove_options($option_array=NULL,$option_key=NULL) {

		// Manually Activate logging, since current function is run before WP init
		$this->configClass->lumiere_start_logger('updateClass');
		$logger = $this->configClass->loggerclass;

		if (!isset($option_array)) 
			$logger->error("[Lumiere][updateOptions][lumiere_remove_options] Cannot remove Lumière options, ($option_array) is undefined.");


		if (!isset($option_key))
			$logger->error("[Lumiere][updateOptions][lumiere_remove_options] Cannot remove Lumière options, ($option_key) is undefined.");

		$option_array_search = get_option($option_array);
		$check_if_exists = array_key_exists ($option_key, $option_array_search);

		if (TRUE === $check_if_exists) {

			unset($option_array_search[$option_key]);
			update_option($option_array, $option_array_search);

			$logger->info("[Lumiere][updateOptions][lumiere_remove_options] Lumière options ($option_key) successfully removed.");

			return true;

		} else {

			$logger->error("[Lumiere][updateOptions][lumiere_remove_options] Cannot remove Lumière options, ($option_key) does not exist.");

		}

		return false;

	}

	/*** Print debug text ( obsolete, switched to Monolog )
	 **
	 ** @parameter optional $code: type of message
	 ** @parameter mandatory $text: text to embed and return
	 **
	 ** returns the text embed with styles, false if no text was provided
	 ** @TODO: check if still utilised and remove 
	 **/
	function print_debug($code=1,$text) {

		switch ($code) {
			default:
				if ((isset($this->isDebug)) && ($this->isDebug == "1"))
					return '<div><strong>[Lumière debug][updateOptions]</strong> '. $text .'</div>';
				break;
			case 1: // success notice, green
				if ((isset($this->isDebug)) && ($this->isDebug == "1"))
					return '<div class="" style="color:green"><strong>[Lumière debug][updateOptions]</strong> '. $text .'</div>';
				break;
			case 2: // info notice, blue
				if ((isset($this->isDebug)) && ($this->isDebug == "1"))
					return '<div class="" style="color:red"><strong>[Lumière debug][updateOptions]</strong> '. $text .'</div>';
				break;
		}

		return false;
	}
}

?>
