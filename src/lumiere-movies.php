<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress plugin
 *
 * This file is included during the WordPress bootstrap process if the plugin is active.
 *
 * @package   lumiere-movies
 * @author    jcvignoli
 * @license   GPL-3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 *
 * @wordpress-plugin
 * Plugin Name: Lumière! Movies
 * Plugin URI: https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 * Description: Add informative popups about movies with information extracted from the IMDb. Display data related to movies in a widget and inside your post. The most comprehensive and simplest plugin for adding movies information.
 * Version: 3.6
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

// Stop direct call.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
}

// Include bootstrap if main files exist.
if ( ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) && ( file_exists( plugin_dir_path( __FILE__ ) . 'class/class-core.php' ) ) ) {

	include_once plugin_dir_path( __FILE__ ) . 'bootstrap.php';

}

// Start the plugin if classes are loaded.
if ( ( class_exists( '\Lumiere\Core' ) ) && ( class_exists( '\Imdb\Config' ) ) ) {

	$lumiere_core = new \Lumiere\Core();

	// Executed upon plugin activation.
	register_activation_hook( __FILE__, [ $lumiere_core, 'lumiere_on_activation' ] );

	// Executed upon plugin deactivation.
	register_deactivation_hook( __FILE__, [ $lumiere_core, 'lumiere_on_deactivation' ] );

	// Display error notice, plugin is not properly installed.
}

