<?php

 #############################################################################
 # Lumière! Movies WordPress Plugin                                          #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Class : this class is externally called (usually by a widget, but        #
 #  also from lumiere_external_call() function) and displays information     #
 #  related to the movie                                                     #
 #									              #
 #############################################################################

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die('You can not call directly this page');
}

class LumiereMovies {

	private $allowed_html_for_escape_functions = [
	    'a' => [
		 'id' => true,
		 'href'  => true,
		 'title' => true,
	    ]
	]; 

	/**
	 * Constructor. Sets up the metabox
	 */
	function __construct() {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $imdb_cache_values, $imdballmeta,$count_me_siffer;

		$imovie = 0; # for counting the main loop

		$count_me_siffer = isset($count_me_siffer) ? $count_me_siffer : 0; # var for counting only one results

		/* Start config class for $config in below Imdb\Title class calls */
		if (class_exists("\Lumiere\Settings")) {
			$config = new \Lumiere\Settings();
			$config->cachedir = $imdb_cache_values['imdbcachedir'] ?? NULL;
			$config->photodir = $imdb_cache_values['imdbphotoroot'] ?? NULL; // ?imdbphotoroot? Bug imdbphp?
			$config->imdb_img_url = $imdb_cache_values['imdbimgdir'] ?? NULL;
			$config->photoroot = $imdb_cache_values['imdbphotodir'] ?? NULL; // ?imdbphotodir? Bug imdbphp?
			$config->language = $imdb_admin_values['imdblanguage'] ?? NULL;
		}

		if (isset ($_GET["mid"])) {

			$movieid = filter_var( $_GET["mid"], FILTER_SANITIZE_NUMBER_INT);
			$movie = new \Imdb\Title($movieid, $config);

		} else {

			$search = new \Imdb\TitleSearch($config);

		}

		while ($imovie < count($imdballmeta)) {	

			$film = $imdballmeta[$imovie];  // get meta data from class widget or lumiere

			// from custom post's field in widget or class in class.core.php
			// a movie name has been specified
			if (isset($film['byname']))  {

				$film = $film['byname'];  // get meta data from class widget or lumiere
				$results = $search->search ($film, array(\Imdb\TitleSearch::MOVIE));
				$midPremierResultat = $results[0]->imdbid();

			// from custom post's field in widget or class in class.core.php
			// a movie ID has been specified
			} elseif (isset($film['bymid']))  {

				$midPremierResultat = $film['bymid']; // get the movie id entered

			} else {

				if ( (isset($_GET["searchtype"])) && ($_GET["searchtype"]=="episode") ) {
					$results = $search->search ($film, array(\Imdb\TitleSearch::TV_SERIES));
				} else {
					$results = $search->search ($film, array(\Imdb\TitleSearch::MOVIE));
				}

				// no movie ID has been specified
				if (! empty($results)) { 	// when imdb find everytime a result, which is not the case for moviepilot

					$midPremierResultat = $results[0]->imdbid(); // search for the movie id
				} else {			// escape if no result found, otherwise imdblt fails

					lumiere_noresults_text();
					break;
				}
			}

			// make sure only one result is displayed
			if (lumiere_count_me($midPremierResultat, $count_me_siffer) == "nomore")
				$this->lumiere_movie_design($config, $midPremierResultat); # passed those two values to the design

			$imovie++;

			$count_me_siffer++; # increment counting only one results
		}	


	}

	/* Function lumiere_movie_design()
	 * This function displays the layout and calls all subfonctions
	 * @param $config -> takes the value of imdb class 
	 * @param $midPremierResultat -> takes the IMDb ID to be displayed
	 */

