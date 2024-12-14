<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress plugin
 *
 * @package           lumiere-movies
 * @author            jcvignoli
 * @copyright         2005 https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Lumière Movies
 * Plugin URI:        https://www.wordpress.org/plugins/lumiere-movies/
 * Description:       Add informative popups about movies with information extracted from the IMDb. Display data related to movies in a widget and inside your post.
 * Version:           4.2.3
 * Requires at least: 5.6
 * Requires PHP:      8.0
 * Author:            psykonevro
 * Author URI:        https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 * Text Domain:       lumiere-movies
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

// Prevent any direct call.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'You are not allowed to call this page directly.' );
}

// Include composer bootstrap.
if ( ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) && ( file_exists( plugin_dir_path( __FILE__ ) . 'class/class-core.php' ) ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

// Get global vars.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'vars.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vars.php';
}

// Get global functions.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'functions.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'functions.php';
}

// Check if Lumière and IMDbPHP classes are installed.
if ( ! class_exists( 'Lumiere\Core' ) ) {
	wp_die( esc_html__( 'Error: Lumière is not correctly installed. Check your install.', 'lumiere-movies' ) );
}
if ( ! class_exists( 'Imdb\Config' ) ) {
	wp_die( esc_html__( 'Error: IMDbPHP libraries are not installed. Check your install.', 'lumiere-movies' ) );
}

// Start the plugin
lum_incompatible_plugins_uninstall( Lumiere\Settings::LUMIERE_INCOMPATIBLE_PLUGINS, __FILE__ ); // Lumière is uninstalled if crappy plugins are found.
$lumiere_core = new Lumiere\Core();

// Executed upon plugin activation.
register_activation_hook( __FILE__, [ $lumiere_core, 'lumiere_on_activation' ] );

// Executed upon plugin deactivation.
register_deactivation_hook( __FILE__, [ $lumiere_core, 'lumiere_on_deactivation' ] );

