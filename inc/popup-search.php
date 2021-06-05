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

require_once (plugin_dir_path( __DIR__ ).'bootstrap.php');

/* VARS */

global $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;;


// Enter in debug mode
if ((isset($imdb_admin_values['imdbdebug'])) && ($imdb_admin_values['imdbdebug'] == "1")){
	print_r($imdb_admin_values);
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	set_error_handler("var_dump");
	libxml_use_internal_errors(true); // avoid endless loops with imdbphp parsing errors 
}


do_action('wp_loaded'); // execute wordpress first codes


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

if (isset ($_GET["film"])){
	$film_sanitized = lumiere_name_htmlize( $_GET["film"] ) ?? NULL;
	$film_sanitized_for_title = sanitize_text_field($_GET['film']);
}

if ( (isset($_GET["searchtype"])) && ($_GET["searchtype"]=="episode") )
	$results = $search->search ($film_sanitized, array(\Imdb\TitleSearch::TV_SERIES));
else 
	$results = $search->search ($film_sanitized, array(\Imdb\TitleSearch::MOVIE));

//--------------------------------------=[Layout]=---------------

//------------------------- 1. recherche, comportement classique
if (($imdb_admin_values['imdbdirectsearch'] == false ) OR ($_GET["norecursive"] == 'yes')) { 
?>
<html>
<head>
<?php wp_head();?>

</head>
<body class="lumiere_body">

<h1><?php esc_html_e('Results related to', 'lumiere-movies'); echo " " . $film_sanitized_for_title ?></h1>

<table class="TableListeResultats">
	<tr>
		<th class="TableListeResultatsTitre"><?php esc_html_e('Titles matching', 'lumiere-movies'); ?></th>
		<th class="TableListeResultatsTitre imdblt_titlematchingdirector"><?php esc_html_e('Director', 'lumiere-movies'); ?></th>
	</tr>

	<?php
	foreach ($results as $res) {
		echo "	<tr>\n";
		
		// ---- movie part
		echo "		<td class='TableListeResultatsColGauche'><a href=\"".esc_url( LUMIERE_URLPOPUPSFILMS . lumiere_name_htmlize( $res->title() ) . "/?mid=".intval($res->imdbid()) )."&film=".lumiere_name_htmlize( $res->title() )."\" title=\"".esc_html__('more on', 'lumiere-movies')." ".sanitize_text_field( $res->title() )."\" >".sanitize_text_field( $res->title() )." (".intval( $res->year() ).")"."</a> \n";
		echo "&nbsp;&nbsp;<a class=\"linkpopup\" href=\"".esc_url( "https://www.imdb.com/title/tt".intval($res->imdbid()) )."\" target=\"_blank\" title='".esc_html__('link to imdb for', 'lumiere-movies')." ".sanitize_text_field( $res->title() )."'>";

		if ($imdb_admin_values['imdbdisplaylinktoimdb'] == true) { # if the user has selected so
			echo '<img loading="eager" class="img-imdb" src="'.esc_url( $imdb_admin_values['imdbplugindirectory'].$imdb_admin_values['imdbpicurl'] ).'" width="'.intval($imdb_admin_values['imdbpicsize']).'" alt="'.esc_html__('link to imdb for', 'lumiere-movies')." ".sanitize_text_field( $res->title() ).'"/></a>';
		}
		echo "</td>\n";
		flush ();
	
		// ---- director part
		$realisateur = $res->director();
		if ( (isset($realisateur['0']['name'])) && (! is_null ($realisateur['0']['name'])) ){
			echo "		<td class='TableListeResultatsColDroite'><a class='link-imdb2' href=\"".esc_url( LUMIERE_URLPOPUPSPERSON . intval($realisateur['0']["imdb"]) . "/?mid=".intval($realisateur['0']["imdb"]) ). "\" title=\"".esc_html__('more on', 'lumiere-movies')." ".sanitize_text_field( $realisateur['0']['name'] )."\" >".sanitize_text_field( $realisateur['0']['name'] )."</a>";

			if ($imdb_admin_values['imdbdisplaylinktoimdb'] == true) { # if the user has selected so
				echo "&nbsp;&nbsp;<a class='link-imdb2' href=\"".esc_url( "https://www.imdb.com/name/nm".intval($realisateur['0']["imdb"]) )."\" target=\"_blank\" title='".esc_html__('link to imdb for', 'lumiere-movies')." ".sanitize_text_field( $realisateur['0']['name'] )."'>";
				echo "<img class='img-imdb' src='".esc_url( $imdb_admin_values['imdbplugindirectory'].$imdb_admin_values['imdbpicurl'] )."' width='".intval($imdb_admin_values['imdbpicsize'])."' alt='".esc_html__('link to imdb for', 'lumiere-movies')." ".$realisateur['0']['name']."'/>";
				echo "</a>";
			}
			echo "</td>\n";
		}
		echo "	</tr>\n";
		flush ();
	} // end foreach  ?> 

</table>
<?php
wp_meta();
wp_footer(); 
?>

<?php exit(); // quit the call of the page, to avoid double loading process ?>

<?php
} else {  //-------------------------------------------------------------------------- 2. direct access, special option

	if ($results[0]) { // test to display the movie even if it's a unique result (if not, PHP error message)
		$nbarrayresult = "0"; // if unique result, data goes in array "0"
	} else {
		$nbarrayresult = "1"; // if multiple results, first movie goes in array "1" 
	}	
	$midPremierResultat = $results[$nbarrayresult]->imdbid() ?? NULL;
	if (isset($_GET['mid']))
		$_GET['mid'] = $midPremierResultat; //"mid" will be transmitted to next include

	require_once ( plugin_dir_path( __DIR__ ) . "/inc/popup-imdb_movie.php");
}
?>
