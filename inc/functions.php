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
function lumiere_unlinkRecursive($dir){
    if(!$dh = @opendir($dir)){
        return;
    }
    while (false !== ($obj = readdir($dh))) {
        if($obj == '.' || $obj == '..') {
            continue;
        }

        if (!@unlink($dir . '/' . $obj)){
            imdblt_unlinkRecursive($dir.'/'.$obj, true);
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

function imdblt_isEmptyDir($dir, $filesbydefault= "3"){	
	return (($files = @scandir($dir)) && count($files) <= $filesbydefault);
} 

/**
 * Remove an html link
 * @param string $toremove Data wherefrom remove every html link
 */

function imdblt_remove_link ($toremove) {
	$toremove = preg_replace("/<a(.*?)>/", "", $toremove);
	return $toremove;
}

/**
 * Sanitize an array
 * 
 */

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

/**
 * Convert an imdb link to a highslide/classic popup link (called 
 * @param string $convert Link to convert into popup highslide link
 */

function imdblt_convert_txtwithhtml_into_popup_people ($convert) {
	global $imdb_admin_values;

	// $result = "<a class=\"link-imdb2 highslide\" onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', width: " . $imdb_admin_values['popupLarg']. ", objectWidth: ". $imdb_admin_values['popupLarg'].", objectHeight: ". $imdb_admin_values['popupLong']. ", headingEval: 'this.a.innerHTML', wrapperClassName: 'titlebar', src: '" . $imdb_admin_values['imdbplugindirectory'] . "inc/popup-imdb_person.php?mid=" . "\${6}" . "' } )\" title=\"". esc_html__('open a new window with IMDb informations', 'imdb'). '" href="#" >';

	// 20210505 new way to create highslide/classic link, if either "imdbpopup_highslide" or "link-imdblt-highslidepeople" class is clicked throw "\${6}" (the mid) to javascript csp_inline_script.js

	if ($imdb_admin_values['imdbpopup_highslide'] == 1) { // highslide popup
			$result = '<a class="link-imdblt-highslidepeople highslide" data-highslidepeople="' . "\${6}" . '" title="' . esc_html__("open a new window with IMDb informations", "imdb") . '">';
	} else {						// classic popup
	    		$result = '<a class="link-imdblt-classicpeople" data-classicpeople="' . "\${6}" . '" title="' . esc_html__("open a new window with IMDb informations", "imdb") . '">';
	}

	$convert = preg_replace("~(<a )((href=)(.+?))(nm)([[:alnum:]]*)\/((.+?)\">)~", $result, $convert);

	return $convert;
}


/**
 * Personal signature
 *
 */

function imdblt_admin_signature(){ ?>
	<div class="soustitre">
	<table class="options">
		
		<tr>
			<td><div class="explain"><?php wp_kses( _e( '<strong>Licensing Info:</strong> Under the GPL licence, "Lumiere Movies" is based on <a href="https://github.com/tboothman/imdbphp/">tboothman</a> classes. Nevertheless, a considerable amount of work was required to implement it in wordpress; check the support page for', 'imdb'), $allowed_html_for_esc_html_functions ); ?> <a href="<?php admin_url(); ?>?page=imdblt_options&subsection=help&helpsub=support"><?php esc_html_e('more', 'imdb') ?></a>.</div>
			</td>
		</tr>
		<tr>
			<td><div class="explain"> &copy; 2005-<?php print date("Y")?> <a href="<?php echo IMDBHOMEPAGE; ?>">Prometheus Group</a></div>
			</td>
		</tr>
	</table>
	</div>
<?php
} 


/**
 * Activate taxomony from wordpress
 *
 */

function create_imdblt_taxonomies() {
	global $imdb_admin_values,$imdb_widget_values;

	if ($imdb_widget_values['imdbtaxonomytitle'] ==  true) {
		register_taxonomy('imdblt_title', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt titles", 'imdb'), 'query_var' => 'imdblt_title', 'rewrite' => array( 'slug' => 'imdblt_title' ) )  ) ; }

	if ($imdb_widget_values['imdbtaxonomygenre'] ==  true) {
		register_taxonomy('imdblt_genre', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt genres", 'imdb'), 'query_var' => 'imdblt_genre', 'rewrite' => array( 'slug' => 'imdblt_genre' ) )  ) ; }

	if ($imdb_widget_values['imdbtaxonomykeywords'] ==  true) {
		register_taxonomy('imdblt_keywords', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt keywords", 'imdb'), 'query_var' => 'imdblt_keywords', 'rewrite' => array( 'slug' => 'imdblt_keywords' ) )  ) ; }

	if ($imdb_widget_values['imdbtaxonomycountry'] == true) {
		register_taxonomy('imdblt_country', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt countries", 'imdb'), 'query_var' => 'imdblt_country', 'rewrite' => array( 'slug' => 'imdblt_country' ) )  ) ; }

	if ($imdb_widget_values['imdbtaxonomylanguage'] == true) {
		register_taxonomy('imdblt_language', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt languages", 'imdb'), 'query_var' => 'imdblt_language', 'rewrite' => array( 'slug' => 'imdblt_language' ) )  ) ; }

	if ($imdb_widget_values['imdbtaxonomycomposer'] == true) {
		register_taxonomy('imdblt_composer', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt composers", 'imdb'), 'query_var' => 'imdblt_composer', 'rewrite' => array( 'slug' => 'imdblt_composer' ) )  ) ; }

	if ($imdb_widget_values['imdbtaxonomycolor'] == true) {
		register_taxonomy('imdblt_color', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt colors", 'imdb'), 'query_var' => 'imdblt_color', 'rewrite' => array( 'slug' => 'imdblt_color' ) )  ) ; }

	if ($imdb_widget_values['imdbtaxonomydirector'] == true) {
		register_taxonomy('imdblt_director', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt directors", 'imdb'), 'query_var' => 'imdblt_director', 'rewrite' => array( 'slug' => 'imdblt_director' ) )  ) ; }

	if ($imdb_widget_values['imdbtaxonomycreator'] == true) {
		register_taxonomy('imdblt_creator', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt creators", 'imdb'), 'query_var' => 'imdblt_creator', 'rewrite' => array( 'slug' => 'imdblt_creator' ) )  ) ; }

	if ($imdb_widget_values['imdbtaxonomyproducer'] == true) {
		register_taxonomy('imdblt_producer', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt producers", 'imdb'), 'query_var' => 'imdblt_producer', 'rewrite' => array( 'slug' => 'imdblt_producer' ) )  ) ; }

	if ($imdb_widget_values['imdbtaxonomyactor'] == true) {
		register_taxonomy('imdblt_actor', array('page','post'), array( 'hierarchical' => false, 'label' => esc_html__("IMDBlt actors", 'imdb'), 'query_var' => 'imdblt_actor', 'rewrite' => array( 'slug' => 'imdblt_actor' ) )  ) ; }
		
	if ($imdb_widget_values['imdbtaxonomywriter'] == true) {
		register_taxonomy('imdblt_writer', array('page','post'), array( 'hierarchical' => true, 'label' => esc_html__("IMDBlt writers", 'imdb'), 'query_var' => 'imdblt_writer', 'rewrite' => array( 'slug' => 'imdblt_writer' ) )  ) ; }
}


/**
 * Text displayed when no result is found
 *
 */

function imdblt_noresults_text(){ 
	echo "<br />";
	echo "<div class='noresult'>".esc_html_e('Sorry, no result found for this reference', 'imdb')."</div>";
	echo "<br />";
} 


/**
 * Recursively test an multi-dimensionnal array
 *
 * @param string $multiarray Array name
 * credits to http://in2.php.net/manual/fr/function.empty.php#94786
 */

function is_multiArrayEmpty($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $value) {
            if (!is_multiArrayEmpty($value)) {
                return false;
            }
        }
    }
    elseif (!empty($mixed)) {
        return false;
    }
    return true;
} 

/**
 * IMDb source link display
 *
 */

function imdblt_source_imdb($midPremierResultat){
	global $imdb_admin_values;

	// Sanitize
	$midPremierResultat_sanitized = sanitize_text_field( $midPremierResultat );

	echo '&nbsp;&nbsp;';
	echo '<a href="https://'.$imdb_admin_values['imdbwebsite'].'/title/tt'.$midPremierResultat_sanitized.'" >';
	echo "<img class='imdbelementSOURCE-picture' src=\"".$imdb_admin_values['imdbplugindirectory'].'pics/imdb-link.png" />';
	echo '&nbsp;&nbsp;IMDb\'s page for this movie</a>';
} 


/**
 * Count me function
 * allows movie total count (how many time a movie is called by plugin
 *
 */

function count_me($thema, &$count_me_siffer) {
	global $count_me_siffer, $test;
	$count_me_siffer++;
	$test[$count_me_siffer] = $thema;
	$ici=array_count_values($test);

	if ($ici[$thema] < 2) 
		return "nomore";
}



/**
 * Highslide popup function
 * constructs a HTML link to open a popup with highslide for searching a movie (using js/csp_inline_scripts.js)
 * (called from lumiere-movies.php)
 */

function imdblt_popup_highslide_film_link ($link_parsed, $popuplarg="", $popuplong="" ) {
	global $imdb_admin_values;
		
	if (! $popuplarg )
		$popuplarg=$imdb_admin_values["popupLarg"];

	if (! $popuplong )
		$popuplong=$imdb_admin_values["popupLong"];

	$parsed_result = '<a class="link-imdblt-highslidefilm" data-highslidefilm="' . imdb_htmlize($link_parsed[1]) . '" title="' . esc_html__("Open a new window with IMDb informations", "imdb") . '">' . $link_parsed[1] . "</a>&nbsp;";

	return $parsed_result;
}


/**
 * Classical popup function
 * constructs a HTML link to open a popup for searching a movie (using js/csp_inline_scripts.js)
 * (called from lumiere-movies.php)
 */

function imdblt_popup_classical_film_link ($link_parsed, $popuplarg="", $popuplong="" ) {
	global $imdb_admin_values;
	
	if (! $popuplarg )
		$popuplarg=$imdb_admin_values["popupLarg"];

	if (! $popuplong )
		$popuplong=$imdb_admin_values["popupLong"];

	$parsed_result = '<a  class="link-imdblt-classicfilm" data-classicfilm="' . imdb_htmlize($link_parsed[1]) . '" title="' . esc_html__("Open a new window with IMDb informations", "imdb") . '">' . $link_parsed[1] . "</a>&nbsp;";
	
	return $parsed_result;
}


/**
 * HTMLizing function
 * transforms movie's name in a way to be able to be searchable (ie "ô" becomes "&ocirc;") 
 * 
 */

function imdb_htmlize ($link) {
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
 * Function imdblt_formatBytes
 * Returns in a proper format a size
 * 
 */
if ( ! function_exists('imdblt_formatBytes')){
	function imdblt_formatBytes($size, $precision = 2) { 
		$base = log($size, 1024); 
		$suffixes = array('bytes', 'Kb', 'Mb', 'Gb', 'Tb');
		return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)]; 
	}
}
/**
 * Function imdblt_glob_recursive
 * Does a glob recursively
 * Does not support flag GLOB_BRACE
 * Credits go to https://www.php.net/manual/fr/function.glob.php#106595
 */

if ( ! function_exists('imdblt_glob_recursive')){
    function imdblt_glob_recursive($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
       
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, imdblt_glob_recursive($dir.'/'.basename($pattern), $flags));
        }
       
        return $files;
    }
}

/**
 * Function imdblt_notice
 * Display a confirmation notice, such as "options saved"
 */

function imdblt_notice($code, $msg) { 
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
?>
