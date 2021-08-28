<?php declare( strict_types = 1 );
/**
 * This page automatically downloads, extracts and deletes highslide library
 *
 * @version     1.0
 * @package lumiere-movies
 */

// prevent direct calls
if ( empty( wp_get_referer() ) && ( 0 !== stripos( wp_get_referer(), admin_url() . 'admin.php?page=lumiere_options' ) ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

/************* Vars **************/

$lumiere_highslidefile_remote_zip = esc_url( \Lumiere\Settings::IMDBBLOGHIGHSLIDE );
$lumiere_highslide_tmp_name = 'highslidetmp.zip';
$lumiere_highslidefile_local_zip = esc_url( plugin_dir_path( __DIR__ ) . $lumiere_highslide_tmp_name );
$lumiere_highslidefile_local_folder = esc_url( plugin_dir_path( __DIR__ ) . 'js/' );

// If is_admin include WP API libraries, else exit
if ( is_admin() ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
} else {
	wp_die( esc_html__( 'You can not call directly this page.', 'lumiere-movies' ) );
}

if ( ( isset( $_GET['highslide'] ) ) && ( $_GET['highslide'] === 'yes' ) ) {

	// Check the website
	if ( ( isset( $lumiere_highslidefile_remote_zip ) ) && ( ! empty( $lumiere_highslidefile_remote_zip ) ) ) {
		$lumiere_highslide_website_validator = wp_safe_remote_get( $lumiere_highslidefile_remote_zip );
	} else {
		wp_safe_redirect( add_query_arg( 'msg', 'highslide_website_unkown', wp_get_referer() ) );
		exit();
	}

	// Download Highslide zip if website is ok
	if ( is_wp_error( $lumiere_highslide_website_validator ) ) {
		wp_safe_redirect( add_query_arg( 'msg', 'highslide_down', wp_get_referer() ) );
		exit();
	} else {
		file_put_contents( $lumiere_highslidefile_local_zip, wp_remote_fopen( $lumiere_highslidefile_remote_zip ) );
	}

	//  Extraction and delete the file if exists, if it has an extension ".", if it ends with zip
	if ( ( unzip_file( $lumiere_highslidefile_local_zip, $lumiere_highslidefile_local_folder ) ) && ( file_exists( $lumiere_highslidefile_local_zip ) ) && end( explode( '.', $lumiere_highslidefile_local_zip ) ) && substr( $lumiere_highslidefile_local_zip, -3 ) == 'zip' ) {
		unlink( esc_url( $lumiere_highslidefile_local_zip ) );
		wp_safe_redirect( add_query_arg( 'msg', 'highslide_success', wp_get_referer() ) );
		exit();
	} else {
		// Extraction failed
		wp_safe_redirect( add_query_arg( 'msg', 'highslide_failure', wp_get_referer() ) );
		exit();
	}

	// Wrong $_GET
} else {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

