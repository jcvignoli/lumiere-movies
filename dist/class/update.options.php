<?php

 #############################################################################
 # Lumière! Movies WordPress Plugin                                          #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Updates to do to the options according to the current plugin version     #
 #									              #
 #############################################################################

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
	wp_die('You can not call directly this page');

if (class_exists("\Lumiere\Settings")) {
	$config = new \Lumiere\Settings();
	$imdb_admin_values = $config->get_imdb_admin_option();
	$imdb_widget_values = $config->get_imdb_widget_option();
	$imdb_cache_values = $config->get_imdb_cache_option();
} else {
	wp_die('Lumière files have been moved. Can not update Lumière!');
}


/************************************************** 3.3.1
*/
if (version_compare( LUMIERE_VERSION, "3.3.1" ) >= 0 ){

	// Deprecated: only one comment is returned by imdbphp libraries
	if ( TRUE === lumiere_remove_options($config->imdbWidgetOptionsName, 'imdbwidgetedst') )
		echo lumiere_notice(1, esc_html__( 'Lumière option successfully removed.', 'lumiere-movies') );

	// New option to manage theme colors for into the post/widget
	if ( TRUE === lumiere_add_options($config->imdbWidgetOptionsName, 'imdbintotheposttheme', 'grey') )
		echo lumiere_notice(1, esc_html__( 'Lumière option successfully added.', 'lumiere-movies') );

}

/*** Add option in array of WordPress options
 *** WordPress doesn't know how to handle adding a specific key in a array of options
 **
 ** @parameter mandatory $option_array : the array of options, such as $config->imdbWidgetOptionsName
 ** @parameter mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
 ** @parameter optional $option_key : the value to add to the key, NULL if not specified
 **
 ** returns TRUE if successful, a wordpress notice if missing mandatory parameters, FALSE if option already exists
 **/

function lumiere_add_options($option_array=NULL,$option_key=NULL,$option_value=NULL) {

	if (!isset($option_array))
		echo lumiere_notice(3, esc_html__( 'Cannot update Lumière, "$option_array" is undefined.', 'lumiere-movies') );

	if (!isset($option_key))
		echo lumiere_notice(3, esc_html__( 'Cannot update Lumière, "$option_key" is undefined.', 'lumiere-movies') );

	$option_array_search = get_option($option_array);
	$check_if_exists = array_key_exists ($option_key, $option_array_search);

	if ( FALSE === $check_if_exists) {
		$option_array_search[$option_key] = $option_value;
		update_option($option_array, $option_array_search);
		return true;
	} else {
		echo lumiere_notice(3, esc_html__( "Lumière option '$option_key' already exists.", 'lumiere-movies') );
	}

	return false;

}

/*** Remove option in array of WordPress options
 *** WordPress doesn't know how to handle removing a specific key in a array of options
 **
 ** @parameter mandatory $option_array : the array of options, such as $config->imdbWidgetOptionsName
 ** @parameter mandatory $option_key : the key in the array of options to be added, such as 'imdbintotheposttheme'
 **
 ** returns TRUE if successful, a wordpress notice if missing mandatory parameters, FALSE if option is not found
 **/

function lumiere_remove_options($option_array=NULL,$option_key=NULL) {

	if (!isset($option_array))
		echo lumiere_notice(3, esc_html__( 'Cannot update Lumière, "$option_array" is undefined.', 'lumiere-movies') );

	if (!isset($option_key))
		echo lumiere_notice(3, esc_html__( 'Cannot update Lumière, "$option_key" is undefined.', 'lumiere-movies') );

	$option_array_search = get_option($option_array);
	$check_if_exists = array_key_exists ($option_key, $option_array_search);

	if (TRUE === $check_if_exists) {
		unset($option_array_search[$option_key]);
		update_option($option_array, $option_array_search);
		return true;
	}

	return false;

}
?>
