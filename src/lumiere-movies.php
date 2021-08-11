<?php
/*
 * Lumière Movies
 * 
 * Plugin Name: Lumière! Movies
 * Plugin URI: https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 * Description: Add informative popups about movies with information extracted from the IMDb. Display data related to movies in a widget and inside your post. The most comprehensive and simplest plugin for adding movies information.
 * Version: 3.5.2
 * Requires at least: 4.6
 * Text Domain: lumiere-movies
 * Domain Path: /languages
 * Author: psykonevro
 * Author URI: https://www.jcvignoli.com/blog
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 3, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
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




