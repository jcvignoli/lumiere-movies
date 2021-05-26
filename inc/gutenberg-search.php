<?php

 #############################################################################
 # Lumière Movies wordpress plugin                                           #
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

require_once (plugin_dir_path( __FILE__ ).'/../bootstrap.php');

do_action('wp_loaded'); // execute wordpress first codes

//---------------------------------------=[Vars]=----------------

global $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;;

// Start config class for $config in below Imdb\Title class calls
if (class_exists("lumiere_settings_conf")) {
	$config = new lumiere_settings_conf();
	$config->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;
	$config->photodir = $imdb_cache_values['imdbphotoroot'] ?? NULL; // ?imdbphotoroot? Bug imdbphp?
	$config->imdb_img_url = $imdb_cache_values['imdbimgdir'] ?? NULL;
	$config->photoroot = $imdb_cache_values['imdbphotodir'] ?? NULL; // ?imdbphotodir? Bug imdbphp?
	$config->language = $imdb_admin_values['imdblanguage'] ?? NULL;
}

# Initialization of IMDBphp
$search = new Imdb\TitleSearch($config);
?>
<html>
<head>
<?php wp_head();?>

</head>
<body id="gutenberg_search">
<?php
if ( (isset($_POST['submitsearchmovie'])) && (isset ($_POST["moviesearched"])) ) {

	$search_sanitized = sanitize_text_field( $_POST["moviesearched"] ) ?? NULL;

	if ($_GET["searchtype"]=="episode") 
		$results = $search->search ($search_sanitized, array(\Imdb\TitleSearch::TV_SERIES));
	else 
		$results = $search->search ($search_sanitized, array(\Imdb\TitleSearch::MOVIE));
?>

<h1 class="searchmovie_title"><?php esc_html_e('Results related to your query:', 'lumiere-movies'); echo " ".$film_sanitized; ?></h1>

<div class="lumiere_container">
	<div class="lumiere_container_flex50"><h2><?php esc_html_e('Titles results', 'lumiere-movies'); ?></h2></div>
	<div class="lumiere_container_flex50"><h2><?php esc_html_e('IMDb identification number', 'lumiere-movies'); ?></h2></div>
</div>


<?php
$limit_search=5;
$i=1;
foreach ($results as $res) {
	if ($i > $limit_search)
		break;

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

<?php echo '<div align="center"><a href="' . esc_url(wp_get_referer()) . '">Try again</a></div>' ; ?>

<?php
} else {
	//---------------------------------------------------------------- No data entered, show the search form 
	if ( (!isset ($_GET["film"])) && ($_GET["gutenberg"] == 'yes') ) {   

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
