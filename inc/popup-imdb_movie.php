<?php

 #############################################################################
 # IMDb Link transformer                                                     #
 # written by Prometheus group                                               #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									     #
 #  Function : Popup movie section    					     #
 #									     #
 #############################################################################

require_once (plugin_dir_path( __FILE__ ).'/../bootstrap.php');

do_action('wp_loaded'); // execute wordpress first codes

//---------------------------------------=[Vars]=----------------
global $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

// Start config class for $config in below Imdb\Title class calls
if (class_exists("imdb_settings_conf")) {
	$config = new imdb_settings_conf();
	$config->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;
	$config->photodir = $imdb_cache_values['imdbphotodir'] ?? NULL;
	$config->imdb_img_url = $imdb_cache_values['imdbimgdir'] ?? NULL;
	$config->photoroot = $imdb_cache_values['imdbphotoroot'] ?? NULL;
}



$movieid_sanitized = filter_var( $_GET["mid"], FILTER_SANITIZE_NUMBER_INT) ?? NULL;
$filmid_sanitized = sanitize_title_for_query( $_GET["film"]) ?? NULL;

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

if ((isset ($movieid_sanitized)) && !empty ($movieid_sanitized)) {
	$movie = new Imdb\Title($movieid_sanitized, $config);
} else {
	$search = new Imdb\TitleSearch($config);
	if ($_GET["searchtype"]=="episode") {
		$movie = $search->search ($filmid_sanitized, array(\Imdb\TitleSearch::TV_SERIES))[0];
	} else {
		$movie = $search->search ($filmid_sanitized, array(\Imdb\TitleSearch::MOVIE))[0];
	}
}

