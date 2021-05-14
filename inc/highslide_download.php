<?php

/* This pages automatically download, extract and delete highslide library
 *
 */

/************* Vars **************/

global $imdb_admin_values;

$highslidefile_remote_zip = esc_url( IMDBBLOGHIGHSLIDE );
$highslide_tmp_name = "highslidetmp.zip";
$highslidefile_local_zip = esc_url( $imdb_admin_values['imdbpluginpath'] . $highslide_tmp_name );
$highslidefile_local_folder = esc_url( $imdb_admin_values['imdbpluginpath'] ."js/" );

if ( (isset($_GET["highslide"])) && ($_GET["highslide"] = "yes") ) {

	// Download Highslide zip
	if (lumiere_checkOnline( IMDBBLOGHIGHSLIDE ))  { 
		file_put_contents( $imdb_admin_values['imdbpluginpath'] . $highslide_tmp_name, fopen($highslidefile_remote_zip, 'r'));
	} else {
		wp_safe_redirect( add_query_arg( "msg", "highslide_down", wp_get_referer() ) );
		exit();
	}
		
	// Open the zip
	$zip = new ZipArchive;
	$res = $zip->open($highslidefile_local_zip);
	if ($res === TRUE)  {

		//  Extraction and delete the file if exists
		if ( ($zip->extractTo($highslidefile_local_folder)) && (file_exists ( $highslidefile_local_zip )) && end(explode(".", $highslidefile_local_zip)) ) {
			$zip->close();
			unlink( esc_url( $highslidefile_local_zip ) );
		}

		wp_safe_redirect( add_query_arg( "msg", "highslide_success", wp_get_referer() ) );
		exit();

	} else {
		// Extraction failed

		wp_safe_redirect( add_query_arg( "msg", "highslide_failure", wp_get_referer() ) );
		exit();
	}

// Wrong $_GET
} else {
	die(esc_html__("You can not call directly this page.", "imdb"));
}


/* Function to check if a website is online
 *
 */

function lumiere_checkOnline($domain) {
   $curlInit = curl_init($domain);
   curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
   curl_setopt($curlInit,CURLOPT_HEADER,true);
   curl_setopt($curlInit,CURLOPT_NOBODY,true);
   curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

   //get answer
   $response = curl_exec($curlInit);

   curl_close($curlInit);
   if ($response) return true;
   return false;
}

?>
