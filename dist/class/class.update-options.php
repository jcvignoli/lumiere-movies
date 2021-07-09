<?php

 #############################################################################
 # Lumière! Movies WordPress Plugin                                          #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #				->					              #
 # Class: Option updates to make according to the current plugin version     #
 #	-> Always put a version earlier for updates, 		              #
 #	as Wordpress checks with previous version.				#
 #	-> progressive increment of the updates,                              #
 #	using $imdb_admin_values['imdbHowManyUpdates']                        #
 #									              #
 #############################################################################

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
	wp_die('You can not call directly this page');

class UpdateOptions {

	private $lumiereVersionPlugin; # store the version for use in runUpdateOptions() function

	private $isDebug;

	function __construct() {

		$this->getLumiereVersions();

		add_filter ( 'wp_head', [ $this, 'runUpdateOptions' ], 0);

	}

	/*** Get current Lumière version
	 *** Extracts from the readme file
	 **
	 **/
	function getLumiereVersions () {

		$lumiere_version_recherche="";
		$lumiere_version="";

		$lumiere_version_recherche = file_get_contents( plugin_dir_path( __DIR__ ) . 'README.txt');
		$lumiere_version = preg_match('#Stable tag:\s(.+)\n#', $lumiere_version_recherche, $lumiere_version_match);

		if ($this->lumiereVersionPlugin = $lumiere_version_match[1])
			return true;

		return false;
	}