	public function lumiere_movie_design($config=NULL, $midPremierResultat=NULL){

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $imdb_cache_values,$magicnumber;

		/* Start config class for $config in below Imdb\Title class calls */
		$movie = new \Imdb\Title($midPremierResultat, $config);

		foreach ( $imdb_widget_values['imdbwidgetorder'] as $magicnumber) {

			if  ( ($magicnumber == $imdb_widget_values['imdbwidgetorder']['title'] ) 
			&& ($imdb_widget_values['imdbwidgettitle'] == true ))
				$this->lumiere_movies_title ($movie);
			if  ( ($magicnumber == $imdb_widget_values['imdbwidgetorder']['pic'] ) 
			&& ($imdb_widget_values['imdbwidgetpic'] == true ) ) 
				$this->lumiere_movies_pics ($movie);
			if ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['country'] ) 
			&& ($imdb_widget_values['imdbwidgetcountry'] == true ) )
				$this->lumiere_movies_country ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['runtime'] ) 
			&& ($imdb_widget_values['imdbwidgetruntime'] == true ) )
				$this->lumiere_movies_runtime ($movie);
			if ( ($magicnumber== $imdb_widget_values['imdbwidgetorder']['rating'] ) 
			&& ($imdb_widget_values['imdbwidgetrating'] == true ) )
				$this->lumiere_movies_rating ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['language']) 
			&& ($imdb_widget_values['imdbwidgetlanguage'] == true ) )
				$this->lumiere_movies_language($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['genre'] )  
			&& ($imdb_widget_values['imdbwidgetgenre'] == true ) )
				$this->lumiere_movies_genre ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['keywords'] )  
			&& ($imdb_widget_values['imdbwidgetkeywords'] == true ) )
				$this->lumiere_movies_keywords ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['goofs'] ) 
			&& ($imdb_widget_values['imdbwidgetgoofs'] == true ) )
				$this->lumiere_movies_goofs ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['comments'] ) 
			&& ($imdb_widget_values['imdbwidgetcomments'] == true ) )
				$this->lumiere_movies_comment ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['quotes'] )
			&& ($imdb_widget_values['imdbwidgetquotes'] == true ) )
				$this->lumiere_movies_quotes ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['taglines'] ) 
			&& ($imdb_widget_values['imdbwidgettaglines'] == true ) )
				$this->lumiere_movies_taglines ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['trailer'] ) 
			&& ($imdb_widget_values['imdbwidgettrailer'] == true ) )
				$this->lumiere_movies_trailer ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['colors'] ) 
			&& ($imdb_widget_values['imdbwidgetcolors'] == true ) )
				$this->lumiere_movies_color ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['alsoknow'] )  
			&& ($imdb_widget_values['imdbwidgetalsoknow'] == true ) )
				$this->lumiere_movies_aka ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['composer'] ) 
			&& ($imdb_widget_values['imdbwidgetcomposer'] == true ) )
				$this->lumiere_movies_composer ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['soundtrack'] ) 
			&& ($imdb_widget_values['imdbwidgetsoundtrack'] == true ) )
				$this->lumiere_movies_soundtrack ($movie);
			if ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['prodcompany'] ) 
			&&  ($imdb_widget_values['imdbwidgetprodcompany'] == true ) )
				$this->lumiere_movies_prodcompany ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['officialsites'] ) 
			&& ($imdb_widget_values['imdbwidgetofficialsites'] == true ) )
				$this->lumiere_movies_officialsite ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['source'] ) 
			&&  ($imdb_widget_values['imdbwidgetsource'] == true ) )
				$this->lumiere_movies_creditlink($midPremierResultat); # doesn't need class but movie id
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['director']) 
			&& ($imdb_widget_values['imdbwidgetdirector'] == true ) )
				$this->lumiere_movies_director ($movie);
			if ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['creator']) 
			&&  ($imdb_widget_values['imdbwidgetcreator'] == true ) )
				$this->lumiere_movies_creator ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['producer'] ) 
			&& ($imdb_widget_values['imdbwidgetproducer'] == true ) )
				$this->lumiere_movies_producer ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['writer'] ) 
			&& ($imdb_widget_values['imdbwidgetwriter'] == true ) )
				$this->lumiere_movies_writer ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['actor'] ) 
			&& ($imdb_widget_values['imdbwidgetactor'] == true ) )
				$this->lumiere_movies_actor ($movie);
			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['plot'] ) 
			&& ($imdb_widget_values['imdbwidgetplot'] == true ) )
				$this->lumiere_movies_plot ($movie);

			$magicnumber++; 


		}

	}


	public function lumiere_movies_title ($movie=NULL) {

		/* Vars */ 

		global $imdb_admin_values, $imdb_widget_values, $wp_query; ?>

									<!-- Lumière! plugin -->

<?php
		$year=intval($movie->year () );
		$title_sanitized=sanitize_text_field( $movie->title() ); ?>

									<!-- title -->

		<div class="imdbelementTITLE"><?php
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomytitle'] == true ) ) { 

				// add taxonomy terms to posts' terms
				wp_set_post_terms($wp_query->post->ID, $title_sanitized, $imdb_admin_values['imdburlstringtaxo'] . 'title', false); 

				# list URL taxonomy page
				echo '<a class="linkincmovie" ';
				echo 'href="' . site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'title/' .lumiere_make_taxonomy_link( esc_html( $title_sanitized ) ) . '" ';
				echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
				echo esc_html( $title_sanitized );
				echo '</a>'; 


			} else {

				echo $title_sanitized;
			}

			if (!empty($year) && ($imdb_widget_values['imdbwidgetyear'] == true ) ) { 
				echo " (".$year.")"; 
			}?>
		</div>
