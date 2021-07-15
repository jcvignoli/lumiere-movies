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
 #  LumiereMovies Class : this class is automatically called so shortags     #
 #  are caught. It displays taxonomy links and add taxonomy according to     #
 #  selected options                                                         #
 #									              #
 #############################################################################

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die('You can not call directly this page');
}

// use Monolog classes in class/imdbphp/Monolog/
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class LumiereMovies {

	/* HTML allowed for use of wp_kses_post()
	 * Usefull for access from outside the class
	 */
	const allowed_html_for_escape_functions = [
	    'a' => [
		 'id' => true,
		 'href'  => true,
		 'title' => true,
	    ]
	]; 

	/* Store all returned movie details search result
	 * Usefull for access from outside the class
	 */
	public $lumiere_result = "";

	/* Store the class of Lumière settings
	 * Usefull to start a new IMDbphp query
	 */
	private $configclass;

	/* Vars from Lumière settings
	 *
	 */
	private $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

	/* Store the class for logging using the Monolog library
	 *
	 */
	private $loggerclass;

	/** Class constructor
	 ** 
	 **/
	function __construct() {

		// Start config class and get the vars
		if (class_exists("\Lumiere\Settings")) {
			$configclass = new \Lumiere\Settings();
			$this->configclass = $configclass;
			$this->imdb_admin_values = $configclass->get_imdb_admin_option();
			$this->imdb_widget_values = $configclass->get_imdb_widget_option();
			$this->imdb_cache_values = $configclass->get_imdb_widget_option();

			// Start logger class if debug is selected
			if ( (isset($this->imdb_admin_values['imdbdebug'])) && ($this->imdb_admin_values['imdbdebug'] == 1) ){

				// Start the debugging class
				/* if starting this, everything get screwed up and gutenberg editor can't save anymore
				tried to use add_action() instead of calling the function, but no difference made
				$debug_start = new \Lumiere\Utils();
				add_action ('admin_init', $debug_start->lumiere_activate_debug($this->imdb_admin_values, '', ''), 99, 4); # add libxml_use_internal_errors(true) which avoid endless loops with imdbphp parsing errors 
				*/

				// Start the logger
				$this->configclass->lumiere_start_logger('movies');
				$this->loggerclass = $this->configclass->loggerclass;

			} else {

				$this->loggerclass = NULL;
			}

		} else {

			wp_die( esc_html__('Cannot start class movie, class Lumière Settings not found', 'lumiere-movies') );

		}

		// Start 
		$this->init();

		if  (! is_admin() ) {
			add_shortcode( 'imdblt', [$this, 'parse_lumiere_tag_transform'] );
			add_shortcode( 'imdbltid', [$this, 'parse_lumiere_tag_transform_id'] );
		}

	}

	function init($imdballmeta=NULL){

		/* Vars */ 
		global $imdballmeta,$count_me_siffer;

		$count_me_siffer = isset($count_me_siffer) ? $count_me_siffer : 0; # var for counting only one results
		$imdballmeta = isset($imdballmeta) ? $imdballmeta : array();
		$output = "";

		// Get main vars from the class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;
		$imdb_cache_values = $this->imdb_cache_values;

		if (isset ($_GET["mid"])) {

			$movieid = filter_var( $_GET["mid"], FILTER_SANITIZE_NUMBER_INT);
			$movie = new \Imdb\Title($movieid, $this->configclass, $this->loggerclass);

		} else {

			$search = new \Imdb\TitleSearch($this->configclass, $this->loggerclass );

		}

		// $imdballmeta var comes from custom post's field in widget or in post
		for ($i=0; $i < count($imdballmeta); $i++) {	

			// sanitize
			$film = $imdballmeta[$i]; 

			// A movie's title has been specified
			if (isset($film['byname']))  {

				// get meta data from class widget or lumiere
				$film = $film['byname'];  

				// check a the movie title exists
				if ( ($film !== null) && !empty($film) && isset($film) ) {

					$results = $search->search ($film, $this->configclass->lumiere_select_type_search() );

				}
				// if a result was found in previous query
				if ( isset($results) && ($results !== null) && !empty($results) ) {

					$midPremierResultat = $results[0]->imdbid();

				// no result, so jump to the next query and forget the current
				} else {

					continue; 
				}


			// no movie's title but a movie's ID has been specified
			} elseif (isset($film['bymid']))  {

				$midPremierResultat = $film['bymid']; // get the movie id entered

			// nothing was specified
			} else {

				$results = $search->search ($film, $this->configclass->lumiere_select_type_search() );

				// a result is found
				if ( ($results !== null) && !empty($results) ) {	

					$midPremierResultat = $results[0]->imdbid(); 

				// break if no result found, otherwise imdbphp library trigger fatal error
				} else {

					lumiere_noresults_text();
					break;
				}
			}

			// make sure only one result is displayed
			if ($this->lumiere_count_me($midPremierResultat, $count_me_siffer) == "nomore") {

				$output .= "\n\t\t\t\t\t\t\t\t\t" . '<!-- ### Lumière! movies plugin ### -->';
				$output .= "\n\t<div class='imdbincluded";

				// add dedicated class for themes
				if (isset($imdb_admin_values['imdbintotheposttheme'])) 
					$output .= ' imdbincluded_' . $imdb_admin_values['imdbintotheposttheme'];
				$output .= "'>";

				$output .= $this->lumiere_movie_design($midPremierResultat); # passed those two values to the design
				$output .= "\n\t</div>";
			}

			$count_me_siffer++; # increment counting only one results

		}

		$this->lumiere_result = $output; # send to meta var the result

		return $output;

	}
		

	function parse_lumiere_tag_transform($atts = array(), $content = null, $tag){

		//shortcode_atts(array( 'id' => 'default id', 'film' => 'default film'), $atts);

		$imdballmeta[] = $content;
		return $this->lumiere_external_call($imdballmeta,'','');

	}

	function parse_lumiere_tag_transform_id($atts = array(), $content = null, $tag){

		$imdballmeta[] = $content;
		return $this->lumiere_external_call('',$imdballmeta,'');

	}

	/**
	* Function external call (ie, inside a post)
	    can come from [imdblt] and [imdbltid]
	**/

	function lumiere_external_call ($moviename=NULL, $filmid=NULL, $external=NULL) {

		global $imdballmeta;

		// Call function from external (using parameter "external" )
		// Especially made to be integrated (ie, inside a php code)
		if ( ($external == "external") && isset($moviename) ) {	

			$imdballmeta[]['byname'] = $moviename;

			return $this->init($imdballmeta);

		}

		// Call function from external (using parameter "external" )
		// Especially made to be integrated (ie, inside a php code)
		if ( ($external == "external") && isset($filmid) )  {

			$imdballmeta[]['bymid'] = $filmid[0];

			return $this->init($imdballmeta);

		}

		//  Call with the parameter - imdb movie name (imdblt)
		if ( isset($moviename) && !empty($moviename) && empty($external) ) {	

			$imdballmeta[]['byname'] = $moviename[0];

			return $this->init($imdballmeta);

		}

		//  Call with the parameter - imdb movie id (imdbltid)
		if ( isset($filmid) && !empty($filmid) && empty($external) )  {

			$imdballmeta[]['bymid'] = $filmid[0];

			return $this->init($imdballmeta);
			
		}

	}


	/* Function to display the layout and calls all subfonctions
	 *
	 * @param $config -> takes the value of imdb class 
	 * @param $midPremierResultat -> takes the IMDb ID to be displayed
	 */
	public function lumiere_movie_design($midPremierResultat=NULL){

		/* Vars */ 
		global $magicnumber;
		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;
		$imdb_cache_values = $this->imdb_cache_values;

		$outputfinal ="";

		/* Start imdbphp class for new query based upon $midPremierResultat */
		$movie = new \Imdb\Title($midPremierResultat, $this->configclass, $this->loggerclass );

		foreach ( $imdb_widget_values['imdbwidgetorder'] as $magicnumber) {

			if  ( ($magicnumber == $imdb_widget_values['imdbwidgetorder']['title'] ) 
			&& ($imdb_widget_values['imdbwidgettitle'] == true ))
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_title ($movie), 'title');

			if  ( ($magicnumber == $imdb_widget_values['imdbwidgetorder']['pic'] ) 
			&& ($imdb_widget_values['imdbwidgetpic'] == true ) ) 
				$outputfinal .= $this->lumiere_movies_pics ($movie);

			if ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['country'] ) 
			&& ($imdb_widget_values['imdbwidgetcountry'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_country ($movie), 'country');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['runtime'] ) 
			&& ($imdb_widget_values['imdbwidgetruntime'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_runtime ($movie), 'runtime');

			if ( ($magicnumber== $imdb_widget_values['imdbwidgetorder']['rating'] ) 
			&& ($imdb_widget_values['imdbwidgetrating'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_rating ($movie), 'rating');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['language']) 
			&& ($imdb_widget_values['imdbwidgetlanguage'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_language($movie), 'language');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['genre'] )  
			&& ($imdb_widget_values['imdbwidgetgenre'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_genre ($movie), 'genre');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['keywords'] )  
			&& ($imdb_widget_values['imdbwidgetkeywords'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_keywords ($movie), 'keyword');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['goofs'] ) 
			&& ($imdb_widget_values['imdbwidgetgoofs'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper( $this->lumiere_movies_goofs ($movie), 'goof');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['comments'] ) 
			&& ($imdb_widget_values['imdbwidgetcomments'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_comment ($movie), 'comment');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['quotes'] )
			&& ($imdb_widget_values['imdbwidgetquotes'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_quotes ($movie), 'quote');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['taglines'] ) 
			&& ($imdb_widget_values['imdbwidgettaglines'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_taglines ($movie), 'tagline');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['trailer'] ) 
			&& ($imdb_widget_values['imdbwidgettrailer'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_trailer ($movie), 'trailer');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['colors'] ) 
			&& ($imdb_widget_values['imdbwidgetcolors'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_color ($movie), 'color');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['alsoknow'] )  
			&& ($imdb_widget_values['imdbwidgetalsoknow'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_aka ($movie), 'alsoknown');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['composer'] ) 
			&& ($imdb_widget_values['imdbwidgetcomposer'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_composer ($movie), 'composer');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['soundtrack'] ) 
			&& ($imdb_widget_values['imdbwidgetsoundtrack'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_soundtrack ($movie), 'soundtrack');

			if ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['prodcompany'] ) 
			&&  ($imdb_widget_values['imdbwidgetprodcompany'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_prodcompany ($movie), 'prodcompany');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['officialsites'] ) 
			&& ($imdb_widget_values['imdbwidgetofficialsites'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_officialsite ($movie), 'officialwebsite');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['source'] ) 
			&&  ($imdb_widget_values['imdbwidgetsource'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_creditlink($midPremierResultat), 'source'); # doesn't need class but movie id

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['director']) 
			&& ($imdb_widget_values['imdbwidgetdirector'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_director ($movie), 'director');

			if ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['creator']) 
			&&  ($imdb_widget_values['imdbwidgetcreator'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_creator ($movie), 'creator');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['producer'] ) 
			&& ($imdb_widget_values['imdbwidgetproducer'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_producer ($movie), 'producer');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['writer'] ) 
			&& ($imdb_widget_values['imdbwidgetwriter'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_writer ($movie), 'writer');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['actor'] ) 
			&& ($imdb_widget_values['imdbwidgetactor'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_actor ($movie), 'actor');

			if  ( ($magicnumber==$imdb_widget_values['imdbwidgetorder']['plot'] ) 
			&& ($imdb_widget_values['imdbwidgetplot'] == true ) )
				$outputfinal .= $this->lumiere_movie_design_addwrapper($this->lumiere_movies_plot ($movie), 'plot');

			$magicnumber++; 

		}
		return $outputfinal;
	}


	/* Function to add an HTML wrapper to text, here <div>
	 *
	 * @param $html -> text to wrap
	 * @param $item -> the item to transform, such as director, title, etc
	 */
	public function lumiere_movie_design_addwrapper($html, $item){

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;

		$outputfinal ="";
		$item = sanitize_text_field( $item );
		$item_caps = strtoupper($item);

		if (empty($html))
			return;

		$outputfinal .= "\n\t\t\t\t\t\t\t" . '<!-- ' . $item . ' -->';

		// title doesn't take item 'lumiere-lines-common' as a class
		if($item != 'title')
			$outputfinal .= "\n\t\t" . '<div class="lumiere-lines-common';
		else
			$outputfinal .= "\n\t\t" . '<div class="imdbelement' . $item_caps; 

		if (isset($imdb_admin_values['imdbintotheposttheme'])) 
			$outputfinal .= ' lumiere-lines-common_' . $imdb_admin_values['imdbintotheposttheme'] . ' imdbelement' . $item_caps. '_' . $imdb_admin_values['imdbintotheposttheme'];

		$outputfinal .= '">';

		$outputfinal .= $html;

		$outputfinal .= "\n\t\t" . '</div>';

		return $outputfinal;

	}

	/* Display the title and possibly the year
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_title ($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output="";
		$year=intval($movie->year ());
		$title_sanitized = sanitize_text_field( $movie->title() );

		$output .= "\n\t\t\t" .$title_sanitized;

		if (!empty($year) && ($imdb_widget_values['imdbwidgetyear'] == true ) ) { 
			$output .= " (".$year.")"; 
		}

		return $output;

	}


	/* Display the picture of the movie
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_pics ($movie=NULL) {

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output="";

		$photo_url = $movie->photo_localurl(true); // create the picture thumbnail for the cache refresh
		$photo_url_sanitized = $movie->photo_localurl(false) ? $movie->photo_localurl(false) : $movie->photo_localurl(true) ;

		$output .= "\n\t\t\t\t\t\t\t" . '<!-- pic -->';
		$output .= "\n\t\t" . '<div class="imdbelementPIC">';

		## The picture is either taken from the movie itself or if it doesn't exist, from a standard "no exist" picture.
		## The width value is taken from plugin settings, and added if the "thumbnail" option is unactivated

			// check if big pictures are selected (extract "_big.jpg" from picture's names, if exists), AND if highslide popup is activated
			if ( (substr( $photo_url_sanitized, -7, -4) == "big" ) && ($imdb_admin_values['imdbpopup_highslide'] == 1) ) {

				// value to store if previous checking is valid, call in lumiere_scripts.js
				$highslidephotook = "ok";

				// Link
				$output .= "\n\t\t\t" . '<a class="highslide_pic" href="' 
					. $photo_url_sanitized 
					. '" title="'
					. esc_attr( $movie->title() ) 
					. '">';

				// loading=\"eager\" to prevent wordpress loading lazy that doesn't go well with cache scripts
				$output .= "\n\t\t\t\t<img loading=\"eager\" class=\"imdbelementPICimg\" src=\"";

			} else {

				// no big picture found OR no highslide popup selected
				// loading=\"eager\" to prevent wordpress loading lazy that doesn't go well with cache scripts
				$output .= "\n\t\t\t".'<img loading="eager" class="imdbelementPICimg" src="';
			}

			// check if a picture exists
			if ($photo_url_sanitized == true){
				// a picture exists, so show it
				$output .= $photo_url_sanitized 
					.'" alt="'
					. esc_html__('Photo of','lumiere-movies') 
					.' ' 
					. esc_attr( $movie->title() ) . '" '; 
			} else { 
				// no picture found, display the replacement pic
				$output .= esc_url( $imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif') . '" alt="'.esc_html__('no picture found', 'lumiere-movies').'" '; 
			}

			$output .= 'width="' . intval( $imdb_admin_values['imdbcoversizewidth'] ) . '" />';

			// new verification, closure code related to previous if
			if ( (isset($highslidephotook))  && ($highslidephotook == "ok") ) 
				$output .= "\n\t\t\t</a>"; 
			else 
				$output .= "\n";

		$output .= "\n\t\t" . '</div>';

		return $output;
	}

	/* Display the country of origin
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_country ($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$country = $movie->country();
		$nbtotalcountry = intval(count($country));

		if (!empty($country)) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Country', 'Countries', $nbtotalcountry, 'lumiere-movies') ), number_format_i18n($nbtotalcountry) );
			$output .= ':</span>';

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomycountry'] == true ) ) { 

				for ($i = 0; $i < $nbtotalcountry; $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'country', esc_attr($country[$i]), '', 'one');
					if ( $i < $nbtotalcountry - 1 )
						$output .= ", ";

				}

			} else {

				for ($i = 0; $i < $nbtotalcountry; $i++) { 
					$output .= sanitize_text_field( $country[$i]);
					if ( $i < $nbtotalcountry - 1 ) $output .= ", ";	
				} // endfor

			} 

 		}

		return $output;

	}


	/* Display the runtime
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_runtime($movie=NULL) {

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;

		$output = "";
		$runtime_sanitized = sanitize_text_field( $movie->runtime() ); 

		if (!empty($runtime_sanitized) ) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= esc_html__('Runtime', 'lumiere-movies') ;
			$output .= ':</span>'; 
			$output .= $runtime_sanitized." ".esc_html__('minutes', 'lumiere-movies');
				
	 	} 

		return $output;

	}

	/* Display the language
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_language($movie=NULL) {

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$languages = $movie->languages();
		$nbtotallanguages = intval( count($languages) );

		if (!empty($languages) ) { 

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Language', 'Languages', $nbtotallanguages, 'lumiere-movies') ), number_format_i18n($nbtotallanguages) );
			$output .= ':</span>';

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomylanguage'] == true ) ) { 

				for ($i = 0; $i < $nbtotallanguages; $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'language', esc_attr($languages[$i]), '', 'one');
					if ( $i < $nbtotallanguages - 1 )
						$output .= ", ";

				}

			} else {
				for ($i = 0; $i < $nbtotallanguages; $i++) { 

					$output .= sanitize_text_field( $languages[$i] );

					if ( $i < $nbtotallanguages - 1 )	$output .= ", "; 	

				}
			} 

	 	}

		return $output;
	}


	/* Display the rating
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_rating($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$votes_sanitized = esc_attr($movie->votes());
		$rating_sanitized = esc_attr($movie->rating());

		if (($votes_sanitized)) { 

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= esc_html__('Rating', 'lumiere-movies');
			$output .= ':</span>';
			
			if  ( (isset($imdb_widget_values['imdbwidgetratingnopics'] )) && ( $imdb_widget_values['imdbwidgetratingnopics'] == true ) ) { // value which doesn't exist yet into plugin; has to be made
				$output .= $votes_sanitized." "; 
				$output .= esc_html__('votes, average ', 'lumiere-movies'); 
				$output .= " ".$rating_sanitized." ";
				$output .= esc_html__('(max 10)', 'lumiere-movies'); 

			// by default, display pictures and votes amount	
			} else {							
				$output .= " <img src=\"".$imdb_admin_values['imdbplugindirectory'].'pics/showtimes/'.(round($rating_sanitized*2, 0)/0.2).
				".gif\" title=\"".esc_html__('vote average ', 'lumiere-movies').$rating_sanitized.esc_html__(' out of 10', 'lumiere-movies')."\"  / >";
				$output .= " (".number_format($votes_sanitized, 0, '', "'")." ".esc_html__('votes', 'lumiere-movies').")";			
			}
			
 		}

		return $output;
	}


	/* Display the genre
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_genre($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$genre = $movie->genres ();	
		$nbtotalgenre = intval( count($genre) );	

		if (!empty($genre))  { 

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Genre', 'Genres', $nbtotalgenre, 'lumiere-movies') ), number_format_i18n($nbtotalgenre) );

			$output .= ':</span>';

			if ( ( $imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomygenre'] == true ) ) { 

				for ($i = 0; $i < $nbtotalgenre; $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'genre', esc_attr($genre[$i]), '', 'one');
					if ( $i < $nbtotalgenre - 1 )
						$output .= ", ";

				}

			} else {

				for ($i = 0; $i < $nbtotalgenre; $i++) { 

					$output .= esc_attr( $genre[$i] );
					if ( $i < $nbtotalgenre - 1 ) $output .= ', ';  

				} 
			} 

		}

		return $output;
	}

	/* Display the keywords
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_keywords($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$keywords = $movie->keywords();
		$nbtotalkeywords = intval( count($keywords) );

		if (!empty($keywords)) { 

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Keyword', 'Keywords', $nbtotalkeywords, 'lumiere-movies') ), number_format_i18n($nbtotalkeywords) );
			$output .= ':</span>';

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomykeywords'] == true ) ) { 

				for ($i = 0; $i < $nbtotalkeywords; $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'keywords', esc_attr($keywords[$i]), '', 'one');
					if ( $i < $nbtotalkeywords - 1 )
						$output .= ", ";

				}
					
			} else {
				for ($i = 0; $i < $nbtotalkeywords; $i++) { 

					$output .= esc_attr( $keywords[$i] ); 

					if ( $i < $nbtotalkeywords - 1 )  $output .= ", "; 										
				} 
			} 

		}

		return $output;
	}


	/* Display the goofs
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_goofs($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$goofs = $movie->goofs (); 
		$nbgoofs = empty($imdb_widget_values['imdbwidgetgoofsnumber']) ? $nbgoofs =  "1" : $nbgoofs =  intval( $imdb_widget_values['imdbwidgetgoofsnumber'] );
		$nbtotalgoofs = intval( count($goofs) );

		if (!empty($goofs))  {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Goof', 'Goofs', $nbtotalgoofs, 'lumiere-movies') ), number_format_i18n($nbtotalgoofs) );
			$output .= ':</span><br />';

			for ($i = 0; $i < $nbgoofs && ($i < $nbtotalgoofs ); $i++) { 

				$output .= "\n\t\t\t\t<strong>".sanitize_text_field( $goofs[$i]['type'] )."</strong>&nbsp;"; 
				$output .= sanitize_text_field( $goofs[$i]['content'] )."<br />\n"; 

			}

		}

		return $output;
	} 
	

	/* Display the main user comment
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_comment($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$comment[] = $movie->comment_split(); # this value is sent into an array!
		$comment_split = $movie->comment_split(); # this value isn't sent into an array, for use in "if" right below
		//$nbcomments = empty($imdb_widget_values['imdbwidgetcommentsnumber']) ? $nbcomments =  "1" : $nbcomments =  $imdb_widget_values['imdbwidgetcommentsnumber'] ;
		//$nbtotalcomments = count($comments) ;

		if (!empty($comment_split))  {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= esc_html__('User comment', 'lumiere-movies') ;
			$output .= ':</span><br />';

			$output .= "<";
			$output .=  "<i>". sanitize_text_field( $comment[0]['title'] ). "</i> by ";

			// if "Remove all links" option is not selected 
			if  ( (isset($imdb_admin_values['imdblinkingkill'])) && ($imdb_admin_values['imdblinkingkill'] == false ) ){ 

				$output .= "<a href=\"" . esc_url($comment[0]["author"]["url"]) . "\">" .  sanitize_text_field($comment[0]["author"]["name"] ). "</a>";

			} else {

				$output .= sanitize_text_field( $comment[0]["author"]["name"] );

			}

			$output .= ">&nbsp;";
			$output .= sanitize_text_field( $comment[0]['comment'] ) ;

		}

		return $output;

	}



	/* Display the quotes
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_quotes($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$quotes = $movie->quotes ();  
		$nbquotes = empty($imdb_widget_values['imdbwidgetquotesnumber']) ? $nbquotes =  "1" : $nbquotes =  intval( $imdb_widget_values['imdbwidgetquotesnumber'] );
		$nbtotalquotes = intval( count($quotes) );

		if (! empty($quotes)) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Quote', 'Quotes', $nbtotalquotes, 'lumiere-movies') ), number_format_i18n($nbtotalquotes) );
			$output .= ':</span><br />';

			for ($i = 0; $i < $nbquotes && ($i < $nbtotalquotes); $i++) { 
				
				//transform <p> tags into <div> tags so they're not impacted by the theme
				$currentquotes = preg_replace ( '~<p>~', "\n\t\t\t<div>", $quotes[$i]);
				$currentquotes = preg_replace ( '~</p>~', "\n\t\t\t</div>", $currentquotes);

				// if "Remove all links" option is not selected 
				if  ($imdb_admin_values['imdblinkingkill'] == false ) { 
					$output .= "\n\t\t\t";
					$output .= $this->lumiere_convert_txtwithhtml_into_popup_people ($currentquotes);

				} else {

					$output .= "\n\t\t". $this->lumiere_remove_link ($currentquotes) ;

				} 
				if ( $i < ($nbquotes -1) ) $output .= "\n\t\t\t<hr>"; // add hr to every quote but the last					
			}

		} 

		return $output;
	}


	/* Display the taglines
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_taglines($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$taglines = $movie->taglines ();
		$nbtaglines = empty($imdb_widget_values['imdbwidgettaglinesnumber']) ? $nbquotes =  "1" : $nbquotes =  intval( $imdb_widget_values['imdbwidgettaglinesnumber'] );
		$nbtotaltaglines = intval( count($taglines) );

		if (!empty($taglines))  {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Tagline', 'Taglines', $nbtotaltaglines, 'lumiere-movies') ), number_format_i18n($nbtotaltaglines) );
			$output .= ':</span>';
			
			for ($i = 0; $i < $nbtaglines && ($i < $nbtotaltaglines); $i++) { 

				$output .= "\n\t\t\t&laquo; " . sanitize_text_field( $taglines[$i] )." &raquo; ";
				if ($i < ( $nbtaglines -1 ) ) $output .= ", "; // add comma to every quote but the last

			} 

		}

		return $output;

	}


	/* Display the trailer
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_trailer($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$trailers = $movie->trailers(TRUE);
		$nbtrailers = empty($imdb_widget_values['imdbwidgettrailernumber']) ? $nbtrailers =  "1" : $nbtrailers =  intval( $imdb_widget_values['imdbwidgettrailernumber'] );
		$nbtotaltrailers = intval( count($trailers) );

		if (!empty($trailers))  {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Trailer', 'Trailers', $nbtotaltrailers, 'lumiere-movies') ), number_format_i18n($nbtotaltrailers) );
			$output .= ':</span>';

			// value $imdb_widget_values['imdbwidgettrailer'] is selected, but value $imdb_widget_values['imdbwidgettrailernumber'] is empty

			for ($i = 0; ($i < $nbtrailers  && ($i < $nbtotaltrailers) ); $i++) { 

				if  ($imdb_admin_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
					$output .= "\n\t\t\t<a href='".esc_url( $trailers[$i]['url'] )."' title='".esc_html__('Watch on IMBb website the trailer for ', 'lumiere-movies') . esc_html__( $trailers[$i]['title'] ) ."'>". sanitize_text_field( $trailers[$i]['title'] ) . "</a>\n";

				} else { // if "Remove all links" option is selected 

					$output .= "\n\t\t\t" . sanitize_text_field( $trailers[$i]['title'] ) . ", " . esc_url( $trailers[$i]['url'] );

				}

				if ($i < ( $nbtrailers -1 ) ) $output .= ", "; // add comma to every quote but the last
			} 

		}

		return $output;

	}



	/* Display the color
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_color($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$colors = $movie->colors ();  
		$nbtotalcolors = intval( count($colors) );

		if (!empty($colors))  { 

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Color', 'Colors', $nbtotalcolors, 'lumiere-movies') ), number_format_i18n($nbtotalcolors) );
			$output .= ':</span>';

			// Taxonomy
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomycolor'] == true ) ) { 

				for ($i = 0; $i < $nbtotalcolors; $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'color', esc_attr($colors[$i]), '', 'one');
					if ( $i < $nbtotalcolors - 1 )
						$output .= ", ";

				}

			// No taxonomy
			} else {

				for ($i = 0; $i < count ($colors); $i++) { 

					$output .= "\n\t\t\t" . sanitize_text_field( $colors[$i] ); 
					if ( $i < $nbtotalcolors - 1 ) $output .= ", "; 										
				}  

			}

		} 

		return $output;

	}



	/* Display the as known as, aka
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_aka($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$alsoknow = $movie->alsoknow ();
		$nbalsoknow = empty($imdb_widget_values['imdbwidgetalsoknownumber']) ? $nbalsoknow =  "1" : $nbalsoknow =  intval( $imdb_widget_values['imdbwidgetalsoknownumber'] );
		$nbtotalalsoknow = intval( count($alsoknow) );

		if (!empty($alsoknow)) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= esc_html__('Also known as', 'lumiere-movies');
			$output .= ':</span>';
			
			for ($i = 0; ($i < $nbtotalalsoknow) && ($i < $nbalsoknow) ; $i++) { 

				$output .= "\n\t\t\t<strong>".sanitize_text_field( $alsoknow[$i]['title'] )."</strong> "."(".sanitize_text_field( $alsoknow[$i]['country'] );

				if (!empty($alsoknow[$i]['comment'])) 
					$output .= " - <i>".sanitize_text_field( $alsoknow[$i]['comment'] )."</i>";

				$output .= ")";
				if ( $i < $nbtotalalsoknow - 1 ) $output .= ", ";

			} // endfor 

		}

		return $output;
	}


	/* Display the composers
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_composer($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$composer = $movie->composer () ;
		$nbtotalcomposer = intval( count($composer) );

		if (!empty($composer))  {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Composer', 'Composers', $nbtotalcomposer, 'lumiere-movies') ), number_format_i18n($nbtotalcomposer) );
			$output .= ':</span>';

			// Taxonomy
			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomycomposer'] == true ) ) { 

				for ($i = 0; $i < $nbtotalcomposer; $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'composer', esc_attr($composer[$i]["name"]), '', 'one');
					if ( $i < $nbtotalcomposer - 1 )
						$output .= ", ";

				}

			// No taxonomy
			} else { 

				for ($i = 0; $i < $nbtotalcomposer; $i++) {
					if  ($imdb_admin_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup

							$output .= "\n\t\t\t" . '<a class="link-imdblt-highslidepeople highslide" data-highslidepeople="' . sanitize_text_field( $composer[$i]["imdb"] ). '" title="' . esc_html__("Link to local IMDb", "imdb") . '">' . sanitize_text_field( $composer[$i]["name"] ) . "</a>";

						} else {// classic popup

							$output .= "\n\t\t\t" . '<a class="link-imdblt-highslidepeople" data-classicpeople="' . sanitize_text_field( $composer[$i]["imdb"] ). '" title="' . esc_html__("Link to local IMDb", 'lumiere-movies') . '">' . sanitize_text_field( $composer[$i]["name"] ). "</a>";

						} 

					// if "Remove all links" option is selected
					} else { 

						$output .= sanitize_text_field( $composer[$i]["name"] );

					} 
	
					if ( $i < $nbtotalcomposer - 1 ) $output .= ", ";

				} // endfor 

			} 


		}

		return $output;

	}


	/* Display the soundtrack
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_soundtrack($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$soundtrack = $movie->soundtrack (); 
		$nbsoundtracks = empty($imdb_widget_values['imdbwidgetsoundtracknumber']) ? $nbsoundtracks =  "1" : $nbsoundtracks =  intval( $imdb_widget_values['imdbwidgetsoundtracknumber'] );
		$nbtotalsountracks = intval( count($soundtrack) );

		if (!empty($soundtrack)) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Soundtrack', 'Soundtracks', $nbtotalsountracks, 'lumiere-movies') ), number_format_i18n($nbtotalsountracks) );
			$output .= ':</span>';

			for ($i = 0; $i < $nbsoundtracks && ($i < $nbtotalsountracks); $i++) { 

				$output .= "\n\t\t\t<strong>".$soundtrack[$i]['soundtrack']."</strong>"; 

				// if "Remove all links" option is not selected 
				if  ($imdb_admin_values['imdblinkingkill'] == false ) { 

					if ( (isset($soundtrack[$i]['credits'][0])) && (!empty($soundtrack[$i]['credits'][0]) ) )
						$output .= "\n\t\t\t - <i>". $this->lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][0]['credit_to'])."</i> ";
						$output .= " (". $this->lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][0]['desc']).") ";
					if ( (isset($soundtrack[$i]['credits'][1])) && (!empty($soundtrack[$i]['credits'][1]) ) )
						if ( (isset($soundtrack[$i]['credits'][1]['credit_to'])) && (!empty($soundtrack[$i]['credits'][1]['credit_to']) ) )
							$output .= "\n\t\t\t - <i>". $this->lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][1]['credit_to'])."</i> ";
						if ( (isset($soundtrack[$i]['credits'][1]['desc'])) && (!empty($soundtrack[$i]['credits'][1]['desc']) ) )
							$output .= " (". $this->lumiere_convert_txtwithhtml_into_popup_people ($soundtrack[$i]['credits'][1]['desc']).") ";
				} else {
					if ( (isset($soundtrack[$i]['credits'][0])) && (!empty($soundtrack[$i]['credits'][0]) ) )
						$output .= "\n\t\t\t - <i>". $this->lumiere_remove_link ($soundtrack[$i]['credits'][0]['credit_to'])."</i> ";
						$output .= " (". $this->lumiere_remove_link ($soundtrack[$i]['credits'][0]['desc']).") ";
					if (!empty($soundtrack[$i]['credits'][1]) )

						$output .= "\n\t\t\t - <i>". $this->lumiere_remove_link ($soundtrack[$i]['credits'][1]['credit_to'])."</i> ";
						$output .= " (". $this->lumiere_remove_link ($soundtrack[$i]['credits'][1]['desc']).") ";
				} // end if remove popup

			} 

 		}

		return $output;

	}



	/* Display the production companies
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_prodcompany($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$prodcompany = $movie->prodCompany ();
		$nbtotalprodcompany = intval( count($prodcompany) );

		if (!empty($prodcompany))  {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Production company', 'Production companies', $nbtotalprodcompany, 'lumiere-movies') ), number_format_i18n($nbtotalprodcompany) );
			$output .= ':</span>';

			for ($i = 0; $i < $nbtotalprodcompany; $i++) { 

				if  ($imdb_admin_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
					$output .= "\n\t\t\t\t". '<div align="center" class="lumiere_container">';
					$output .= "\n\t\t\t\t\t". '<div class="lumiere_align_left lumiere_flex_auto">';
					$output .= "<a href='".esc_url( $prodcompany[$i]['url'])."' title='".esc_html__($prodcompany[$i]['name'])."'>";
					$output .= esc_attr( $prodcompany[$i]['name'] );
					$output .= '</a>'; 
					$output .= '</div>';
					$output .= "\n\t\t\t\t\t". '<div class="lumiere_align_right lumiere_flex_auto">';
						if (!empty($prodcompany[$i]['notes']))
							$output .= esc_attr( $prodcompany[$i]['notes'] );
						else
							$output .= "&nbsp;";
					$output .= '</div>';
					$output .= "\n\t\t\t\t". '</div>';
				} else { // if "Remove all links" option is selected 

					$output .= esc_attr( $prodcompany[$i]['name'] )."<br />";

				}  // end if remove popup

			}  // endfor


		}

		return $output;

	}


	/* Display the official site
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_officialsite($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$officialSites = $movie->officialSites ();
		$nbtotalofficialSites = intval( count($officialSites) );

		if (!empty($officialSites))  {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Official website', 'Official websites', $nbtotalofficialSites, 'lumiere-movies') ), number_format_i18n($nbtotalofficialSites) );
			$output .= ':</span>';

			for ($i = 0; $i < $nbtotalofficialSites; $i++) { 

				$output .= "\n\t\t\t<a href='".esc_url($officialSites[$i]['url'])."' title='".esc_html__( $officialSites[$i]['name'] )."'>";
				$output .= sanitize_text_field( $officialSites[$i]['name'] );
				$output .= "</a>";
				if ($i < $nbtotalofficialSites - 1) $output .= ", ";

			} 

		}

		return $output;
	}


	/* Display the director
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_director($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$director = $movie->director(); 
		$nbtotaldirector = intval( count($director) );

		if (!empty($director)) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Director', 'Directors', $nbtotaldirector, 'lumiere-movies') ), number_format_i18n($nbtotaldirector) );
			$output .= ':</span>';

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomydirector'] == true )  ) {

				for ($i = 0; $i < $nbtotaldirector; $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'director', esc_attr($director[$i]["name"]), '', 'one');
					if ( $i < $nbtotaldirector - 1 )
						$output .= ", ";

				}

			} else { 

				for ($i = 0; $i < $nbtotaldirector; $i++) {

					if  ($imdb_admin_values['imdblinkingkill'] == false ) { // if "Remove all links" option is not selected 
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup 

							$output .= "\n\t\t\t\t" . '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $director[$i]["imdb"] ) . '" title="' . esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . esc_attr( $director[$i]["name"] ) . '</a>';

						// classic popup 
						} else { 

							$output .= "\n\t\t\t\t" . '<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="' . $director[$i]["imdb"] . '" title="' . esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . $director[$i]["name"] . '</a>';
						} 

					} else { // if "Remove all links" option is selected 

						$output .= esc_attr( $director[$i]["name"] );

					}  // end if remove popup

					if ( $i < $nbtotaldirector - 1 ) $output .= ', ';

				} // endfor 
				
			} 

		} 

		return $output;

	}


	/* Display the creator (for series only)
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_creator($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$creator = $movie->creator(); 
		$nbtotalcreator = intval( count($creator) );

		if (!empty($creator)) { 

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Creator', 'Creators', $nbtotalcreator, 'lumiere-movies') ), number_format_i18n($nbtotalcreator) );
			$output .= ':</span>&nbsp;';

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomycreator'] == true ) ) { 

				for ($i = 0; $i < $nbtotalcreator; $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'creator', esc_attr($creator[$i]["name"]), '', 'one');
					if ( $i < $nbtotalcreator - 1 )
						$output .= ", ";

				}

			} else { 

				for ($i = 0; $i < $nbtotalcreator; $i++) {

					// if "Remove all links" option is not selected 
					if  ($imdb_admin_values['imdblinkingkill'] == false ) { 

						// highslide popup
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { 
							$output .= '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . $creator[$i]["imdb"] . '" title="' . esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . $creator[$i]["name"] . '</a>';

						// classic popup
						} else { 

							$output .= '<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="' . $creator[$i]["imdb"] . '" title="' . esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . $creator[$i]["name"] . '</a>';
							$output .= sanitize_text_field( $creator[$i]["name"] )."</a>";
							} 
					// if "Remove all links" option is selected 

						if ( $i < $nbtotalcreator - 1 ) $output .= ', ';

					} else { 

						$output .= sanitize_text_field( $creator[$i]["name"] );
						if ( $i < $nbtotalcreator - 1 ) $output .= ', ';
					}  
				} 
				
			} 

		}

		return $output;

	}


	/* Display the producer
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_producer($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$producer = $movie->producer(); 
		$nbproducer = empty($imdb_widget_values['imdbwidgetproducernumber']) ? $nbproducer =  "1" : $nbproducer =  intval( $imdb_widget_values['imdbwidgetproducernumber'] );
		$nbtotalproducer = intval( count($producer) );

		if (!empty($producer)) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Producer', 'Producers', $nbtotalproducer, 'lumiere-movies') ), number_format_i18n($nbtotalproducer) );

			$output .= ':</span>';

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomyproducer'] == true ) ) { 

				for ($i = 0; ($i < $nbtotalproducer) && ($i < $nbproducer); $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'producer', esc_attr($producer[$i]["name"]), esc_attr($producer[$i]["role"]), 'two');

				}

			// no taxonomy
			} else { 

				for ($i = 0; ($i < $nbtotalproducer) && ($i < $nbproducer); $i++) {

					$output .= "\n\t\t\t\t". '<div align="center" class="lumiere_container">';
					$output .= "\n\t\t\t\t\t". '<div class="lumiere_align_left lumiere_flex_auto">';

					// if "Remove all links" option is not selected 
					if  ($imdb_admin_values['imdblinkingkill'] == false ) { 

						// highslide popup
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { 

							$output .= "\n\t\t\t\t\t". '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $producer[$i]["imdb"] ) . '" title="' . esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . esc_attr( $producer[$i]["name"] ) . '</a>';

						} else {  // classic popup

							$output .= "\n\t\t\t\t\t". '<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="' . esc_attr( $producer[$i]["imdb"] ) .'" title="' . esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . esc_attr( $producer[$i]["name"] ) . '</a>';

						} 

					// if "Remove all links" option is selected 
					} else { 

						$output .= esc_attr( $producer[$i]["name"] );

					} 
					$output .= "\n\t\t\t\t\t". '</div>';
					$output .= "\n\t\t\t\t\t". '<div align="right">';

						if (!empty($producer[$i]["role"] ) )
							$output .= esc_attr( $producer[$i]["role"] ); 
						else
							$output .= "&nbsp;";

					$output .= "\n\t\t\t\t". '</div>';
					$output .= "\n\t\t\t". '</div>';

				} // endfor 
				
			} 

		}

		return $output;
	}



	/* Display the writer
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_writer($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$writer = $movie->writing(); 
		$nbtotalwriters = intval( count($writer) );

		if (!empty($writer)) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Writer', 'Writers', $nbtotalwriters, 'lumiere-movies') ), number_format_i18n($nbtotalwriters) );
			$output .= ':</span>';

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomywriter'] == true ) ) { 

				for ($i = 0; $i < $nbtotalwriters; $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'writer', esc_attr($writer[$i]["name"]), esc_attr($writer[$i]["role"]), 'two');

				}

			} else { 

				for ($i = 0; $i < $nbtotalwriters; $i++) {

					$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
					$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

					// if "Remove all links" option is not selected 
					if  ($imdb_admin_values['imdblinkingkill'] == false ) { 

						// highslide popup
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) {

							$output .= '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $writer[$i]["imdb"] ) . '" title="' . esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . sanitize_text_field( $writer[$i]["name"] ) . '</a>';

						// classic popup
						} else {

							$output .=  '<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="' . esc_attr( $writer[$i]["imdb"] ) . '" title="' . esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . sanitize_text_field( $writer[$i]["name"] ) . '</a>';

						} 

					// if "Remove all links" option is selected 
					} else { 

						$output .= sanitize_text_field( $writer[$i]["name"] );

					} 
						$output .= "\n\t\t\t\t" . '</div>';
						$output .= "\n\t\t\t\t" . '<div align="right">';

								if (!empty($writer[$i]["role"] ) )
									$output .= sanitize_text_field( $writer[$i]["role"] ); 
								else
									$output .= "&nbsp;";

						$output .= "\n\t\t\t\t" . '</div>';
						$output .= "\n\t\t\t" . '</div>';
				} // endfor 
				
			} 

		}

		return $output;
	}


	/* Display the actor
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_actor($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$cast = $movie->cast(); 
		$nbactors = empty($imdb_widget_values['imdbwidgetactornumber']) ? $nbactors =  "1" : $nbactors =  intval( $imdb_widget_values['imdbwidgetactornumber'] );
		$nbtotalactors = intval( count($cast) );

		if (!empty($cast)) { 

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Actor', 'Actors', $nbtotalactors, 'lumiere-movies') ), number_format_i18n($nbtotalactors) );
			$output .= ':</span>';

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomyactor'] == true ) ) {

				for ($i = 0; ($i < $nbtotalactors) && ($i < $nbactors); $i++) {

					$output .= $this->lumiere_make_display_taxonomy( 'actor', esc_attr($cast[$i]["name"]), esc_attr($cast[$i]["role"]), 'two');

				}

			} else { 

				for ($i = 0; $i < $nbactors && ($i < $nbtotalactors); $i++) { 

					$output .= "\n\t\t\t\t". '<div align="center" class="lumiere_container">';
					$output .= "\n\t\t\t\t\t". '<div class="lumiere_align_left lumiere_flex_auto">';

					// if "Remove all links" option is not selected 
					if  ($imdb_admin_values['imdblinkingkill'] == false ) { 

						// highslide popup
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { 
							$output .= "\n\t\t\t\t\t". '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $cast[$i]["imdb"] ) . '" title="'. esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . esc_attr( $cast[$i]["name"] ) . '</a>';

						// classic popup 
						} else {  
						
							$output .= "\n\t\t\t\t\t". '<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="' . esc_attr( $cast[$i]["imdb"] ) . '" title="' . esc_html__('open a new window with IMDb informations', 'lumiere-movies') . esc_attr( $cast[$i]["name"] ) . '</a>';

						} 

					} else { // if "Remove all links" option is selected 

						$output .= esc_attr( $cast[$i]["name"] );

					} 

					$output .=  '</div>';
					$output .= "\n\t\t\t\t\t". '<div class="lumiere_align_right lumiere_flex_auto">';
					$output .= esc_attr( preg_replace('/\n/', "", $cast[$i]["role"]) ); # remove the <br> which break the layout
					$output .= '</div>';
					$output .=  "\n\t\t\t\t". '</div>';

				} // endfor 
				
			} 

		} 

		return $output;
	}

	/* Display the actor, simplified way : only actor's names
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_actor_short($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$cast = $movie->cast(); 
		$nbactors = empty($imdb_widget_values['imdbwidgetactornumber']) ? $nbactors =  "1" : $nbactors =  intval( $imdb_widget_values['imdbwidgetactornumber'] );
		$nbtotalactors = intval( count($cast) );

		if (!empty($cast)) { 

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Actor', 'Actors', $nbtotalactors, 'lumiere-movies') ), number_format_i18n($nbtotalactors) );
			$output .= ':</span>';

			if ( ($imdb_admin_values['imdbtaxonomy'] == true ) && ($imdb_widget_values['imdbtaxonomyactor'] == true ) ) {

				for ($i = 0; ($i < $nbactors) && ($i < $nbtotalactors); $i++) { 

					// add taxonomy terms to posts' terms
					if (null !==(get_the_ID()))
						wp_set_post_terms(get_the_ID(), sanitize_text_field( $cast[$i]["name"]), $imdb_admin_values['imdburlstringtaxo'] . 'actor', false); 

					// display the text
					$output .= "\n\t\t\t" . '<span>';
					$output .= "\n\t\t\t\t<a class=\"linkincmovie\" href=\"" 
. esc_url( site_url() . '/' . $imdb_admin_values['imdburlstringtaxo'] . 'actor/' .$this->lumiere_make_taxonomy_link( $cast[$i]["name"] ) ) . '" title="' . esc_html__("Find similar taxonomy results", "lumiere-movies") . "\">";
					$output .= "\n\t\t\t\t" . esc_attr( $cast[$i]["name"] );
					$output .= "\n\t\t\t\t" . '</a>';
					$output .= "\n\t\t\t" . '</span>';
				}

			} else { 

				for ($i = 0; $i < $nbactors && ($i < $nbtotalactors); $i++) { 

					$output .= "\n\t\t\t\t". '<span>';

					// if "Remove all links" option is not selected 
					if  ($imdb_admin_values['imdblinkingkill'] == false ) { 

						// highslide popup
						if ($imdb_admin_values['imdbpopup_highslide'] == 1) { 
							$output .= '<a class="linkincmovie link-imdblt-highslidepeople highslide" data-highslidepeople="' . esc_attr( $cast[$i]["imdb"] ) . '" title="'. esc_html__('open a new window with IMDb informations', 'lumiere-movies') . '">' . esc_attr( $cast[$i]["name"] ) . '</a>';

						// classic popup 
						} else {  
						
							$output .= '<a class="linkincmovie link-imdblt-classicpeople highslide" data-classicpeople="' . esc_attr( $cast[$i]["imdb"] ) . '" title="' . esc_html__('open a new window with IMDb informations', 'lumiere-movies') . esc_attr( $cast[$i]["name"] ) . '</a>';

						} 

					} else { // if "Remove all links" option is selected 

						$output .= esc_attr( $cast[$i]["name"] );

					} 

					$output .=  '</span>';

				} // endfor 
				
			} 

		} 

		return $output;
	}


	/* Display the plot
	 *
	 * @param mandatory object $movie -> takes the value of imdb class 
	 */
	public function lumiere_movies_plot($movie=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$plot = $movie->plot(); 
		$nbplots = empty($imdb_widget_values['imdbwidgetplotnumber']) ? $nbplots =  "1" : $nbplots =  intval( $imdb_widget_values['imdbwidgetplotnumber'] );
		$nbtotalplots = intval( count($plot) );

		// tested if the array contains data; if not, doesn't go further
		if (!lumiere_is_multiArrayEmpty($plot)) { 

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= sprintf(esc_attr(_n('Plot', 'Plots', $nbtotalplots, 'lumiere-movies') ), number_format_i18n($nbtotalplots) );
			$output .= ':</span><br />';

			for ($i = 0; ( ($i < $nbtotalplots) && ($i < $nbplots) ); $i++) { 

				// if "Remove all links" option is not selected 
				if  ($imdb_admin_values['imdblinkingkill'] == false ) { 

					$output .= wp_kses_post( $plot[$i], self::allowed_html_for_escape_functions ) . "\n";
				} else {

					$output .= wp_kses_post( $this->lumiere_remove_link ($plot[$i]), self::allowed_html_for_escape_functions ) . "\n";
				} 

				if ( $i < ( ($i < ($nbtotalplots -1) ) && ($i < ($nbplots -1) ) ) ) { 
					$output .= "\n<hr>\n";
				} // add hr to every quote but the last					
			}

		}

		return $output;
	}


	/* Display the credit link
	 *
	 * @param mandatory (int) $midPremierResultat -> IMDb ID
	 */
	public function lumiere_movies_creditlink($midPremierResultat=NULL) {

		/* Vars */ 

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;

		$output = "";
		$midPremierResultat_sanitized = filter_var( $midPremierResultat, FILTER_SANITIZE_NUMBER_INT );

 		// if "Remove all links" option is not selected 
		if ( ($imdb_admin_values['imdblinkingkill'] == false ) && ($imdb_widget_values['imdbwidgetsource'] == true ) ) {

			$output .= "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
			$output .= esc_html__('Source', 'lumiere-movies');
			$output .= ':</span>';

			$output .= "\n\t\t\t\t" . '<img class="imdbelementSOURCE-picture" width="33" height="15" src="' . esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/imdb-link.png" ) . '" />';
			$output .= 	'<a class="link-incmovie-sourceimdb" title="'
					.esc_html__("Go to IMDb website for this movie", 'lumiere-movies').'" href="'
					. esc_url( 'https://www.imdb.com/title/tt' .$midPremierResultat_sanitized ) . '" >'
					.'&nbsp;&nbsp;' 
					. esc_html__("IMDb's page for this movie", 'lumiere-movies') . '</a>';

 		} 

		return $output;
	}

	/** Do taxonomy layouts and register taxonomy terms
	 **
	 ** @ param (string) mandatory $typeItem: the general category of the item, ie 'director', 'color'
	 ** @ param (string) mandatory $firstTitle: the name of the first string to display, ie "Stanley Kubrick"
	 ** @ param (string) optional $secondTitle: the name of a second string to display, utilised in $layout 'two', ie "director"
	 ** @ param (int) optional $layout: the type of the layout, either 'one' or 'two'
	 **
	 ** returns the text to be outputed
	 **/
	function lumiere_make_display_taxonomy( $typeItem, $firstTitle, $secondTitle=NULL, $layout = 'one') {

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;
		
		// ************** Vars and sanitization */
		$lang_term = 'en'; # language to register the term with, always English
		$output = "";
		$list_taxonomy_term = "";
		$layout = esc_attr($layout);
		$taxonomy_category = esc_attr($typeItem);
		$taxonomy_term = esc_attr( $firstTitle );
		$secondTitle = esc_attr($secondTitle);
		$taxonomy_url_string_first = esc_attr( $imdb_admin_values['imdburlstringtaxo'] );
		$taxonomy_category_full = $taxonomy_url_string_first . $taxonomy_category;


		// ************** Add taxonomy

		if (null !==(get_the_ID())) {

			// delete if exists, for debugging purposes
			# if ( $term_already = get_term_by('name', $taxonomy_term, $taxonomy_category_full ) )
			#	 wp_delete_term( $term_already->term_id, $taxonomy_category_full) ;

			if ( taxonomy_exists( $taxonomy_category_full ) ){

				// if the tag doesn't exist
				if ( ! $term = term_exists( $taxonomy_term, $taxonomy_category_full ) ) 
					// insert it and get its id
					$term = wp_insert_term($taxonomy_term, $taxonomy_category_full, array('lang' => $lang_term) );

				// Create a list of Lumière tags meant to be inserted to Lumière Taxonomy
				$list_taxonomy_term .= $taxonomy_term . ", " ;

			}
		}
		if ( $term && !is_wp_error( $term ) ) {

			// Link Lumière tags to Lumière Taxonomy
			wp_set_post_terms(get_the_ID(), $list_taxonomy_term, $taxonomy_category_full, true);  

			// Add Lumière tags to the current WordPress post, but we don't want it
			# wp_set_post_tags(get_the_ID(), $list_taxonomy_term, 'post_tag', true); 

			// Compatibility with Polylang WordPress plugin, add a language to the taxonomy term
			if ( function_exists('pll_set_term_language') ) 
				$this->lumiere_add_taxo_lang_to_polylang( $term['term_id'], $lang_term );

		}

		// ************** Return layout

		// layout=two: display the layout for double entry details, ie actors
		if ( $layout =='two') {

			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
			$output .= "\n\t\t\t\t\t<a class=\"linkincmovie\" href=\"" 
					. esc_url( site_url() . '/' . $taxonomy_category_full 
					. '/' . $this->lumiere_make_taxonomy_link( $taxonomy_term ) ) 
					. '" title="' . esc_html__("Find similar taxonomy results", "lumiere-movies") 
					. "\">";
			$output .= "\n\t\t\t\t\t" . $taxonomy_term;
			$output .= "\n\t\t\t\t\t" . '</a>';
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
			$output .= preg_replace('/\n/', "", esc_attr( $secondTitle ) ); # remove breaking space
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t" . '</div>';

		// layout=one: display the layout for all details separated by comas, ie keywords
		} elseif ( $layout == 'one') {

			$output .= '<a class="linkincmovie" '
					. 'href="' . site_url() . '/' 
					. $taxonomy_category_full . '/' 
					. $this->lumiere_make_taxonomy_link( $taxonomy_term ) . '" '
					. 'title="' . esc_html__('Find similar taxonomy results', 'lumiere-movies') . '">';
			$output .= $taxonomy_term;
			$output .= '</a>'; 

		}

		return $output;

	}


	/** Convert an imdb link to a highslide/classic popup link
	 ** 
	 ** @param string $convert Link to be converted into popup highslide link
	 **/

	function lumiere_convert_txtwithhtml_into_popup_people ($convert) {

		// Get main vars from the current class
		$imdb_admin_values = $this->imdb_admin_values;

		if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup
				$result = '<a class="link-imdblt-highslidepeople highslide" data-highslidepeople="' . "\${6}" . '" title="' . esc_html__("open a new window with IMDb informations", 'lumiere-movies') . '">';
		} else {						// classic popup
		    		$result = '<a class="link-imdblt-classicpeople" data-classicpeople="' . "\${6}" . '" title="' . esc_html__("open a new window with IMDb informations", 'lumiere-movies') . '">';
		}

		$convert = preg_replace("~(<a )((href=)(.+?))(nm)([[:alnum:]]*)\/((.+?)\">)~", $result, $convert);

		return $convert;
	}

	/** Count me function
	 ** allows movie total count (how many time a movie is called by plugin
	 ** probably can be replaced by in_array()...?
	 **/
	function lumiere_count_me($thema, &$count_me_siffer) {

		global $count_me_siffer, $test;
		$count_me_siffer++;
		$test[$count_me_siffer] = $thema;
		$ici=array_count_values($test);

		if ($ici[$thema] < 2) 
			return "nomore";

	}

	/** Remove an html link
	 ** 
	 ** @param mandatory string $text text to be cleaned from every html link
	 **/
	function lumiere_remove_link ($text) {

		$output = preg_replace("/<a(.*?)>/", "", $text);

		return $output;

	}

	/**
	 * Create an html link for taxonomy
	 */
	function lumiere_make_taxonomy_link ($taxonomy) {

		$taxonomy = preg_replace("/\s/", "-", $taxonomy);# replace space by hyphen
		$taxonomy = strtolower($taxonomy); # convert to small characters
		return $taxonomy;

	}

	/** Polylang WordPress Plugin Compatibility
	** Add a language to the taxonomy term in Polylang 
	** Perhaps needed to keep the rewrite path to ie /imdblt_director/name_of_director functional with Polylang
	**
	** @param mandatory string $term_id -> id of the taxonomy term, usually got after taxonomy term insertition
	** @param optional string $lang -> language of the taxonomy term utilised by Polylang
	 **/
	function lumiere_add_taxo_lang_to_polylang( $term_id, $lang = 'en' ) {

		return pll_set_term_language($term_id, $lang);

	}


} // end of class


/* Auto load the class
 * Conditions: not admin area, not already page of Lumière 
 * (as if it is the case the class is manually called from these pages whenever it's needed)
 */
if(!is_admin()){

	$config = new \Lumiere\Settings();
	$lumiere_list_urls  = $config->lumiere_list_all_pages;
	$utils = new \Lumiere\Utils();

	if (!$utils->lumiere_array_contains_term($lumiere_list_urls, $_SERVER['REQUEST_URI'])){

		$lumiere_movie = new LumiereMovies();

	}
}
?>
