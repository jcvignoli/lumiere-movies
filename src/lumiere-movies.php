<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress plugin
 *
 * @package           lumieremovies
 * @copyright         2005 https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Lumière Movies
 * Plugin URI:        https://www.wordpress.org/plugins/lumiere-movies/
 * Description:       Add movies/persons data to your blog with info from IMDb. Display it in your popups, widget and inside your post.
 * Author:            psykonevro
 * Author URI:        https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 * Version:           4.6.1
 * Text Domain:       lumiere-movies
 * Requires at least: 5.6
 * Requires PHP:      8.1
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

// Prevent any direct call.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'You are not allowed to call this page directly.' );
}

// Get global vars.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'vars.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vars.php';
}

// Include composer bootstrap.
if ( file_exists( LUM_WP_PATH . 'vendor/autoload.php' ) ) {
	require_once LUM_WP_PATH . 'vendor/autoload.php';
}

// Get global functions.
if ( file_exists( LUM_WP_PATH . 'functions.php' ) ) {
	require_once LUM_WP_PATH . 'functions.php';
}

// Global function: Lumière is uninstalled if crappy plugins are found.
lum_incompatible_plugins_uninstall( LUMIERE_INCOMPATIBLE_PLUGINS, __FILE__ );

// Instanciate Core class.
$lumiere_core = new Lumiere\Core();

// Executed upon plugin activation.
register_activation_hook( __FILE__, [ $lumiere_core, 'lumiere_on_activation' ] );

// Executed upon plugin deactivation.
register_deactivation_hook( __FILE__, [ $lumiere_core, 'lumiere_on_deactivation' ] );

