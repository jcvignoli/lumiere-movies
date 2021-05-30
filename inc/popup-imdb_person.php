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
 #  Function : Popup people section    					       #
 #									              #
 #############################################################################

//---------------------------------------=[Vars]=----------------

require_once (plugin_dir_path( __DIR__ ).'bootstrap.php');

do_action('wp_loaded'); // execute wordpress first codes

//---------------------------------------=[Vars]=----------------
global $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

// Start config class for $config in below Imdb\Title class calls
if (class_exists("lumiere_settings_conf")) {
	$config = new lumiere_settings_conf();
	$config->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;
	$config->photodir = $imdb_cache_values['imdbphotoroot'] ?? NULL; // ?imdbphotoroot? Bug imdbphp?
	$config->imdb_img_url = $imdb_cache_values['imdbimgdir'] ?? NULL;
	$config->photoroot = $imdb_cache_values['imdbphotodir'] ?? NULL; // ?imdbphotodir? Bug imdbphp?
	$config->language = $imdb_admin_values['imdblanguage'] ?? NULL;
}

if (isset ($_GET["film"]))
	$film_sanitized = sanitize_text_field( $_GET["film"] ) ?? NULL;

if (isset ($_GET["mid"]))
	$mid_sanitized = filter_var( $_GET["mid"], FILTER_SANITIZE_NUMBER_INT) ?? NULL;

// if neither film nor mid are set, throw a 404 error
if (empty($film_sanitized ) && empty($mid_sanitized)){
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

if (isset ($mid_sanitized)) {
	$person = new Imdb\Person($mid_sanitized, $config);
	$person_name_sanitized = sanitize_text_field( $person->name() );

//--------------------------------------=[Layout]=---------------
?>
<html>
<head>
<?php wp_head();?>

</head>
<body class="lumiere_body">

                                                <!-- top page menu -->
<table class='tabletitrecolonne'>
    <tr>
        <td class='titrecolonne'>
            <a class="historyback"><?php esc_html_e('Back', 'lumiere-movies'); ?></a>
        </td>
 		<td class='titrecolonne'>
			<a class='linkpopup' href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] ."inc/popup-imdb_person.php?mid=". $mid_sanitized . "&film=" . $film_sanitized . "&info=" ); ?>" title='<?php echo $person_name_sanitized.": ".esc_html__('Filmography', 'lumiere-movies'); ?>'><?php esc_html_e('Filmography', 'lumiere-movies'); ?></a>
		</td>
		
		<td class='titrecolonne'>
			<a class='linkpopup' href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] ."inc/popup-imdb_person.php?mid=". $mid_sanitized . "&film=" . $film_sanitized . "&info=bio" ); ?>" title='<?php echo $person_name_sanitized.": ".esc_html__('Biography', 'lumiere-movies'); ?>'><?php esc_html_e('Biography', 'lumiere-movies'); ?></a>
		</td>
		
		<td class="titrecolonne">
			<a class='linkpopup' href="<?php echo esc_url( $imdb_admin_values[imdbplugindirectory] ."inc/popup-imdb_person.php?mid=". $mid_sanitized . "&film=" . $film_sanitized . "&info=divers" ); ?>" title='<?php echo $person_name_sanitized.": ".esc_html__('Misc', 'lumiere-movies'); ?>'><?php esc_html_e('Misc', 'lumiere-movies'); ?>
		</td>
		
		<td class='titrecolonne'></td>
   </tr>
</table>

                                                <!-- Photo & identity -->

