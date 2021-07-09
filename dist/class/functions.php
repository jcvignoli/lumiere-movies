<?php

/* General vars */

$allowed_html_for_esc_html_functions = [
    'a'      => [
        'href'  => [],
        'title' => [],
    ],
];

/**
 * Recursively delete a directory
 *
 * @param string $dir Directory name
 * credits to http://ch.php.net/manual/en/function.unlink.php#87045
 */
if ( ! function_exists('lumiere_unlinkRecursive')){
	function lumiere_unlinkRecursive($dir){
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
}
/**
 * Recursively scan a directory
 *
 * @param string $dir Directory name
 * @param string $filesbydefault it's the count of files contained in folder and not taken into account for the count
 * credits to http://ch2.php.net/manual/en/function.is-dir.php#85961 & myself
 */

if ( ! function_exists('lumiere_isEmptyDir')){
	function lumiere_isEmptyDir($dir, $filesbydefault= "3"){	
		return (($files = @scandir($dir)) && count($files) <= $filesbydefault);
	}
} 


/**
 * Sanitize an array
 * 
 */

if ( ! function_exists('lumiere_recursive_sanitize_text_field')){
	function lumiere_recursive_sanitize_text_field($array) {
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
}

/**
 * Personal signature
 *
 */

if ( ! function_exists('lumiere_admin_signature')){
	function lumiere_admin_signature(){
		global $allowed_html_for_esc_html_functions;

		$output = "\t\t<div class=\"soustitre\">\n";
		$output .= "\t\t\t".
			wp_kses( __( '<strong>Licensing Info:</strong> Under a GPL licence, "Lumiere Movies" is based on <a href="https://github.com/tboothman/imdbphp/" target="_blank">tboothman</a> classes. Nevertheless, a considerable amount of work was required to implement it in wordpress; check the support page for', 'lumiere-movies'), $allowed_html_for_esc_html_functions ). "<a href=\"" .
			esc_url( admin_url() . "admin.php?page=imdblt_options&subsection=help&helpsub=support"). "\"> ".
			esc_html__('more', 'lumiere-movies') ."</a>.";
		$output .= "\t\t\t<br /><br /><div>\n\t\t\t\t<div> &copy; 2005-" . date("Y") . " <a href=\"" .  IMDBABOUTENGLISH . '" target="_blank">Lost Highway</a>, <a href="' . IMDBHOMEPAGE . '" target="_blank">Lumière! wordpress plugin' . '</a>, version ' . LUMIERE_VERSION . "\n</div>". "\n</div>";
		$output .= "\t\t</div>\n";

		return $output;

	} 
}

/**
 * Text displayed when no result is found
 *
 */
if ( ! function_exists('lumiere_noresults_text')){
	function lumiere_noresults_text(){ 
		echo "<br />";
		echo "<div class='noresult'>".esc_html_e('No result found for this query.', 'lumiere-movies')."</div>";
		echo "<br />";
	} 
}

/**
 * Recursively test an multi-dimensionnal array
 *
 * @param string $multiarray Array name
 * credits to http://in2.php.net/manual/fr/function.empty.php#94786
 */

if ( ! function_exists('lumiere_is_multiArrayEmpty')){
	function lumiere_is_multiArrayEmpty($mixed) {
	    if (is_array($mixed)) {
		 foreach ($mixed as $value) {
		     if (!lumiere_is_multiArrayEmpty($value)) {
		         return false;
		     }
		 }
	    }
	    elseif (!empty($mixed)) {
		 return false;
	    }
	    return true;
	} 
}

/* Function lumiere_array_key_exists_wildcard
 * Search with a wildcard in $keys of an array
 * @param: $return = key-value to get simpler array of results
 * https://magp.ie/2013/04/17/search-associative-array-with-wildcard-in-php/
 */
if ( ! function_exists('lumiere_array_key_exists_wildcard')){
	function lumiere_array_key_exists_wildcard ( $array, $search, $return = '' ) {
	    $search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );
	    $result = preg_grep( '/^' . $search . '$/i', array_keys( $array ) );
	    if ( $return == 'key-value' )
		 return array_intersect_key( $array, array_flip( $result ) );
	    return $result;
	}
}

/**
 * HTMLizing function
 * transforms movie's name in a way to be able to be searchable (ie "ô" becomes "&ocirc;") 
 * ----> should use a wordpress dedicated function instead, like esc_url() ?
 */

if ( ! function_exists('lumiere_name_htmlize')){
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
}

/**
 * Function lumiere_formatBytes
 * Returns in a proper format a size
 * 
 */

if ( ! function_exists('lumiere_formatBytes')){
	function lumiere_formatBytes($size, $precision = 2) { 
		$base = log($size, 1024); 
		$suffixes = array('bytes', 'Kb', 'Mb', 'Gb', 'Tb');
		return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)]; 
	}
}
/**
 * Function lumiere_glob_recursive
 * Does a glob recursively
 * Does not support flag GLOB_BRACE
 * Credits go to https://www.php.net/manual/fr/function.glob.php#106595
 */

