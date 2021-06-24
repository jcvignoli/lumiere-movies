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
}


if ( "3.3.1" == LUMIERE_VERSION ){
	// Deprecated: only one comment is returned by imdbphp libraries
	if (get_option($imdb_widget_values['imdbwidgetcommentsnumber']))
		delete_option($imdb_widget_values['imdbwidgetcommentsnumber']);
	// New option to manage theme colors for into the post/widget
	if (!get_option($imdb_widget_values['imdbintotheposttheme']))
		add_option($imdb_widget_values['imdbintotheposttheme'], 'grey', '', '');
}





?>