<table class="TableauPresentation">
    <tr>
        <td colspan="2">
            <div class="identity"><?php echo $person_name_sanitized; ?> &nbsp;&nbsp;</div>
            <div class="soustitreidentity">
			<?php  // Born
			  $birthday = $person->born(); 
			  if (!empty($birthday)) {
			  echo "<strong>".esc_html__('Born on', 'lumiere-movies')."</strong> ".intval($birthday["day"])." ".sanitize_text_field($birthday["month"])." ".intval($birthday["year"]);
			  }
			  if (!empty($birthday["place"])) { echo ", ".esc_html__('in', 'lumiere-movies')." ".sanitize_text_field($birthday["place"]);} ?>
			  <?php // Dead
		      $death = $person->died();
			  if (!empty($death)) {
			  echo "<br /><strong>".esc_html__('Died on', 'lumiere-movies')."</strong> ".intval($death["day"])." ".sanitize_text_field($death["month"])." ".intval($death["year"]);			
			  if (!empty($death["place"])) echo ", ".esc_html__('in', 'lumiere-movies')." ".sanitize_text_field($death["place"]);
			  if (!empty($death["cause"])) echo ", ".esc_html__('cause', 'lumiere-movies')." ".sanitize_text_field($death["cause"]);
			  }	?>
			</div>
			
            <?php flush (); ?>
        </td>
                                                <!-- displaying photo -->
        <td rowspan=110 class="colpicture">
             <?php if (($photo_url = $person->photo_localurl() ) != FALSE){ 
	            echo '<img loading="eager" class="imdbincluded-picture" src="'.esc_url($photo_url).'" alt="'.$person_name_sanitized.'" '; 
              } else{ 
                echo '<img loading="eager" class="imdbincluded-picture" src="'.esc_url($imdb_admin_values['imdbplugindirectory']."pics/no_pics.gif").'" alt="'.esc_html__('no picture', 'lumiere-movies').'" '; 
             } 
	// add width only if "Display only thumbnail" is on "no"
	if ($imdb_admin_values['imdbcoversize'] == FALSE){
		echo 'width="'.intval($imdb_admin_values['imdbcoversizewidth']).'px" ';
	}

echo '/ >'; ?>

         </td>
    </tr>
</table>

                                                <!-- under section  -->