<?php 
	}


	public function lumiere_movies_pics ($movie=NULL) {

		/* Vars */ 

		global $imdb_admin_values, $imdb_widget_values;

		$photo_url = $movie->photo_localurl(); // create the normal picture for the cache refresh
		$photo_url_sanitized = $movie->photo_localurl(intval($imdb_admin_values['imdbcoversize'])) ; ?>

									<!-- pic -->
		<div class="imdbelementPICdiv">
<?php 			## The picture is either taken from the movie itself or if it doesn't exist, from a standard "no exist" picture.
			## The width value is taken from plugin settings, and added if the "thumbnail" option is unactivated

			// check if big pictures are selected (extract "_big.jpg" from picture's names, if exists), AND if highslide popup is activated
			if ( (substr( $photo_url, -7, -4) == "big" ) && ($imdb_admin_values['imdbpopup_highslide'] == 1) ) {
				// value to store if previous checking is valid, call in lumiere_scripts.js
				$highslidephotook = "ok";
				echo '<a href="'.$photo_url_sanitized.'" class="highslide" id="highslide-pic" title="';
				echo sanitize_text_field( $movie->title() ).'"> <img loading="eager" class="imdbelementPICimg" src="';
			} else {
				// no big picture OR no highslide popup
				echo "\t".'<img loading="eager" class="imdbelementPICimg" src="';
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

			if ( (isset($highslidephotook))  && ($highslidephotook == "ok") ) { echo "</a>\n"; } else { echo "\n"; } // new verification, closure code related to previous if ?>
		</div>

<?php	}


	public function lumiere_movies_country ($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $wp_query;

		$country = $movie->country();

		if (!empty($country)) { ?>

									<!-- Country -->

		<div class="lumiere-lines-common imdbelementCOUNTRY">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Country', 'Countries', count($country), 'lumiere-movies')))); ?>:</span><?php 
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomycountry'] == true ) ) { 
				for ($i = 0; $i < count ($country); $i++) { 
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field($country[$i]), $imdb_admin_values['imdburlstringtaxo'] . 'country', false); 

					# list URL taxonomy page
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'country/' .lumiere_make_taxonomy_link( esc_html( $country[$i] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $country[$i] );
					echo '</a>'; 
					if ( $i < count ($country) - 1 ) echo ", ";
				}

			} else {

				for ($i = 0; $i < count ($country); $i++) { 
					echo sanitize_text_field( $country[$i]);
					if ( $i < count ($country) - 1 ) echo ", ";	
				} // endfor

			} // end if ?>

			</div>
<?php 		}
	}



	public function lumiere_movies_runtime($movie=NULL) {

		$runtime_sanitized = sanitize_text_field( $movie->runtime() ); 

		if (!empty($runtime_sanitized) ) { ?>

									<!-- runtime -->

		<div class="lumiere-lines-common imdbelementRUNTIME">
			<span class="imdbincluded-subtitle"><?php esc_html_e('Runtime', 'lumiere-movies'); ?></span>
			<?php echo $runtime_sanitized." ".esc_html__('minutes', 'lumiere-movies'); ?>

		</div>

<?php	 	} 
	}


	public function lumiere_movies_language($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $wp_query;

		$languages = $movie->languages();

		if (!empty($languages) ) { ?>

									<!-- Language -->

		<div class="lumiere-lines-common imdbelementLANGUAGE">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Language', 'Languages', count($languages), 'lumiere-movies')))); ?>:</span><?php

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomylanguage'] == true ) ) { 

				for ($i = 0; $i < count ($languages); $i++) { 
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field( $languages[$i] ), $imdb_admin_values['imdburlstringtaxo'] . 'language', false); 

					# list URL taxonomy page
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'language/' .lumiere_make_taxonomy_link( esc_html( $languages[$i] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $languages[$i] );
					echo '</a>'; 
					if ( $i < count ($languages) - 1 )	echo ", ";
				}

			} else {
				for ($i = 0; $i < count ($languages); $i++) { 
					echo sanitize_text_field( $languages[$i] );
					if ( $i < count ($languages) - 1 )	echo ", "; 	
				} 
			} // end if ?>

		</div>
<?php	 	}
	}


	public function lumiere_movies_rating($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$votes_sanitized = esc_html($movie->votes());
		$rating_sanitized = esc_html($movie->rating());

		if (($votes_sanitized)) { ?>

									<!-- Rating et votes -->

		<div class="lumiere-lines-common imdbelementRATING">
			<span class="imdbincluded-subtitle"><?php esc_html_e('Rating', 'lumiere-movies'); ?>:</span><?php
			
			if  ( (isset($imdb_widget_values['imdbwidgetratingnopics'] )) && ( $imdb_widget_values['imdbwidgetratingnopics'] == true ) ) { // value which doesn't exist yet into plugin; has to be made
				echo $votes_sanitized." "; 
				echo esc_html_e('votes, average ', 'lumiere-movies'); 
				echo " ".$rating_sanitized." ";
				echo esc_html_e('(max 10)', 'lumiere-movies'); 
			} else {							// by default, display pictures and votes amount	
				echo " <img src=\"".$imdb_admin_values['imdbplugindirectory'].'pics/showtimes/'.(round($rating_sanitized*2, 0)/0.2).
				".gif\" title=\"".esc_html__('vote average ', 'lumiere-movies').$rating_sanitized.esc_html__(' out of 10', 'lumiere-movies')."\"  / >";
				echo " (".number_format($votes_sanitized, 0, '', "'")." ".esc_html__('votes', 'lumiere-movies').")";			
			}
			
			?>

		</div>
<?php 		} 
	}



	public function lumiere_movies_genre($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values,$imdb_widget_values, $wp_query;

		$genre = $movie->genres ();	

		if (!empty($genre))  { ?>
									<!-- genres -->

		<div class="lumiere-lines-common imdbelementGENRE">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Genre', 'Genres', count($genre), 'lumiere-movies')))); ?>:</span><?php 

			if ( ( $imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomygenre'] == true ) ) { 

				for ($i = 0; $i < count ($genre); $i++) { 
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field($genre[$i]), $imdb_admin_values['imdburlstringtaxo'] . 'genre', false); 
				} 

				# list URL taxonomy page
				for ($i = 0; $i < count ($genre); $i++) {
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'genre/' .lumiere_make_taxonomy_link( esc_html( $genre[$i] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $genre[$i] );
					echo '</a>'; 
					if ( $i < count ($genre) - 1 ) echo ', '; 
				}

			} else {
				for ($i = 0; $i < count ($genre); $i++) { 
					echo esc_html( $genre[$i] ); echo ", "; 										
				} 
			} // end if ?>

		</div>
<?php	
		}
	}


	public function lumiere_movies_keywords($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $wp_query;

		$keywords = $movie->keywords();

		if (!empty($keywords)) { ?>
									<!-- Keywords -->

		<div class="lumiere-lines-common imdbelementKEYWORDS">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Keyword', 'Keywords', count($keywords), 'lumiere-movies')))); ?>:</span><?php 
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomykeywords'] == true ) ) { 

				for ($i = 0; $i < count ($keywords); $i++) { 
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field($keywords[$i]), $imdb_admin_values['imdburlstringtaxo'] . 'keywords', false); 

					# list URL taxonomy page
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'keywords/' . lumiere_make_taxonomy_link( esc_html( $keywords[$i] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $keywords[$i] );
					echo '</a>'; 
					if ( $i < count ($keywords) - 1 )  echo ", ";
				}
					
			} else {
				for ($i = 0; $i < count ($keywords); $i++) { 
					echo esc_html( $keywords[$i] ); 
					if ( $i < count ($keywords) - 1 )  echo ", "; 										
				} 
			} // end if ?>

		</div>
<?php
		}
	}


	public function lumiere_movies_goofs($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$goofs = $movie->goofs (); 

		if (!empty($goofs))  {?>
									<!-- goofs -->

			<div class="lumiere-lines-common imdbelementGOOF">
				<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Goof', 'Goofs', count($goofs), 'lumiere-movies')))); ?>:</span><br /><?php

			// value $imdb_widget_values['imdbwidgetgoofsnumber'] is selected, but value $imdb_widget_values['imdbwidgetgoofsnumber'] is empty
			if (empty($imdb_widget_values['imdbwidgetgoofsnumber'])){$nbgoofs =  "1";} else {$nbgoofs =  $imdb_widget_values['imdbwidgetgoofsnumber'];}

			for ($i = 0; $i <  $nbgoofs && ($i < count($goofs)); $i++) { 
				echo "<strong>".sanitize_text_field( $goofs[$i]['type'] )."</strong>&nbsp;"; 
				echo sanitize_text_field( $goofs[$i]['content'] )."<br />\n"; 
			} // endfor ?>
			</div>
<?php 		}
	} 
	

	public function lumiere_movies_comment($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$comments[] = $movie->comment_split (); // this value is sent into an array!
		$comment_split = $movie->comment_split (); // this value isn't sent into an array, for use in "if" right below
		if (!empty($comment_split))  {?>
									<!-- comments -->

		<div class="lumiere-lines-common imdbelementCOMMENT">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n("User's comment", "User's comments", count($comments), 'lumiere-movies')))); ?>:</span><br><?php 

			// value $imdb_widget_values['imdbwidgetcommentsnumber'] is selected, but value $imdb_widget_values['imdbwidgetcommentsnumber'] is empty
			if (empty($imdb_widget_values['imdbwidgetcommentsnumber'])){$nbusercomments =  "1";} else {	$nbusercomments =  $imdb_widget_values['imdbwidgetcommentsnumber'];}

			for ($i = 0; $i < $nbusercomments && ($i < count($comments)); $i++) { 

				echo "<";
				echo  "<i>". sanitize_text_field( $comments[$i]['title'] ). "</i> by ";

				// if "Remove all links" option is not selected 
				if  ($imdb_widget_values['imdblinkingkill'] == false ) { 

					echo "<a href=\"".esc_url($comments[$i]["author"]["url"])."\">" .  sanitize_text_field($comments[$i]["author"]["name"] ). "</a>&nbsp;";

				} else {

					echo sanitize_text_field( $comments[$i]["author"]["name"] ).'&nbsp;';

				}
				echo ">";

				echo sanitize_text_field( $comments[$i]['comment'] ) ;

				if ( $i < (count($comments) -1) ) { echo "\n<hr>\n";} // add hr to every quote but the last					
			} ?>

		</div>