if (($imdb_admin_values['imdbdirectsearch'] == false ) OR ($_GET["norecursive"] == 'yes')) { //------------------------- 1. search all results related to the name of the movie

	if ($_GET["searchtype"]=="episode") 
		$results = $search->search ( $filmid_sanitized, array(\Imdb\TitleSearch::TV_SERIES));
	else 
		$results = $search->search ( $filmid_sanitized, array(\Imdb\TitleSearch::MOVIE));

	//require_once ('popup-header.php'); 
	get_header(); 
?>
<h1><?php esc_html_e('Results related to', 'imdb'); echo " " . sanitize_text_field( $movie->title() ); ?></h1>

<table class='TableListeResultats'>
	<tr>
		<th class="TableListeResultatsTitre"><?php esc_html_e('Titles matching', 'imdb'); ?></th>
		<th class="TableListeResultatsTitre imdblt_titlematchingdirector"><?php esc_html_e('Director', 'imdb'); ?></th>
	</tr>

	<?php
	foreach ($results as $res) {
		echo "	<tr>\n";
		
		// ---- movie part
		echo "		<td class='TableListeResultatsColGauche'><a href=\"".esc_url( $imdb_admin_values['imdbplugindirectory']."inc/popup-imdb_movie.php?mid=".sanitize_text_field( $res->imdbid() ) . "&film=".sanitize_text_field( $res->title() ) )."\" title=\"".esc_html__('more on', 'imdb')." ".sanitize_text_field( $res->title() )."\" >".sanitize_text_field( $res->title() )." (".intval( $res->year() ).")"."</a> \n";
		echo "&nbsp;&nbsp;<a class=\"imdblink\" href=\"https://www.imdb.com/title/tt". sanitize_text_field( $res->imdbid() )."\" target=\"_blank\" title='".esc_html__('link to imdb for', 'imdb')." ".sanitize_text_field( $res->title() )."'>";

			if ($imdb_admin_values['imdbdisplaylinktoimdb'] == true) { # if the user has selected so
		echo "<img  class='img-imdb' src='".esc_url( $imdb_admin_values[imdbplugindirectory].$imdb_admin_values[imdbpicurl] )."' width='".intval( $imdb_admin_values[imdbpicsize] )."' alt='".esc_html__('link to imdb for', 'imdb')." ".sanitize_text_field( $res->title() )."'/></a>";	
			}
		echo "</td>\n";
		flush ();
	
		// ---- director part
		$realisateur =  $res->director() ;
		if (! is_null ($realisateur['0']['name'])){
		echo "		<td class='TableListeResultatsColDroite'><a href=\"".esc_url( $imdb_admin_values[imdbplugindirectory] )."inc/popup-imdb_person.php?mid=".sanitize_text_field( $realisateur['0']['imdb'] )."&film=".$filmid_sanitized."\" title=\"".esc_html__('more on', 'imdb')." ".sanitize_text_field( $realisateur['0']['name'] )."\" >".sanitize_text_field( $realisateur['0']['name'] )."</a>";

			if ($imdb_admin_values[imdbdisplaylinktoimdb] == true) { # if the user has selected so
		echo "&nbsp;&nbsp;<a class=\"imdblink\" href=\"".esc_url("https://imdb.com/name/nm".$realisateur['0']['imdb'] )."\" target=\"_blank\" title='".esc_html__('link to imdb for', 'imdb')." ".sanitize_text_field( $realisateur['0']['name'] )."'>";
		echo "<img class='img-imdb' src='".esc_url( $imdb_admin_values[imdbplugindirectory].$imdb_admin_values[imdbpicurl] )."' width='".intval( $imdb_admin_values[imdbpicsize] )."' alt='".esc_html__('link to imdb for', 'imdb')." ".sanitize_text_field( $realisateur['0']['name'] )."'/>";
		echo "</a>";
			}
			
		echo "</td>\n";
		}
		echo "	</tr>\n";
		flush ();
	} // end foreach  ?> 

</table>
<?php
// call wordpress footer functions;
wp_meta();
//get_footer(); // this one gets too much uneeded information
wp_footer(); ?>

</body>
</html>

<?php exit(); // quit the call of the page, to avoid double loading process ?>


<?php
} else {  //-------------------------------------------------------------------------- 2. accès direct, option spéciale

//--------------------------------------=[Layout]=---------------

		//require_once ('popup-header.php'); 
get_header(); 
?>
                                                <!-- top page menu -->
<table class='tabletitrecolonne'>
    <tr>
        <td class='titrecolonne'>
            <a class="searchaka" href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] . "inc/" . "popup-search.php?film=" . sanitize_text_field( imdb_htmlize( $movie->title() ) ) . "&norecursive=yes" ); ?>" title="<?php esc_html_e('Search for movies with the same name', 'imdb'); ?>"><?php esc_html_e('Search AKAs', 'imdb'); ?></a>
        </td>
        <td class='titrecolonne'>
		<a href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] ."inc/" . "popup-imdb_movie.php?mid=" . $movieid_sanitized . "&film=" . sanitize_text_field( $_GET['film'] ) . "&info=" ); ?>" title='<?php echo sanitize_title( $movie->title() ).": ".esc_html__('Movie', 'imdb'); ?>'><?php esc_html_e('Movie', 'imdb'); ?></a>
	</td>
        <td class='titrecolonne'>
		<a href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] ."inc/" . "popup-imdb_movie.php?mid=" . $movieid_sanitized . "&film=" . sanitize_text_field( $_GET['film'] ) . "&info=actors" ); ?>" title='<?php echo sanitize_title( $movie->title() ).": ".esc_html__('Actors', 'imdb'); ?>'><?php esc_html_e('Actors', 'imdb'); ?></a>
	</td>
        <td class='titrecolonne'>
		<a href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] ."inc/" . "popup-imdb_movie.php?mid=" . $movieid_sanitized . "&film=" . sanitize_text_field( $_GET['film'] ) . "&info=crew" ); ?>" title='<?php echo sanitize_title ( $movie->title() ).": ".esc_html__('Crew', 'imdb'); ?>'><?php esc_html_e('Crew', 'imdb'); ?></a>
	</td>
        <td class='titrecolonne'>
		<a href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] ."inc/" . "popup-imdb_movie.php?mid=" . $movieid_sanitized . "&film=" . sanitize_text_field( $_GET['film'] ) . "&info=resume" ); ?>" title='<?php echo sanitize_title( $movie->title() ).": ".esc_html__('Plot', 'imdb'); ?>'><?php esc_html_e('Plot', 'imdb'); ?></a>
	</td>
        <td class='titrecolonne'>
		<a href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] ."inc/" . "popup-imdb_movie.php?mid=" . $movieid_sanitized . "&film=" . sanitize_text_field( $_GET['film'] ) . "&info=divers" ); ?>" title='<?php echo sanitize_title( $movie->title() ).": ".esc_html__('Misc', 'imdb'); ?>'><?php esc_html_e('Misc', 'imdb'); ?></a>
	</td>
    </tr>
