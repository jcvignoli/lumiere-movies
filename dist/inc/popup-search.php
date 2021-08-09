<?php
/**
 * Popup for movie search: Independant page that displays movie search inside a popup
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 */

namespace Lumiere;

class PopupSearch {

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
	private $loggerClass;

	/* Settings from class \Lumiere\Settings
	 *
	 */
	private $imdb_admin_values;

	/* Settings from class \Lumiere\Settings
	 * To include the type of (movie, TVshow, Games) search
	 */
	private $typeSearch;


	function __construct(){

		// Start Lumière config class
		if (class_exists("\Lumiere\Settings")) {

			$this->configClass = new \Lumiere\Settings();
			$this->imdb_admin_values = $this->configClass->imdb_admin_values;

			// Get the type of search: movies, series, games
			$this->typeSearch = $this->configClass->lumiere_select_type_search();

			// Start class Utils
			$this->utilsClass = new \Lumiere\Utils();

			if ( (isset($this->configClass->imdb_admin_values['imdbdebug'])) && ($this->configClass->imdb_admin_values['imdbdebug'] == 1) && ( current_user_can( 'manage_options' ) ) ){

				// Activate debug
				$this->utilsClass->lumiere_activate_debug($this->imdb_admin_values, NULL, 'libxml'); # add libxml_use_internal_errors(true) which avoid endless loops with imdbphp parsing errors 

				// Start the logger
				$this->configClass->lumiere_start_logger('popupSearch');

				$this->loggerClass = $this->configClass->loggerclass;

			} else {

				$this->loggerClass = NULL;

			}

		} else {

			wp_die( 'Cannot start popup movies, class Lumière Settings not found' );

		}

		$this->layout();

	}

	function layout() {

		# Initialization of IMDBphp classes
		if (class_exists("\Imdb\TitleSearch")) {
			$search = new \Imdb\TitleSearch( $this->configClass, $this->loggerClass );
		}

		if (isset ($_GET["film"])){
			$film_sanitized = $this->utilsClass->lumiere_name_htmlize( $_GET["film"] ) ?? NULL;
			$film_sanitized_for_title = sanitize_text_field($_GET['film']);
		}

		$results = $search->search ($film_sanitized, $this->typeSearch );


		// Norecursive = yes, so do a search
		if ( (isset($_GET["norecursive"])) && ($_GET["norecursive"] == 'yes')) { 

			do_action('wp_loaded'); // execute wordpress first codes


		?><!DOCTYPE html>
		<html>
		<head>
		<?php wp_head();?>
		</head>
		<body class="lumiere_popup_search lumiere_body<?php if (isset($this->imdb_admin_values['imdbpopuptheme'])) echo ' lumiere_body_' . $this->imdb_admin_values['imdbpopuptheme'];?>">

		<div id="lumiere_loader" class="lumiere_loader_center"></div>

		<h1 align="center"><?php esc_html_e('Results related to', 'lumiere-movies'); echo " <i>" . $film_sanitized_for_title; ?></i></h1>

		<?php
		// if no movie was found at all
		if (empty($results) ){
			echo "<h2 align='center'><i>".esc_html__( "No result found.", 'lumiere-movies') . "</i></h2>";
			wp_footer(); 
		?></body></html><?php
			die();
		}?>

		<div class="lumiere_display_flex lumiere_align_center">
			<div class="lumiere_flex_auto lumiere_width_fifty_perc">
				<?php esc_html_e('Matching titles', 'lumiere-movies'); ?>
			</div>
			<div class="lumiere_flex_auto lumiere_width_fifty_perc">
				<?php esc_html_e('Director', 'lumiere-movies'); ?>
			</div>
		</div>

		<?php

			$current_line=0;
			foreach ($results as $res) {

				// Limit the number of results according to value set in admin		
				$current_line++;
				if ( $current_line > $this->imdb_admin_values['imdbmaxresults']){
					echo '</div>';
					echo '<div align="center"><i>' 
						. esc_html__('Maximum of results reached. You can increase it in admin options.', 'lumiere-movies') 
						. '</div>'; 
					wp_footer();
					echo '</i></body></html>';
					exit();
				}

				echo "\n<div class='lumiere_display_flex lumiere_align_center'>";
			
				// ---- movie part
				echo "\n\t<div class='lumiere_flex_auto lumiere_width_fifty_perc lumiere_align_left'>";

				echo "\n\t\t<a href=\"".esc_url( $this->configClass->lumiere_urlpopupsfilms 
					. $this->utilsClass->lumiere_name_htmlize( $res->title() ) 
					. "/?mid=".sanitize_text_field($res->imdbid()) )
					."&film=" . $this->utilsClass->lumiere_name_htmlize( $res->title() )
					."\" title=\"".esc_html__('more on', 'lumiere-movies')." "
					.sanitize_text_field( $res->title() )."\" >"
					.sanitize_text_field( $res->title() )
					." (".intval( $res->year() ).")"."</a> \n";

				echo "\n\t</div>";

				// ---- director part
				echo "\n\t<div class='lumiere_flex_auto lumiere_width_fifty_perc lumiere_align_right'>";

				$realisateur = $res->director();
				if ( (isset($realisateur['0']['name'])) && (! is_null ($realisateur['0']['name'])) ){

					echo "\n\t\t<a class='link-imdb2' href=\""
						.esc_url( $this->configClass->lumiere_urlpopupsperson 
						. sanitize_text_field($realisateur['0']["imdb"]) 
						. "/?mid=".sanitize_text_field($realisateur['0']["imdb"]) )
						. "\" title=\"".esc_html__('more on', 'lumiere-movies')
						." ".sanitize_text_field( $realisateur['0']['name'] )
						."\" >".sanitize_text_field( $realisateur['0']['name'] )
						."</a>";

				} else {

					echo "\n\t\t<i>" . esc_html__('No director found.', 'lumiere-movies') . '</i>';

				}

				echo "\n\t</div>";

				echo "\n</div>";

			} // end foreach  ?> 

		</div>
		<?php
		wp_footer(); 
		?>
		</body>
		</html>
		<?php
		exit(); // quit the call of the page, to avoid double loading process 


		// No "Norecursive" provided, so search for the first result provided in "?film="
		} else { 

			if ($results[0]) { // test to display the movie even if it's a unique result (if not, PHP error message)
				$nbarrayresult = "0"; // if unique result, data goes in array "0"
			} else {
				$nbarrayresult = "1"; // if multiple results, first movie goes in array "1" 
			}	
			$midPremierResultat = $results[$nbarrayresult]->imdbid() ?? NULL;
			if (isset($_GET['mid']))
				$_GET['mid'] = $midPremierResultat; //"mid" will be transmitted to next include

			require_once ( plugin_dir_path( __DIR__ ) . "/" . \Lumiere\Settings::popup_movie_url );
		}

	}

}

$PopupSearch = new \Lumiere\PopupSearch();


