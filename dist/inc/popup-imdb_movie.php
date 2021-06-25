<?php

 #############################################################################
 # Lumière! wordpress plugin                                                 #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Function : Popup movie section    					       #
 #									              #
 #############################################################################

require_once (plugin_dir_path( __DIR__ ).'bootstrap.php');

/* VARS */
global $imdb_admin_values, $imdb_widget_values, $imdb_cache_values, $imdballmeta;

// HTML tags to keep when using strip_tags()
$striptags_keep = '<div><span><br><img>';

$movieid_sanitized = isset($_GET["mid"]) ? filter_var( $_GET["mid"], FILTER_SANITIZE_NUMBER_INT) : NULL;
$filmid_sanitized = isset($_GET["film"]) ? lumiere_name_htmlize( $_GET["film"] ) : NULL;
$film_sanitized_for_title = isset($_GET["film"]) ? sanitize_text_field($_GET["film"]) : NULL;

// Enter in debug mode
if ((isset($imdb_admin_values['imdbdebug'])) && ($imdb_admin_values['imdbdebug'] == "1")){
	lumiere_debug_display($imdb_cache_values, 'SetError', 'libxml'); # add libxml_use_internal_errors(true) which avoid endless loops with imdbphp parsing errors 
}

// Start config class for $config in below Imdb\Title class calls
if (class_exists("\Lumiere\Settings")) {
	$config = new \Lumiere\Settings();
	$config->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;
	$config->photodir = $imdb_cache_values['imdbphotoroot'] ?? NULL; // ?imdbphotoroot? Bug imdbphp?
	$config->imdb_img_url = $imdb_cache_values['imdbimgdir'] ?? NULL;
	$config->photoroot = $imdb_cache_values['imdbphotodir'] ?? NULL; // ?imdbphotodir? Bug imdbphp?
	$config->language = $imdb_admin_values['imdblanguage'] ?? NULL;
}

// if neither film nor mid are set, throw a 404 error
if (empty($movieid_sanitized ) && empty($filmid_sanitized)){
	global $wp_query;

	$wp_query->set_404();

	// In case you need to make sure that `have_posts()` return false.
	// Maybe there's a reset function on WP_Query but I couldn't find one.
	$wp_query->post_count = 0;
	$wp_query->posts = [];
	$wp_query->post = false;

	status_header(404);

	$template = get_404_template();
	return $template;
}

if ( (isset ($movieid_sanitized)) && (!empty ($movieid_sanitized)) && (!empty ($config)) ) {
	$movie = new \Imdb\Title($movieid_sanitized, $config);
	$filmid_sanitized = lumiere_name_htmlize($movie->title());
	$film_sanitized_for_title = sanitize_text_field($movie->title());

} elseif (!empty ($config)) {
	$search = new \Imdb\TitleSearch($config);
	if ( (isset($_GET["searchtype"])) && ($_GET["searchtype"]=="episode") ) {
		$movie = $search->search ($filmid_sanitized, array(\Imdb\TitleSearch::TV_SERIES))[0];
	} else {
		$movie = $search->search ($filmid_sanitized, array(\Imdb\TitleSearch::MOVIE))[0];
	}
} else {
	esc_html_e('No config option set', 'lumiere-movies');
	exit();
}

// run Lumière class
$display = new \Lumiere\LumiereMovies();