</table>

<table class="TableauPresentation" width="100%">
    <tr width="100%">
        <td colspan="2">
            <div class="titrefilm"><?php $title_sanitized=sanitize_text_field($movie->title()); echo $title_sanitized; ?> &nbsp;&nbsp;(<?php echo sanitize_text_field( $movie->year () ); ?>)</div>
            <div class="soustitrefilm"><?php echo sanitize_text_field( $movie->tagline() ); ?> </div>
            <?php flush (); ?>
        </td>
                                                <!-- Movie's picture display -->
        <td class="colpicture">
	 <?php 	## The picture is either taken from the movie itself or if it doesn't exist, from a standard "no exist" picture.
		## The width value is taken from plugin settings, and added if the "thumbnail" option is unactivated
echo '<img class="imdbincluded-picture" src="';

	if ($photo_url = $movie->photo_localurl() ) { 
		echo esc_url( $photo_url ).'" alt="'.esc_attr( $movie->title() ).'" '; 
	} else { 
		echo $imdb_admin_values['imdbplugindirectory'].'pics/no_pics.gif" alt="'.esc_html__('no picture', 'imdb').'" '; 
	}

	// add width only if "Display only thumbnail" is on "no"
	if ($imdb_admin_values['imdbcoversize'] == FALSE){
		echo 'width="'.intval( $imdb_admin_values['imdbcoversizewidth'] ).'px" ';
	}

echo '/ >'; ?>

         </td>
    </tr>
</table>
  
 
<table class="TableauSousRubrique">

