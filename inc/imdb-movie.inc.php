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
 #  Function : this page is externally called (usually by a widget, but      #
 #  also from lumiere_external_call() function ) and displays information    #
 #  related to the movie                                                     #
 #									              #
 #############################################################################

require_once ( plugin_dir_path(__DIR__) . 'bootstrap.php');

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

$count_me_siffer= 0; // value to allow movie total count (called from every 'taxonomised' part)

if (isset ($_GET["mid"])) {
	$movieid = filter_var( $_GET["mid"], FILTER_SANITIZE_NUMBER_INT);
	$movie = new Imdb\Title($movieid, $config);
} else {
	$search = new Imdb\TitleSearch($config);
}

$imovie = 0;

while ($imovie < count($imdballmeta)) {	

	$film = $imdballmeta[$imovie];  // get meta data (movie's name) 

	// from custom post's field imdb-movie-widget
	if ($imdballmeta == "imdb-movie-widget-noname") {
		// a movie ID has been specified
		$midPremierResultat = $moviespecificid; // get the movie id entered

	} else {

		if ($_GET["searchtype"]=="episode") {
			$results = $search->search ($film, array(\Imdb\TitleSearch::TV_SERIES));
		} else {
			$results = $search->search ($film, array(\Imdb\TitleSearch::MOVIE));
		}

		// no movie ID has been specified
		if (! empty($results[0])) { 	// when imdb find everytime a result, which is not the case for moviepilot
			$midPremierResultat = $results[0]->imdbid(); // search for the movie id
		} else {			// escape if no result found, otherwise imdblt fails
			lumiere_noresults_text();
			break;
		}
	}	

	$movie = new Imdb\Title($midPremierResultat, $config);

	if (isset ($midPremierResultat) ) {
		$movieid = $midPremierResultat;
		

		$imovie++;

//--------------------------------------=[Layout]=---------------


?>
					<!-- imdb widget -->
<?php
		foreach ( $imdb_widget_values['imdbwidgetorder'] as $magicnumber) {
	


	if  (($imdb_widget_values['imdbwidgettitle'] == true ) && ($magicnumber == $imdb_widget_values['imdbwidgetorder']['title'] )) { 
	$year=intval($movie->year () );
	$title_sanitized=sanitize_text_field( $movie->title() );?>
										<!-- title -->
		<div class="imdbelementTITLE"><?php
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomytitle'] == true ) && (lumiere_count_me('imdblt_title', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding evey taxonomy from several movies's titles...
				for ($i = 0; $i + 1 < count ($title_sanitized); $i++) { 
					wp_set_object_terms($wp_query->post->ID, $title_sanitized, 'imdblt_title', true); #add taxonomy terms to posts' terms
				} 	wp_set_object_terms($wp_query->post->ID, $title_sanitized, 'imdblt_title', true);  #add last taxonomy term to posts' terms

				# list URL taxonomy page
				for ($i = 0; $i < count ($title_sanitized); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_title/' .lumiere_make_taxonomy_link( esc_html( $title_sanitized ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $title_sanitized );
					echo '</a>'; 
				}

			} else {
					echo $title_sanitized;
			}

			if (!empty($year) && ($imdb_widget_values['imdbwidgetyear'] == true ) ) { 
				echo " (".$year.")"; 
			}?>
		</div>
	<?php 
	}; flush ();




	if  (($imdb_widget_values['imdbwidgetpic'] == true ) && ($magicnumber == $imdb_widget_values['imdbwidgetorder']['pic'] )) { 
	$photo_url = $movie->photo_localurl(); // create the normal picture for the cache refresh
	$photo_url_sanitized = $movie->photo_localurl(intval($imdb_admin_values['imdbcoversize'])) ; ?>
										<!-- pic -->
		<div class="imdbelementPICdiv">
		<?php 	## The picture is either taken from the movie itself or if it doesn't exist, from a standard "no exist" picture.
			## The width value is taken from plugin settings, and added if the "thumbnail" option is unactivated

			// check if big pictures are selected (extract "_big.jpg" from picture's names, if exists), AND if highslide popup is activated
			if ( (substr( $photo_url, -7, -4) == "big" ) && ($imdb_admin_values['imdbpopup_highslide'] == 1) ) {
				// value to store if previous checking is valid, call in lumiere_scripts.js
				$highslidephotook = "ok";
				echo '<a href="'.$photo_url_sanitized.'" class="highslide" id="highslide-pic" title="';
				echo sanitize_text_field( $movie->title() ).'"> <img loading="eager" class="imdbelementPICimg" src="';
			} else {
				// no big picture OR no highslide popup
				echo '<img loading="eager" class="imdbelementPICimg" src="';
			}

			// check if a picture exists
			if ($photo_url_sanitized != FALSE){
				// a picture exists, therefore show it!
				echo $photo_url_sanitized .'" alt="'.esc_html__('Photo of','lumiere-movies') .' ' . esc_attr( $movie->title() ).'" '; 
			} else { 
				// no picture found, display the replacement pic
				echo esc_url( $imdb_admin_values['imdbplugindirectory'].'pics/no_pics.gif"').' alt="'.esc_html__('no picture', 'lumiere-movies').'" '; 
			}


				echo 'width="'.intval( $imdb_admin_values['imdbcoversizewidth'] ).'" ';


		echo "/ >"; 
		if ($highslidephotook == "ok") { echo "</a>\n"; } else { echo "\n"; } // new verification, closure code related to previous if ?>
		</div>
	<?php 
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['country'] ) {
		$country = $movie->country();
		if (!empty($country) && ($imdb_widget_values['imdbwidgetcountry'] == true ) ) { ?>
										<!-- Country -->
			<ul class="imdbelementCOUNTRYul">
				<li class="imdbincluded-lined imdbelementCOUNTRYli">
					<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Country', 'Countries', count($country), 'lumiere-movies')))); ?>:</span><?php 
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomycountry'] == true ) && (lumiere_count_me('imdblt_country', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding evey taxonomy from several movies's genre...
				for ($i = 0; $i + 1 < count ($country); $i++) { 
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field($country[$i]), 'imdblt_country', true); #add taxonomy terms to posts' terms
				} 	wp_set_object_terms($wp_query->post->ID, sanitize_text_field($country[$i]), 'imdblt_country', true);  #add last taxonomy term to posts' terms

				# list URL taxonomy page
				for ($i = 0; $i < count ($country); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_country/' .lumiere_make_taxonomy_link( esc_html( $country[$i] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $country[$i] );
					echo '</a>'; 
				}

			} else {
				for ($i = 0; $i + 1 < count ($country); $i++) { 
					echo sanitize_text_field( $country[$i]); echo ", "; 										
				} // endfor
				echo sanitize_text_field($country[$i]); 
	} // end if ?>
				</li>
			</ul>
	<?php 	}
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['runtime'] ) {
	$runtime_sanitized = sanitize_text_field( $movie->runtime() ); 
		if (!empty($runtime_sanitized) && ($imdb_widget_values['imdbwidgetruntime'] == true )) { 

			echo "\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- runtime -->';
			echo "\n\t\t" . '<ul class="imdbelementRUNTIMEul">';
			echo "\n\t\t\t" . '<li class="imdbincluded-lined imdbelementRUNTIMEli">';
			echo "\n\t\t\t\t" . '<span class="imdbincluded-subtitle">' . esc_html__('Runtime', 'lumiere-movies') . ':</span>';
			echo $runtime_sanitized." ".esc_html__('minutes', 'lumiere-movies'); 
			echo "\n\t\t\t" . '</li>';
			echo "\n\t\t" . '</ul>';
	 	} 
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['language']) {
	$languages = $movie->languages();
		if (!empty($languages) && ($imdb_widget_values['imdbwidgetlanguage'] == true )) { ?>
										<!-- Language -->
			<ul class="imdbelementLANGUAGEul">
			<li class="imdbincluded-lined imdbelementLANGUAGEli">
				<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Language', 'Languages', count($languages), 'lumiere-movies')))); ?>:</span><?php
			if ( ($imdb_admin_values[imdbtaxonomy] == true ) && ($imdb_widget_values[imdbtaxonomylanguage] == true ) && (lumiere_count_me('imdblt_languages', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding evey taxonomy from several movies's genre...
				for ($i = 0; $i + 1 < count ($languages); $i++) { 
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $languages[$i] ), 'imdblt_language', true); #add taxonomy terms to posts' terms
				} 	wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $languages[$i]), 'imdblt_language', true);  #add last taxonomy term to posts' terms

				# list URL taxonomy page
				for ($i = 0; $i < count ($languages); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_language/' .lumiere_make_taxonomy_link( esc_html( $languages[$i] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $languages[$i] );
					echo '</a>'; 
				}

			} else {
				for ($i = 0; $i + 1 < count ($languages); $i++) { 
					echo sanitize_text_field( $languages[$i] ); echo ", "; 										
				} 
					echo sanitize_text_field( $languages[$i] ); // endfor
			} // end if ?>
				</li>
			</ul>
	<?php 	}
	}; 
	flush ();

	if ($magicnumber== $imdb_widget_values['imdbwidgetorder']['rating'] ) {
	$votes_sanitized = intval($movie->votes());
	$rating_sanitized = intval($movie->rating());
		if (($votes_sanitized) && ($imdb_widget_values['imdbwidgetrating'] == true ) ) { ?>
										<!-- Rating et votes -->
			<ul class="imdbelementRATINGul">
			<li class="imdbincluded-lined imdbelementRATINGli">
				<span class="imdbincluded-subtitle"><?php esc_html_e('Rating', 'lumiere-movies'); ?>:</span><?php
			
			if ( $imdb_widget_values['imdbwidgetratingnopics'] == true ) { // value which doesn't exist yet into plugin; has to be made
				echo $votes_sanitized." "; 
				echo esc_html_e('votes, average ', 'lumiere-movies'); 
				echo " ".$rating_sanitized." ";
				echo esc_html_e('(max 10)', 'lumiere-movies'); 
			} else {							// by default, display pictures and votes amount	
				echo " <img src=\"".$imdb_admin_values['imdbplugindirectory'].'pics/showtimes/'.(round($rating_sanitized*2, 0)/0.2).
				".gif\" title=\"".esc_html__('vote average ', 'lumiere-movies').$rating_sanitized.esc_html__(' out of 10', 'lumiere-movies')."\"  / >";
				echo " (".$votes_sanitized." ".esc_html__('votes', 'lumiere-movies').")";			
			}
			
			?></li>
			</ul>
	<?php 	} 
	}; flush ();


	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['genre'] ) {
	$genre = $movie->genres ();	
		if (! (empty($genre)) && ($imdb_widget_values['imdbwidgetgenre'] == true )) {?>
										<!-- genres -->
			<ul class="imdbelementGENREul">
			<li class="imdbincluded-lined imdbelementGENREli"><span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Genre', 'Genres', count($genre), 'lumiere-movies')))); ?>:</span><?php 

			if ( ($imdb_admin_values[imdbtaxonomy] == true ) && ($imdb_widget_values['imdbtaxonomygenre'] == true ) && (lumiere_count_me('imdblt_genre', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding evey taxonomy from several movies's genre...
				for ($i = 0; $i + 1 < count ($genre); $i++) { 
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field($genre[$i]), 'imdblt_genre', true); #add taxonomy terms to posts' terms
				} 	wp_set_object_terms($wp_query->post->ID, sanitize_text_field($genre[$i]), 'imdblt_genre', true);  #add last taxonomy term to posts' terms

				# list URL taxonomy page
				for ($i = 0; $i < count ($genre); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_genre/' .lumiere_make_taxonomy_link( esc_html( $genre[$i] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $genre[$i] );
					echo '</a>'; 
				}

			} else {
				for ($i = 0; $i + 1 < count ($genre); $i++) { 
					echo sanitize_text_field( $genre[$i] ); echo ", "; 										
				} 
					echo sanitize_text_field($genre[$i]); // endfor
			} // end if ?>
				</li>
			</ul>
<?php		} 
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['keywords'] ) {
		$keywords = $movie->keywords();
		if (!empty($keywords) && ($imdb_widget_values['imdbwidgetkeywords'] == true ) ) { ?>
										<!-- Keywords -->
			<ul class="imdbelementKEYWORDSul">
				<li class="imdbincluded-lined imdbelementKEYWORDSli">
					<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Keyword', 'Keywords', count($keywords), 'lumiere-movies')))); ?>:</span><?php 
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomykeywords'] == true ) && (lumiere_count_me('imdblt_keywords', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding evey taxonomy from several movies's genre...
				for ($i = 0; $i + 1 < count ($keywords); $i++) { 
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field($keywords[$i]), 'imdblt_keywords', true); #add taxonomy terms to posts' terms

				} 	wp_set_object_terms($wp_query->post->ID, sanitize_text_field($keywords[$i]), 'imdblt_keywords', true);  #add last taxonomy term to posts' terms

				# list URL taxonomy page
				for ($i = 0; $i < count ($keywords); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_keywords/' .lumiere_make_taxonomy_link( esc_html( $keywords[$i] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $keywords[$i] );
					echo '</a>'; 
				}
					
			} else {
				for ($i = 0; $i + 1 < count ($keywords); $i++) { 
					echo sanitize_text_field( $keywords[$i] ); echo ", "; 										
				} 
					echo sanitize_text_field( $keywords[$i] ); // endfor
			} // end if ?>
				</li>
			</ul>
	<?php 	}
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['goofs'] ) {
	$goofs = $movie->goofs (); 
		if (! (empty($goofs)) && ($imdb_widget_values['imdbwidgetgoofs'] == true )) {?>
										<!-- goofs -->
			<ul class="imdbelementGOOFul">
			<li class="imdbincluded-lined imdbelementGOOFli"><span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Goof', 'Goofs', count($goofs), 'lumiere-movies')))); ?>:</span><?php

			// value $imdb_widget_values['imdbwidgetgoofsnumber'] is selected, but value $imdb_widget_values['imdbwidgetgoofsnumber'] is empty
			if (empty($imdb_widget_values['imdbwidgetgoofsnumber'])){
				$nbgoofs =  "1";
			} else {
				$nbgoofs =  $imdb_widget_values['imdbwidgetgoofsnumber'];
			}

			for ($i = 0; $i <  $nbgoofs && ($i < count($goofs)); $i++) { 
				echo "<strong>".sanitize_text_field( $goofs[$i]['type'] )."</strong>&nbsp;"; 
				echo sanitize_text_field( $goofs[$i]['content'] )."<br />\n"; 
			} // endfor ?></li>
			</ul>
	<?php } 
	}; 
	flush ();	

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['comments'] ) {
	$comments[] = $movie->comment_split (); // this value is sent into an array!
	$comment_split = $movie->comment_split (); // this value isn't sent into an array, for use in "if" right below
		if (! (empty($comment_split)) && ($imdb_widget_values['imdbwidgetcomments'] == true )) {?>
										<!-- comments -->
			<ul class="imdbelementCOMMENTul">
			<li class="imdbincluded-lined imdbelementCOMMENTli"><span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n("User's comment", "User's comments", count($comments), 'lumiere-movies')))); ?>:</span><?php 

			// value $imdb_widget_values['imdbwidgetcommentsnumber'] is selected, but value $imdb_widget_values['imdbwidgetcommentsnumber'] is empty
			if (empty($imdb_widget_values['imdbwidgetcommentsnumber'])){
				$nbusercomments =  "1";
			} else {
				$nbusercomments =  $imdb_widget_values['imdbwidgetcommentsnumber'];
			}

			for ($i = 0; $i < $nbusercomments && ($i < count($comments)); $i++) { 
				echo  "<i>". sanitize_text_field( $comments[$i]['title'] ). "</i> by ";

				if  ($imdb_widget_values['imdblinkingkill'] == false ) { 
				// if "Remove all links" option is not selected 
					echo "<a href=\"".esc_url($comments[$i]["author"]["url"])."\">" .  sanitize_text_field($comments[$i]["author"]["name"] ). "</a><br /><br />";
				} else {
					echo sanitize_text_field( $comments[$i]["author"]["name"] ). "<br /><br />";
				}
					echo sanitize_text_field( $comments[$i]['comment'] ) . "<br />";
			} ?></li>
			</ul>
	<?php } 
	}; 
	flush ();	

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['quotes'] ) {
	$quotes = $movie->quotes ();  
		if (! (empty($quotes)) && ($imdb_widget_values['imdbwidgetquotes'] == true )) {?>
										<!-- quotes -->
			<ul class="imdbelementQUOTEul">
			<li class="imdbincluded-lined imdbelementQUOTEli"><span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Quote', 'Quotes', count($quotes), 'lumiere-movies')))); ?>:</span><?php

			// value $imdb_widget_values['imdbwidgetquotesnumber'] is selected, but value $imdb_widget_values['imdbwidgetquotesnumber'] is empty
			if (empty($imdb_widget_values['imdbwidgetquotesnumber'])){
				$nbquotes =  "1";
			} else {
				$nbquotes =  $imdb_widget_values['imdbwidgetquotesnumber'];
			}

			for ($i = 0; $i < $nbquotes && ($i < count($quotes)); $i++) { 
				if  ($imdb_widget_values['imdblinkingkill'] == false ) { 
				// if "Remove all links" option is not selected 
					echo lumiere_convert_txtwithhtml_into_popup_people ($quotes[$i]) . "<br /><br />";
				} else {
					echo " ". lumiere_remove_link ($quotes[$i]) . "<br /><br />";
				} 
			}?></li>
			</ul>
	<?php } 
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['taglines'] ) {
	$taglines = $movie->taglines ();

		if (! (empty($taglines)) && ($imdb_widget_values['imdbwidgettaglines'] == true )) {?>
										<!-- taglines -->
			<ul class="imdbelementTAGLINEul">
			<li class="imdbincluded-lined imdbelementTAGLINEli">
				<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Tagline', 'Taglines', count($taglines), 'lumiere-movies')))); ?>:</span><?php 

			// value $imdb_widget_values['imdbwidgettaglinesnumber'] is selected, but value $imdb_widget_values['imdbwidgettaglinesnumber'] is empty
			if (empty($imdb_widget_values['imdbwidgettaglinesnumber'])){
				$nbtaglines =  "1";
			} else {
				$nbtaglines =  $imdb_widget_values['imdbwidgettaglinesnumber'];
			}
			
			for ($i = 0; $i < $nbtaglines && ($i < count($taglines)); $i++) { 
				echo sanitize_text_field( $taglines[$i] )." &raquo; ";
			} ?></li>
			</ul>
	<?php } }; flush ();	


	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['trailer'] ) {
	$trailers = $movie->trailers(TRUE);
		if (! (empty($trailers)) && ($imdb_widget_values['imdbwidgettrailer'] == true )) {?>
										<!-- trailers -->
			<ul class="imdbelementTRAILERul">
			<li class="imdbincluded-lined imdbelementTRAILERli">
				<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Trailer', 'Trailers', $imdb_widget_values['imdbwidgettrailernumber'], 'lumiere-movies')))); ?>:</span><?php 

			// value $imdb_widget_values['imdbwidgettrailer'] is selected, but value $imdb_widget_values['imdbwidgettrailernumber'] is empty
			if (empty($imdb_widget_values['imdbwidgettrailernumber'])){
				$nbtrailers =  "1";
			} else {
				$nbtrailers =  $imdb_widget_values['imdbwidgettrailernumber'];
			}

			for ($i = 0; ($i < $nbtrailers  && ($i < count($trailers)) ); $i++) { 
				if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
					echo "<a href='".esc_url( $trailers[$i]['url'] )."' title='".esc_html__('Watch on IMBb website the trailer for ', 'lumiere-movies') . esc_attr( $trailers[$i]['title'] ) ."'>". sanitize_text_field( $trailers[$i]['title'] ) . "</a><br />\n";
				} else { // if "Remove all links" option is selected 
					echo sanitize_text_field( $trailers[$i]['title'] ).", ",esc_url( $trailers[$i]['url'] )."<br />\n";
				}
			} ?></li>
			</ul>
	<?php } }; 
	flush ();	

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['colors'] ) {
	$colors = $movie->colors ();  
		if (! (empty($colors)) && ($imdb_widget_values['imdbwidgetcolors'] == true )) {?>
										<!-- colors -->
			<ul class="imdbelementCOLORul">
			<li class="imdbincluded-lined imdbelementCOLORli">
				<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Color', 'Colors', count($colors), 'lumiere-movies')))); ?>:</span><?php
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomycolor'] == true ) && (lumiere_count_me('imdblt_color', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding evey taxonomy from several movies's genre...
				for ($i = 0; $i + 1 < count ($colors); $i++) { 
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $colors[$i] ), 'imdblt_color', true); #add taxonomy terms to posts' terms
				} 	
				wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $colors[$i] ), 'imdblt_color', true);  #add last taxonomy term to posts' terms

				# list URL taxonomy page
				for ($i = 0; $i < count ($colors); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_color/' .lumiere_make_taxonomy_link( esc_html( $colors[$i] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $colors[$i] );
					echo '</a>'; 
				}

			} else {
				for ($i = 0; $i + 1 < count ($colors); $i++) { 
					echo sanitize_text_field( $colors[$i] ); echo ", "; 										
				} 
					echo sanitize_text_field( $colors[$i] ); // endfor
			} // end if ?>
				</li>
			</ul>
	<?php 	} // end if 
	}; // end if 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['alsoknow'] ) {
	$alsoknow = $movie->alsoknow ();
		if (! (empty($alsoknow)) && ($imdb_widget_values['imdbwidgetalsoknow'] == true )) {?>
										<!-- alsoknow -->
			<ul class="imdbelementALSOKNOWul">
			<li class="imdbincluded-lined imdbelementALSOKNOWli">
				<span class="imdbincluded-subtitle"><?php esc_html_e('Also known as', 'lumiere-movies'); ?>:</span><?php 
			
			for ($i = 0; $i < count ($alsoknow); $i++) { 
				echo " <strong>".sanitize_text_field( $alsoknow[$i]['title'] )."</strong> "."(".sanitize_text_field( $alsoknow[$i]['country'] );
				if (!empty($alsoknow[$i]['comment'])) 
					echo " - <i>".sanitize_text_field( $alsoknow[$i]['comment'] )."</i>";
				echo "),"; 
			} // endfor ?></li>
			</ul>
	<?php } 
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['composer'] ) {
	$composer = $movie->composer ();  
		if (! (empty($composer)) && ($imdb_widget_values['imdbwidgetcomposer'] == true )) {?>
										<!-- composer -->
			<ul class="imdbelementCOMPOSERul">
			<li class="imdbincluded-lined imdbelementCOMPOSERli"><span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Composer', 'Composers', count($composer), 'lumiere-movies')))); ?>:</span><?php 
			if ( ($imdb_admin_values[imdbtaxonomy] == true ) && ($imdb_widget_values[imdbtaxonomycomposer] == true ) && (lumiere_count_me('imdblt_composer', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding evey taxonomy from several movies's genre...
				for ($i = 0; $i < count ($composer); $i++) {
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $composer[$i]["name"] ), 'imdblt_composer', true); #add taxonomy terms to posts' terms
				} 
				wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $composer[$i]["name"] ), 'imdblt_composer', true);  #add last taxonomy term to posts' terms

				# list URL taxonomy page
				for ($i = 0; $i < count ($composer); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_composer/' .lumiere_make_taxonomy_link( esc_html( $composer[$i]["name"] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $composer[$i]["name"] );
					echo '</a>'; 
				}

			} else { 
				for ($i = 0; $i < count ($composer); $i++) {
					if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup
							echo '<a  class="link-imdblt-highslidepeople highslide" data-highslidepeople="' . sanitize_text_field( $composer[$i]["imdb"] ). '" title="' . esc_html__("Link to local IMDb", "imdb") . '">' . sanitize_text_field( $composer[$i]["name"] ) . "</a>&nbsp;";
						} else {// classic popup
							echo '<a  class="link-imdblt-highslidepeople" data-classicpeople="' . sanitize_text_field( $composer[$i]["imdb"] ). '" title="' . esc_html__("Link to local IMDb", 'lumiere-movies') . '">' . sanitize_text_field( $composer[$i]["name"] ). "</a>&nbsp;";
						} 
					} else { // if "Remove all links" option is selected 
						echo sanitize_text_field( $composer[$i]["name"] );
					}  // end if remove popup
				} // endfor 
			} // end if imdbtaxonomycomposer ?></li>
		</ul>
	<?php } // end imdbwidgetcomposer
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['soundtrack'] ) {
	$soundtrack = $movie->soundtrack (); 
		if (!empty($soundtrack) && ($imdb_widget_values['imdbwidgetsoundtrack'] == true )) {?>
										<!-- soundtrack -->
			<ul class="imdbelementSOUNDTRACKul">
			<li class="imdbincluded-lined imdbelementSOUNDTRACKli"><span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Soundtrack', 'Soundtracks', count($soundtrack), 'lumiere-movies')))); ?>:</span><?php

			// value $imdb_widget_values['imdbwidgetsoundtracknumber'] is selected, but value $imdb_widget_values['imdbwidgetsoundtracknumber'] is empty
			if (empty($imdb_widget_values['imdbwidgetsoundtracknumber'])){
				$nbsoundtracks =  "1";
			} else {
				$nbsoundtracks =  $imdb_widget_values['imdbwidgetsoundtracknumber'];
			}

			for ($i = 0; $i < $nbsoundtracks && ($i < count($soundtrack)); $i++) { 
				echo "<strong>".$soundtrack[$i]['soundtrack']."</strong>"; 
				if  ($imdb_widget_values['imdblinkingkill'] == false ) { 
				// if "Remove all links" option is not selected 
					if (!empty($soundtrack[$i]['credits'][0]) )
						echo " - <i>". lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][0]['credit_to'])."</i> ";
						echo " (". lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][0]['desc']).") ";
					if (!empty($soundtrack[$i]['credits'][1]) )
						echo " - <i>". lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][1]['credit_to'])."</i> ";
						echo " (". lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][1]['desc']).") ";
				} else {
					if (!empty($soundtrack[$i][credits][0]) )
						echo " - <i>". lumiere_remove_link ($soundtrack[$i]['credits'][0]['credit_to'])."</i> ";
						echo " (". lumiere_remove_link ($soundtrack[$i]['credits'][0]['desc']).") ";
					if (!empty($soundtrack[$i][credits][1]) )
						echo " - <i>". lumiere_remove_link ($soundtrack[$i]['credits'][1]['credit_to'])."</i> ";
						echo " (". lumiere_remove_link ($soundtrack[$i]['credits'][1]['desc']).") ";
				} // end if remove popup
				echo "\n";
			}  // endfor ?></li>
			</ul>
	<?php } 
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['prodCompany'] ) {
	$prodCompany = $movie->prodCompany ();
		if (! (empty($prodCompany)) && ($imdb_widget_values['imdbwidgetprodCompany'] == true )) {?>
										<!-- Production company -->
			<ul class="imdbelementPRODCOMPANYul">
			<li class="imdbincluded-lined imdbelementPRODCOMPANYli"><span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Production company', 'Production companies', count($prodCompany), 'lumiere-movies')))); ?>:</span><?php
			for ($i = 0; $i < count ($prodCompany); $i++) { 
					if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						echo "<a href='".esc_url( $prodCompany[$i]['url'])."' title='".esc_attr($prodCompany[$i]['name'])."'>";
						echo sanitize_text_field( $prodCompany[$i]['name'] );
						echo "</a><br />";
					} else { // if "Remove all links" option is selected 
						echo sanitize_text_field( $prodCompany[$i]['name'] )."<br />";
					}  // end if remove popup
			}  // endfor ?></li>
			</ul>
	<?php } 
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['officialSites'] ) {
	$officialSites = $movie->officialSites ();
		if (! (empty($officialSites)) && ($imdb_widget_values['imdbwidgetofficialSites'] == true )) {?>
										<!-- official websites -->
			<ul class="imdbelementOFFICIALWEBSITEul">
			<li class="imdbincluded-lined imdbelementOFFICIALWEBSITEli"><span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Official website', 'Official websites', count($officialSites), 'lumiere-movies')))); ?>:</span><?php
			for ($i = 0; $i < count ($officialSites); $i++) { 
				echo "<a href='".esc_url($officialSites[$i]['url'])."' title='".esc_attr( $officialSites[$i]['name'] )."'>";
				echo sanitize_text_field( $officialSites[$i]['name'] );
				echo "</a> ";
			}  // endfor ?></li>
			</ul>
	<?php } 
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['director']) {
	$director = $movie->director(); 
		if (!empty($director) && ($imdb_widget_values['imdbwidgetdirector'] == 1 )) {?>
										<!-- director -->
		<ul class="imdbelementDIRECTORul">
		<li class="imdbincluded-lined imdbelementDIRECTORli"><?php
			echo "\n\t\t\t" . '<span class="imdbincluded-subtitle">' . sprintf(esc_html(_n('Director', 'Directors', count($director), 'lumiere-movies'))) . ':</span>&nbsp;'."\n\t\t\t";

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomydirector'] == true ) && (lumiere_count_me('imdblt_director', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding every taxonomy from several movies's genre...
				for ($i = 0; $i < count ($director); $i++) {
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $director[$i]["name"] ), 'imdblt_director', true); #add taxonomy terms to posts' terms
				} 
				wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $director[$i]["name"] ), 'imdblt_director', true);  #add last taxonomy term to posts' terms

				# list URL taxonomy page
				for ($i = 0; $i < count ($director); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_director/' .lumiere_make_taxonomy_link( esc_html( $director[$i]["name"] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $director[$i]["name"] );
					echo '</a>'; 
				}

			} else { 
				for ($i = 0; $i < count ($director); $i++) {
					if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ( $i > 0 ) echo ', ';
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup ?>
							<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="<?php echo esc_html( $director[$i]["imdb"] ); ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo esc_html( $director[$i]["name"] ); ?></a>
<?php						} else { 
							// classic popup ?>
							<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="<?php echo $director[$i]["imdb"]; ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo $director[$i]["name"]; ?></a><?php
						} 
					} else { // if "Remove all links" option is selected 
						if ( $i > 0 ) echo ', ';
						echo esc_html( $director[$i]["name"] );
					}  // end if remove popup

				} // endfor 
				
				} // end if imdbtaxonomydirector 
			
			echo "\n\t\t" . '</li>';
			echo "\n\t\t" . '</ul>';

		} // end imdbwidgetdirector
	}; // end magic number
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['creator']) {
	$creator = $movie->creator(); 
		if (!empty($creator) && ($imdb_widget_values['imdbwidgetcreator'] == true )) {
			echo "\n\t\t\t\t\t" . '<!-- creator -->';
			echo "\n\t\t\t" . '<ul class="imdbelementCREATORul">';
			echo "\n\t\t\t" . '<li class="imdbincluded-lined imdbelementCREATORli">';
			echo "\n\t\t\t\t" . '<span class="imdbincluded-subtitle">'. sprintf(esc_html(_n('Creator', 'Creators', count($creator), 'lumiere-movies'))) . ':</span>&nbsp;';

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values[imdbtaxonomycreator] == true ) && (lumiere_count_me('imdblt_creator', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding every taxonomy from several movies's genre...
				for ($i = 0; $i < count ($creator); $i++) {
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $creator[$i]["name"] ), 'imdblt_creator', true); #add taxonomy terms to posts' terms
				} 
				wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $creator[$i]["name"] ), 'imdblt_creator', true);  #add last taxonomy term to posts' terms
				
				# list URL taxonomy page
				for ($i = 0; $i < count ($creator); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_creator/' .lumiere_make_taxonomy_link( esc_html( $creator[$i]["name"] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $creator[$i]["name"] );
					echo '</a>'; 
				}

			} else { 
				for ($i = 0; $i < count ($creator); $i++) {
					if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ( $i > 0 ) echo ', ';
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup ?>
							<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="<?php echo $creator[$i]["imdb"]; ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo $creator[$i]["name"]; ?></a>
<?php						} else { // classic popup ?>
							<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="<?php echo $creator[$i]["imdb"]; ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo $creator[$i]["name"]; ?></a><?php		
						echo sanitize_text_field( $creator[$i]["name"] )."</a>";
						} 
					} else { // if "Remove all links" option is selected 
						if ( $i > 0 ) echo ', ';
						echo sanitize_text_field( $creator[$i]["name"] );
					}  // end if remove popup
				} // endfor 
				
			} // end if imdbtaxonomycreator
			
			?></li>
		</ul>
	<?php } // end imdbwidgetcreator
	}; 
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['producer'] ) {
	$producer = $movie->producer(); 
		if (!empty($producer) && ($imdb_widget_values['imdbwidgetproducer'] == true )) {?>
										<!-- producers -->
			<ul class="imdbelementPRODUCERul">
			<li class="imdbincluded-lined imdbelementPRODUCERli">
				<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Producer', 'Producers', count($producer), 'lumiere-movies')))); ?>:</span><?php
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values[imdbtaxonomyproducer] == true ) && (lumiere_count_me('imdblt_producer', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding every taxonomy from several movies's genre...
				for ($i = 0; $i < count ($producer); $i++) {
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $producer[$i]["name"] ), 'imdblt_producer', true); #add taxonomy terms to posts' terms
				} 
				wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $producer[$i]["name"] ), 'imdblt_producer', true);  #add last taxonomy term to posts' terms

				# list URL taxonomy page
				for ($i = 0; $i < count ($producer); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_producer/' .lumiere_make_taxonomy_link( esc_html( $producer[$i]["name"] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $producer[$i]["name"] );
					echo '</a>'; 
				}

			} else { 
				for ($i = 0; $i < count ($producer); $i++) { ?>
						<div align="center" class="imdbdiv-liees">
							<div class="imdblt_float_left">
<?php					if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup ?>
							<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="<?php echo esc_attr( $producer[$i]["imdb"] ); ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo sanitize_text_field( $producer[$i]["name"] ); ?></a>
<?php						} else {  // classic popup ?>
							<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="<?php echo esc_attr( $producer[$i]["imdb"] ); ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo sanitize_text_field( $producer[$i]["name"] ); ?></a><?php		
						} 
					} else { // if "Remove all links" option is selected 
						echo sanitize_text_field( $producer[$i]["name"] );
					}  // end if remove popup ?>
							</div>
							<div align="right">
								<?php if ($producer[$i]["role"] ) echo sanitize_text_field( $producer[$i]["role"] ); echo "&nbsp;"; ?>
							</div>
						</div><?php
				} // endfor 
				
			} // end if imdbtaxonomyproducer ?>
			</li>
			</ul>
	<?php } // end imdbwidgetproducer
	}; flush ();


	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['writer'] ) {
	$writer = $movie->writing(); 
		if (!empty($writer) && ($imdb_widget_values['imdbwidgetwriter'] == true )) {?>
										<!-- writers -->
		<ul class="imdbelementWRITERul">
		<li class="imdbincluded-lined imdbelementWRITERli">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Writer', 'Writers', count($write), 'lumiere-movies')))); ?>:</span><?php
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomywriter'] == true ) && (lumiere_count_me('imdblt_writer', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding every taxonomy from several movies's genre...
				for ($i = 0; $i < count ($writer); $i++) {
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $writer[$i]["name"] ), 'imdblt_writer', true); #add taxonomy terms to posts' terms
				} 
				wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $writer[$i]["name"] ), 'imdblt_writer', true);  #add last taxonomy term to posts' terms
				
				# list URL taxonomy page
				for ($i = 0; $i < count ($writer); $i++) {
					echo "\n\t\t\t\t". '<div align="center" class="imdbdiv-liees">';
					echo "\n\t\t\t\t\t". '<div class="imdblt_float_left">';
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_writer/' .lumiere_make_taxonomy_link( esc_html( $writer[$i]["name"] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $writer[$i]["name"] );
					echo '</a>'; 
					echo '</div>';
					echo "\n\t\t\t\t\t". '<div align="right">';
					echo esc_html( $writer[$i]["role"] );
					echo '</a>'; 
					echo '</div>';
					echo "\n\t\t\t\t". '</div>';
				}

			} else { 
				for ($i = 0; $i < count ($writer); $i++) { ?>
						<div align="center" class="imdbdiv-liees">
							<div class="imdblt_float_left">
<?php					if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup ?>
							<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="<?php echo esc_attr( $writer[$i]["imdb"] ); ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo sanitize_text_field( $writer[$i]["name"] ); ?></a>
<?php						} else {  // classic popup ?>
							<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="<?php echo esc_attr( $writer[$i]["imdb"] ); ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo sanitize_text_field( $writer[$i]["name"] ); ?></a><?php		
						} 
					} else { // if "Remove all links" option is selected 
						echo sanitize_text_field( $writer[$i]["name"] );
					}  // end if remove popup ?>
							</div>
							<div align="right">
								<?php if ($writer[$i]["role"] ) echo sanitize_text_field( $producer[$i]["role"] ); echo "&nbsp;"; ?>
							</div>
						</div><?php
				} // endfor 
				
			} // end if imdbtaxonomywriter ?>
			</li>
			</ul>
	<?php } // end imdbwidgetwriter
	}; 
	flush ();


	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['actor'] ) {
	$cast = $movie->cast(); 
		if (!empty($cast) && ($imdb_widget_values['imdbwidgetactor'] == true )) { ?>
										<!-- actors -->
			<ul class="imdbelementACTORul">
			<li class="imdbincluded-lined imdbelementACTORli">
				<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Actor', 'Actors', count($cast), 'lumiere-movies')))); ?>:</span><?php 
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values[imdbtaxonomyactor] == true ) && (lumiere_count_me('imdblt_actor', $count_me_siffer) == "nomore") ) { 
			// lumiere_count_me() to avoid adding every taxonomy from several movies's genre...
				for ($i = 0; $i < $imdb_widget_values[imdbwidgetactornumber] && ($i < count($cast)); $i++) { 
					#add taxonomy terms to posts' terms
					wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $cast[$i]["name"] ), 'imdblt_actor', true); 
				} 
				#add last taxonomy term to posts' terms
				wp_set_object_terms($wp_query->post->ID, sanitize_text_field( $cast[$i]["name"] ), 'imdblt_actor', true);  

				# list URL taxonomy page
				for ($i = 0; $i < count ($cast); $i++) {
					echo "\n\t\t\t\t". '<div align="center" class="imdbdiv-liees">';
					echo "\n\t\t\t\t\t". '<div class="imdblt_float_left">';
					// remove the <br> which break the layout
					echo esc_html( preg_replace('/\n/', "", $cast[$i]["role"]) ); 
					echo '</div>';
					echo "\n\t\t\t\t\t". '<div align="right">';
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/imdblt_actor/' .lumiere_make_taxonomy_link( esc_html( $cast[$i]["name"] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $cast[$i]["name"] );
					echo '</a>'; 
					echo '</div>';
					echo "\n\t\t\t\t". '</div>';
				}


			} else { 

				// value $imdb_widget_values['imdbwidgetactornumber'] is selected, but value $imdb_widget_values['imdbwidgetactornumber'] is empty
				if (empty($imdb_widget_values['imdbwidgetactornumber'])){
					$nbactors =  "1";
				} else {
					$nbactors =  intval($imdb_widget_values['imdbwidgetactornumber'] );
				}

				for ($i = 0; $i < $nbactors && ($i < count($cast)); $i++) { 
					echo "\n\t\t\t\t". '<div align="center" class="imdbdiv-liees">';
					echo "\n\t\t\t\t\t". '<div class="imdblt_float_left">';
					echo esc_html( preg_replace('/\n/', "", $cast[$i]["role"]) ); // remove the <br> which break the layout
					echo '</div>';
					echo "\n\t\t\t\t\t". '<div align="right">';
				if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
					if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup
						echo '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $cast[$i]["imdb"] ) . '" title="'. esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . esc_html( $cast[$i]["name"] ) . '</a>';
					} else {  // classic popup ?>
						<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="<?php echo esc_attr( $cast[$i]["imdb"] ); ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo esc_html( $cast[$i]["name"] ); ?></a><?php		
					} 
				} else { // if "Remove all links" option is selected 
					echo esc_html( $cast[$i]["name"] );
				} // end if remove popup 
				echo '</div>';
				echo "\n\t\t\t\t". '</div>';
				} // endfor 
				
			} // end if imdbtaxonomyactor

			echo "\n\t\t" . '</li>';
			echo "\n\t\t" . '</ul>';
	
		} // end imdbwidgetactor
	}; // end magic number
	flush ();

	if  ($magicnumber==$imdb_widget_values['imdbwidgetorder']['plot'] ) {
	$plot = $movie->plot (); 
		if (!lumiere_is_multiArrayEmpty($plot) && ($imdb_widget_values['imdbwidgetplot'] == true )) {
		// here is tested if the array contains data; if not, doesn't go further ?>
										<!-- Plots -->
			<ul class="imdbelementPLOTul">
			<li class="imdbincluded-lined imdbelementPLOTli">
				<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Plot', 'Plots', count($plot), 'lumiere-movies')))); ?>:</span><?php

				// value $imdb_widget_values['imdbwidgetplotnumber'] is selected, but value $imdb_widget_values['imdbwidgetplotnumber'] is empty
				if (empty($imdb_widget_values['imdbwidgetplotnumber'])){
					$nbplots =  "1";
				} else {
					$nbplots =  intval ($imdb_widget_values['imdbwidgetplotnumber'] );
				}

				for ($i = 0; $i < $nbplots  && ($i < count ($plot)); $i++) { 
					if ($i > 0) { echo '<hr>';} // add hr to every quote but the first

					if  ($imdb_widget_values['imdblinkingkill'] == false ) { 
					// if "Remove all links" option is not selected 
						echo sanitize_text_field( $plot[$i] );
					} else {
						echo lumiere_remove_link ($plot[$i]);
					} 
					
				}// endfor ?></li> 
			</li>
			</ul>
	<?php } 
	}; flush ();


		$magicnumber++; 
		} // end foreach ?>



									<!-- Source credit link -->
	<?php if ( ($imdb_widget_values['imdblinkingkill'] == false ) && ($imdb_widget_values['imdbwidgetsource'] == true ) ) { 
	// if "Remove all links" option is not selected ?>
	<ul class="imdbelementSOURCEul">
	<li class="imdbincluded-lined imdbelementSOURCEli">
		<span class="imdbincluded-subtitle"><?php esc_html_e('Source', 'lumiere-movies'); ?>:</span><?php esc_url( lumiere_source_imdb($midPremierResultat) );?>
	</li>
	</ul>
	<?php } 
?>
					<!-- end imdb widget -->
<?php 
 //--------------------------------------=[end Layout]=---------------

	} else { // if is not set a $midPremierResultat
		lumiere_noresults_text();
	} // end if is set a $midPremierResultat

} //end while

?>