//------------------------- 1. search all results related to the name of the movie
if (($imdb_admin_values['imdbdirectsearch'] == false ) OR ( (isset($_GET["norecursive"])) && ($_GET["norecursive"] == 'yes')) ) { 

	if ( (isset($_GET["searchtype"])) && ($_GET["searchtype"]=="episode") )
		$results = $search->search ( $filmid_sanitized, array(\Imdb\TitleSearch::TV_SERIES));
	else 
		$results = $search->search ( $filmid_sanitized, array(\Imdb\TitleSearch::MOVIE));

do_action('wp_loaded'); // execute wordpress first codes # still useful?

?><!DOCTYPE html>
<html>
<head>
<?php wp_head();?>
</head>
<body class="lumiere_body<?php if (isset($imdb_admin_values['imdbpopuptheme'])) echo ' lumiere_body_' . $imdb_admin_values['imdbpopuptheme'];?>">

<div id="lumiere_loader" class="center"></div>

<?php
// if no movie was found at all
if (empty($movie) ){
	echo "<h1 align='center'>".esc_html__( "No result found for", 'lumiere-movies')." <i>".$filmid_sanitized."</i></h1>";
	get_footer(); 
	die();
}?>
<h1 align="center"><?php esc_html_e('Results related to', 'lumiere-movies'); echo " <i>" . $filmid_sanitized_for_title; ?></i></h1>

<table class='TableListeResultats'>
	<tr>
		<th class="TableListeResultatsTitre"><?php esc_html_e('Titles matching', 'lumiere-movies'); ?></th>
		<th class="TableListeResultatsTitre imdblt_titlematchingdirector"><?php esc_html_e('Director', 'lumiere-movies'); ?></th>
	</tr>

	<?php
	$current_line=0;

	foreach ($results as $res) {

		// Limit the number of results according to value set in admin		
		$current_line++;
		if ( $current_line > $imdb_admin_values['imdbmaxresults']){
			echo '</table>';echo '<div align="center">' . esc_html__('Maximum of results reached.', 'lumiere-movies') . '</div>'; wp_footer(); echo '</body></html>';exit();}

		echo "	<tr>\n";
		
		// ---- movie part
		echo "		<td class='TableListeResultatsColGauche'><a class='linkpopup' href=\"".esc_url( LUMIERE_URLPOPUPSFILMS . sanitize_text_field( $res->title() ) . "/?mid=".sanitize_text_field( $res->imdbid() ) . "&film=".sanitize_text_field( $res->title() ) )."\" title=\"".esc_html__('more on', 'lumiere-movies')." ".sanitize_text_field( $res->title() )."\" >".sanitize_text_field( $res->title() )." (".intval( $res->year() ).")"."</a> \n";
		echo "&nbsp;&nbsp;<a class=\"linkpopup\" href=\"https://www.imdb.com/title/tt". sanitize_text_field( $res->imdbid() )."\" target=\"_blank\" title='".esc_html__('link to imdb for', 'lumiere-movies')." ".sanitize_text_field( $res->title() )."'>";

			if ($imdb_admin_values['imdbdisplaylinktoimdb'] == true) { # if the user has selected so
		echo "<img class='img-imdb' src='".esc_url( $imdb_admin_values['imdbplugindirectory'].$imdb_admin_values['imdbpicurl'] )."' width='".intval( $imdb_admin_values['imdbpicsize'] )."' alt='".esc_html__('link to imdb for', 'lumiere-movies')." ".sanitize_text_field( $res->title() )."'/></a>";	
			}
		flush ();
	
		// ---- director part
		$realisateur =  $res->director() ;
		if ( (isset($realisateur['0']['name'])) && (! is_null ($realisateur['0']['name'])) ){
		echo "\t<td class='TableListeResultatsColDroite'><a class='linkpopup' href=\"" . esc_url( LUMIERE_URLPOPUPSPERSON . sanitize_text_field( $realisateur['0']["imdb"] ) . "/?mid=" . sanitize_text_field( $realisateur['0']["imdb"] )."&film=" . $filmid_sanitized ) ."\" title=\"".esc_html__('more on', 'lumiere-movies') . " " . sanitize_text_field( $realisateur['0']['name'] ) . "\" >" . sanitize_text_field( $realisateur['0']['name'] ) . "</a>";

			if ($imdb_admin_values['imdbdisplaylinktoimdb'] == true) { # if the user has selected so
		echo "&nbsp;&nbsp;<a class=\"linkpopup\" href=\"".esc_url("https://imdb.com/name/nm".$realisateur['0']["imdb"] )."\" target=\"_blank\" title='".esc_html__('link to imdb for', 'lumiere-movies')." ".sanitize_text_field( $realisateur['0']['name'] )."'>";
		echo "<img class='img-imdb' src='".esc_url( $imdb_admin_values['imdbplugindirectory'].$imdb_admin_values['imdbpicurl'] )."' width='".intval( $imdb_admin_values['imdbpicsize'] )."' alt='".esc_html__('link to imdb for', 'lumiere-movies')." ".sanitize_text_field( $realisateur['0']['name'] )."'/>";
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
wp_footer(); ?>
</body>
</html>
<?php 

exit(); // quit the call of the page, to avoid double loading process ?>


<?php
} else {  //-------------------------------------------------------------------------- 2. accès direct, option spéciale

//--------------------------------------=[Layout]=---------------

// Head ?><!DOCTYPE html>
<html>
<head>
<?php wp_head();?>

</head>
<body class="lumiere_body<?php if (isset($imdb_admin_values['imdbpopuptheme'])) echo ' lumiere_body_' . $imdb_admin_values['imdbpopuptheme'];?>">

                                                <!-- top page menu -->

<div class="lumiere_container ">
	<div class="titrecolonne lumiere_flex_auto">
            <a class="searchaka" href="<?php echo esc_url( LUMIERE_URLPOPUPSSEARCH . "?film=" . $filmid_sanitized . "&norecursive=yes" ); ?>" title="<?php esc_html_e('Search for movies with the same name', 'lumiere-movies'); ?>"><?php esc_html_e('Search AKAs', 'lumiere-movies'); ?></a>
        </div>
	<div class="titrecolonne lumiere_flex_auto">
		<a class='linkpopup' href="<?php echo esc_url( LUMIERE_URLPOPUPSFILMS . $filmid_sanitized . "/?mid=" . $movieid_sanitized . "&film=" . $filmid_sanitized . "&info=" ); ?>" title='<?php echo sanitize_title( $movie->title() ).": ".esc_html__('Movie', 'lumiere-movies'); ?>'><?php esc_html_e('Summary', 'lumiere-movies'); ?></a>
        </div>
	<div class="titrecolonne lumiere_flex_auto">
		<a class='linkpopup' href="<?php echo esc_url( LUMIERE_URLPOPUPSFILMS . $filmid_sanitized . "/?mid=" . $movieid_sanitized . "&film=" . $filmid_sanitized . "&info=actors" ); ?>" title='<?php echo esc_html( $movie->title() ).": ".esc_html__('Actors', 'lumiere-movies'); ?>'><?php esc_html_e('Actors', 'lumiere-movies'); ?></a>
        </div>
	<div class="titrecolonne lumiere_flex_auto">
		<a class='linkpopup' href="<?php echo esc_url( LUMIERE_URLPOPUPSFILMS . $filmid_sanitized . "/?mid=" . $movieid_sanitized . "&film=" . $filmid_sanitized . "&info=crew" ); ?>" title='<?php echo esc_html ( $movie->title() ).": ".esc_html__('Crew', 'lumiere-movies'); ?>'><?php esc_html_e('Crew', 'lumiere-movies'); ?></a>
        </div>
	<div class="titrecolonne lumiere_flex_auto">
		<a class='linkpopup' href="<?php echo esc_url( LUMIERE_URLPOPUPSFILMS . $filmid_sanitized . "/?mid=" . $movieid_sanitized . "&film=" . $filmid_sanitized  . "&info=resume" ); ?>" title='<?php echo esc_html( $movie->title() ).": ".esc_html__('Plot', 'lumiere-movies'); ?>'><?php esc_html_e('Plot', 'lumiere-movies'); ?></a>
        </div>
	<div class="titrecolonne lumiere_flex_auto">
		<a class='linkpopup' href="<?php echo esc_url( LUMIERE_URLPOPUPSFILMS . $filmid_sanitized . "/?mid=" . $movieid_sanitized . "&film=" . $filmid_sanitized  . "&info=divers" ); ?>" title='<?php echo esc_html( $movie->title() ).": ".esc_html__('Misc', 'lumiere-movies'); ?>'><?php esc_html_e('Misc', 'lumiere-movies'); ?></a>
	</div>
</div>

<div class="lumiere_display_flex">
	<div class="lumiere_flex_auto">
		<div class="titrefilm"><?php $title_sanitized=sanitize_text_field($movie->title()); echo $title_sanitized; ?> &nbsp;&nbsp;(<?php echo sanitize_text_field( $movie->year () ); ?>)</div>
		<div class="soustitrefilm"><?php echo sanitize_text_field( $movie->tagline() ); ?> </div>
	</div> 
	<div>
                                                <!-- Movie's picture display -->
	 <?php 	## The picture is either taken from the movie itself or if it doesn't exist, from a standard "no exist" picture.
		## The width value is taken from plugin settings, and added if the "thumbnail" option is unactivated
echo '<img loading="eager" class="imdbincluded-picture" src="';

	if ($photo_url = $movie->photo_localurl() ) { 
		echo esc_url( $photo_url ).'" alt="'.esc_attr( $movie->title() ).'" '; 
	} else { 
		echo $imdb_admin_values['imdbplugindirectory'].'pics/no_pics.gif" alt="'.esc_html__('no picture', 'lumiere-movies').'" '; 
	}

	// add width only if "Display only thumbnail" is on "no"
	if ($imdb_admin_values['imdbcoversize'] == FALSE){
		echo 'width="'.intval( $imdb_admin_values['imdbcoversizewidth'] ).'px" ';
	}

echo '/ >'; ?>

	</div> 
</div> 

<?php if ( (!isset($_GET['info'])) || (empty($_GET['info'])) ){      // display something when nothing has been selected in the menu
         //---------------------------------------------------------------------------introduction part start ?>

                                                <!-- Director -->
	<div>
<?php 
	$director = $movie->director(); 
	if ( (isset($director)) && (!empty($director)) ) {
		$nbtotaldirector=count($director);
?>
       <span class="imdbincluded-subtitle"><?php echo sprintf(esc_attr(_n('Director', 'Directors', $nbtotaldirector, 'lumiere-movies'),  number_format_i18n( $nbtotaldirector ))); ?></span>
<?php		for ($i = 0; $i < $nbtotaldirector; $i++) { ?>
		<a class='linkpopup' href="<?php echo esc_url( LUMIERE_URLPOPUPSPERSON . $director[$i]["imdb"] . "/?mid=" . $director[$i]["imdb"] . "&film=".  $title_sanitized  ); ?>" title='<?php esc_html_e('link to imdb', 'lumiere-movies'); ?>'><?php
 			echo "\n\t\t\t" . sanitize_text_field( $director[$i]["name"] ); 
			if ( $i < $nbtotaldirector -1 ) echo ', '; 
?></a>
<?php		} // endfor 
	} // endisset ?>
	</div>
                                                <!-- Main actors -->
	<div>
<?php 	#### Main actors, limited by admin options
	$cast = $movie->cast();
	$nbactors = empty($imdb_widget_values['imdbwidgetactornumber']) ? $nbactors =  "1" : $nbactors =  intval( $imdb_widget_values['imdbwidgetactornumber'] );
	$nbtotalactors = intval( count($cast) );

	if (!empty($cast)) { ?>
		<span class="imdbincluded-subtitle"><?php esc_html_e('Actors', 'lumiere-movies'); ?></span>
<?php 
		for ($i = 0; ($i < $nbactors) && ($i < $nbtotalactors); $i++) { ?>
			<a class='linkpopup' href="<?php echo esc_url( LUMIERE_URLPOPUPSPERSON  . $cast[$i]["imdb"] . "/?mid=" . $cast[$i]["imdb"] ); ?>" title='<?php esc_html_e('link to imdb', 'lumiere-movies'); ?>'><?php
			 echo "\n\t\t\t" . sanitize_text_field( $cast[$i]["name"] ); ?></a>
<?php		} // endfor 
	} // endisset ?>
	</div>    

	<?php #### Runtime
	$runtime = sanitize_text_field( $movie->runtime() );
	if (!empty($runtime)) { ?>
                                                <!-- Runtime -->
	<div>
	<?php	echo '<span class="imdbincluded-subtitle">' 
		. esc_html__('Runtime', 'lumiere-movies')
		. '</span>'
		. $runtime
		." "
		.esc_html__('minutes', 'lumiere-movies');	?>
	</div> 
	<?php } ?>

	<?php #### Votes
	if (null !== ($movie->votes() ) ) { 
		$votes_sanitized = esc_html($movie->votes());
		$rating_sanitized = esc_html($movie->rating()); ?>                 
	                              <!-- Rating -->
	<div><?php 
			echo '<span class="imdbincluded-subtitle">' 
					. esc_html__('Rating', 'lumiere-movies')
					. '</span>';
			echo " <img src=\"".$imdb_admin_values['imdbplugindirectory'].'pics/showtimes/'.(round($rating_sanitized*2, 0)/0.2)
				. ".gif\" title=\"".esc_html__('vote average ', 'lumiere-movies').$rating_sanitized.esc_html__(' out of 10', 'lumiere-movies')."\"  / >";
			echo " (".number_format($votes_sanitized, 0, '', "'")." ".esc_html__('votes', 'lumiere-movies').")";	?>
	</div> 
	<?php } ?>

	<?php #### Language
	$languages = $movie->languages();
	$nbtotallanguages = count($languages);
	if ( (isset($languages)) && (!empty($languages)) ) { ?>
                                                <!-- Language -->
	<div>
	<?php
		echo '<span class="imdbincluded-subtitle">' 
			. sprintf(esc_attr(_n('Language', 'Languages', $nbtotallanguages, 'lumiere-movies') ) )
			. '</span>';
		for ($i = 0; $i < $nbtotallanguages; $i++) {
			echo sanitize_text_field( $languages[$i] );
			if ($i < $nbtotallanguages -1) echo ", ";
		}?>
	</div> 
	<?php } ?>


	<?php #### Country
	$country = $movie->country();
	$nbtotalcountry = count($country);
	if ( (isset($country)) && (!empty($country)) ) { ?>
                                                <!-- Country -->
	<div>
	<?php
		echo '<span class="imdbincluded-subtitle">' 
			. sprintf(esc_attr(_n('Country', 'Countries', $nbtotalcountry, 'lumiere-movies') ) )
			. '</span>';
		for ($i = 0; $i < $nbtotalcountry; $i++) {
			echo sanitize_text_field( $country[$i] );
			if ($i < $nbtotalcountry -1) echo ", ";
		}?>
	</div> 
	<?php } ?>


	<?php #### Genre
	$genre = $movie->genre();
	if ( (isset($genre)) && (!empty($genre)) ) { 
		$gen = $movie->genres();
		$nbtotalgenre = count($gen);?>
                                                <!-- Genre -->
	<div>
	<?php
		echo '<span class="imdbincluded-subtitle">' 
			. sprintf(esc_attr(_n('Genre', 'Genres', $nbtotalgenre, 'lumiere-movies') ) )
			. '</span>';
		for ($i = 0; $i < $nbtotalgenre; $i++) {
			echo sanitize_text_field( $gen[$i] );
			if ($i < $nbtotalgenre -1) echo ", ";
		}?>
	</div> 
	<?php } ?>

<?php /*
                                                <!-- Sound -->
	$sound = $movie->sound () ?? NULL;

	if ( (isset($sound)) && (!empty($sound)) ) { ?>
     <tr>
        <td class="TitreSousRubriqueColGauche">
            <div class="TitreSousRubrique"><?php esc_html_e('Sound', 'lumiere-movies'); ?>&nbsp;</div>
        </td>
        
        <td colspan="2" class="TitreSousRubriqueColDroite">
		<li><?php	
		   	for ($i = 0; $i + 1 < count ($sound); $i++) {
				echo sanitize_text_field( $sound[$i] );
				echo ", ";
			}
			echo sanitize_text_field( $sound[0] );
            ?></li>
        </td>
     </tr>
	<?php	
	} */?>

<?php } //------------------------------------------------------------------------------ introduction part end ?>



<?php  if ( (isset($_GET['info'])) && ($_GET['info'] == 'actors') ){ 
            // ------------------------------------------------------------------------------ casting part start ?>

                                                <!-- Actors --> 
<?php 
	$cast = $movie->cast();
	$nbactors = empty($imdb_widget_values['imdbwidgetactornumber']) ? $nbactors =  "1" : $nbactors =  intval( $imdb_widget_values['imdbwidgetactornumber'] );
	$nbtotalactors = intval( count($cast) );

	if (!empty($cast)) { ?>

	<div class="imdbincluded-subtitle"><?php echo sprintf(esc_attr(_n('Actor', 'Actors', $nbtotalactors, 'lumiere-movies') ), number_format_i18n($nbtotalactors) ); ?></div>

           
<?php 	for ($i = 0; ($i < $nbtotalactors); $i++) { 
		echo "\n\t" . '<div align="center" class="lumiere_container">';
		echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
		echo sanitize_text_field( $cast[$i]["role"] ); 
		echo "\n\t\t" . '</div>';
		echo "\n\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
		echo "\n\t\t" 
			. '<a class="linkpopup" href="' 
			. esc_url( LUMIERE_URLPOPUPSPERSON  
			. $cast[$i]["imdb"] 
			. "/?mid=" . $cast[$i]["imdb"] ) 
			. '" title="' 
			. esc_html__('link to imdb', 'lumiere-movies') 
			. '">';
		echo "\n\t\t" . sanitize_text_field( $cast[$i]["name"] ); 
		echo "\n\t\t</a>";
		echo "\n\t\t</div>";
		echo "\n\t</div>";
 		} // endfor 
	} //end endisset ?>		
		
<?php } // ------------------------------------------------------------------------------ casting part end ?>

<?php  if ( (isset($_GET['info'])) && ($_GET['info'] == 'crew') ){ 
            // ------------------------------------------------------------------------------ crew part start ?>

                                                <!-- director -->
        <?php $director = $movie->director(); 
		  if ( (isset($director)) && (!empty($director)) ) {
			$director_count=count($director);?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique"><?php echo(sprintf(_n('Director', 'Directors', $director_count, 'lumiere-movies'),  number_format_i18n( $director_count ))); ?>&nbsp;</div>
            </td>
            
            <td colspan="2" class="TitreSousRubriqueColDroite">
                <?php for ($i = 0; $i < $director_count; $i++) { ?>
					<li>
						<div align="center">
							<div class="imdblt_float_left">
								<?php if ( $i > 0 ) echo ', '; ?>
								<a class='linkpopup' href="<?php echo esc_url( LUMIERE_URLPOPUPSPERSON . $director[$i]["imdb"] . "/?mid=" . $director[$i]["imdb"] . "&film=".  $title_sanitized  ); ?>" title='<?php esc_html_e('link to imdb', 'lumiere-movies'); ?>'>
								<?php echo sanitize_text_field( $director[$i]["name"] ); ?></a>
							</div>
							<div align="right">
								<?php echo sanitize_text_field( $director[$i]["role"] ); ?>
							</div>
						</div>
					</li>
                <?php }; // endfor ?>
			<br /><br />
            </td>
        </tr>
        <?php }; 
		flush(); // send to user data already run through ?>	
                                                <!-- Writer -->
        <?php $write = $movie->writing(); 
		  if (!empty($write)) {?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique"><?php echo(sprintf(_n('Writer', 'Writers', count($write), 'lumiere-movies'))); ?>&nbsp;</div>
            </td>
            
		<td colspan="2" class="TitreSousRubriqueColDroite">
		<?php  for ($i = 0; $i < count ($write); $i++) {  ?>
			<li>
				<div align="center" class="imdbdiv-liees">
					<div class="imdblt_float_left">
						<a class='linkpopup' href="<?php echo esc_url( LUMIERE_URLPOPUPSPERSON . $write[$i]["imdb"] . "/?mid=" . $write[$i]["imdb"] . "&film=".  $title_sanitized  ) ?>" title='<?php esc_html_e('link to imdb', 'lumiere-movies'); ?>'>
						<?php echo sanitize_text_field( $write[$i]["name"] ); ?></a>
					</div>
					<div align="right">
			                	<?php echo sanitize_text_field( $write[$i]["role"] ); ?>
					</div>
				</div>
			</li>
                <?php }; // endfor ?>
		<br />
            </td>
        </tr>
        <?php }; 
	flush(); // send to user data already run through ?>	
		
                                                <!-- producer -->
        <?php $produce = $movie->producer(); 
		if (!empty($produce)) { ?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique"><?php echo(sprintf(_n('Producer', 'Producers', count($produce), 'lumiere-movies'))); ?>&nbsp;</div>
            </td>
            
		<td colspan="2" class="TitreSousRubriqueColDroite">
                <?php  for ($i = 0; $i < count ($produce); $i++) {  ?>
			<li>
				<div align="center" class="imdbdiv-liees">
					<div class="imdblt_float_left">
                		            	<a class='linkpopup' href="<?php echo esc_url( LUMIERE_URLPOPUPSPERSON . $produce[$i]["imdb"] . "/?mid=" . $produce[$i]["imdb"] . "&film=".  $title_sanitized  ); ?>" title='<?php esc_html_e('link to imdb', 'lumiere-movies'); ?>'>
                		            	<?php echo sanitize_text_field( $produce[$i]["name"] ); ?></a>
					</div>
					<div align="right">
						<?php echo sanitize_text_field( $produce[$i]["role"] ); ?>
					</div>
				</div>
			</li>
                <?php }; // endfor ?>
            	</td>
        </tr>
	<?php }; ?>
		
		
<?php } //----------------------------------------------------------------------------- crew part end ?>

     
<?php  if ( (isset($_GET['info'])) && ($_GET['info'] == 'resume') ){ 
            // ------------------------------------------------------------------------------ resume part start ?>

                                                <!-- resume short --> 
        <?php $plotoutline = $movie->plotoutline();
				if (!empty($plotoutline)) { 
					$plotoutline_count=count(array($plotoutline));?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique"><?php echo(sprintf(_n('Plot outline', 'Plots outline', $plotoutline_count, 'lumiere-movies'), number_format_i18n( $plotoutline_count ) )); ?>&nbsp;</div>
            </td>
            
            <td colspan="2" class="TitreSousRubriqueColDroite">
				<li><?php echo sanitize_text_field( $plotoutline ); ?><br /><br /></li>
            </td>
        </tr>
    	 <?php 	} ?>

                                                <!-- resume long --> 
        <?php $plot = $movie->plot (); 
			if (!empty($plot)) { ?>
	<tr>
		<td class="TitreSousRubriqueColGauche">
			<div class="TitreSousRubrique"><?php echo(sprintf(_n('Plot', 'Plots', $plot, 'lumiere-movies',count($plot)))); ?>&nbsp;&nbsp;</div>
		</td>
            
		<td colspan="2" class="TitreSousRubriqueColDroite">
			<li>
				<?php for ($i = 1; $i < count ($plot); $i++) {
					echo "<strong>($i)</strong> ". $plot[$i] ."<br /><br />"; 
				};?>
			</li>
		</td>
	</tr>
    	<?php 	} ?>
	 
<?php	 } // ------------------------------------------------------------------------------ resume part end ?>


<?php  if ( (isset($_GET['info'])) && ($_GET['info'] == 'divers') ){ 
            // ------------------------------------------------------------------------------ misc part start ?>

                                                <!-- Trivia --> 
		 <?php $trivia = $movie->trivia();
		  $gc = count($trivia);
		  if ($gc > 0) { ?>
	        <tr>
			<td class="TitreSousRubriqueColGauche">
				<div class="TitreSousRubrique"><?php echo(sprintf(_n('Trivia', 'Trivias', count($trivia), 'lumiere-movies'))); ?>&nbsp;</div>
			</td>
			<td colspan="2" class="TitreSousRubriqueColDroite">
				<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
				<div class="hidesection">
			<?php		
			for ($i=0;$i<$gc;++$i) {
     				if (empty($trivia[$i])) break;
					$ii = $i+"1";
					echo "<li><strong>($ii)</strong> ".preg_replace("/https\:\/\/".str_replace(".","\.",$movie->imdbsite)."\/name\/nm(\d{7})\//","popup-imdb_person.php?mid=\\1",sanitize_text_field( $trivia[$i]) )."</li><br />\n";
		    }; ?>
				</div>
            		</td>
    	    	</tr>	
    		<?php } ?>


                                                <!-- Soundtrack -->

		<?php $soundtracks = $movie->soundtrack();
			  $gc = count($soundtracks);
			  if ($gc > 0) { ?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique">
					<?php echo(sprintf(_n('Soundtrack', 'Soundtracks', count($soundtracks), 'lumiere-movies'))); ?> 
				</div>
           	</td>

		<td colspan="2" class="TitreSousRubriqueColDroite">
			<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
			<div class="hidesection">            
	 			<?php for ($i=0;$i<$gc;++$i) {
						$ii = $i+"1";
							if (empty($soundtracks[$i])) break;
						$credits2_isset = (isset($soundtracks[$i]["credits"][1])) ? $soundtracks[$i]["credits"][1] : "" ;
						$credit1 = preg_replace("/https\:\/\/".str_replace(".","\.",$movie->imdbsite)."\/name\/nm(\d{7})\//","popup-imdb_person.php?mid=\\1",sanitize_text_field( $soundtracks[$i]["credits"][0] ));
						$credit2 = preg_replace("/http\:\/\/".str_replace(".","\.",$movie->imdbsite)."\/name\/nm(\d{7})\//","popup-imdb_person.php?mid=\\1",sanitize_text_field($credits2_isset));
						echo "<li><strong>($ii)</strong> ".sanitize_text_field( $soundtracks[$i]["soundtrack"] )." ".$credit1." ".$credit2."</li><br />";
    				} 
				flush(); // send to user data already run through ?>
			</div>
		</td>
	</tr>
		<?php } ?>

                                                <!-- Goofs --> 
		 <?php $goofs = $movie->goofs();
		  $gc    = count($goofs);
		  if ($gc > 0) { ?>
        <tr>
            	<td class="TitreSousRubriqueColGauche">
                	<div class="TitreSousRubrique"><?php echo(sprintf(_n('Goof', 'Goofs', count($goofs), 'lumiere-movies'))); ?>&nbsp;</div>
            	</td>
		<td colspan="2" class="TitreSousRubriqueColDroite">
			<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
			<div class="hidesection">       		            			  
				<?php		
				for ($i=0;$i<$gc;++$i) {
					 if (empty($goofs[$i])) break;
					 $ii = $i+"1";
				echo "<li><strong>($ii) ".sanitize_text_field( $goofs[$i]["type"] )."</strong> ".sanitize_text_field( $goofs[$i]["content"] )."</li><br />";
				}; ?>
			</div>
            	</td>
        </tr>
    	<?php } ?>

<?php	 } // ------------------------------------------------------------------------------ misc part end ?>
</table>
<br />

<?php 	
	wp_footer(); 
?>
</body>
</html>
<?php exit(); // quit the call of the page, to avoid double loading process 
}
?>