	/*** Run updates of options
	 *** add/remove/update options 
	 **
	 **
	 ** returns the feedback of options updated/removed/added
	 **/
	function runUpdateOptions() {

		if (class_exists("\Lumiere\Settings")) {

			$config = new \Lumiere\Settings();
			$imdb_admin_values = $config->get_imdb_admin_option();
			$this->isDebug = $imdb_admin_values['imdbdebug'];
			$imdb_widget_values = $config->get_imdb_widget_option();
			$imdb_cache_values = $config->get_imdb_cache_option();

		} else {
			wp_die('Lumière files have been moved. Can not update Lumière!');
		}

		$output = "";

		
		/************************************************** 3.4 */
		if ( (version_compare( $this->lumiereVersionPlugin, "3.3.4" ) >= 0 )
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 4 ) ){				# update 4

			$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
			$this->lumiere_update_options($config->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

			// Add 'imdbSerieMovies'
			// New option to select to search for movies, series, or both
			if ( TRUE === $this->lumiere_add_options($config->imdbAdminOptionsName, 'imdbseriemovies', 'movies+series') ) {
				$output .= $this->print_debug(1, '<strong>Lumière option imdbSerieMovies successfully added.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbSerieMovies could not be added.</strong>');
			}

			// Add 'imdbHowManyUpdates'
			// New option to manage the number of 
			if ( TRUE === $this->lumiere_add_options($config->imdbAdminOptionsName, 'imdbHowManyUpdates', 1 ) ) {
				$output .= $this->print_debug(1, '<strong>Lumière option imdbHowManyUpdates successfully added.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbHowManyUpdates could not be added.</strong>');
			}

		/************************************************** 3.3.4 */

		} elseif ( (version_compare( $this->lumiereVersionPlugin, "3.3.3" ) >= 0 )
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 3 ) ){ 				# update 3

			$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
			$this->lumiere_update_options($config->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

			// Remove 'imdbdisplaylinktoimdb'
			// Deprecated: removed links to IMDb in popup search and movie
			if ( TRUE === $this->lumiere_remove_options($config->imdbAdminOptionsName, 'imdbdisplaylinktoimdb') ){
				$output .= $this->print_debug(1, '<strong>Lumière option imdbdisplaylinktoimdb successfully removed.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbdisplaylinktoimdb not removed.</strong>');
			}

			// Remove 'imdbpicsize'
			// Deprecated: removed links to IMDb in popup search and movie
			if ( TRUE === $this->lumiere_remove_options($config->imdbAdminOptionsName, 'imdbpicsize') ){
				$output .= $this->print_debug(1, '<strong>Lumière option imdbpicsize successfully removed.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbpicsize not removed.</strong>');
			}

			// Remove 'imdbpicurl'
			// Deprecated: removed links to IMDb in popup search and movie
			if ( TRUE === $this->lumiere_remove_options($config->imdbAdminOptionsName, 'imdbpicurl') ){
				$output .= $this->print_debug(1, '<strong>Lumière option imdbpicurl successfully removed.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbpicurl not removed.</strong>');
			}

			// Move 'imdblinkingkill'
			// Variable moved from widget options to admin
			if ( TRUE === $this->lumiere_remove_options($config->imdbWidgetOptionsName, 'imdblinkingkill') ){
				$output .= $this->print_debug(1, '<strong>Lumière option imdblinkingkill successfully removed.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdblinkingkill not removed.</strong>');
			}
			if ( TRUE === $this->lumiere_add_options($config->imdbAdminOptionsName, 'imdblinkingkill', 'false') ){
				$output .= $this->print_debug(1, '<strong>Lumière option imdblinkingkill successfully added.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdblinkingkill not added.</strong>');
			}

			// Move 'imdbautopostwidget'
			// Variable moved from widget options to admin
			if ( TRUE === $this->lumiere_remove_options($config->imdbWidgetOptionsName, 'imdbautopostwidget') ){
				$output .= $this->print_debug(1, '<strong>Lumière option imdbautopostwidget successfully removed.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbautopostwidget not removed.</strong>');
			}

			if ( TRUE === $this->lumiere_add_options($config->imdbAdminOptionsName, 'imdbautopostwidget', 'false') ){
				$output .= $this->print_debug(1, '<strong>Lumière option imdbautopostwidget successfully added.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbautopostwidget not added.</strong>');
			}

			// Move 'imdbintotheposttheme'
			// Variable moved from widget options to admin
			if ( TRUE === $this->lumiere_remove_options($config->imdbWidgetOptionsName, 'imdbintotheposttheme') ) {
				$output .= $this->print_debug(1, '<strong>Lumière option imdbintotheposttheme successfully removed.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbintotheposttheme not removed.</strong>');
			}
			if ( TRUE === $this->lumiere_add_options($config->imdbAdminOptionsName, 'imdbintotheposttheme', 'grey') ) {
				$output .= $this->print_debug(1, '<strong>Lumière option imdbintotheposttheme successfully added.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbintotheposttheme not added.</strong>');
			}


		/************************************************** 3.3.3 */

		} elseif ( (version_compare( $this->lumiereVersionPlugin, "3.3.2" ) >= 0 ) 
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 2 ) ){				# update 2

			$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
			$this->lumiere_update_options($config->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

			// Update 'imdbwidgetsource'
			// No need to display the source by default
			$updateLumiereOptions = $imdb_widget_values['imdbwidgetsource'] = true;
			if ( TRUE === $this->lumiere_update_options($config->imdbWidgetOptionsName, 'imdbwidgetsource', '0') ) {
				$output .= $this->print_debug(1, '<strong>Lumière option imdbwidgetsource successfully updated.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbwidgetsource not updated.</strong>');
			}

		/************************************************** 3.3.1 */

		} elseif ( (version_compare( $this->lumiereVersionPlugin, "3.3" ) >= 0 )
			&& ($imdb_admin_values['imdbHowManyUpdates'] == 1 ) ){				# update 1

			$nb_of_updates = ( $imdb_admin_values['imdbHowManyUpdates'] + 1 ); 
			$this->lumiere_update_options($config->imdbAdminOptionsName, 'imdbHowManyUpdates', $nb_of_updates );

			// Remove 'imdbwidgetcommentsnumber'
			// Deprecated: only one comment is returned by imdbphp libraries
			if ( TRUE === $this->lumiere_remove_options($config->imdbWidgetOptionsName, 'imdbwidgetcommentsnumber') ){
				$output .= $this->print_debug(1, '<strong>Lumière option imdbwidgetcommentsnumber successfully removed.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbwidgetcommentsnumber not removed.</strong>');
			}

			// Add 'imdbintotheposttheme'
			// New option to manage theme colors for into the post/widget
			if ( TRUE === $this->lumiere_add_options($config->imdbWidgetOptionsName, 'imdbintotheposttheme', 'grey') ) {
				$output .= $this->print_debug(1, '<strong>Lumière option imdbintotheposttheme successfully added.</strong>');
			} else {
				$output .= $this->print_debug(2, '<strong>Lumière option imdbintotheposttheme not added.</strong>');
			}

		}

		echo $output; 

	}