<?php 
		}
	}



	public function lumiere_movies_quotes($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$quotes = $movie->quotes ();  

		if (! empty($quotes)) {?>
									<!-- quotes -->

		<div class="lumiere-lines-common imdbelementQUOTE">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Quote', 'Quotes', count($quotes), 'lumiere-movies')))); ?>:</span><br />
<?php			// value $imdb_widget_values['imdbwidgetquotesnumber'] is selected, but value $imdb_widget_values['imdbwidgetquotesnumber'] is empty
			if (empty($imdb_widget_values['imdbwidgetquotesnumber'])){$nbquotes =  "1";} else {	$nbquotes =  $imdb_widget_values['imdbwidgetquotesnumber'];}

			for ($i = 0; $i < $nbquotes && ($i < count($quotes)); $i++) { 

				// if "Remove all links" option is not selected 
				if  ($imdb_widget_values['imdblinkingkill'] == false ) { 

					echo "\t\t" . lumiere_convert_txtwithhtml_into_popup_people ($quotes[$i]) . "\n";

				} else {

					echo " ". lumiere_remove_link ($quotes[$i]) ;

				} 
				if ( $i < ($nbquotes -1) ) { echo "\n\t\t<hr>\n";} // add hr to every quote but the last					
			}?>

		</div>

<?php		} 
	}



	public function lumiere_movies_taglines($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$taglines = $movie->taglines ();

		if (!empty($taglines))  {?>
									<!-- taglines -->

		<div class="lumiere-lines-common imdbelementTAGLINE">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Tagline', 'Taglines', count($taglines), 'lumiere-movies')))); ?>:</span><?php 

			// value $imdb_widget_values['imdbwidgettaglinesnumber'] is selected, but value $imdb_widget_values['imdbwidgettaglinesnumber'] is empty
			if (empty($imdb_widget_values['imdbwidgettaglinesnumber'])){$nbtaglines =  "1";} else {$nbtaglines =  $imdb_widget_values['imdbwidgettaglinesnumber'];}
			
			for ($i = 0; $i < $nbtaglines && ($i < count($taglines)); $i++) { 

				echo " &laquo; " . sanitize_text_field( $taglines[$i] )." &raquo; ";
				if ($i < ( $nbtaglines -1 ) ) echo "\n<hr>\n"; // add hr to every quote but the last

			} ?>

		</div>
<?php 
		}
	}



	public function lumiere_movies_trailer($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$trailers = $movie->trailers(TRUE);
		if (!empty($trailers))  {?>
									<!-- trailers -->

		<div class="lumiere-lines-common imdbelementTRAILER">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Trailer', 'Trailers', $imdb_widget_values['imdbwidgettrailernumber'], 'lumiere-movies')))); ?>:</span><?php 

			// value $imdb_widget_values['imdbwidgettrailer'] is selected, but value $imdb_widget_values['imdbwidgettrailernumber'] is empty
			if (empty($imdb_widget_values['imdbwidgettrailernumber'])){$nbtrailers =  "1";} else {$nbtrailers =  $imdb_widget_values['imdbwidgettrailernumber'];}

			for ($i = 0; ($i < $nbtrailers  && ($i < count($trailers)) ); $i++) { 
				if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
					echo "<a href='".esc_url( $trailers[$i]['url'] )."' title='".esc_html__('Watch on IMBb website the trailer for ', 'lumiere-movies') . esc_attr( $trailers[$i]['title'] ) ."'>". sanitize_text_field( $trailers[$i]['title'] ) . "</a><br />\n";
				} else { // if "Remove all links" option is selected 
					echo sanitize_text_field( $trailers[$i]['title'] ).", ",esc_url( $trailers[$i]['url'] )."<br />\n";
				}
			} ?>

		</div>
<?php 
		}
	}



	public function lumiere_movies_color($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $wp_query;

		$colors = $movie->colors ();  

		if (!empty($colors))  { ?>
									<!-- colors -->

		<div class="lumiere-lines-common imdbelementCOLOR">
				<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Color', 'Colors', count($colors), 'lumiere-movies')))); ?>:</span><?php
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomycolor'] == true ) ) { 

				for ($i = 0; $i < count ($colors); $i++) { 
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field( $colors[$i] ), $imdb_admin_values['imdburlstringtaxo'] . 'color', false); 

					# list URL taxonomy page
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'color/' .lumiere_make_taxonomy_link( esc_html( $colors[$i] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $colors[$i] );
					echo '</a>'; 
					if ( $i < count ($colors) - 1 ) echo ", ";
				}

			} else {
				for ($i = 0; $i < count ($colors); $i++) { 
					echo sanitize_text_field( $colors[$i] ); 
					if ( $i < count ($colors) - 1 ) echo ", "; 										
				}  // endfor
			} // end if ?>

		</div>

<?php 
		} 
	}



	public function lumiere_movies_aka($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$alsoknow = $movie->alsoknow ();

		if (!empty($alsoknow)) {?>
									<!-- alsoknow -->

		<div class="lumiere-lines-common imdbelementALSOKNOW">
			<span class="imdbincluded-subtitle"><?php esc_html_e('Also known as', 'lumiere-movies'); ?>:</span><?php 
			
			for ($i = 0; $i < count ($alsoknow); $i++) { 
				echo " <strong>".sanitize_text_field( $alsoknow[$i]['title'] )."</strong> "."(".sanitize_text_field( $alsoknow[$i]['country'] );
				if (!empty($alsoknow[$i]['comment'])) 
					echo " - <i>".sanitize_text_field( $alsoknow[$i]['comment'] )."</i>";
				echo "),"; 
			} // endfor ?>

		</div>
<?php 
		}
	}


	public function lumiere_movies_composer($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $wp_query;

		$composer = $movie->composer () ;

		if (!empty($composer))  {?>
									<!-- composer -->

		<div class="lumiere-lines-common imdbelementCOMPOSER">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Composer', 'Composers', count($composer), 'lumiere-movies')))); ?>:</span><?php 
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomycomposer'] == true ) ) { 

				for ($i = 0; $i < count ($composer); $i++) {
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field( $composer[$i]["name"] ), $imdb_admin_values['imdburlstringtaxo'] . 'composer', false);

					# list URL taxonomy page
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'composer/' .lumiere_make_taxonomy_link( esc_html( $composer[$i]["name"] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $composer[$i]["name"] );
					echo '</a>'; 
					if ( $i < count ($composer) - 1 ) echo ", ";

				}

			} else { 
				for ($i = 0; $i < count ($composer); $i++) {
					if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup
							echo '<a  class="link-imdblt-highslidepeople highslide" data-highslidepeople="' . sanitize_text_field( $composer[$i]["imdb"] ). '" title="' . esc_html__("Link to local IMDb", "imdb") . '">' . sanitize_text_field( $composer[$i]["name"] ) . "</a>";
						} else {// classic popup
							echo '<a  class="link-imdblt-highslidepeople" data-classicpeople="' . sanitize_text_field( $composer[$i]["imdb"] ). '" title="' . esc_html__("Link to local IMDb", 'lumiere-movies') . '">' . sanitize_text_field( $composer[$i]["name"] ). "</a>";
						} 
					} else { // if "Remove all links" option is selected 
						echo sanitize_text_field( $composer[$i]["name"] );
					}  // end if remove popup
	
					if ( $i < count ($composer) - 1 ) echo ", ";

				} // endfor 
			} // end if imdbtaxonomycomposer ?>

		</div>

