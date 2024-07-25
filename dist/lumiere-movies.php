<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress plugin
 *
 * @package           lumiere-movies
 * @author            jcvignoli
 * @copyright         2005 https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Lumière Movies
 * Plugin URI:        https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 * Description:       Add informative popups about movies with information extracted from the IMDb. Display data related to movies in a widget and inside your post.
 * Version:           4.1.8
 * Requires at least: 5.6
 * Requires PHP:      8.0
 * Author:            psykonevro
 * Author URI:        https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 * Text Domain:       lumiere-movies
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Stop direct call.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'You are not allowed to call this page directly.' );
}

// Include composer bootstrap.
if ( ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) && ( file_exists( plugin_dir_path( __FILE__ ) . 'class/class-core.php' ) ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

// Check if the classes are installed.
if ( ! class_exists( 'Lumiere\Core' ) ) {
	wp_die( esc_html__( 'Error: Lumière is not installed. Check your install.', 'lumiere-movies' ) );
}
if ( ! class_exists( 'Imdb\Config' ) ) {
	wp_die( esc_html__( 'Error: Imdbphp libraries are not installed. Check your install.', 'lumiere-movies' ) );
}

// Remove Lumière if crappy plugins are active
if ( count( array_intersect( Lumiere\Settings::LUMIERE_INCOMPATIBLE_PLUGINS, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) > 0 ) { // @phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Modifying core WP hook!

	if ( ! function_exists( 'lumiere_notice_install_error' ) ) {
		function lumiere_notice_install_error(): void {
			$incompatible_name_plugins = ucwords( str_replace( '-', ' ', implode( ',', preg_replace( '#/.*#', '', Lumiere\Settings::LUMIERE_INCOMPATIBLE_PLUGINS ) ) ) ) . '. ';
			$class = 'notice notice-error is-dismissible';
			$message = __( 'Lumière is incompatible with the following plugins: ', 'lumiere-movies' );
			$message_end = __( 'Lumière has been deactivated and cannot be activated unless you deactivate ', 'lumiere-movies' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) . esc_html( $incompatible_name_plugins ) . esc_html( $message_end ) );
		}
	}

	add_action( 'admin_notices', 'lumiere_notice_install_error' );
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	deactivate_plugins( __FILE__ );
	return;
}

// Start the plugin
$lumiere_core = new Lumiere\Core();

// Executed upon plugin activation.
register_activation_hook( __FILE__, [ $lumiere_core, 'lumiere_on_activation' ] );

// Executed upon plugin deactivation.
register_deactivation_hook( __FILE__, [ $lumiere_core, 'lumiere_on_deactivation' ] );