	/*** Add option in array of WordPress options
	 *** WordPress doesn't know how to handle adding a specific key in a array of options
	 **
	 ** @parameter mandatory $option_array : the array of options, such as $config->imdbWidgetOptionsName
	 ** @parameter mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 ** @parameter optional $option_key : the value to add to the key, NULL if not specified
	 **
	 ** returns text if successful, a notice if missing mandatory parameters,FALSE if option already exists
	 **/

	function lumiere_add_options($option_array=NULL,$option_key=NULL,$option_value=NULL) {

		if (!isset($option_array))
			echo $this->print_debug(2, "[lumiere_add_options] Cannot update Lumière options, ($option_array) is undefined." );

		if (!isset($option_key))
			echo $this->print_debug(2, "[lumiere_add_options] Cannot update Lumière options, ($option_key) is undefined." );

		$option_array_search = get_option($option_array);
		$check_if_exists = array_key_exists ($option_key, $option_array_search);

		if ( FALSE === $check_if_exists) {
			$option_array_search[$option_key] = $option_value;
			update_option($option_array, $option_array_search);
			return true;
		} else {
			echo $this->print_debug(2, "[lumiere_add_options] Lumière option ($option_key) already exists." );
		}

		return false;

	}

	/*** Update option in array of WordPress options
	 *** WordPress doesn't know how to handle updating a specific key in a array of options
	 **
	 ** @parameter mandatory $option_array : the array of options, such as $config->imdbWidgetOptionsName
	 ** @parameter mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 ** @parameter optional $option_key : the value to add to the key, NULL if not specified
	 **
	 ** returns text if successful, a notice if missing mandatory parameters, FALSE if option already exists
	 **/
	function lumiere_update_options($option_array=NULL,$option_key=NULL,$option_value=NULL) {

		if (!isset($option_array))
			echo $this->print_debug(2, "[lumiere_update_options] Cannot update Lumière options, ($option_array) is undefined." );

		if (!isset($option_key))
			echo $this->print_debug(2, "[lumiere_update_options] Cannot update Lumière options, ($option_array) is undefined." );

		$option_array_search = get_option($option_array);
		$check_if_exists = array_key_exists ($option_key, $option_array_search);

		if ( TRUE === $check_if_exists) {
			$option_array_search[$option_key] = $option_value;
			update_option($option_array, $option_array_search);
			return true;
		} else {
			echo $this->print_debug(2, "[lumiere_update_options] Lumière option ($option_key) was not found." );
		}

		return false;

	}

	/*** Remove option in array of WordPress options
	 *** WordPress doesn't know how to handle removing a specific key in a array of options
	 **
	 ** @parameter mandatory $option_array : the array of options, such as $config->imdbWidgetOptionsName
	 ** @parameter mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
	 **
	 ** returns TRUE if successful, a notice if missing mandatory parameters, FALSE if option is not found
	 **/
	function lumiere_remove_options($option_array=NULL,$option_key=NULL) {

		if (!isset($option_array))
			echo $this->print_debug(2, "[lumiere_remove_options] Cannot update Lumière options, ($option_array) is undefined." );

		if (!isset($option_key))
			echo $this->print_debug(2, "[lumiere_remove_options] Cannot update Lumière options, ($option_array) is undefined." );

		$option_array_search = get_option($option_array);
		$check_if_exists = array_key_exists ($option_key, $option_array_search);

		if (TRUE === $check_if_exists) {
			unset($option_array_search[$option_key]);
			update_option($option_array, $option_array_search);
			return true;
		} else {
			echo $this->print_debug(2, "[lumiere_remove_options] Cannot remove Lumière options, ($option_key) does not exist." );
		}

		return false;

	}

	/*** Print debug text
	 **
	 ** @parameter optional $code: type of message
	 ** @parameter mandatory $text: text to embed and return
	 **
	 ** returns the text embed with styles, false if no text was provided
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

$start_update_options = new \Lumiere\UpdateOptions();

?>
