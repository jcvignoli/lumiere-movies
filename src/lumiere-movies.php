<?php declare( strict_types = 1 );
/**
 * Lumière Movies WordPress plugin
 *
 * @wordpress-plugin
 * Plugin Name: Lumière! Movies
 * Plugin URI: https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
 * Description: Add informative popups about movies with information extracted from the IMDb. Display data related to movies in a widget and inside your post.
 * Version: 3.9.3
 * Requires at least: 5.3
 * Requires PHP: 8.0
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
 *
 * @package   lumiere-movies
 * @author    jcvignoli
 * @license   GPL-3.0 <https://www.gnu.org/licenses/gpl-3.0.en.html>
 * @link       https://wordpress.org/plugins/lumiere-movies/
 * @since      3.0
 */

// Stop direct call.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'You are not allowed to call this page directly.', 'lumiere-movies' ) );
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
if ( count( array_intersect( Lumiere\Settings::LUMIERE_INCOMPATIBLE_PLUGINS, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) > 0 ) { // @phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- We need access to core WP function!

	if ( ! function_exists( 'lumiere_notice_install_error' ) ) {
		function lumiere_notice_install_error(): void {
			$incompatible_name_plugins = ucwords( str_replace( '-', ' ', implode( ',', preg_replace( '#/.*#', '', Lumiere\Settings::LUMIERE_INCOMPATIBLE_PLUGINS ) ) ) ) . '. ';
			$class = 'notice notice-error is-dismissible';
			$message = __( 'Lumière is incompatible with the following plugins: ', 'lumiere-movies' );
			$message_end = __( 'Lumière has been deactivated and cannot be activated.', 'lumiere-movies' );
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

$lumiere_core = new Lumiere\Core();

// Executed upon plugin activation.
register_activation_hook( __FILE__, [ $lumiere_core, 'lumiere_on_activation' ] );

// Executed upon plugin deactivation.
register_deactivation_hook( __FILE__, [ $lumiere_core, 'lumiere_on_deactivation' ] );

