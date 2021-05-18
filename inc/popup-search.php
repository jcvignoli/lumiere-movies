<?php

 #############################################################################
 # Lumiere Movies                                                     #
 # written by Prometheus group                                               #
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

if (isset ($_GET["film"]))
	$film_sanitized = sanitize_text_field( $_GET["film"] ) ?? NULL;

if ($_GET["searchtype"]=="episode") 
	$results = $search->search ($film_sanitized, array(\Imdb\TitleSearch::TV_SERIES));
else 
	$results = $search->search ($film_sanitized, array(\Imdb\TitleSearch::MOVIE));

//--------------------------------------=[Layout]=---------------

if (($imdb_admin_values['imdbdirectsearch'] == false ) OR ($_GET["norecursive"] == 'yes')) { //------------------------- 1. recherche, comportement classique
	//require_once ('popup-header.php'); 
	get_header(); 
?>
<h1><?php esc_html_e('Results related to', 'imdb'); echo $film_sanitized; ?></h1>

<table class="TableListeResultats">
	<tr>
		<th class="TableListeResultatsTitre"><?php esc_html_e('Titles matching', 'imdb'); ?></th>
		<th class="TableListeResultatsTitre imdblt_titlematchingdirector"><?php esc_html_e('Director', 'imdb'); ?></th>
	</tr>

	<?php
	foreach ($results as $res) {
		echo "	<tr>\n";
		
		// ---- movie part
		echo "		<td class='TableListeResultatsColGauche'><a href=\"".esc_url($imdb_admin_values['imdbplugindirectory']."inc/popup-imdb_movie.php?mid=".intval($res->imdbid()) )."&film=".$film_sanitized."\" title=\"".esc_html__('more on', 'imdb')." ".sanitize_text_field( $res->title() )."\" >".sanitize_text_field( $res->title() )."(".intval( $res->year() ).")"."</a> \n";
		echo "&nbsp;&nbsp;<a class=\"imdblink\" href=\"".esc_url( "https://www.imdb.com/title/tt".intval($res->imdbid()) )."\" target=\"_blank\" title='".esc_html__('link to imdb for', 'imdb')." ".sanitize_text_field( $res->title() )."'>";

		if ($imdb_admin_values[imdbdisplaylinktoimdb] == true) { # if the user has selected so
			echo "<img  class='img-imdb' src='".esc_url( $imdb_admin_values['imdbplugindirectory'].$imdb_admin_values['imdbpicurl'] )."' width='".intval($imdb_admin_values['imdbpicsize'])."' alt='".esc_html__('link to imdb for', 'imdb')." ".sanitize_text_field( $res->title() )."'/></a>";	
		}
		echo "</td>\n";
		flush ();
	
		// ---- director part
		$realisateur = $res->director();
		if (! is_null ($realisateur['0']['name'])){
			echo "		<td class='TableListeResultatsColDroite'><a href=\"".esc_url($imdb_admin_values[imdbplugindirectory]."inc/popup-imdb_person.php?mid=".intval($realisateur['0']['imdb'])."&film=".sanitize_text_field($_GET['film']) )."\" title=\"".esc_html__('more on', 'imdb')." ".sanitize_text_field( $realisateur['0']['name'] )."\" >".sanitize_text_field( $realisateur['0']['name'] )."</a>";

			if ($imdb_admin_values[imdbdisplaylinktoimdb] == true) { # if the user has selected so
				echo "&nbsp;&nbsp;<a class=\"imdblink\" href=\"".esc_url( "https://www.imdb.com/name/nm".intval($realisateur['0']['imdb']) )."\" target=\"_blank\" title='".esc_html__('link to imdb for', 'imdb')." ".sanitize_text_field( $realisateur['0']['name'] )."'>";
				echo "<img class='img-imdb' src='".esc_url( $imdb_admin_values[imdbplugindirectory].$imdb_admin_values[imdbpicurl] )."' width='".intval($imdb_admin_values[imdbpicsize])."' alt='".esc_html__('link to imdb for', 'imdb')." ".$realisateur['0']['name']."'/>";
				echo "</a>";
			}
			echo "</td>\n";
		}
		echo "	</tr>\n";
		flush ();
	} // end foreach  ?> 

</table>
<?php // call wordpress footer functions;
wp_meta();
//get_footer(); // this one gets too much uneeded information
wp_footer(); 
?>
</body>
</html> 

<?php exit(); // quit the call of the page, to avoid double loading process ?>

<?php
} else {  //-------------------------------------------------------------------------- 2. acc�s direct, option sp�ciale

	if ($results[0]) { // test pour afficher le film m�me lorsque celui-ci est un r�sultat unique (sinon, msg erreur php)
		$nbarrayresult = "0"; // lorsque r�sultat unique, tout s'affiche dans l'array "0"
	} else {
		$nbarrayresult = "1"; // lorsque r�sultats multiples, le premier film s'affiche dans l'array "1"
	}	
	$midPremierResultat = $results[$nbarrayresult]->imdbid() ?? NULL;
	$_GET['mid'] = $midPremierResultat; //"mid" will be transmitted to next include
	require_once ( $imdb_admin_values['imdbplugindirectory'] . "inc/popup-imdb_movie.php");
}
?>