<?php		}
	}


	public function lumiere_movies_soundtrack($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$soundtrack = $movie->soundtrack (); 

		if (!empty($soundtrack)) {?>
									<!-- soundtrack -->

		<div class="lumiere-lines-common imdbelementSOUNDTRACK">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Soundtrack', 'Soundtracks', count($soundtrack), 'lumiere-movies')))); ?>:</span><?php

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
					if ( (isset($soundtrack[$i]['credits'][0])) && (!empty($soundtrack[$i]['credits'][0]) ) )
						echo " - <i>". lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][0]['credit_to'])."</i> ";
						echo " (". lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][0]['desc']).") ";
					if ( (isset($soundtrack[$i]['credits'][1])) && (!empty($soundtrack[$i]['credits'][1]) ) )
						if ( (isset($soundtrack[$i]['credits'][1]['credit_to'])) && (!empty($soundtrack[$i]['credits'][1]['credit_to']) ) )
							echo " - <i>". lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][1]['credit_to'])."</i> ";
						if ( (isset($soundtrack[$i]['credits'][1]['desc'])) && (!empty($soundtrack[$i]['credits'][1]['desc']) ) )
							echo " (". lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][1]['desc']).") ";
				} else {
					if ( (isset($soundtrack[$i][credits][0])) && (!empty($soundtrack[$i][credits][0]) ) )
						echo " - <i>". lumiere_remove_link ($soundtrack[$i]['credits'][0]['credit_to'])."</i> ";
						echo " (". lumiere_remove_link ($soundtrack[$i]['credits'][0]['desc']).") ";
					if (!empty($soundtrack[$i][credits][1]) )
						echo " - <i>". lumiere_remove_link ($soundtrack[$i]['credits'][1]['credit_to'])."</i> ";
						echo " (". lumiere_remove_link ($soundtrack[$i]['credits'][1]['desc']).") ";
				} // end if remove popup
				echo "\n";
			}  // endfor ?>

		</div>