<?php if (empty($_GET['info'])){      // display something when nothing has been selected in the menu
         //---------------------------------------------------------------------------introduction part start ?>
     
                                                <!-- Title akas -->         
     <tr> 
         <td class="TitreSousRubriqueColGauche">
            <div class="TitreSousRubrique"><?php esc_html_e('AKA', 'imdb'); ?>&nbsp;</div>
         </td>
         <td colspan="2" class="TitreSousRubriqueColDroite">
		 	<li>
<?php 	$aka = $movie->alsoknow();
	//$cc  = count($aka);
	if (!empty($aka)) {
		foreach ( $aka as $ak){
      			echo sanitize_text_field( $ak["title"] );
			if (!empty($ak["year"])) {
				echo " ". intval( $ak["year"] );
			};
      			if (!empty($ak["country"])) {
      				echo  " (".sanitize_text_field($ak["country"].")" );
			}
			/*if (empty($ak["lang"])) { 
					if (!empty($ak["comment"])) {
					echo ", ".$ak["comment"]; }
			} else {
				if (!empty($ak["comment"])) {
					echo ", ".$ak["comment"];}
			echo " [".$ak["lang"]."]";
	  			}*/
	  		echo "<br />";
		}
		flush();
  	}  ?>
			</li>
         </td>
     </tr>
                                                <!-- Year -->
     <tr>
        <td class="TitreSousRubriqueColGauche">
            <div class="TitreSousRubrique"><?php esc_html_e('Year', 'imdb'); ?>&nbsp;</div>
        </td>
        <td colspan="2" class="TitreSousRubriqueColDroite">
             <li><?php echo intval( $movie->year() ); ?></li>
        </td>
     </tr>
                                                <!-- Runtime -->
     <tr>
        <td class="TitreSousRubriqueColGauche">
            <div class="TitreSousRubrique"><?php esc_html_e('Runtime', 'imdb'); ?>&nbsp;</div>
         </td>

        
        <td colspan="2" class="TitreSousRubriqueColDroite">
		<?php $runtime = sanitize_text_field( $movie->runtime() );
		if (!empty($runtime)) { ?>
        	<li><?php echo $runtime." ".esc_html__('minutes', 'imdb'); ?></li>
		<?php }; 
		flush(); // send to user data already run through ?>
        </td>
     </tr>
     
     <?php if ($movie->votes()) { ?>              <!-- Rating and votes -->
     <tr>
        <td class="TitreSousRubriqueColGauche">
           <div class="TitreSousRubrique"><?php esc_html_e('Rating', 'imdb'); ?>&nbsp;</div>
        </td>
        
        <td colspan="2" class="TitreSousRubriqueColDroite">
            <li><?php esc_html_e('Vote average', 'imdb'); ?> <?php echo sanitize_text_field( $movie->rating() ); ?>, <?php esc_html_e('with ', 'imdb'); echo intval( $movie->votes() ) . " "; esc_html_e('votes', 'imdb'); ?></li>
        </td>
     </tr>
     <?php }; ?>
     
                                                <!-- Language -->
	<?php   $languages = $movie->languages();
	if (!empty($languages)) { ?>
     <tr>
        <td class="TitreSousRubriqueColGauche">
            <div class="TitreSousRubrique"><?php echo(sprintf(_n('Language', 'Languages', count($languages), 'imdb'))); ?>&nbsp;</div>
        </td>
        
        <td colspan="2" class="TitreSousRubriqueColDroite">
	        <li><?php 
		for ($i = 0; $i + 1 < count($languages); $i++) {
			echo sanitize_text_field( $languages[$i] );
			echo ", ";
		}
		echo sanitize_text_field( $languages[$i] ); 
		?></li>
        </td>
     </tr>
     <?php }; 
	flush(); // send to user data already run through ?>
             
			                                    <!-- Country -->
	<?php $country = $movie->country();
	if (!empty($country)) { ?>
     <tr>
        <td class="TitreSousRubriqueColGauche">
            <div class="TitreSousRubrique"><?php echo(sprintf(_n('Country', 'Countries', count($country), 'imdb'))); ?>&nbsp;</div>
        </td>
        
        <td colspan="2" class="TitreSousRubriqueColDroite">
            <li><?php
                    for ($i = 0; $i + 1 < count ($country); $i++) {
	                echo sanitize_text_field( $country[$i] );
	                echo ", ";
                    }
                    echo sanitize_text_field( $country[$i] ); 
            ?></li>
        </td>
     </tr>
     <?php }; ?>

                                                <!-- All Genres -->
     <tr>
        <td class="TitreSousRubriqueColGauche">
            <div class="TitreSousRubrique"><?php esc_html_e('Genre', 'imdb'); ?>&nbsp;</div>
        </td>
        
        <td colspan="2" class="TitreSousRubriqueColDroite">
		<li><?php 
		$test = $movie->genre ();  
		if (! empty($test)) {
			$gen = $movie->genres();
			
                        for ($i = 0; $i + 1 < count ($gen); $i++) {
	                    echo sanitize_text_field( $gen[$i] );
	                    echo ", ";
                        }
            	echo $gen[$i];
		}
		flush(); // send to user data already run through  
		?></li>
        </td>
     </tr>
                                                <!-- Colors -->
     <tr>
        <td class="TitreSousRubriqueColGauche">
            <div class="TitreSousRubrique"><?php esc_html_e('Color', 'imdb'); ?>&nbsp;</div>
        </td>
        
        <td colspan="2" class="TitreSousRubriqueColDroite">
		<li><?php	$col = $movie->colors ();
                   	for ($i = 0; $i + 1 < count ($col); $i++) {
	           		echo sanitize_text_field( $col[$i] );
	           		echo ", ";
                      	}
                       	echo sanitize_text_field( $col[$i] );
		?></li>
        </td>
     </tr>
                                                <!-- Sound -->
     <tr>
        <td class="TitreSousRubriqueColGauche">
            <div class="TitreSousRubrique"><?php esc_html_e('Sound', 'imdb'); ?>&nbsp;</div>
        </td>
        
        <td colspan="2" class="TitreSousRubriqueColDroite">
            <li><?php   $sound = $movie->sound ();
                        for ($i = 0; $i + 1 < count ($sound); $i++) {
	                    echo sanitize_text_field( $sound[$i] );
	                    echo ", ";
                        }
            echo $sound[$i];
            ?></li>
        </td>
     </tr>

<?php } //------------------------------------------------------------------------------ introduction part end ?>


