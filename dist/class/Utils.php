<?php

/**
 * Class of tools: general utilities available for any class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) 
	wp_die('You can not call directly this page');


/**
 * Class of function tools
 *
 */

class Utils {

	/* Store the class of Lumière settings
	 * Usefull to start a new IMDbphp query
	 */
	private $configClass;

	/* Vars from Lumière settings
	 *
	 */
	private $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

	/* Store the class for logging using the Monolog library
	 *
	 */
	private $loggerClass;

	/** Class constructor
	 ** 
	 **/
	function __construct () {

		// Start config class and get the vars
		if (class_exists("\Lumiere\Settings")) {

			$configClass = new \Lumiere\Settings();
			$this->configClass = $configClass;
			$this->imdb_admin_values = $configClass->get_imdb_admin_option();
			$this->imdb_widget_values = $configClass->get_imdb_widget_option();
			$this->imdb_cache_values = $configClass->get_imdb_widget_option();

			// Start logger class if debug is selected
			if ( (isset($this->imdb_admin_values['imdbdebug'])) && ($this->imdb_admin_values['imdbdebug'] == 1) ){
				// Start the logger
				$this->configClass->lumiere_start_logger('utils');
				$this->loggerClass = $this->configClass->loggerclass;

			} else {

				$this->loggerClass = NULL;
			}

		} else {

			wp_die( esc_html__('Cannot start class utils, class Lumière Settings not found', 'lumiere-movies') );

		}

	}

	/**
	 * Recursively delete a directory
	 *
	 * @param string $dir Directory name
	 * credits to http://ch.php.net/manual/en/function.unlink.php#87045
	 */
	public function lumiere_unlinkRecursive($dir){
		if(!$dh = @opendir($dir)){
			return;
		}
		while (false !== ($obj = readdir($dh))) {
			if($obj == '.' || $obj == '..') {
				continue;
			}

			if (!@unlink($dir . '/' . $obj)){
				$this->lumiere_unlinkRecursive($dir.'/'.$obj, true);
			}
		}
		closedir($dh);
		return;
	} 

	/**
	 * Recursively scan a directory
	 *
	 * @param string $dir Directory name
	 * @param string $filesbydefault it's the count of files contained in folder and not taken into account for the count
	 * credits to http://ch2.php.net/manual/en/function.is-dir.php#85961 & myself
	 */
	public function lumiere_isEmptyDir($dir, $filesbydefault= "3"){	

		return (($files = @scandir($dir)) && count($files) <= $filesbydefault);

	}


	/**
	 * Sanitize an array
	 * 
	 */
	public function lumiere_recursive_sanitize_text_field($array) {
	    foreach ( $array as $key => &$value ) {
		 if ( is_array( $value ) ) {
		     $value = recursive_sanitize_text_field($value);
		 }
		 else {
		     $value = sanitize_text_field( $value );
		 }
	    }
	    return $array;
	}

	/**
	 * Personal signature for administration
	 *
	 */
	public function lumiere_admin_signature(){

		// Config settings
		$config = $this->configClass;

		// Authorise this html tags wp_kses()
		$allowed_html_for_esc_html_functions = [
		    'a'      => [
			 'href'  => [],
			 'title' => [],
		    ],
		];

		$output = "\t\t<div class=\"soustitre\">\n";

		$output .= "\t\t\t".
			/* translators: %1$s is replaced with an html link */
			wp_sprintf( wp_kses( __('<strong>Licensing Info:</strong> Under a GPL licence, "Lumiere Movies" is based on <a href="%1$s" target="_blank">tboothman</a> classes. Nevertheless, a considerable amount of work was required to implement it in wordpress; check the support page for', 'lumiere-movies'), $allowed_html_for_esc_html_functions ), \Lumiere\Settings::IMDBPHPGIT  ); 

		$output .= "<a href=\""
			. esc_url( admin_url() . "admin.php?page=lumiere_options&subsection=help&helpsub=support"). "\"> "
			. esc_html__( 'more', 'lumiere-movies') ."</a>.";

		$output .= "\t\t\t<br /><br /><div>\n\t\t\t\t<div> &copy; 2005-" . date("Y") . " <a href=\"" .  \Lumiere\Settings::IMDBABOUTENGLISH . '" target="_blank">Lost Highway</a>, <a href="' . \Lumiere\Settings::IMDBHOMEPAGE . '" target="_blank">Lumière! wordpress plugin' . '</a>, version ' . $config->lumiere_version . "\n</div>". "\n</div>";

		$output .= "\t\t</div>\n";

		return $output;

	} 

	/**
	 * Text displayed when no result is found
	 * This text is logged if the debug logging is activated
	 *
	 * @param string optional $text: text to display/log. if no text provided, default text is provided
	 */
	public function lumiere_noresults_text($text='No result found for this query.'){ 

		$this->configClass->lumiere_maybe_log('debug', "[Lumiere] $text");

		echo "\n".'<div class="noresult" align="center" style="font-size:16px;color:red;padding:15px;">'
			. $text
		 	. "</div>\n";

	} 

