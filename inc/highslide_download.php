<?php

/* This pages automatically download, extract and delete highslide library
 *
 */

// prevent direct calls
if (empty(wp_get_referer()) && (0 !== stripos( wp_get_referer(), admin_url() . 'admin.php?page=imdblt_options' )) )
	wp_die(esc_html__("You can not call directly this page.", "imdb"));

/************* Vars **************/

// Include WP API libraries
include(ABSPATH . "wp-admin/includes/admin.php");
WP_Filesystem();

global $imdb_admin_values;

$highslidefile_remote_zip = esc_url( IMDBBLOGHIGHSLIDE );
$highslide_tmp_name = "highslidetmp.zip";
$highslidefile_local_zip = esc_url( $imdb_admin_values['imdbpluginpath'] . $highslide_tmp_name );
$highslidefile_local_folder = esc_url( $imdb_admin_values['imdbpluginpath'] ."js/" );

if ( (isset($_GET["highslide"])) && (  $_GET["highslide"]  = "yes" ) ) {

	// Check the website
	if ( (isset($highslidefile_remote_zip)) && (!empty($highslidefile_remote_zip)) ) {
		$highslide_website_validator = wp_safe_remote_get( $highslidefile_remote_zip );
	} else {
		wp_safe_redirect( add_query_arg( "msg", "highslide_website_unkown", wp_get_referer() ) );
		exit();
	}
   	
	// Download Highslide zip if website is ok
	if (is_wp_error($highslide_website_validator))  { 
		wp_safe_redirect( add_query_arg( "msg", "highslide_down", wp_get_referer() ) );
		exit();
	} else {
		file_put_contents( $highslidefile_local_zip, wp_remote_fopen($highslidefile_remote_zip));
	}
		
	//  Extraction and delete the file if exists, if it has an extension ".", if it ends with zip
	if ( (unzip_file($highslidefile_local_zip,$highslidefile_local_folder)) && (file_exists ( $highslidefile_local_zip )) && end(explode(".", $highslidefile_local_zip)) && substr($highslidefile_local_zip, -3) == "zip" ) {
		unlink( esc_url( $highslidefile_local_zip ) );
		wp_safe_redirect( add_query_arg( "msg", "highslide_success", wp_get_referer() ) );
		exit();
	} else {
		// Extraction failed
		wp_safe_redirect( add_query_arg( "msg", "highslide_failure", wp_get_referer() ) );
		exit();
	}

// Wrong $_GET
} else {
	wp_die(esc_html__("You can not call directly this page.", "imdb"));
}

?>
