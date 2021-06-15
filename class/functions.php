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
 * Remove an html link
 * @param string $toremove Data wherefrom remove every html link
 */

if ( ! function_exists('lumiere_remove_link')){
	function lumiere_remove_link ($toremove) {
		$toremove = preg_replace("/<a(.*?)>/", "", $toremove);
		return $toremove;
	}
}

/**
 * Create an html link for taxonomy
 */

if ( ! function_exists('lumiere_make_taxonomy_link')){
	function lumiere_make_taxonomy_link ($taxonomy) {
		$taxonomy = preg_replace("/\s/", "-", $taxonomy);# replace space by hyphen
		$taxonomy = strtolower($taxonomy); # convert to small characters
		return $taxonomy;
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
 * Convert an imdb link to a highslide/classic popup link (called 
 * @param string $convert Link to convert into popup highslide link
 */

if ( ! function_exists('lumiere_convert_txtwithhtml_into_popup_people')){
	function lumiere_convert_txtwithhtml_into_popup_people ($convert) {
		global $imdb_admin_values;

		if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup
				$result = '<a class="link-imdblt-highslidepeople highslide" data-highslidepeople="' . "\${6}" . '" title="' . esc_html__("open a new window with IMDb informations", 'lumiere-movies') . '">';
		} else {						// classic popup
		    		$result = '<a class="link-imdblt-classicpeople" data-classicpeople="' . "\${6}" . '" title="' . esc_html__("open a new window with IMDb informations", 'lumiere-movies') . '">';
		}

		$convert = preg_replace("~(<a )((href=)(.+?))(nm)([[:alnum:]]*)\/((.+?)\">)~", $result, $convert);

		return $convert;
	}
}

/**
 * Personal signature
 *
 */

if ( ! function_exists('lumiere_admin_signature')){
	function lumiere_admin_signature(){
		global $allowed_html_for_esc_html_functions;
		echo "\t\t<div class=\"soustitre\">\n";
		echo "\t\t\t".
			wp_kses( __( '<strong>Licensing Info:</strong> Under a GPL licence, "Lumiere Movies" is based on <a href="https://github.com/tboothman/imdbphp/" target="_blank">tboothman</a> classes. Nevertheless, a considerable amount of work was required to implement it in wordpress; check the support page for', 'lumiere-movies'), $allowed_html_for_esc_html_functions ). "<a href=\"" .
			esc_url( admin_url() . "admin.php?page=imdblt_options&subsection=help&helpsub=support"). "\"> ".
			esc_html__('more', 'lumiere-movies') ."</a>.";
		echo "\t\t\t<br /><br /><div>\n\t\t\t\t<div> &copy; 2005-" . date("Y") . " <a href=\"" .  IMDBABOUTENGLISH . '" target="_blank">Lost Highway</a>, <a href="' . IMDBHOMEPAGE . '" target="_blank">Lumière! wordpress plugin' . '</a>, version ' . LUMIERE_VERSION . "\n</div>". "\n</div>";
		echo "\t\t</div>\n";
	} 
}

/**
 * Activate taxomony from wordpress
 *
 */

if ( ! function_exists('lumiere_create_taxonomies')){
	function lumiere_create_taxonomies() {

		global $imdb_admin_values,$imdb_widget_values;

		foreach ( lumiere_array_key_exists_wildcard($imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
			$filter_taxonomy = str_replace('imdbtaxonomy', '', $key );

			if ($imdb_widget_values[ 'imdbtaxonomy'.$filter_taxonomy ] ==  1) {

				register_taxonomy($imdb_admin_values['imdburlstringtaxo'].$filter_taxonomy, array('page','post'), 
					array( 
		/* remove metaboxes from edit interface, keep the menu of post */
		'show_ui'                    => true,
		'show_in_quick_edit'         => false,
		'meta_box_cb'                => false,
		/* other settings */
		'hierarchical' => false, 
		'label' => esc_html__("Lumière ".$filter_taxonomy, 'lumiere-movies'), 
		'query_var' => $imdb_admin_values['imdburlstringtaxo'].$filter_taxonomy, 
		'rewrite' => array( 'slug' => $imdb_admin_values['imdburlstringtaxo'].$filter_taxonomy ) 
					)  
				) ; 
			}
		}

		// Limit rewrites calls to taxonomy pages and admin interface
		if ( ( 0 === stripos( $_SERVER['REQUEST_URI'], esc_url( site_url( '', 'relative' ) . '/' . $imdb_admin_values['imdburlstringtaxo']) ) ) || ( is_admin() ) ){

			flush_rewrite_rules();

		}
	}
}

/**
 * Text displayed when no result is found
 *
 */
if ( ! function_exists('lumiere_noresults_text')){
	function lumiere_noresults_text(){ 
		echo "<br />";
		echo "<div class='noresult'>".esc_html_e('Sorry, no result found for this reference', 'lumiere-movies')."</div>";
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
 * IMDb source link display
 *
 */

if ( ! function_exists('lumiere_source_imdb')){
	function lumiere_source_imdb($midPremierResultat){
		global $imdb_admin_values;

		// Sanitize
		$midPremierResultat_sanitized = intval( $midPremierResultat );

		echo '<img class="imdbelementSOURCE-picture" width="33" height="15" src="' . esc_url( $imdb_admin_values['imdbplugindirectory'] . "pics/imdb-link.png" ) . '" />';
		echo '<a class="link-incmovie-sourceimdb" title="'.esc_html__("Go to IMDb website for this movie", 'lumiere-movies').'" href="'. esc_url( "https://".$imdb_admin_values['imdbwebsite'] . '/title/tt' .$midPremierResultat_sanitized ) . '" >';
		echo '&nbsp;&nbsp;' . esc_html__("IMDb's page for this movie", 'lumiere-movies') . '</a>';
	}
}

/**
 * Count me function
 * allows movie total count (how many time a movie is called by plugin
 *
 */

if ( ! function_exists('lumiere_count_me')){
	function lumiere_count_me($thema, &$count_me_siffer) {
		global $count_me_siffer, $test;
		$count_me_siffer++;
		$test[$count_me_siffer] = $thema;
		$ici=array_count_values($test);

		if ($ici[$thema] < 2) 
			return "nomore";
	}
}


/**
 * Highslide popup function
 * constructs a HTML link to open a popup with highslide for searching a movie (using js/lumiere_scripts.js)
 * (called from lumiere-movies.php)
 */

if ( ! function_exists('lumiere_popup_highslide_film_link')){
	function lumiere_popup_highslide_film_link ($link_parsed, $popuplarg="", $popuplong="" ) {
		global $imdb_admin_values;
			
		if (! $popuplarg )
			$popuplarg=$imdb_admin_values["popupLarg"];

		if (! $popuplong )
			$popuplong=$imdb_admin_values["popupLong"];

		$parsed_result = '<a class="link-imdblt-highslidefilm" data-highslidefilm="' . lumiere_name_htmlize($link_parsed[1]) . '" title="' . esc_html__("Open a new window with IMDb informations", 'lumiere-movies') . '">' . $link_parsed[1] . "</a>&nbsp;";

		return $parsed_result;
	}
}

/**
 * Classical popup function
 * constructs a HTML link to open a popup for searching a movie (using js/lumiere_scripts.js)
 * (called from lumiere-movies.php)
 */

if ( ! function_exists('lumiere_popup_classical_film_link')){
	function lumiere_popup_classical_film_link ($link_parsed, $popuplarg="", $popuplong="" ) {
		global $imdb_admin_values;
		
		if (! $popuplarg )
			$popuplarg=$imdb_admin_values["popupLarg"];

		if (! $popuplong )
			$popuplong=$imdb_admin_values["popupLong"];

		$parsed_result = '<a class="link-imdblt-classicfilm" data-classicfilm="' . lumiere_name_htmlize($link_parsed[1]) . '" title="' . esc_html__("Open a new window with IMDb informations", 'lumiere-movies') . '">' . $link_parsed[1] . "</a>&nbsp;";
		
		return $parsed_result;
	}
}

/** OBSOLETE, replaced by lumiere_name_htmlize()
 * HTMLizing function
 * transforms movie's name in a way to be able to be searchable (ie "ô" becomes "&ocirc;") 
 * ----> should use a wordpress dedicated function instead, like esc_url() ?
 */

if ( ! function_exists('lumiere_htmlize')){
	function lumiere_htmlize ($link) {
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
				echo '<div class="notice notice-success"><p>'. $msg .'</p></div>';
				break;
			case 2: // info notice, blue
				echo '<div class="notice notice-info"><p>'. $msg .'</p></div>';
				break;
			case 3: // simple error, red
				echo '<div class="notice notice-error"><p>'. $msg .'</p></div>';
				break;
			case 4: // warning error, yellow
				echo '<div "notice notice-warning">'. $msg .'</div>';
				break;
		}
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


/* lumiere_make_htaccess()
 * Create inc/.htaccess upon plugin activation
 * called in inc/options-general.php and lumiere-movies.php (upon activation)
 */
if (!function_exists('lumiere_make_htaccess')) {
	function lumiere_make_htaccess(){
		/* vars */
		$imdblt_blog_subdomain = site_url( '', 'relative' ) ?? ""; #ie: /subdirectory-if-exists/
		$imdblt_plugin_full_path = plugin_dir_path( __DIR__ ) ?? wp_die( esc_html__("There was an error when generating the htaccess file.", 'lumiere-movies') ); # ie: /fullpathtoplugin/subdirectory-if-exists/wp-content/plugins/lumiere-movies/
		$imdblt_plugin_path = str_replace( $imdblt_blog_subdomain, "", wp_make_link_relative( plugin_dir_url( __DIR__ ))); #ie: /wp-content/plugins/lumiere-movies/
		$imdblt_htaccess_file = $imdblt_plugin_full_path  . "inc/.htaccess" ?? wp_die( esc_html__("There was an error when generating the htaccess file.", 'lumiere-movies') ); # ie: /fullpathtoplugin/subdirectory-if-exists/wp-content/plugins/lumiere-movies/inc/.htaccess
		$imdblt_slug_path_movie = substr(LUMIERE_URLSTRINGFILMS, 1);
		$imdblt_slug_path_search = substr(LUMIERE_URLSTRING, 1);
		$imdblt_slug_path_person = substr(LUMIERE_URLSTRINGPERSON, 1);

		// .htaccess text, including Rewritebase with $blog_subdomain
		$imdblt_htaccess_file_txt = "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase ".$imdblt_blog_subdomain."/"."\n\n";

		# Gutenberg search
		$imdblt_htaccess_file_txt .= "## gutenberg-search.php\nRewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/gutenberg-search.php [NC]"."\n"."RewriteRule ^.+$ wp-admin/lumiere/search/ [L,R,QSA]"."\n\n";

		# highslide
		$imdblt_htaccess_file_txt .= "## highslide_download.php\nRewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/highslide_download.php [NC]"."\n"."RewriteRule ^.+$ wp-admin/admin.php?page=imdblt_options [L,R,QSA]"."\n\n";

		## move_template_taxonomy.php
		$imdblt_htaccess_file_txt .= "## move_template_taxonomy.php\nRewriteCond %{THE_REQUEST} ".$imdblt_plugin_path."inc/move_template_taxonomy.php [NC]"."\n"."RewriteRule ^.+$ wp-admin/admin.php?page=imdblt_options&subsection=widgetoption&widgetoption=taxo [L,R,QSA]"."\n\n";

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
*/

		$imdblt_htaccess_file_txt .= "</IfModule>";

		// write the .htaccess file if it can be written and close
		$inc_folder_htaccess = plugin_dir_path( __DIR__ ) . 'inc';
		if ( (isset($imdblt_htaccess_file)) && ( substr(sprintf('%o', fileperms( $inc_folder_htaccess )), -3) == "777" ) ) {
			file_put_contents($imdblt_htaccess_file, $imdblt_htaccess_file_txt);
			// lumiere_notice(1, esc_html__( 'htaccess file successfully generated.', 'lumiere-movies') ); # is not displayed
		} 
	}
}

/**
 * Function lumiere_debug_display
 * Returns a debug
 * 
 */

if (!function_exists('lumiere_debug_display')) {
	function lumiere_debug_display($options = "no array of options found", $set_error = NULL, $libxml_use = NULL) {

		echo '<div><font size="-2">';

		print_r($options);
		error_reporting(E_ALL);
		ini_set("display_errors", 1);

		if ( (isset($set_error)) && ($set_error == "SetError") )
			set_error_handler("var_dump"); # unset in options-cache.php as it breaks the pictures part of cache

		echo '</font></div>';

		if ( (isset($libxml_use)) && ($libxml_use == "libxml") )
			libxml_use_internal_errors(true); // avoid endless loops with imdbphp parsing errors 

	}
}

?>