	/**
	 * Recursively test an multi-dimensionnal array
	 *
	 * @param array mandatory $multiarray Array name
	 * credits to http://in2.php.net/manual/fr/function.empty.php#94786
	 */
	function lumiere_is_multiArrayEmpty($mixed) {

	    if (is_array($mixed)) {

		 foreach ($mixed as $value) {

		     if (!$this->lumiere_is_multiArrayEmpty($value)) {

			  return false;

		     }
		 }

	    } elseif (!empty($mixed)) {

		 return false;

	    }

	    return true;
	} 

	/* Function lumiere_array_key_exists_wildcard
	 * Search with a wildcard in $keys of an array
	 * @param: $return = key-value to get simpler array of results
	 * https://magp.ie/2013/04/17/search-associative-array-with-wildcard-in-php/
	 */
	function lumiere_array_key_exists_wildcard ( $array, $search, $return = '' ) {

	    $search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );

	    $result = preg_grep( '/^' . $search . '$/i', array_keys( $array ) );

	    if ( $return == 'key-value' )
		 return array_intersect_key( $array, array_flip( $result ) );

	    return $result;

	}

	/**
	 * HTMLizing function
	 * transforms movie's name in a way to be able to be searchable (ie "ô" becomes "&ocirc;") 
	 * ----> should use a wordpress dedicated function instead, like esc_url() ?
	 */
	function lumiere_name_htmlize ($link) {

	    // a. quotes escape
	    $lienhtmlize = addslashes($link);      

	    // b.converts db to html -> no more needed
	    //$lienhtmlize = htmlentities($lienhtmlize,ENT_NOQUOTES,"UTF-8");

	    // c. regular expression to convert all accents; weird function...
	    $lienhtmlize = preg_replace('/&(?!#[0-9]+;)/s', '&amp;', $lienhtmlize);

	    // d. turns spaces to "+", which allows titles including several words
	    $lienhtmlize = str_replace(array(' '), array('+'), $lienhtmlize);
	    
	    return $lienhtmlize; 
	}

	/**
	 * Function lumiere_formatBytes
	 * Returns in a proper format a size
	 * 
	 * @param integer mandatory $size the unformatted number of the size
	 * @param integer optional $precision how many numbers after comma, two by default
	 */
	function lumiere_formatBytes($size, $precision = 2) { 
		$base = log($size, 1024); 
		$suffixes = array('bytes', 'Kb', 'Mb', 'Gb', 'Tb');
		return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)]; 
	}

	/**
	 * Function lumiere_glob_recursive
	 * Does a glob recursively
	 * Does not support flag GLOB_BRACE
	 * Credits go to https://www.php.net/manual/fr/function.glob.php#106595
	 */
	function lumiere_glob_recursive($pattern, $flags = 0) {

		$files = glob($pattern, $flags);

		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {

			$files = array_merge($files, $this->lumiere_glob_recursive($dir.'/'.basename($pattern), $flags));

		}

		return $files;
	}


	/**
	 * Function lumiere_notice
	 * Display a confirmation notice, such as "options saved"
	 *
	 * @param integer mandatory $code type of message
	 * @param string mandatory $msg text to display
	 */
	function lumiere_notice($code, $msg) { 

		switch ($code) {
			default:
			case 1: // success notice, green
				return '<div class="notice notice-success"><p>'. $msg .'</p></div>';
				break;
			case 2: // info notice, blue
				return '<div class="notice notice-info"><p>'. $msg .'</p></div>';
				break;
			case 3: // simple error, red
				return '<div class="notice notice-error"><p>'. $msg .'</p></div>';
				break;
			case 4: // warning error, yellow
				return '<div "notice notice-warning">'. $msg .'</div>';
				break;
		}
		return false;
	}

	/**
	 * Function str_contains
	 * Returns if a string is contained in a value
	 * Introduced in PHP 8
	 */
	function str_contains($haystack, $needle) {

		return $needle !== '' && mb_strpos($haystack, $needle) !== false;

	}

	/**
	 * Function lumiere_array_contains_term
	 * Returns if a term in an array is contained in a value
	 * 
	 */
	function lumiere_array_contains_term($array_list, $term) {

		if ( preg_match('('.implode('|',$array_list).')', $term ) ) {

			return true;

		} else {

			return false;

		}
	}

	/**
	 * Function lumiere_activate_debug
	 * Returns optionaly an array of the options passed
	 * 
	 * @param object optional $options array of Lumière options, do not display anything on screen if empty
	 * @param string optional $set_error set to 'no_var_dump' to avoid the call to var_dump function
	 * @param string optional $libxml_use set to 'libxml to call php function libxml_use_internal_errors(true)
	 */
	function lumiere_activate_debug($options = NULL, $set_error = NULL, $libxml_use = false) {

		// If the user can't manage options, exit
		if ( !current_user_can( 'manage_options' ) ) 
			return false;

		// Set the highest level of debug reporting
		error_reporting(E_ALL);
		ini_set("display_errors", 1);

		if ( (isset($libxml_use)) && ($libxml_use == "libxml") )
			libxml_use_internal_errors(true); // avoid endless loops with imdbphp parsing errors 

		// Exit if no Lumière option array requested to show
		if ( (NULL == $options) || empty($options) || !isset($options) )
			return false;

		echo '<div><strong>[Lumière options]</strong><font size="-3"> ';

		if(NULL !== $options)
			print_r( $options );

		if ( $set_error != "no_var_dump" )
			set_error_handler("var_dump"); 

		echo ' </font><strong>[/Lumière options]</strong></div>';

	}

	/**
	 * Function checking if item/person template is missing or if a new one is available
	 * Returns a link to copy the template if true and a message explaining if missing/update the template
	 * 
	 * @param array mandatory $type type to search (actor, genre, etc)
	 */
	public function lumiere_check_taxo_template($type) {

		$imdb_admin_values = $this->imdb_admin_values;
		$output = "";

		// Get the type to build the links
		$lumiere_taxo_title = esc_html($type);

		// Files paths
		$lumiere_taxo_file_tocopy = in_array($lumiere_taxo_title, $this->configClass->array_people, true) ? $lumiere_taxo_file_tocopy = "taxonomy-imdblt_people.php" : $lumiere_taxo_file_tocopy = "taxonomy-imdblt_items.php";
		$lumiere_taxo_file_copied = "taxonomy-" . $this->configClass->imdb_admin_values['imdburlstringtaxo'] . $lumiere_taxo_title . ".php";
		$lumiere_current_theme_path = get_stylesheet_directory()."/";
		$lumiere_current_theme_path_file = $lumiere_current_theme_path . $lumiere_taxo_file_copied ;
		$lumiere_taxonomy_theme_path = $this->configClass->imdb_admin_values['imdbpluginpath'] . "theme/";
		$lumiere_taxonomy_theme_file = $lumiere_taxonomy_theme_path . $lumiere_taxo_file_tocopy;

		// Find the version
		$pattern="~Version: (.+)~i";
		# Copied version to the user theme folder
		if (file_exists($lumiere_current_theme_path_file)){

			$content = file_get_contents($lumiere_current_theme_path_file);

			if (preg_match($pattern, $content, $match)){

				$version_theme = $match[1];

			} else {

				$version_theme = "no_theme";

			}

		} else {

			$output .= "<br />";
			$output .= "<a href='" . esc_url( admin_url() . "admin.php?page=lumiere_options&subsection=dataoption&widgetoption=taxo&taxotype=" . $lumiere_taxo_title ) . "' " 
					."title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >"
					. "<img src='".esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-widget-copy-theme.png') . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />"
					. esc_html__("Copy template", 'lumiere-movies') . "</a>";

			$output .= '<div><font color="red">'
				. esc_html__("No $lumiere_taxo_title template found", 'lumiere-movies')
				. '</font></div>';

			return $output;
		}
		# Original version
		if (file_exists($lumiere_taxonomy_theme_file)) {

			$content = file_get_contents($lumiere_taxonomy_theme_file); 

			if (preg_match($pattern, $content, $match)){

				$version_origin = $match[1];

			} else {

				$version_theme = "no_origin";

			}

		} else {

			return false;
		}		

		// Return a message if there is a new version of the template
		if ($version_theme != $version_origin)  {

			$output .= "<br />";
			$output .= "<a href='" . esc_url( admin_url() . "admin.php?page=lumiere_options&subsection=dataoption&widgetoption=taxo&taxotype=" . $lumiere_taxo_title ) . "' " 
					."title='" . esc_html__("Copy a standard taxonomy template to your template folder to display this taxonomy.", 'lumiere-movies') . "' >"
					. "<img src='".esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-widget-copy-theme.png') . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />"
					. esc_html__("Copy template", 'lumiere-movies') . "</a>";

			$output .= '<div><font color="red">'
				. esc_html__("New $lumiere_taxo_title template version available", 'lumiere-movies')
				. '</font></div>';

			return $output;

		}

		return false;
	}


	/* Check if the block widget is active
	 * Use the current name by default
	 */
	function lumiere_block_widget_isactive( $blockname = \Lumiere\LumiereWidget::block_widget_name ){
	    $widget_blocks = get_option( 'widget_block' );
	    foreach( $widget_blocks as $widget_block ) {
		 if ( ! empty( $widget_block['content'] ) 
		      && has_block( $blockname, $widget_block['content'] ) 
		 ) {
		     return true;
		 }
	    }
	    return false;
	}
}
?>