<?php 		}
	}



	public function lumiere_movies_prodcompany($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$prodcompany = $movie->prodCompany ();

		if (!empty($prodcompany))  {?>

									<!-- Production company -->

		<div class="lumiere-lines-common imdbelementPRODCOMPANY">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Production company', 'Production companies', count($prodcompany), 'lumiere-movies')))); ?>:</span><?php
			for ($i = 0; $i < count ($prodcompany); $i++) { 
				if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
					echo "\n\t\t\t\t". '<div align="center" class="imdbdiv-liees">';
					echo "\n\t\t\t\t\t". '<div class="lumiere_float_left">';
					echo "<a href='".esc_url( $prodcompany[$i]['url'])."' title='".esc_attr($prodcompany[$i]['name'])."'>";
					echo esc_html( $prodcompany[$i]['name'] );
					echo '</a>'; 
					echo '</div>';
					echo "\n\t\t\t\t\t". '<div align="right">';
						if (!empty($prodcompany[$i]['notes']))
							echo esc_html( $prodcompany[$i]['notes'] );
						else
							echo "&nbsp;";
					echo '</div>';
					echo "\n\t\t\t\t". '</div>';
				} else { // if "Remove all links" option is selected 
					echo esc_html( $prodcompany[$i]['name'] )."<br />";
				}  // end if remove popup
			}  // endfor ?>

		</div>
<?php
		}
	}


	public function lumiere_movies_officialsite($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$officialSites = $movie->officialSites ();
		if (!empty($officialSites))  {?>
									<!-- official websites -->

		<div class="lumiere-lines-common imdbelementOFFICIALWEBSITE">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Official website', 'Official websites', count($officialSites), 'lumiere-movies')))); ?>:</span><?php
			for ($i = 0; $i < count ($officialSites); $i++) { 
				echo "<a href='".esc_url($officialSites[$i]['url'])."' title='".esc_attr( $officialSites[$i]['name'] )."'>";
				echo sanitize_text_field( $officialSites[$i]['name'] );
				echo "</a>";
				if ($i < count ($officialSites) - 1) echo ", ";
			}  // endfor ?>

		</div>
<?php
		}
	}


	public function lumiere_movies_director($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $wp_query;

		$director = $movie->director(); 

		if (!empty($director)) {?>
									<!-- director -->

		<div class="lumiere-lines-common imdbelementDIRECTOR"><?php
			echo "\n\t\t\t" . '<span class="imdbincluded-subtitle">' . sprintf(esc_html(_n('Director', 'Directors', count($director), 'lumiere-movies'))) . ':</span>' . "\n\t\t\t";

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomydirector'] == true )  ) { 			# lumiere_count_me() to avoid adding every taxonomy from several movies's genre...

				for ($i = 0; $i < count ($director); $i++) {
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field( $director[$i]["name"] ), $imdb_admin_values['imdburlstringtaxo'] . 'director', false); 

					# list URL taxonomy page
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'director/' .lumiere_make_taxonomy_link( esc_html( $director[$i]["name"] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $director[$i]["name"] );
					echo '</a>';
					if ( $i < count ($director) - 1 ) echo ", ";
				}

			} else { 
				for ($i = 0; $i < count ($director); $i++) {
					if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ( $i < count ($director) - 1 ) echo ', ';
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
			
			echo "\n\t\t" . '</div>';

		} // end imdbwidgetdirector
	}


	public function lumiere_movies_creator($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $wp_query, $count_me_siffer;

		$creator = $movie->creator(); 

		if (!empty($creator)) { ?>
							<!-- creator -->

		<div class="lumiere-lines-common imdbelementCREATOR">
			<span class="imdbincluded-subtitle"><?php echo sprintf(esc_html(_n('Creator', 'Creators', count($creator), 'lumiere-movies'))); ?>:</span>&nbsp;
<?php
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomycreator'] == true ) /*&& (lumiere_count_me($imdb_admin_values['imdburlstringtaxo'] . 'creator', $count_me_siffer) == "nomore")*/ ) { 
			// lumiere_count_me() to avoid adding every taxonomy from several movies's genre...

				for ($i = 0; $i < count ($creator); $i++) {
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field( $creator[$i]["name"] ), $imdb_admin_values['imdburlstringtaxo'] . 'creator', false); 
				
					# list URL taxonomy page
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'creator/' .lumiere_make_taxonomy_link( esc_html( $creator[$i]["name"] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $creator[$i]["name"] );
					echo '</a>'; 
				}

			} else { 
				for ($i = 0; $i < count ($creator); $i++) {
					if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ( $i < count ($creator) - 1 ) echo ', ';
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup ?>
							<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="<?php echo $creator[$i]["imdb"]; ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo $creator[$i]["name"]; ?></a>
<?php						} else { // classic popup ?>
							<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="<?php echo $creator[$i]["imdb"]; ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo $creator[$i]["name"]; ?></a><?php		
						echo sanitize_text_field( $creator[$i]["name"] )."</a>";
						} 
					} else { // if "Remove all links" option is selected 
						if ( $i < count ($creator) - 1 ) echo ', ';
						echo sanitize_text_field( $creator[$i]["name"] );
					}  // end if remove popup
				} // endfor 
				
			} // end if imdbtaxonomycreator
			
			?>
		</div>

