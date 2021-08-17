<?php

/**
 * Class of tools: general utilities available for any class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
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

	/* \Lumiere\Settings class
	 *
	 */
	private $configClass;

	/* \Lumiere\Settings vars
	 *
	 */
	private $imdb_admin_values, $imdb_widget_values, $imdb_cache_values;

public $debug_is_active;
	/* Class constructor
	 * 
	 */
	function __construct () {

		// Start config class and get the vars
		if (class_exists("\Lumiere\Settings")) {

			$this->configClass = new \Lumiere\Settings('utilsClass');
			$this->imdb_admin_values = $this->configClass->get_imdb_admin_option();
			$this->imdb_widget_values = $this->configClass->get_imdb_widget_option();
			$this->imdb_cache_values = $this->configClass->get_imdb_widget_option();


		} else {

			wp_die( esc_html__('Cannot start class utils, class Lumière Settings not found', 'lumiere-movies') );

		}

		$this->debug_is_active = false;
	}

	/**
	 * Recursively delete a directory
	 *
	 * @param string $dir Directory name
	 *
	 * @credits http://ch.php.net/manual/en/function.unlink.php#87045
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
	 *
	 * @credits http://ch2.php.net/manual/en/function.is-dir.php#85961 & myself
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

		$this->configClass->loggerclass->debug("[Lumiere] $text");

		echo "\n".'<div class="noresult" align="center" style="font-size:16px;color:red;padding:15px;">'
			. $text
		 	. "</div>\n";

	} 

	/**
	 * Recursively test an multi-dimensionnal array
	 *
	 * @param array mandatory $multiarray Array name
	 *
	 * @credits http://in2.php.net/manual/fr/function.empty.php#94786
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
	 *
	 * @param: $return = key-value to get simpler array of results
	 *
	 * @credit: https://magp.ie/2013/04/17/search-associative-array-with-wildcard-in-php/
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
	static function lumiere_name_htmlize ($link) {

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
	static function lumiere_formatBytes($size, $precision = 2) { 
		$base = log($size, 1024); 
		$suffixes = array('bytes', 'Kb', 'Mb', 'Gb', 'Tb');
		return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)]; 
	}

	/* Does a glob recursively
	 * Does not support flag GLOB_BRACE
	 *
	 * @credits https://www.php.net/manual/fr/function.glob.php#106595
	 */
	function lumiere_glob_recursive($pattern, $flags = 0) {

		$files = glob($pattern, $flags);

		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {

			$files = array_merge($files, $this->lumiere_glob_recursive($dir.'/'.basename($pattern), $flags));

		}

		return $files;
	}


	/*
	 * Function lumiere_notice
	 * Display a confirmation notice, such as "options saved"
	 *
	 * @param integer mandatory $code type of message
	 * @param string mandatory $msg text to display
	 */
	static function lumiere_notice($code, $msg) { 

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

	/* Function str_contains
	 * 
	 * Returns if a string is contained in a value
	 * Introduced in PHP 8
	 * here for compatibilty purpose
	 */
	static function str_contains($haystack, $needle) {

		return $needle !== '' && mb_strpos($haystack, $needle) !== false;

	}

	/* Returns if a term in an array is contained in a value
	 * 
	 * 
	 */
	function lumiere_array_contains_term($array_list, $term) {

		if ( preg_match('('.implode('|',$array_list).')', $term ) ) {

			return true;

		} else {

			return false;

		}
	}

	/* Activate debug on screen
	 * 
	 * @param (object) optional $options Lumière options, display nothing if empty
	 * @param (string) optional $set_error set to 'no_var_dump' to avoid the call to var_dump function
	 * @param (string) optional $libxml_use set to 'libxml to call php function libxml_use_internal_errors(true)
	 * @param (string) optional $get_screen set to 'screen to display WordPress get_current_screen()
	 *
	 * @since 3.5
	 * @param string optional $get_screen set to 'screen' to display wp function get_current_screen()
	 *
	 * @return optionaly an array of the options passed in $options
	 */
	function lumiere_activate_debug( array $options = null, string $set_error = null, string $libxml_use = null, string $get_screen = null) {

		// Set on true to show debug is active if called again.
		$this->debug_is_active = true;

		// If the user can't manage options and it's not a cron, exit.
		if ( ( ! current_user_can( 'manage_options' ) ) || ! 'DOING_CRON' && ! define( 'DOING_CRON' ) ) {
			return false;
		}

		// Set the highest level of debug reporting.
		error_reporting(E_ALL);
		ini_set("display_errors", 1);

		// avoid endless loops with imdbphp parsing errors.
		if ( ( isset($libxml_use ) ) && ( $libxml_use == 'libxml' ) ) {
			libxml_use_internal_errors(true);
		}

		if ( $set_error != "no_var_dump" ) {
			set_error_handler("var_dump");
		}

		if ( $get_screen == 'screen' ) {
			$currentScreen = get_current_screen();
			echo  '<div align="center"><strong>[WP current screen]</strong>';
			print_r( $currentScreen );
			echo '</div>';
		}

		// Exit if no Lumière option array requested to show
		if ( ( null !== $options ) && ! empty( $options ) && isset( $options ) ) {

			echo '<div><strong>[Lumière options]</strong><font size="-3"> ';
			print_r( $options );
			echo ' </font><strong>[/Lumière options]</strong></div>';

		}

	}

	/* Check if the block widget is active
	 * Use the current name by default
	 */
	static function lumiere_block_widget_isactive( $blockname = \Lumiere\LumiereWidget::block_widget_name ){
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
