<?php
/**
 * IMDbPHP search: Display search results related to a movie to get their IMDbID
 *
 * @author		Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright		2021, Lost Highway
 *
 * @version		1.0
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
}

class Search {

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

	/* Settings from class \Lumiere\Settings
	 * To include the type of (movie, TVshow, Games) search
	 */
	private $typeSearch;


	/* Constructor
	 *
	 */
	function __construct(){

		//As an external file, need to include manually bootstrap
		require_once (plugin_dir_path( __DIR__ ).'bootstrap.php');

		if (class_exists("\Lumiere\Settings")) {

			// Start Settings class
			$this->configClass = new \Lumiere\Settings('gutenbergSearch');
			$this->imdb_admin_values = $this->configClass->imdb_admin_values;

			// Get the type of search: movies, series, games
			$this->typeSearch = $this->configClass->lumiere_select_type_search();

			// Start Utils Class
			$this->utilsClass = new \Lumiere\Utils();

			// Start debug mode
			if ( (isset($this->imdb_admin_values['imdbdebug'])) && ($this->imdb_admin_values['imdbdebug'] == 1) && ( current_user_can( 'manage_options' )) ){

				// Activate the debug
				$this->utilsClass->lumiere_activate_debug(NULL, NULL, 'libxml', $this->configClass); # add libxml_use_internal_errors(true) which avoid endless loops with imdbphp parsing errors 

			} 

			// Start the logger
			$this->configClass->lumiere_start_logger('gutenbergSearch');
			$this->logger = $this->configClass->loggerclass;

		}

		$this->layout();

	}



	/* Display layout
	 *
	 */
	function layout() {

?><!DOCTYPE html>
<html>
<head>
<?php wp_head();?>
</head>
<body id="gutenberg_search">
<?php
		if ( (isset ($_GET["moviesearched"])) && (!empty ($_GET["moviesearched"])) ){

			# Initialization of IMDBphp
			$search = new \Imdb\TitleSearch($this->configClass, $this->logger );

			$search_sanitized = isset($_GET["moviesearched"]) ? sanitize_text_field( $_GET["moviesearched"] ) : NULL;

			$this->logger->debug("[Lumiere][gutenbergSearch] Querying '$search_sanitized'");

			$results = $search->search ($search_sanitized, $this->typeSearch );

?>
<h1 class="searchmovie_title lumiere_italic"><?php esc_html_e('Results related to your query:', 'lumiere-movies'); ?> <span class="lumiere_gutenberg_results"><?php echo $search_sanitized; ?></span></h1>
<div class="lumiere_container">
	<div class="lumiere_container_flex50"><h2><?php esc_html_e('Titles results', 'lumiere-movies'); ?></h2></div>
	<div class="lumiere_container_flex50"><h2><?php esc_html_e('Identification number', 'lumiere-movies'); ?></h2></div>
</div>
<?php
		$limit_search = isset($this->imdb_admin_values['imdbmaxresults']) ? intval($this->imdb_admin_values['imdbmaxresults']) : 5;
		$i=1;
		foreach ($results as $res) {
			if ($i > $limit_search){
				$this->logger->debug("[Lumiere][gutenbergSearch] Limit of '$limit_search' results reached.");
				echo '<div class="lumiere_italic lumiere_padding_five lumiere_align_center">' 
					. esc_html__('Maximum of results reached. You can increase it in admin options.', 'lumiere-movies');
				echo '</div>';
				break;
			}

			echo "\n" . '<div class="lumiere_container lumiere_container_gutenberg_border">';
			
			// ---- movie name results
			echo "\n\t<div class='lumiere_container_flex50 lumiere_italic lumiere_gutenberg_results'>".esc_html( $res->title() )." (".intval( $res->year() ).")".'</div>';

			// ---- imdb id results
			echo "\n\t<div class='lumiere_container_flex50 lumiere_align_center lumiere_gutenberg_results'>";
			echo "\n\t\t<span class='lumiere_bold'>".esc_html__('IMDb ID:', 'lumiere-movies').'</span> ';
			echo "\n\t\t" . '<span class="lumiere_gutenberg_copy_class"'
					. ' id="imdbid_'.esc_html( $res->imdbid() ).'">'
					. esc_html( $res->imdbid() )
					.'</span>';

			echo "\n\t" . '</div>';
			echo "\n</div>";


			$i++;

		} // end foreach  

		echo '<div align="center"><a href="' 
			. esc_url( site_url( '', 'relative' ) . \Lumiere\Settings::gutenberg_search_url ) 
			. '">Do a new query</a></div>' ; 

		} else {

			//----------------------------------------------------- No data entered, show the search form 
			if (!isset ($_GET["film"]) ) {   

				echo "\n<div align='center'>";
				echo "\n\t" . '<h1 id="searchmovie_title">'.esc_html__('Search a movie IMDb ID', 'lumiere-movies').'</h1>';
				echo "\n\t".'<form action="" method="get" id="searchmovie">';
				echo "\n\t\t".'<input type="text" id="moviesearched" name="moviesearched">';

				// Nonce field deactivated, since it can be called from everywhere
				// wp_nonce_field('submit_gutenberg', 'submit_gutenberg'); 

				echo "\n\t\t".'<input type="submit" value="Search">';
				echo "\n\t" . '</form>';
				echo "\n" . '</div>';

			} else {

				wp_die( esc_html__("You are not allowed to call this page directly.", "lumiere-movies") );
			}
		}

?>

<script type='text/javascript' src='<?php echo esc_url( $this->configClass->lumiere_js_dir . 'lumiere_scripts_search.min.js?vers='); echo esc_html($this->configClass->lumiere_version); ?>' id='lumiere_scripts_search-js'></script>
</body>
</html><?php

		exit();

	}

}

if ( current_user_can( 'manage_options' )){
	new \Lumiere\Search();
} else {
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));
}