<?php  if ($_GET['info'] == 'actors'){ 
            // ------------------------------------------------------------------------------ casting part start ?>

                                                <!-- casting --> 
        <?php $cast = $movie->cast(); 
			if (!empty($cast)) { ?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique"><?php esc_html_e('Casting', 'imdb'); ?>&nbsp;</div>
            </td>
            
            <td colspan="2" class="TitreSousRubriqueColDroite">
                <?php for ($i = 0; $i < count ($cast); $i++) { ?>
					<li>
						<div align="center" class="imdbdiv-liees">
							<div class="imdblt_float_left">
								<?php echo sanitize_text_field( $cast[$i]["role"] ); ?>
							</div>
							<div align="right">
								<a href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory]."inc/" . "popup-imdb_person.php?mid=" . $cast[$i]["imdb"] . "&film=".  $title_sanitized ); ?>" title='<?php esc_html_e('link to imdb', 'imdb'); ?>'>
								<?php echo sanitize_text_field( $cast[$i]["name"] ); ?></a>
							</div>
						</div>
					</li>
                <?php }; // endfor ?>
            </td>
        </tr>
        <?php }; ?>		
		
<?php } // ------------------------------------------------------------------------------ casting part end ?>

<?php if ($_GET['info'] == 'crew'){ 
            // ------------------------------------------------------------------------------ crew part start ?>

                                                <!-- director -->
        <?php $director = $movie->director(); 
		  if (!empty($director)) {?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique"><?php echo(sprintf(_n('Director', 'Directors', count($director), 'imdb'))); ?>&nbsp;</div>
            </td>
            
            <td colspan="2" class="TitreSousRubriqueColDroite">
                <?php for ($i = 0; $i < count ($director); $i++) { ?>
					<li>
						<div align="center">
							<div class="imdblt_float_left">
								<?php if ( $i > 0 ) echo ', '; ?>
								<a href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory]."inc/" . "popup-imdb_person.php?mid=" . $director[$i]["imdb"] . "&film=".  $title_sanitized  ); ?>" title='<?php esc_html_e('link to imdb', 'imdb'); ?>'>
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
                <div class="TitreSousRubrique"><?php echo(sprintf(_n('Writer', 'Writers', count($write), 'imdb'))); ?>&nbsp;</div>
            </td>
            
		<td colspan="2" class="TitreSousRubriqueColDroite">
		<?php  for ($i = 0; $i < count ($write); $i++) {  ?>
			<li>
				<div align="center" class="imdbdiv-liees">
					<div class="imdblt_float_left">
						<a href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory]."inc/popup-imdb_person.php?mid=" . $write[$i]["imdb"] . "&film=".  $title_sanitized  ) ?>" title='<?php esc_html_e('link to imdb', 'imdb'); ?>'>
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
                <div class="TitreSousRubrique"><?php echo(sprintf(_n('Producer', 'Producers', count($produce), 'imdb'))); ?>&nbsp;</div>
            </td>
            
		<td colspan="2" class="TitreSousRubriqueColDroite">
                <?php  for ($i = 0; $i < count ($produce); $i++) {  ?>
			<li>
				<div align="center" class="imdbdiv-liees">
					<div class="imdblt_float_left">
                		            	<a href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory]."inc/popup-imdb_person.php?mid=" . $produce[$i]["imdb"] . "&film=".  $title_sanitized  ); ?>" title='<?php esc_html_e('link to imdb', 'imdb'); ?>'>
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

     
<?php  if ($_GET['info'] == 'resume'){ 
            // ------------------------------------------------------------------------------ resume part start ?>

                                                <!-- resume short --> 
        <?php $plotoutline = $movie->plotoutline();
				if (!empty($plotoutline)) { ?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique"><?php echo(sprintf(_n('Plot outline', 'Plots outline', count($plotoutline), 'imdb'))); ?>&nbsp;</div>
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
			<div class="TitreSousRubrique"><?php echo(sprintf(_n('Plot', 'Plots', count($plot), 'imdb'))); ?>&nbsp;&nbsp;</div>
		</td>
            
		<td colspan="2" class="TitreSousRubriqueColDroite">
			<li>
				<?php for ($i = 1; $i < count ($plot); $i++) {
					echo "<strong>($i)</strong> ".sanitize_text_field( $plot[$i] )."<br /><br />"; 
				};?>
			</li>
		</td>
	</tr>
    	<?php 	} ?>
	 
<?php	 } // ------------------------------------------------------------------------------ resume part end ?>