<table class="TableauSousRubrique">
	<?php if (empty($_GET['info'])){      // display only when nothing is selected from the menu
	//---------------------------------------------------------------------------start filmography part ?>

                                       <!-- Filmography -->
		<?php $ff = array("director","actor", "producer");
		  foreach ($ff as $var) {
			$fdt = "movies_$var";
			$filmo = $person->$fdt();
			$flname = ucfirst($var);
			if (!empty($filmo)) { ?>
			  <tr>
				<td class="TitreSousRubriqueColGauche">
					<div class="TitreSousRubrique"><?php echo sanitize_text_field($flname);?> filmo</div>
				</td>
			
				<td colspan="2" class="TitreSousRubriqueColDroite">
					<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
					<div class="hidesection">
			<?php
				$ii = "0";
				$tc = count($filmo);
			  	foreach ($filmo as $film) {
			  		$nbfilms = $tc-$ii;
					echo "<li><strong>($nbfilms)</strong> <a class='linkpopup' href='".esc_url( $imdb_admin_values[imdbplugindirectory] ."inc/popup-imdb_movie.php?mid=".$film["mid"])."'>".sanitize_text_field( $film["name"] )."</a>";

					if (!empty($film["year"])) {
						echo " (".intval($film["year"]).")";
					} 

				// if (empty($film["chname"])) { 		//-> the result sent is not empty, but a breakline instead
				if ($film["chname"]=="
") {
					echo "";
				} else {
					if (empty($film["chid"])) { 
						if (!empty($film["chname"]))
							echo " as ".sanitize_text_field($film["chname"]);
					} else { 
						echo " as <a class='linkpopup' href='".esc_url("https://".$person->imdbsite."/character/ch".intval($film["chid"]) )."/'>".$film["chname"]."</a>"; }
				}

				echo "</li>\n\t\t";
				$ii++;

			  } //end for each filmo
			} // endif filmo ?>
		    			</div>
		    		</td>
	    	    	</tr>			
		<?php } //endforeach
		flush(); // send to user data already run through ?>



                                       <!-- Filmography as soundtrack -->
		<?php 	$soundtrack=$person->movies_soundtrack() ;
 			if (!empty($soundtrack)) { ?>
				  <tr>
					<td class="TitreSousRubriqueColGauche">
						<div class="TitreSousRubrique"><?php esc_html_e('Soundtrack', 'lumiere-movies'); ?> filmo</div>
					</td>		
				<td colspan="2" class="TitreSousRubriqueColDroite">
					<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
					<div class="hidesection">
						<?php
						for ($i=0;$i<count($soundtrack);++$i) {
							$ii = $i+"1";
							echo "<li><strong>($ii)</strong> ";
							echo "<a class='linkpopup' href='popup-imdb_movie.php?mid=".intval($soundtrack[$i]["mid"])."'>".sanitize_text_field($soundtrack[$i]["name"])."</a>";
							if (!empty($soundtrack[$i]["name"])) 
								echo " (".$soundtrack[$i]["year"].")";
							echo "</li>\n";
						} ?>
		    			</div>
		    		</td>
	    	    	</tr>

	<?php		}



		} //------------------------------------------------------------------------------ end filmo part ?>

     <?php if ($_GET['info'] == 'bio'){ 
            	// ------------------------------------------------------------------------------ partie bio ?>
                                       <!-- Biographie -->

                        				<!-- Biographical movies -->
		<?php $pm = $person->pubmovies();
			  if (!empty($pm)) { ?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique">
					<?php esc_html_e('Biographical movies', 'lumiere-movies') ?>
				</div>
 			</td>
			
				<td colspan="2" class="TitreSousRubriqueColDroite">
					<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
					<div class="hidesection">
			<?php
				for ($i=0;$i<count($pm);++$i) {
					$ii = $i+"1";
					echo "<li><strong>($ii)</strong> ";
					echo "<a class='linkpopup' href='". esc_url( $imdb_admin_values[imdbplugindirectory] ."inc/popup-imdb_movie.php?mid=".intval($pm[$i]["imdb"]) )."'>".$pm[$i]["name"]."</a>";
					if (!empty($pm[$i]["year"])) 
						echo " (".intval($pm[$i]["year"]).")";
					echo "</li>\n";
				} ?>
				</div>
           		</td>
        	</tr>
		<?php	} 
		flush(); // send to user data already run through ?>

        <?php $bio = $person->bio(); ?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique"><?php esc_html_e('Biography', 'lumiere-movies'); ?>&nbsp;</div>
            </td>
            
            <td colspan="2" class="TitreSousRubriqueColDroite">
		<li>

		<?php // echo preg_replace('/http\:\/\/'.str_replace(".","\.",$person->imdbsite).'\/name\/nm(\d{7})\//','?mid=\\1&engine='.$_GET['engine'],$bio[$idx]["desc"]);		
    		if (count($bio)<2) $idx = 0; else $idx = 1;
			echo sanitize_text_field( $bio[$idx]["desc"] ); // above's doesn't work, made this one 
 ?>
		</li>
            </td>
        </tr>

</table>
<br />
     <?php } //------------------------------------------------------------------------------ end bio's part ?>

     <?php if ($_GET['info'] == 'divers'){ 
            // ------------------------------------------------------------------------------ misc part ?>
                                       <!-- Misc -->

                           <!-- Trivia -->
		<?php $trivia = $person->trivia();
		if (!empty($trivia)) {
		$tc = count($trivia); ?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique">
					<?php esc_html_e('Trivia', 'lumiere-movies'); ?>
				</div>
            </td>
			
				<td colspan="2" class="TitreSousRubriqueColDroite">
					<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
					<div class="hidesection">
			            
 			<?php 	for ($i=0;$i<$tc;++$i) {
					$ii = $i+"1";
					echo "<li><strong>($ii)</strong> ".sanitize_text_field( $trivia[$i] )."</li>\n";
				} ?>
					</div>
				</td>
       			</tr>
		<?php } 
		flush(); // send to user data already run through ?>


                           <!-- Nicknames -->
		<?php $nicks = $person->nickname();
			  if (!empty($nicks)) {?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique"><?php echo esc_html_e('Nicknames', 'lumiere-movies') ?></div>
            </td>
			
		<td colspan="2" class="TitreSousRubriqueColDroite">
			<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
			<div class="hidesection">
			<?php 
			$txt = "";
			$i = "1";
   			foreach ($nicks as $nick) {
				$txt = "<br><li><strong>($i)</strong> ".sanitize_text_field( $nick );
				echo substr($txt,4)."</li>\n";
				$i++;
  			} ?>
			</div>
            	</td>
        </tr>
		<?php } ?>
		
                           <!-- Personal Quotes -->
		<?php $quotes = $person->quotes();
			  if (!empty($quotes)) { 
	  			$tc = count($quotes); ?>
        <tr>
            	<td class="TitreSousRubriqueColGauche">
               		<div class="TitreSousRubrique">
				<?php esc_html_e('Personal quotes', 'lumiere-movies') ?>
			</div>
 		</td>
			
		<td colspan="2" class="TitreSousRubriqueColDroite">
			<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
			<div class="hidesection">
				<?php 
				for ($i=0;$i<$tc;++$i) {
					$ii = $i+"1";
					echo "<li><strong>($ii)</strong> ".sanitize_text_field( $quotes[$i] )."</li>\n";
				} ?>
			</div>
           	 </td>
        </tr>
		<?php } 
		flush(); // send to user data already run through ?>


                           <!-- Trademarks -->
		<?php $tm = $person->trademark();
			  if (!empty($tm)) { ?>
        <tr>
            <td class="TitreSousRubriqueColGauche">
                <div class="TitreSousRubrique">
					<?php esc_html_e('Trademarks', 'lumiere-movies') ?>
				</div>
 			</td>
			
		<td colspan="2" class="TitreSousRubriqueColDroite">
			<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
			<div class="hidesection">
			<?php 
				for ($i=0;$i<count($tm);++$i) {
					$ii = $i+"1";
					echo "<li><strong>($ii)</strong> ".sanitize_text_field( $tm[$i] )."</li>\n";
				} ?>
		</div>
            </td>
        </tr>
		<?php } 
		flush(); // send to user data already run through ?>



                           <!-- selffilmo -->
		<?php $ff = array("self");
		  foreach ($ff as $var) {
			$fdt = "movies_$var";
			$filmo = $person->$fdt();
			$flname = ucfirst($var);
			if (!empty($filmo)) { ?>
			  <tr>
				<td class="TitreSousRubriqueColGauche">
					<div class="TitreSousRubrique"><?php echo sanitize_text_field( $flname );?> filmo</div>
				</td>
			
		<td colspan="2" class="TitreSousRubriqueColDroite">
			<div class="activatehidesection">[+] <?php esc_html_e('click to expand', 'lumiere-movies'); ?> [+]</div>
			<div class="hidesection">
			<?php
				$ii = "0";
				$tc = count($filmo);
			  foreach ($filmo as $film) {
			  	$nbfilms = $tc-$ii;
				echo "<li><strong>($nbfilms)</strong> <a class='linkpopup' href='".esc_url($imdb_admin_values[imdbplugindirectory] ."inc/popup-imdb_movie.php?mid=".intval($film["mid"]) )."'>".sanitize_text_field($film["name"])."</a>";
				if (!empty($film["year"])) {
				echo " (".intval($film["year"]).")";
				} 
				if (empty($film["chname"])) echo "";
				else {
				  if (empty($film["chid"])) echo " as ".sanitize_text_field( $film["chname"] );
				  else echo " as <a class='linkpopup' href='". esc_url("https://".$person->imdbsite."/character/ch".intval($film["chid"]))."/'>".$film["chname"]."</a>";
				}
				echo "</li>\n\t\t";
				$ii++;
			  }
			}?>
					</div>
            			</td>
    	    		</tr>			
			<?php }?>			


		  
     <?php } //------------------------------------------------------------------------------ end misc part ?>		   
</table>
<br />
<?php 	// call wordpress footer functions;
	wp_meta();
	//get_footer(); // this one gets too much uneeded information
	wp_footer(); 
?>
</body>
</html>
<?php 	exit(); // quit the call of the page, to avoid double loading process ?>

<?php
	} else { // escape if no result found, otherwise imdblt fails
		lumiere_noresults_text();
}
?>
