<?php

// *********************
// ********************* CLASS imdblt_core
// *********************

// namespace imdblt;

require_once (plugin_dir_path( __FILE__ ).'/../bootstrap.php');

if (class_exists("imdb_settings_conf")) {
	$imdb_ft = new imdb_settings_conf();
	$imdb_admin_values = $imdb_ft->get_imdb_admin_option();
	$imdb_widget_values = $imdb_ft->get_imdb_widget_option();
	$imdb_cache_values = $imdb_ft->get_imdb_cache_option();
}


global $imdb_ft, $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

class imdblt_core {

// add the class from lumiere-movies.php

} // end class

?>
