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
if ( ! defined( 'ABSPATH' ) ) 
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));


require_once (plugin_dir_path( __DIR__ ).'bootstrap.php');

if (class_exists("\Lumiere\Settings")) {

	$config = new \Lumiere\Settings();
	$imdb_admin_values = $config->imdb_admin_values;
	$imdb_widget_values = $config->imdb_widget_values;
	$imdb_cache_values = $config->imdb_cache_values;

	// Get the type of search: movies, series, games
	$typeSearch = $config->lumiere_select_type_search();

	// Start utils and logger class if debug is selected
	if ( (isset($config->imdb_admin_values['imdbdebug'])) && ($config->imdb_admin_values['imdbdebug'] == 1) ){

		// Start the class Utils to activate debug
		$debug_start = new \Lumiere\Utils();
		$debug_start->lumiere_activate_debug(NULL, '', 'libxml', $config); # add libxml_use_internal_errors(true) which avoid endless loops with imdbphp parsing errors 

		// Start the logger
		$config->lumiere_start_logger('gutenbergSearch');

		// Store the class so we can use in imdbphp class
		$logger = $config->loggerclass;
	} 

}

# Initialization of IMDBphp
$search = new \Imdb\TitleSearch($config, $logger );
?>
<html>
<head>
<?php wp_head();?>

</head>
<body id="gutenberg_search">
<?php
if ( (isset($_POST['submitsearchmovie'])) && (isset ($_POST["moviesearched"])) ) {

	$search_sanitized = isset($_POST["moviesearched"]) ? sanitize_text_field( $_POST["moviesearched"] ) : NULL;

	$results = $search->search ($search_sanitized, $typeSearch );

?>

<h1 class="searchmovie_title"><?php esc_html_e('Results related to your query:', 'lumiere-movies'); ?> <font color="blue"><i><?php echo $search_sanitized; ?></i></font></h1>

<div class="lumiere_container">
	<div class="lumiere_container_flex50"><h2><?php esc_html_e('Titles results', 'lumiere-movies'); ?></h2></div>
	<div class="lumiere_container_flex50"><h2><?php esc_html_e('IMDb identification number', 'lumiere-movies'); ?></h2></div>
</div>


<?php
$limit_search = $imdb_admin_values['imdbmaxresults']; # from admin selection
$i=1;
foreach ($results as $res) {
	if ($i > $limit_search){
		echo '<div align="center"><i>' 
			. esc_html__('Maximum of results reached. You can increase it in admin options.', 'lumiere-movies');
		echo '</i></div>';
		break;
	}

	echo "\n" . '<div class="lumiere_container lumiere_container_border">';
	
	// ---- movie name results
	echo "\n\t<div class='lumiere_container_flex50 lumiere_italic'>".esc_html( $res->title() )." (".intval( $res->year() ).")".'</div>';

	// ---- imdb id results
	echo "\n\t<div class='lumiere_container_flex50 lumiere_align_center'><span class='lumiere_bold'>".esc_html__('IMDb ID:', 'lumiere-movies').'</span> ';
	echo esc_html($res->imdbid() );

	echo '</div>';
	echo "\n</div>";


	$i++;
} // end foreach  ?> 

<?php echo '<div align="center"><a href="' . esc_url(wp_get_referer()) . '">Do a new query</a></div>' ; ?>

<?php
} else {
	//---------------------------------------------------------------- No data entered, show the search form 
	if (!isset ($_GET["film"]) ) {   

		echo "\n<div align='center'>";
		echo "\n".'<h1 id="searchmovie_title">'.esc_html__('Search a movie', 'lumiere-movies').'</h1>';
		echo "\n".'<form action="" method="post" id="searchmovie">';
		echo "\n\t".'<label for="moviesearched"><span class="label_moviesearched">'.esc_html__('Search', 'lumiere-movies').'</span></label>';
		echo "\n\t".'<input type="text" id="moviesearched" name="moviesearched">';
		echo "\n\t".'<input type="submit" name="submitsearchmovie" value="Go" >';
		echo "\n".'</form>';
		echo '</div>';
	} else {
		wp_die("you can't call this page directly");
	}
}
?>

</body>
</html> 
<?php exit(); ?>
