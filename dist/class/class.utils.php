<?php

 #############################################################################
 # Lumière! Movies WordPress Plugin                                          #
 # written by Lost Highway                                                   #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #       			                                                	#
 #  Function : Utilities class             				     	#
 #											#
 #############################################################################

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
	function __construct () {

		// Start config class and get the vars
		if (class_exists("\Lumiere\Settings")) {

			$configclass = new \Lumiere\Settings();
			$this->configclass = $configclass;
			$this->imdb_admin_values = $configclass->get_imdb_admin_option();
			$this->imdb_widget_values = $configclass->get_imdb_widget_option();
			$this->imdb_cache_values = $configclass->get_imdb_widget_option();

			// Start logger class if debug is selected
			if ( (isset($this->imdb_admin_values['imdbdebug'])) && ($this->imdb_admin_values['imdbdebug'] == 1) ){
				// Start the logger
				$this->configclass->lumiere_start_logger('utils');
				$this->loggerclass = $this->configclass->loggerclass;

			} else {

				$this->loggerclass = NULL;
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
				lumiere_unlinkRecursive($dir.'/'.$obj, true);
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
		$config = new \Lumiere\Settings();

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
			. esc_url( admin_url() . "admin.php?page=imdblt_options&subsection=help&helpsub=support"). "\"> "
			. esc_html__( 'more', 'lumiere-movies') ."</a>.";

		$output .= "\t\t\t<br /><br /><div>\n\t\t\t\t<div> &copy; 2005-" . date("Y") . " <a href=\"" .  \Lumiere\Settings::IMDBABOUTENGLISH . '" target="_blank">Lost Highway</a>, <a href="' . \Lumiere\Settings::IMDBHOMEPAGE . '" target="_blank">Lumière! wordpress plugin' . '</a>, version ' . $config->lumiere_version . "\n</div>". "\n</div>";

		$output .= "\t\t</div>\n";

		return $output;

	} 

	/**
	 * Text displayed when no result is found
	 * @param string $text: if no text provided, default 
	 * This text is logged if the debugin is activated
	 */
	public function lumiere_noresults_text($text='No result found for this query.'){ 

		if($this->loggerclass !== NULL) {
			$this->loggerclass->debug("[Lumiere] $text");
		}

		echo "\n".'<div class="noresult" align="center" style="font-size:16px;color:red;padding:15px;">'
			. $text
		 	. "</div>\n";

	} 

	/**
	 * Recursively test an multi-dimensionnal array
	 *
	 * @param string $multiarray Array name
	 * credits to http://in2.php.net/manual/fr/function.empty.php#94786
	 */
	function lumiere_is_multiArrayEmpty($mixed) {

	    if (is_array($mixed)) {

		 foreach ($mixed as $value) {

		     if (!lumiere_is_multiArrayEmpty($value)) {

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

			$files = array_merge($files, lumiere_glob_recursive($dir.'/'.basename($pattern), $flags));

		}

		return $files;
	}


	/**
	 * Function lumiere_notice
	 * Display a confirmation notice, such as "options saved"
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
	 * Function lumiere_debug_display
	 * Returns optionaly an array of the options passed
	 * 
	 * @param array optional $options array of Lumière options, do not display anything on screen if empty
	 * @param string optional $set_error set to 'no_var_dump' to avoid the call to var_dump function
	 * @param string optional $libxml_use set to 'libxml to call php function libxml_use_internal_errors(true)
	 */
	function lumiere_activate_debug($options = NULL, $set_error = NULL, $libxml_use = false) {

		// If the user can't manage options, exit
		if ( !current_user_can( 'manage_options' ) ) 
			return false;

		// Set high level of debug reporting
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
}
?>