if ( ! function_exists('lumiere_glob_recursive')){
    function lumiere_glob_recursive($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
       
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, lumiere_glob_recursive($dir.'/'.basename($pattern), $flags));
        }
       
        return $files;
    }
}

/**
 * Function lumiere_notice
 * Display a confirmation notice, such as "options saved"
 */

if ( ! function_exists('lumiere_notice')){
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
}

/**
 * Function str_contains
 * Returns if a string is contained in a value
 * Introduced in PHP 8
 */

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

# 2021 07 04 function obsolete
/* lumiere_make_htaccess()
 * Create inc/.htaccess upon plugin activation
 * called in inc/options-general.php and lumiere-movies.php (upon activation)
 */
/*
if (!function_exists('lumiere_make_htaccess')) {
	function lumiere_make_htaccess(){

		$imdblt_blog_subdomain = site_url( '', 'relative' ) ?? ""; #ie: /subdirectory-if-exists/
		$imdblt_plugin_full_path = plugin_dir_path( __DIR__ ) ?? wp_die( esc_html__("There was an error when generating the htaccess file.", 'lumiere-movies') ); # ie: /fullpathtoplugin/subdirectory-if-exists/wp-content/plugins/lumiere-movies/
		$imdblt_plugin_path = str_replace( $imdblt_blog_subdomain, "", wp_make_link_relative( plugin_dir_url( __DIR__ ))); #ie: /wp-content/plugins/lumiere-movies/
		$full_path_to_containing_htaccess_folder = $imdblt_plugin_full_path . 'inc/' ; # folder including htaccess
		$imdblt_htaccess_file = $full_path_to_containing_htaccess_folder  . ".htaccess" ?? wp_die( esc_html__("There was an error when generating the htaccess file.", 'lumiere-movies') ); # ie: /fullpathtoplugin/subdirectory-if-exists/wp-content/plugins/lumiere-movies/inc/.htaccess

		$imdblt_slug_path_movie = substr(LUMIERE_URLSTRINGFILMS, 1);
		$imdblt_slug_path_search = substr(LUMIERE_URLSTRING, 1);
		$imdblt_slug_path_person = substr(LUMIERE_URLSTRINGPERSON, 1);

		$imdblt_htaccess_file_txt = "";
		$imdblt_htaccess_file_txt = "### Begin Lumiere plugin\n";
		// .htaccess text, including Rewritebase with $blog_subdomain
		$imdblt_htaccess_file_txt .= "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase ".$imdblt_blog_subdomain."/"."\n\n";

		# Gutenberg search
		$imdblt_htaccess_file_txt .= "## gutenberg-search.php\nRewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/gutenberg-search.php [NC]"."\n"."RewriteRule ^.+$ wp-admin/lumiere/search/ [L,R,QSA]"."\n\n";

		# highslide
		$imdblt_htaccess_file_txt .= "## highslide_download.php\nRewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/highslide_download.php [NC]"."\n"."RewriteRule ^.+$ wp-admin/admin.php?page=imdblt_options [L,R,QSA]"."\n\n";

		## move_template_taxonomy.php
		$imdblt_htaccess_file_txt .= "## move_template_taxonomy.php\nRewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/move_template_taxonomy.php [NC]"."\n"."RewriteRule ^.+$ wp-admin/admin.php?page=imdblt_options&subsection=dataoption&widgetoption=taxo [L,R,QSA]"."\n\n";

/* We don't need it
		# popup-search
		$imdblt_htaccess_file_txt .= "## popup-search.php\nRewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-search.php\?film=([^\s]+)(&norecursive=[^\s]+)?"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_search."%1/ [L,R,QSA]"."\n\n";

		# popup-imdb-movie.php
		$imdblt_htaccess_file_txt .= "## popup-imdb_movie.php"."\n"."RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_movie.php\?film=([^\s]+) [NC]\nRewriteRule ^.+$ ".$imdblt_slug_path_movie."%1/ [L,R,QSA]"."\n\n";
		$imdblt_htaccess_file_txt .= "RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_movie.php\?mid=([^\s]+)&film=&info=([^\s]+)? [NC]"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_movie."%1/ [L,R,QSA]"."\n\n";
		$imdblt_htaccess_file_txt .= "RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_movie.php\?mid=?([^&#]+)&film=([^\s]+)?(&info=[^\s]*) [NC]"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_movie."%2/ [L,R,QSA]"."\n\n";
		$imdblt_htaccess_file_txt .= "RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_movie.php\?mid=([^\s]+) [NC]"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_movie."%1/ [L,R,QSA]"."\n\n";

		# popup-imdb_person.php
		$imdblt_htaccess_file_txt .= "## popup-imdb_person.php"."\n"."RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_person.php\?mid=([^&#]+)&(film=[^\s]+)(&info=[^\s]+)? [NC]"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_person."%1/ [L,R,QSA]"."\n\n"; 
		$imdblt_htaccess_file_txt .= "RewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/popup-imdb_person.php\?mid=([^\s]+) [NC]"."\n"."RewriteRule ^.+$ ".$imdblt_slug_path_person."%1/ [L,R,QSA]"."\n\n";
//

		$imdblt_htaccess_file_txt .= "</IfModule>\n";
		$imdblt_htaccess_file_txt .= "### End Lumiere plugin\n\n";


		// Is the folder including htaccess writable? Check if it is chmod 777
		if ( substr(sprintf('%o', fileperms( $full_path_to_containing_htaccess_folder )), -3) != "777" )
			// If we can't change permissions to chmod 777 for writing htaccess file, exit
			if (!chmod( $full_path_to_containing_htaccess_folder, 0777 ))
				return false;

		// write the .htaccess file if it can be written and close
		// display confirmation message for general options
		if ( (touch($imdblt_htaccess_file)) && ( file_put_contents( $imdblt_htaccess_file, $imdblt_htaccess_file_txt)) ) {

			return true;

		} else { 
		
			return false;
		}
	}
}*/

