<?php
/**
 * Popup for people: Independant page that displays star information inside a popup
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 */

namespace Lumiere;

class PopupPerson {

	/* Class \Lumiere\Utils
	 *
	 */
	private $utilsClass;

	/* Class \Lumiere\Settings
	 *
	 */
	private $configClass;

	/* Class \Monolog\Logger
	 *
	 */
	private $logger;

	/* Settings from class \Lumiere\Settings
	 *
	 */
	private $imdb_admin_values;

	function __construct(){

		//As an external file, need to include manually bootstrap
		require_once (plugin_dir_path( __DIR__ ).'bootstrap.php');

		// Start Lumière config class
		if (class_exists("\Lumiere\Settings")) {

			// Get the main vars
			$this->configClass = new \Lumiere\Settings('popupPerson');
			$this->imdb_admin_values = $this->configClass->imdb_admin_values;

			// Start the class Utils
			$this->utilsClass = new \Lumiere\Utils();

			// Start the debugging
			add_action( 'wp_head', [ $this, 'lumiere_maybe_start_debug' ], 0 );

			// Start the logger
			$this->configClass->lumiere_start_logger('popupMovie');
			$this->logger = $this->configClass->loggerclass;


		} else {

			wp_die( esc_html__('Cannot start popup person, class Lumière Settings not found', 'lumiere-movies') );

		}

		$this->layout();
	}