<?php 	if ($_GET['info'] == 'divers'){ 
            // ------------------------------------------------------------------------------ misc part start ?>

                                                <!-- Trivia --> 
		 <?php $trivia = $movie->trivia();
		  $gc = count($trivia);
		  if ($gc > 0) { ?>
	        <tr>
			<td class="TitreSousRubriqueColGauche">
				<div class="TitreSousRubrique"><?php echo(sprintf(_n('Trivia', 'Trivias', count($trivia), 'imdb'))); ?>&nbsp;</div>
			</td>
			<td colspan="2" class="TitreSousRubriqueColDroite">
				<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'imdb'); ?> [+]</div>
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
					<?php echo(sprintf(_n('Soundtrack', 'Soundtracks', count($soundtracks), 'imdb'))); ?> 
				</div>
           	</td>

		<td colspan="2" class="TitreSousRubriqueColDroite">
			<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'imdb'); ?> [+]</div>
			<div class="hidesection">            
	 			<?php for ($i=0;$i<$gc;++$i) {
						$ii = $i+"1";
							if (empty($soundtracks[$i])) break;
						$credit1 = preg_replace("/https\:\/\/".str_replace(".","\.",$movie->imdbsite)."\/name\/nm(\d{7})\//","popup-imdb_person.php?mid=\\1",sanitize_text_field( $soundtracks[$i]["credits"][0] ));
						$credit2 = preg_replace("/http\:\/\/".str_replace(".","\.",$movie->imdbsite)."\/name\/nm(\d{7})\//","popup-imdb_person.php?mid=\\1",sanitize_text_field( $soundtracks[$i]["credits"][1] ));
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
                	<div class="TitreSousRubrique"><?php echo(sprintf(_n('Goof', 'Goofs', count($goofs), 'imdb'))); ?>&nbsp;</div>
            	</td>
		<td colspan="2" class="TitreSousRubriqueColDroite">
			<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'imdb'); ?> [+]</div>
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
<?php 	// call wordpress footer functions;
	wp_meta();
	//get_footer(); // this one gets too much uneeded information
	wp_footer(); 
?>
</body>
</html>
<?php 	exit(); // quit the call of the page, to avoid double loading process 
}
?>
