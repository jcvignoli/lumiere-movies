<?php
/*
Plugin Name: Lumière! Movies
Plugin URI: https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
Description: Add clickable links to informative popups about movies with information extracted from the IMDb. Display data related to movies and people in a widget or inside your post. Fully customizable. The most comprehensive and simplest plugin if you write about movies.
Version: 3.5.1
Requires at least: 4.6
Text Domain: lumiere-movies
Domain Path: /languages
Author: psykonevro
Author URI: https://www.jcvignoli.com/blog
*/

// Stop direct call
if ( ! defined( 'ABSPATH' ) ) 
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));

// Include bootstrap if main files exist
if ( (file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' )) && (file_exists( plugin_dir_path( __FILE__ ) . 'class/Core.php' )) ) {

	include_once ( plugin_dir_path( __FILE__ ) . 'bootstrap.php' );

}

// Start the plugin if classes are loaded
if ( (class_exists("\Lumiere\Core")) && (class_exists('\Imdb\Config')) ){

	$start = new \Lumiere\Core() ?? NULL;

	# Executed upon plugin activation
	register_activation_hook( __FILE__, [ $start , 'lumiere_on_activation' ] );

	# Executed upon plugin deactivation
	register_deactivation_hook( __FILE__, [ $start , 'lumiere_on_deactivation' ] );

	# Executed upon plugin deactivation
	// @TODO: stop using deactivation to do uninstall work
	//register_uninstall_hook(__FILE__, 'lumiere_on_uninstall' );

// Display error notice, plugin is not properly installed
} else {

	add_action('admin_notices', 'lumiere_installation_error');

}

/* Display error notice upon bad installation
 *
 */
function lumiere_installation_error() {

	$class = 'notice notice-error';
	$message = 'Lumière Movies WordPress plugin has been incorrectly installed. Check your install or reinstall the plugin.';

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );		

}