<?php		}
	}



	public function lumiere_movies_producer($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $wp_query, $count_me_siffer;

		$producer = $movie->producer(); 

		if (!empty($producer)) {?>
									<!-- producers -->

		<div class="lumiere-lines-common imdbelementPRODUCER">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Producer', 'Producers', count($producer), 'lumiere-movies')))); ?>:</span><?php
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomyproducer'] == true ) ) { 

				for ($i = 0; $i < count ($producer); $i++) {
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field( $producer[$i]["name"] ), $imdb_admin_values['imdburlstringtaxo'] . 'producer', false); 

					# list URL taxonomy page
					echo "\n\t\t\t\t". '<div align="center" class="imdbdiv-liees">';
					echo "\n\t\t\t\t\t". '<div class="lumiere_float_left">';
					echo '<a class="linkincmovie" ';
					echo 'href="' . esc_url( site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'producer/' .lumiere_make_taxonomy_link( esc_html( $producer[$i]["name"] ) ) ). '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $producer[$i]["name"] );
					echo '</a>'; 
					echo '</div>';
					echo "\n\t\t\t\t\t". '<div align="right">';
					echo esc_html( $producer[$i]["role"] );
					echo '</a>'; 
					echo '</div>';
					echo "\n\t\t\t\t". '</div>';

				}

			} else { 
				for ($i = 0; $i < count ($producer); $i++) { ?>

						<div align="center" class="imdbdiv-liees">
							<div class="lumiere_float_left">
<?php					if  ($imdb_widget_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup ?>
							<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="<?php echo esc_attr( $producer[$i]["imdb"] ); ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo esc_html( $producer[$i]["name"] ); ?></a>
<?php						} else {  // classic popup ?>
							<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="<?php echo esc_attr( $producer[$i]["imdb"] ); ?>" title="<?php esc_html_e('open a new window with IMDb informations', 'lumiere-movies'); ?>"><?php echo esc_html( $producer[$i]["name"] ); ?></a><?php		
						} 
					} else { // if "Remove all links" option is selected 
						echo esc_html( $producer[$i]["name"] );
					}  // end if remove popup ?>
							</div>
							<div align="right">
								<?php 
								if (!empty($producer[$i]["role"] ) )
									echo esc_html( $producer[$i]["role"] ); 
								else
									echo "&nbsp;"; ?>

							</div>
						</div><?php
				} // endfor 
				
			} // end if imdbtaxonomyproducer ?>

		</div>
<?php
		}
	}



	public function lumiere_movies_writer($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $wp_query;

		$writer = $movie->writing(); 

		if (!empty($writer)) {?>
									<!-- writers -->

		<div class="lumiere-lines-common imdbelementWRITER">
			<span class="imdbincluded-subtitle"><?php echo (sprintf(esc_attr(_n('Writer', 'Writers', count($writer), 'lumiere-movies')))); ?>:</span><?php
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomywriter'] == true ) ) { 
			// lumiere_count_me() to avoid adding every taxonomy from several movies's genre...

				for ($i = 0; ($i < count($writer)); $i++) { 
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field( $writer[$i]["name"]), $imdb_admin_values['imdburlstringtaxo'] . 'writer', false); 
				
					# list URL taxonomy page
					echo "\n\t\t\t\t". '<div align="center" class="imdbdiv-liees">';
					echo "\n\t\t\t\t\t". '<div class="lumiere_float_left">';
					echo '<a class="linkincmovie" ';
					echo 'href="' . esc_url( site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'writer/' .lumiere_make_taxonomy_link( esc_html( $writer[$i]["name"] ) ) ). '" ';
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
							<div class="lumiere_float_left">
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
								<?php 
								if (!empty($writer[$i]["role"] ) )
									echo sanitize_text_field( $writer[$i]["role"] ); 
								else
									echo "&nbsp;"; ?>

							</div>
						</div><?php
				} // endfor 
				
			} // end if imdbtaxonomywriter ?>

		</div>