/**
 * Function lumiere_debug_display
 * Returns a debug
 * 
 * @param options the array of the passed Lumière options
 * @param set_error set to 'no_var_dump' to avoid the call to var_dump function (usefull for options-cache.php)
 * @param libxml_use set to 'libxml to call php function libxml_use_internal_errors(true)
 * @param imdbphpclass pass the class so we can call the debug function
 */

if (!function_exists('lumiere_debug_display')) {
	function lumiere_debug_display($options = "no array of options found", $set_error = NULL, $libxml_use = false, $imdbphpclass = false) {

		global $imdb_admin_values;

		// Debug function from imdbphp libraries
		if (isset($imdbphpclass)) {
			$imdbphpclass->debug = $imdb_admin_values['imdbdebug'] ?? NULL;
		}

		echo '<div><strong>[Lumière options]</strong><font size="-3"> ';

		print_r($options);
		error_reporting(E_ALL);
		ini_set("display_errors", 1);

		if ( $set_error != "no_var_dump" )
			set_error_handler("var_dump"); 

		echo ' </font><strong>[/Lumière options]</strong></div>';

		if ( (isset($libxml_use)) && ($libxml_use == "libxml") )
			libxml_use_internal_errors(true); // avoid endless loops with imdbphp parsing errors 

	}
}

?>
