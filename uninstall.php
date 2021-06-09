<?php

 #############################################################################
 # Lumière! Movies plugin                                                    #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #       			                                                 #
 #  Function : Uninstall completely Lumière! when removing the plugin	       #
 #       	  			                                          #
 #############################################################################


if (!defined('WP_UNINSTALL_PLUGIN')) 
    die;

function lumiere_unregister_taxonomy() {

	global $imdb_admin_values;

	// search for all imdbtaxonomy* in config array, 
	// if a taxonomy is found, let's get related terms and delete them
	foreach ( lumiere_array_key_exists_wildcard($imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
		$filter_taxonomy = str_replace('imdbtaxonomy', '', strtoupper($imdb_admin_values['imdburlstringtaxo']  .$key) );

		$terms = get_terms( array(
			'taxonomy' => $filter_taxonomy,
			'hide_empty' => false
		) );

		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $filter_taxonomy ); 
			unregister_taxonomy( $filter_taxonomy );
		}
	}
}
add_action( 'init', 'lumiere_unregister_taxonomy' );
do_action ('lumiere_unregister_taxonomy' );

# Delete the options after needing them
delete_option( 'imdbAdminOptions' ); 
delete_option( 'imdbWidgetOptions' );
delete_option( 'imdbCacheOptions' );

echo "Lumière! options deleted.";

?>