<?php
		}
	}



	public function lumiere_movies_actor($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values, $wp_query;

		$cast = $movie->cast(); 

		if (!empty($cast)) { ?>
								<!-- actors -->

		<div class="lumiere-lines-common imdbelementACTOR">
				<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Actor', 'Actors', count($cast), 'lumiere-movies')))); ?>:</span><?php 
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomyactor'] == true ) /*&& (lumiere_count_me($imdb_admin_values['imdburlstringtaxo'] . 'actor', $count_me_siffer) == "nomore")*/ ) {
			// lumiere_count_me() to avoid adding every taxonomy from several movies's genre...

				// value $imdb_widget_values['imdbwidgetactornumber'] is selected, but value $imdb_widget_values['imdbwidgetactornumber'] is empty
				if ( (isset($imdb_widget_values['imdbwidgetactor'])) && (empty($imdb_widget_values['imdbwidgetactornumber'])) ) { $nbactors =  "1";} else {$nbactors =  $imdb_widget_values['imdbwidgetactornumber']; }

				for ($i = 0; ($i < $nbactors) && ($i < count($cast)); $i++) { 
					// add taxonomy terms to posts' terms
					wp_set_post_terms($wp_query->post->ID, sanitize_text_field( $cast[$i]["name"]), $imdb_admin_values['imdburlstringtaxo'] . 'actor', false); 

					# list URL taxonomy page
					echo "\n\t\t\t\t". '<div align="center" class="imdbdiv-liees">';
					echo "\n\t\t\t\t\t". '<div class="lumiere_float_left">';
					// remove the <br> which break the layout
					echo esc_html( preg_replace('/\n/', "", $cast[$i]["role"]) ); 
					echo '</div>';
					echo "\n\t\t\t\t\t". '<div align="right">';
					echo '<a class="linkincmovie" ';
					echo 'href="' . site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'actor/' .lumiere_make_taxonomy_link( esc_html( $cast[$i]["name"] ) ) . '" ';
					echo 'title="' . esc_attr('Find similar taxonomy results', 'lumiere-movies') . '">';
					echo esc_html( $cast[$i]["name"] );
					echo '</a>'; 
					echo '</div>';
					echo "\n\t\t\t\t". '</div>';
		
				}


			} else { 

				// if $imdb_widget_values['imdbwidgetactornumber'] is selected, but value $imdb_widget_values['imdbwidgetactornumber'] is empty
				if ( (isset($imdb_widget_values['imdbwidgetactor'])) && (empty($imdb_widget_values['imdbwidgetactornumber'])) ) { $nbactors =  "1";} else {$nbactors =  $imdb_widget_values['imdbwidgetactornumber']; }

				for ($i = 0; $i < $nbactors && ($i < count($cast)); $i++) { 
					echo "\n\t\t\t\t". '<div align="center" class="imdbdiv-liees">';
					echo "\n\t\t\t\t\t". '<div class="lumiere_float_left">';
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
				
			} // end if imdbtaxonomyactor ?>

		</div>
	
<?php		} 
	}



	public function lumiere_movies_plot($movie=NULL) {

		/* Vars */ 
		global $imdb_admin_values, $imdb_widget_values;

		$plot = $movie->plot (); 

		// tested if the array contains data; if not, doesn't go further
		if (!lumiere_is_multiArrayEmpty($plot)) { ?>
									<!-- Plots -->

		<div class="lumiere-lines-common imdbelementPLOT">
			<span class="imdbincluded-subtitle"><?php echo(sprintf(esc_attr(_n('Plot', 'Plots', count($plot), 'lumiere-movies')))); ?>:</span><br /><?php

				// value $imdb_widget_values['imdbwidgetplotnumber'] is selected, but value $imdb_widget_values['imdbwidgetplotnumber'] is empty
				if ( isset($imdb_widget_values['imdbwidgetplot']) && (empty($imdb_widget_values['imdbwidgetplotnumber'])) ){$nbplots =  "1";} else { $nbplots =  $imdb_widget_values['imdbwidgetplotnumber'];}
				for ($i = 0; $i < $nbplots  && ($i < count ($plot)); $i++) { 
					if  ($imdb_widget_values['imdblinkingkill'] == false ) { 
					// if "Remove all links" option is not selected 
						echo wp_kses_post( $plot[$i], $this->allowed_html_for_escape_functions ) . "\n";
					} else {
						echo lumiere_remove_link ($plot[$i]). "\n";
					} 
					if ( $i < ($nbplots -1) ) { echo "\n<hr>\n";} // add hr to every quote but the last					
				}// endfor ?>

		</div>
<?php
		}
	}



	public function lumiere_movies_creditlink($midPremierResultat=NULL) {

		/* Vars */ 
		global $imdb_widget_values; ?>
									<!-- Source credit link -->

<?php 
		if ( ($imdb_widget_values['imdblinkingkill'] == false ) && ($imdb_widget_values['imdbwidgetsource'] == true ) ) { 
	// if "Remove all links" option is not selected ?>
		<div class="lumiere-lines-common imdbelementSOURCE">
			<span class="imdbincluded-subtitle"><?php esc_html_e('Source', 'lumiere-movies'); ?>:</span><?php esc_url( lumiere_source_imdb($midPremierResultat) );?>

		</div>
<?php
 		} 
	}

} // end of class

?>