	/**
	 *  Wrapps the start of the logger
	 *  Allows to start later in the process
	 */
	public function lumiere_maybe_start_debug() {

		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( 1 == $this->imdb_admin_values['imdbdebug'] ) && ( $this->utilsClass->debug_is_active === false ) ) {

			$this->utilsClass->lumiere_activate_debug();

		}
	}

	private function layout() {

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

		} elseif (!empty ($mid_sanitized)) {

			// Since the class \Imdb\Person doesn't return Null but throws a fatal error if no result is found with imdbid
			try {

				// If result is null, throw an exception
				if (NULL == ($person = new \Imdb\Person($mid_sanitized, $this->configClass, $this->logger ))) {

					throw new Exception ($e);

				// Result is not null, create the var utilised later on
				} else {

					$person_name_sanitized = sanitize_text_field( $person->name() );	

				}
				
			// Catch the error throw (if any) and show the error, then exit
			} catch (Error|Exception $e) { 

				$this->utilsClass->lumiere_noresults_text($e->getMessage());
				exit();
			}

		} else {

			$this->utilsClass->lumiere_noresults_text();
		}

		?><!DOCTYPE html>
		<html>
		<head>
		<?php wp_head();?>

		</head>
		<body class="lumiere_body<?php if (isset($this->imdb_admin_values['imdbpopuptheme'])) echo ' lumiere_body_' . $this->imdb_admin_values['imdbpopuptheme'];?>">

				                                  <!-- top page menu -->

		<div class="lumiere_container lumiere_font_em_11 lumiere_titlemenu">
			<div class="lumiere_flex_auto">
			     <a class="historyback"><?php esc_html_e('Back', 'lumiere-movies'); ?></a>
			 </div>
			<div class="lumiere_flex_auto">
				<a class='linkpopup' href="<?php echo esc_url( $this->configClass->lumiere_urlpopupsperson . $mid_sanitized . "/?mid=". $mid_sanitized . "&info=" ); ?>" title='<?php echo $person_name_sanitized.": ".esc_html__('Summary', 'lumiere-movies'); ?>'><?php esc_html_e('Summary', 'lumiere-movies'); ?></a>
			 </div>
			<div class="lumiere_flex_auto">
				<a class='linkpopup' href="<?php echo esc_url( $this->configClass->lumiere_urlpopupsperson . $mid_sanitized . "/?mid=". $mid_sanitized . "&info=filmo" ); ?>" title='<?php echo $person_name_sanitized.": ".esc_html__('Full filmography', 'lumiere-movies'); ?>'><?php esc_html_e('Full filmography', 'lumiere-movies'); ?></a>
			 </div>
			<div class="lumiere_flex_auto">
				<a class='linkpopup' href="<?php echo esc_url( $this->configClass->lumiere_urlpopupsperson . $mid_sanitized . "/?mid=". $mid_sanitized . "&info=bio" ); ?>" title='<?php echo $person_name_sanitized.": ".esc_html__('Full biography', 'lumiere-movies'); ?>'><?php esc_html_e('Full biography', 'lumiere-movies'); ?></a>
			 </div>
			<div class="lumiere_flex_auto">
				<a class='linkpopup' href="<?php echo esc_url( $this->configClass->lumiere_urlpopupsperson . $mid_sanitized . "/?mid=". $mid_sanitized . "&info=misc" ); ?>" title='<?php echo $person_name_sanitized.": ".esc_html__('Misc', 'lumiere-movies'); ?>'><?php esc_html_e('Misc', 'lumiere-movies'); ?></a>
			 </div>
		</div>

				                                  <!-- Photo & identity -->
		<div class="lumiere_display_flex lumiere_font_em_11 lumiere_align_center">
			<div class="lumiere_flex_auto lumiere_width_eighty_perc">
				<div class="identity"><?php echo $person_name_sanitized; ?></div>
				<div class=""><font size="-1"><?php  

				# Birth
				$birthday = count($person->born() ) ? $person->born() : ""; 
				if ( (isset($birthday)) && (!empty($birthday)) ) {
					$birthday_day = (isset( $birthday["day"] ) ) ? intval($birthday["day"]) : "";
					$birthday_month = (isset( $birthday["month"] ) ) ? sanitize_text_field($birthday["month"]) : "";
					$birthday_year = (isset( $birthday["year"] ) ) ? intval($birthday["year"]) : "";

					echo "\n\t\t\t" . '<span class="imdbincluded-subtitle">'
						. esc_html__('Born on', 'lumiere-movies')."</span>"
						. $birthday_day . " " 
						. $birthday_month . " " 
						. $birthday_year ;
				}

				if ( (isset($birthday["place"])) && (!empty($birthday["place"])) ){ 
					echo ", ".esc_html__('in', 'lumiere-movies')." ".sanitize_text_field($birthday["place"]);
				}

				echo "\n\t\t" . '</font></div>';
				echo "\n\t\t" . '<div class=""><font size="-1">';

				# Death
				$death = (null !== $person->died() ) ? $person->died() : "";
				if ( (isset($death)) && (!empty($death)) ){

					echo "\n\t\t\t" . '<span class="imdbincluded-subtitle">' 
						. esc_html__('Died on', 'lumiere-movies')."</span>"
						.intval($death["day"])." "
						.sanitize_text_field($death["month"])." "
						.intval($death["year"]);

					if ( (isset($death["place"])) && (!empty($death["place"])) ) 
						echo ", ".esc_html__('in', 'lumiere-movies')." ".sanitize_text_field($death["place"]);

					if ( (isset($death["cause"])) && (!empty($death["cause"])) )
						echo ", ".esc_html__('cause', 'lumiere-movies')." ".sanitize_text_field($death["cause"]);
				}

				echo "\n\t\t" .'</font></div>';
				echo "\n\t\t" . '<div class="lumiere_padding_two lumiere_align_left"><font size="-1">';

				# Biography
				$bio = $person->bio();
				$nbtotalbio = count($bio);

				if ( (isset($bio)) && (!empty($bio)) ) {
					echo "\n\t\t\t" . '<span class="imdbincluded-subtitle">' 
						. esc_html__('Biography', 'lumiere-movies') 
						. '</span>';

			    		if ( $nbtotalbio < 2 ) $idx = 0; else $idx = 1;

					$bio_text = sanitize_text_field( $bio[$idx]["desc"] );
					$click_text = esc_html__('click to expand', 'lumiere-movies');
					$max_length = 200; # number of characters

					if( strlen( $bio_text ) > $max_length) {

						$str_one = substr( $bio_text, 0, $max_length);
						$str_two = substr( $bio_text, $max_length, strlen( $bio_text ) );
						$final_text = "\n\t\t\t" . $str_one
							. "\n\t\t\t" .'<span class="activatehidesection"><strong>&nbsp;(' . $click_text . ')</strong></span> '
							. "\n\t\t\t" .'<span class="hidesection">' 
							. "\n\t\t\t" . $str_two 
							. "\n\t\t\t" .'</span>';
						echo $final_text;

					} else {

						echo $bio_text;

					}

				}?>

				</font></div>
			</div> 
				                                  <!-- star photo -->
			<div class="lumiere_flex_auto lumiere_width_twenty_perc lumiere_padding_two"><?php 		

				$small_picture = $person->photo_localurl(false); // get small poster for cache
				$big_picture = $person->photo_localurl(true); // get big poster for cache
				$photo_url = isset($small_picture) ? $small_picture : $big_picture; // take the smaller first, the big if no small found
				if ( (isset($photo_url)) && (!empty($photo_url)) ){ 

					echo '<a class="highslide_pic_popup" href="'.esc_url($photo_url).'">';
					echo "\n\t\t" . '<img loading="eager" class="imdbincluded-picture" src="'
						.esc_url($photo_url)
						.'" alt="'
						.$person_name_sanitized.'" '; 

					// add width only if "Display only thumbnail" is on "no"
					if ($this->imdb_admin_values['imdbcoversize'] == FALSE)
						echo 'width="' . intval($this->imdb_admin_values['imdbcoversizewidth']) . 'px" />';

					echo '</a>'; 

				// No picture was downloaded, display "no picture"
				} else{
		 
					echo '<a class="highslide_pic_popup">';
					echo "\n\t\t" 
						. '<img loading="eager" class="imdbincluded-picture" src="'
						.esc_url($this->imdb_admin_values['imdbplugindirectory']."pics/no_pics.gif")
						.'" alt="'
						.esc_html__('no picture', 'lumiere-movies')
						.'" '; 

					// add width only if "Display only thumbnail" is on "no"
					if ($this->imdb_admin_values['imdbcoversize'] == FALSE)
						echo 'width="' . intval($this->imdb_admin_values['imdbcoversizewidth']) . 'px" />';

					echo '</a>'; 
			      } ?>

			</div> 
		</div> 

		<hr>


		<?php 
		//---------------------------------------------------------------------------summary 
		if ( (!isset($_GET['info'])) || (empty($_GET['info'])) ){      // display only when nothing is selected from the menu


			############## Director actor and producer filmography

			$list_all_movies_functions = array("director","actor");
			$nblimitcatmovies = 9;

			foreach ($list_all_movies_functions as $var) {
				$all_movies_functions = "movies_$var";
				$filmo = $person->$all_movies_functions();
				$catname = ucfirst($var);

				if ( (isset($filmo)) && (!empty($filmo)) ) {
					$nbfilmpercat=0;
					$nbtotalfilmo = count($filmo);
					$nbtotalfilms = $nbtotalfilmo-$nbfilmpercat;

					echo "\n\t\t\t\t\t\t\t" .' <!-- ' .  sanitize_text_field($catname) . ' filmography -->';
					echo "\n\t" . '<div align="center" class="lumiere_container">';
					echo "\n\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';

					echo "\n\t" . '<div>';
					echo "\n\t\t" . '<span class="imdbincluded-subtitle">' . sanitize_text_field($catname) . ' filmography' . ' </span>';

				  	for($i=0; $i < $nbtotalfilmo; $i++) {

						echo " <a class='linkpopup' href='".esc_url( $this->configClass->lumiere_urlpopupsfilms . '?mid=' . esc_html($filmo[$i]["mid"]) )."'>".sanitize_text_field( $filmo[$i]["name"] )."</a>";

						if (!empty($filmo[$i]["year"])) {
							echo " (";
							echo intval($filmo[$i]["year"]);
							echo ")";
						} 

						// if (empty($film["chname"])) { 		//-> the result sent is not empty, but a breakline instead
						if ($filmo[$i]["chname"]=="\n") {
							echo "";
						} else {
							if (empty($filmo["chid"])) { 
								if (!empty($filmo[$i]["chname"]))
									echo ' as <i>' . sanitize_text_field($filmo[$i]["chname"]) . '</i>';
							} else { 
								echo ' as <i><a class="linkpopup" href="' . esc_url("https://".$person->imdbsite."/character/ch".intval($filmo["chid"]) ) . '/">' . $filmo[$i]["chname"] . '</a></i>'; }

							// Display a "show more" after XX results
							if ($i == $nblimitcatmovies) 
								echo '&nbsp;<span class="activatehidesection"><font size="-1"><strong>(' 
									. esc_html__( 'see all', 'lumiere-movies' ) 
									. ')</strong></font></span> '
									. '<span class="hidesection">';

							if ($i == $nbtotalfilmo ) 
								echo '</span>';

						}

						$nbfilmpercat++;
					} //end for each filmo

					echo "\n\t" . '</div>';

				} // end if

			} // endforeach main

		}


		//---------------------------------------------------------------------------full filmography
		if ( (isset($_GET['info'] )) && ($_GET['info'] == 'filmo') ){ 


			############## All filmography

			/* vars */
			$list_all_movies_functions = array("director","actor", "producer", 'archive', 'crew', 'self', 'soundtrack',  'thanx', 'writer' ); # list of types of movies to query
			$nblimitmovies = 5; # max number of movies before breaking with "see all"

			foreach ($list_all_movies_functions as $var) {
				$all_movies_functions = "movies_$var";
				$filmo = $person->$all_movies_functions();
				$catname = ucfirst($var);

				if ( (isset($filmo)) && (!empty($filmo)) ) {
					$nbfilmpercat=0;
					$nbtotalfilmo = count($filmo);
					$nbtotalfilms = $nbtotalfilmo-$nbfilmpercat;

					echo "\n\t\t\t\t\t\t\t" .' <!-- ' .  sanitize_text_field($catname) . ' filmography -->';
					echo "\n" . '<div>';
					echo "\n\t" . '<span class="imdbincluded-subtitle">' . sanitize_text_field($catname) . ' filmography' . '</span> (' .$nbtotalfilms. ')';

				  	for($i=0; $i < $nbtotalfilmo; $i++) {

						// Display a "show more" after XX results
						if ($i == $nblimitmovies) 
							echo "\n\t" .'<span class="activatehidesection"><font size="-1"><strong>&nbsp;(' 
								. esc_html__( 'see all', 'lumiere-movies' ) 
								. ')</strong></font></span> '
								. "\n\t" .'<div class="hidesection">'; # start of hidden div

						// after XX results, show a table like list of results

						if ($i >= $nblimitmovies) {

							echo "\n\t\t" . '<div align="center" class="lumiere_container">';
							echo "\n\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
							echo "\n\t\t\t\t" ." <a class='linkpopup' href='"
								.esc_url( $this->configClass->lumiere_urlpopupsfilms 
								. '?mid=' . esc_html($filmo[$i]["mid"]) )
								."'>"
								.sanitize_text_field( $filmo[$i]["name"] )
								."</a>";
							if (!empty($filmo[$i]["year"])) {
								echo " (";
								echo intval($filmo[$i]["year"]);
								echo ")";
							} 
							echo "\n\t\t\t" . '</div>';
							echo "\n\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
									if ($filmo[$i]["chname"]=="\n") {
										echo "";
									} else {

										if (empty($filmo["chid"])) { 
											if (!empty($filmo[$i]["chname"]))
												echo ' as <i>' 
												. sanitize_text_field($filmo[$i]["chname"]) 
												. '</i>';
										} else { 
											echo ' as <i><a class="linkpopup" href="' 
											. esc_url("https://"
											.$person->imdbsite
											."/character/ch"
											.intval($filmo["chid"]) ) 
											. '/">' 
											. $filmo[$i]["chname"] 
											. '</a></i>'; 
										}

									}
							echo "\n\t\t\t</div>";
							echo "\n\t\t</div>";

							// Last cat movie, close the hidden div
							if ($i == $nbtotalfilmo -1 ) {
								echo "\n\t" .'</div>';

							}
							continue;
						}

						// before XX results, show a shortened list of results

						echo "\n\t" ." <a class='linkpopup' href='"
								.esc_url( $this->configClass->lumiere_urlpopupsfilms 
								. '?mid=' . esc_html($filmo[$i]["mid"]) )
								."'>".sanitize_text_field( $filmo[$i]["name"] )
								."</a>";

						if (!empty($filmo[$i]["year"])) {
							echo " (";
							echo intval($filmo[$i]["year"]);
							echo ")";
						} 

						// if (empty($film["chname"])) { 		//-> the result sent is not empty, but a breakline instead
						if ($filmo[$i]["chname"]=="\n") {
							echo "";
						} else {

							if (empty($filmo["chid"])) { 
								if (!empty($filmo[$i]["chname"]))
									echo ' as <i>' . sanitize_text_field($filmo[$i]["chname"]) . '</i>';
							} else { 
								echo ' as <i><a class="linkpopup" href="' 
									. esc_url("https://"
									.$person->imdbsite
									."/character/ch"
									.intval($filmo["chid"]) ) 
									. '/">' 
									. $filmo[$i]["chname"] 
									. '</a></i>'; 
							}

						}

						$nbfilmpercat++;

					} //end for each filmo

					echo "\n" . '</div>';

				} // end if

			} // endforeach main

		}

		// ------------------------------------------------------------------------------ partie bio 
		if ( (isset($_GET['info'] )) && ($_GET['info'] == 'bio') ){ 


			############## Biographical movies

			$biomovie = $person->pubmovies();
			$nbtotalbiomovie = count($biomovie);

			if ( (isset($biomovie)) && (!empty($biomovie)) ) { 

				echo "\n\t\t\t\t\t\t\t" .' <!-- Biographical movies -->';
				echo "\n" . '<div id="lumiere_popup_biomovies">';
				echo "\n\t" .'<span class="imdbincluded-subtitle">' . esc_html__('Biographical movies', 'lumiere-movies') . '</span>';

				for ($i=0; $i < $nbtotalbiomovie; ++$i) {
					$ii = $i+"1";
					echo "<a class='linkpopup' href='". esc_url( $this->configClass->lumiere_urlpopupsfilms ."?mid=".intval($biomovie[$i]["imdb"]) )."'>".$biomovie[$i]["name"]."</a>";
					if (!empty($biomovie[$i]["year"])) 
						echo " (".intval($biomovie[$i]["year"]).")";
				} 

				echo '</div>';

			} 

			############## Portrayed in

			$portrayedmovie = $person->pubportraits();
			$nbtotalportrayedmovie = count($portrayedmovie);

			if ( (isset($portrayedmovie)) && (!empty($portrayedmovie)) ) { 

				echo "\n\t\t\t\t\t\t\t" .' <!-- Portrayed in -->';
				echo "\n" . '<div id="lumiere_popup_biomovies">';
				echo "\n\t" .'<span class="imdbincluded-subtitle">' . esc_html__('Portrayed in', 'lumiere-movies') . '</span>';

				for ($i=0; $i < $nbtotalportrayedmovie; ++$i) {
					$ii = $i+"1";
					echo "<a class='linkpopup' href='". esc_url( $this->configClass->lumiere_urlpopupsfilms ."?mid=".intval($portrayedmovie[$i]["imdb"]) )."'>".$portrayedmovie[$i]["name"]."</a>";
					if (!empty($portrayedmovie[$i]["year"])) 
						echo " (".intval($portrayedmovie[$i]["year"]).") ";
				} 

				echo '</div>';

			}

			############## Interviews

			$interviews = $person->interviews();
			$nbtotalinterviews = count($interviews);

			if ( (isset($interviews)) && (!empty($interviews)) ) { 

				echo "\n\t\t\t\t\t\t\t" .' <!-- Interviews -->';
				echo "\n" . '<div id="lumiere_popup_biomovies">';
				echo "\n\t" .'<span class="imdbincluded-subtitle">' . esc_html__('Interviews', 'lumiere-movies') . '</span>';

				for ($i=0; $i < $nbtotalinterviews; $i++) {

					echo $interviews[$i]["name"] . ' ';

					if (!empty($interviews[$i]["full"])) 
						echo " (".intval($interviews[$i]["full"]) . ') ';

					if (!empty($interviews[$i]["details"])) 
						echo $interviews[$i]["details"] . '';

					if ($i < $nbtotalinterviews -1)
						echo ', ';

				} 

				echo '</div>';

			}

			############## Publicity printed

			$pubprints = $person->pubprints();
			$nbtotalpubprints = count($pubprints);
			$nblimitpubprints = 9;

			if ( (isset($pubprints)) && (!empty($pubprints)) ) { 

				echo "\n\t\t\t\t\t\t\t" .' <!-- Publicity printed -->';
				echo "\n" . '<div id="lumiere_popup_biomovies">';
				echo "\n\t" .'<span class="imdbincluded-subtitle">' 
					. esc_html__( 'Printed publicity', 'lumiere-movies' )
					. '</span>';

				for ($i=0; $i < $nbtotalpubprints; $i++) {

					// Display a "show more" after XX results
					if ($i == $nblimitpubprints) 
						echo "\n\t" .'<span class="activatehidesection"><font size="-1"><strong>&nbsp;(' 
							. esc_html__( 'see all', 'lumiere-movies' ) 
							. ')</strong></font></span> '
							. "\n\t" .'<span class="hidesection">';

					if (!empty($pubprints[$i]["author"])) {
						$text = preg_replace( '~/name/nm(\d{7})\/\"~', $this->configClass->lumiere_urlpopupsperson . "popup-imdb_person.php?mid=\\1\" class=\"linkpopup\"", $pubprints[$i]["author"] ); # transform imdb to local link
						echo "\n\t\t" .$text;
					}

					if (!empty($pubprints[$i]["title"])) 
						echo ' <i>' . esc_html( $pubprints[$i]["title"] ) . '</i> ';

					if (!empty($pubprints[$i]["year"])) 
						echo '(' . intval($pubprints[$i]["year"]) . ')';

					if (!empty($pubprints[$i]["details"])) 
						echo  esc_html( $pubprints[$i]["details"] ) . ' ';

					if ($i < $nbtotalpubprints -1)
						echo ', ';

					if ($i == $nbtotalpubprints -1)
						echo "\n\t" .'</span>';

				} 

				echo "\n" .'</div>';

			}

		} 

		// ------------------------------------------------------------------------------ misc part 
		if ( (isset($_GET['info'] )) && ($_GET['info'] == 'misc') ){ 


			############## Trivia

			$trivia = $person->trivia();
			$nbtotaltrivia = count($trivia);
			$nblimittrivia = 3; # max number of trivias before breaking with "see all"

			if ( (isset($trivia)) && (!empty($trivia)) ) { 

				echo "\n\t\t\t\t\t\t\t" .' <!-- Trivia -->';
				echo "\n" . '<div id="lumiere_popup_biomovies">';
				echo "\n\t" .'<span class="imdbincluded-subtitle">' . esc_html__('Trivia', 'lumiere-movies') . ' </span>' . '(' . $nbtotaltrivia . ') <br />';

				for ($i=0; $i < $nbtotaltrivia; $i++) {

					// Display a "show more" after 3 results
					if ($i == $nblimittrivia) 
						echo "\n\t\t" .'<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>(' 
							. esc_html__( 'see all', 'lumiere-movies' ) 
							. ')</strong></font></div>'
							. "\n\t\t" .'<div class="hidesection">';

					echo "\n\t\t\t" .'<div>';
					$text = preg_replace( '~https\:\/\/\www\.imdb\.com\/name/nm(\d{7})\?(.+?)\"~', $this->configClass->lumiere_urlpopupsperson . "popup-imdb_person.php?mid=\\1\" class=\"linkpopup\"", $trivia[$i] ); # transform imdb to local link
					$text = preg_replace( '~^\s\s\s\s\s\s\s(.*)<br \/>\s\s\s\s\s$~', "\\1", $text ); # clean output

					echo "\n\t\t\t\t" .' * ' . $text;
					echo "\n\t\t\t" .'</div>';

					if ($i == $nbtotaltrivia ) 
						echo "\n\t\t" .'</div>';


				} 

				echo "\n\t" . '</div>';
				echo "\n" . '</div>';

			}



			############## Nicknames

			$nickname = $person->nickname();
			$nbtotalnickname = count($nickname);

			if ( (isset($nickname)) && (!empty($nickname)) ) { 

				echo "\n\t\t\t\t\t\t\t" .' <!-- Nicknames -->';
				echo "\n" . '<div id="lumiere_popup_biomovies">';
				echo "\n\t" .'<span class="imdbincluded-subtitle">' . esc_html__('Nicknames', 'lumiere-movies') . ' </span>';

				for ($i=0; $i < $nbtotalnickname; $i++) {

					$txt = "";

		   			foreach ($nickname as $nick) {
						$txt = str_replace('<br>', ', ', $nick);
						echo sanitize_text_field( $txt );
		  			} 
				}

				echo "\n" . '</div>';

			}


			############## Personal quotes

			$quotes = $person->quotes();
			$nbtotalquotes = count($quotes);
			$nblimitquotes = 3;

			if ( (isset($quotes)) && (!empty($quotes)) ) { 

				echo "\n\t\t\t\t\t\t\t" .' <!-- Personal quotes -->';
				echo "\n" . '<div id="lumiere_popup_quotes">';
				echo "\n\t" .'<span class="imdbincluded-subtitle">' . esc_html__('Personal quotes', 'lumiere-movies') . ' </span> (' . $nbtotalquotes . ')';

				for ($i=0; $i < $nbtotalquotes; $i++) {

					// Display a "show more" after XX results
					if ($i == $nblimitquotes) 
						echo "\n\t\t" .'<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>(' 
							. esc_html__( 'see all', 'lumiere-movies' ) 
							. ')</strong></font></div>'
							. "\n\t\t" .'<div class="hidesection">';

					echo "\n\t\t\t" .'<div>';
					echo ' * ' . sanitize_text_field( $quotes[$i] );
					echo '</div>';

					if ($i == $nbtotalquotes -1 ) 
						echo "\n\t\t" .'</div>';

				}

				echo "\n" . '</div>';

			}



			############## Trademarks

			$trademark = $person->trademark();
			$nbtotaltrademark = count($trademark);
			$nblimittradmark = 5;

			if ( (isset($trademark)) && (!empty($trademark)) ) { 

				echo "\n\t\t\t\t\t\t\t" .' <!-- Trademarks -->';
				echo "\n" . '<div id="lumiere_popup_biomovies">';
				echo "\n\t" .'<span class="imdbincluded-subtitle">' . esc_html__('Trademarks', 'lumiere-movies') . ' </span>';

				for ($i=0; $i < $nbtotaltrademark; $i++) {

					// Display a "show more" after XX results
					if ($i == $nblimittradmark) 
						echo "\n\t\t" .'<div class="activatehidesection lumiere_align_center"><font size="-1"><strong>(' 
							. esc_html__( 'see all', 'lumiere-movies' ) 
							. ')</strong></font></div>'
							. "\n\t\t" .'<div class="hidesection">';

					echo "\n\t\t\t" .'<div>@ ';
					echo sanitize_text_field( $trademark[$i] );
					echo '</div>';

					if ($i == $nbtotaltrademark -1 ) 
						echo "\n\t\t" .'</div>';

				}

				echo "\n" . '</div>';

			}

		} 
		//------------------------------------------------------------------------------ end misc part ?>		   

		<br /><br /><?php 	

			wp_meta();
			wp_footer();

		
		?></body>
		</html><?php

		exit(); // quit the call of the page, to avoid double loading process 

	}

}

new \Lumiere\PopupPerson();



