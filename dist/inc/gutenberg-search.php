<?php

 #############################################################################
 # Lumière! Movies wordpress plugin                                          #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Function : Displays a popup with search results related to a movie       #
 #									              #
 #############################################################################

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
}

require_once (plugin_dir_path( __DIR__ ).'bootstrap.php');

if (class_exists("\Lumiere\Settings")) {

	$configClass = new \Lumiere\Settings();
	$imdb_admin_values = $configClass->imdb_admin_values;
	$imdb_widget_values = $configClass->imdb_widget_values;
	$imdb_cache_values = $configClass->imdb_cache_values;

	// Get the type of search: movies, series, games
	$typeSearch = $configClass->lumiere_select_type_search();

	// Start utils and logger class if debug is selected
	if ( (isset($configClass->imdb_admin_values['imdbdebug'])) && ($configClass->imdb_admin_values['imdbdebug'] == 1) ){

		// Start the class Utils to activate debug
		$utilsClass = new \Lumiere\Utils();
		$utilsClass->lumiere_activate_debug(NULL, '', 'libxml', $configClass); # add libxml_use_internal_errors(true) which avoid endless loops with imdbphp parsing errors 

		// Start the logger
		$configClass->lumiere_start_logger('gutenbergSearch');

		// Store the class so we can use in imdbphp class
		$logger = $configClass->loggerclass;
	} 

}

# Initialization of IMDBphp
$search = new \Imdb\TitleSearch($configClass, $logger );

?><!DOCTYPE html>
<html>

<head>
	<?php wp_head();?>
</head>

<body id="gutenberg_search">
<?php
if ( (isset ($_GET["moviesearched"])) && (!empty ($_GET["moviesearched"])) ){

	$search_sanitized = isset($_GET["moviesearched"]) ? sanitize_text_field( $_GET["moviesearched"] ) : NULL;

	$configClass->lumiere_maybe_log('debug', "[Lumiere][gutenbergSearch] Querying '$search_sanitized'");

	$results = $search->search ($search_sanitized, $typeSearch );

?>

<h1 class="searchmovie_title lumiere_italic"><?php esc_html_e('Results related to your query:', 'lumiere-movies'); ?> <span class="lumiere_gutenberg_results"><?php echo $search_sanitized; ?></span></h1>

<div class="lumiere_container">
	<div class="lumiere_container_flex50"><h2><?php esc_html_e('Titles results', 'lumiere-movies'); ?></h2></div>
	<div class="lumiere_container_flex50"><h2><?php esc_html_e('Identification number', 'lumiere-movies'); ?></h2></div>
</div>


<?php
$limit_search = isset($imdb_admin_values['imdbmaxresults']) ? intval($imdb_admin_values['imdbmaxresults']) : 5;
$i=1;
foreach ($results as $res) {
	if ($i > $limit_search){
		$configClass->lumiere_maybe_log('debug', "[Lumiere][gutenbergSearch] Limit of '$limit_search' results reached.");
		echo '<div class="lumiere_italic lumiere_padding_five lumiere_align_center">' 
			. esc_html__('Maximum of results reached. You can increase it in admin options.', 'lumiere-movies');
		echo '</div>';
		break;
	}

	echo "\n" . '<div class="lumiere_container lumiere_container_gutenberg_border">';
	
	// ---- movie name results
	echo "\n\t<div class='lumiere_container_flex50 lumiere_italic lumiere_gutenberg_results'>".esc_html( $res->title() )." (".intval( $res->year() ).")".'</div>';

	// ---- imdb id results
	echo "\n\t<div class='lumiere_container_flex50 lumiere_align_center lumiere_gutenberg_results'><span class='lumiere_bold'>".esc_html__('IMDb ID:', 'lumiere-movies').'</span> ';
	echo '<span class="lumiere_gutenberg_copy_class">'
			. esc_html($res->imdbid() )
			.'</span>';

	echo '</div>';
	echo "\n</div>";


	$i++;
} // end foreach  ?> 

<?php echo '<div align="center"><a href="' . esc_url( site_url( '', 'relative' ) . \Lumiere\Settings::gutenberg_search_url ) . '">Do a new query</a></div>' ; ?>

<?php
} else {
	//---------------------------------------------------------------- No data entered, show the search form 
	if (!isset ($_GET["film"]) ) {   

		echo "\n<div align='center'>";
		echo "\n".'<h1 id="searchmovie_title">'.esc_html__('Search a movie IMDb ID', 'lumiere-movies').'</h1>';
		echo "\n".'<form action="" method="get" id="searchmovie">';
		echo "\n\t".'<label for="moviesearched"><span class="label_moviesearched">'.esc_html__('Search', 'lumiere-movies').'</span></label>';
		echo "\n\t".'<input type="text" id="moviesearched" name="moviesearched">';

// 		Nonce field deactivated, since it can be called from everywhere
//		wp_nonce_field('submit_gutenberg', 'submit_gutenberg'); 

		echo "\n\t".'<input type="submit" value="Go" >';
		echo "\n".'</form>';
		echo '</div>';
	} else {
		wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
	}
}?>

<script type='text/javascript' src='<?php echo esc_url( plugin_dir_url( __DIR__ ) ."js/lumiere_scripts_search.js?vers="); echo esc_html($configClass->lumiere_version); ?>' id='lumiere_scripts_search-js'></script>

</body>
</html>
<?php
exit(); ?>
