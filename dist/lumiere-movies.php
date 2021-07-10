<?php
// Lumière wordpress plugin
//
// (c) 2005-21 Lost Highway
// https://www.jcvignoli.com/blog
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// *****************************************************************

/*
Plugin Name: Lumière! Movies
Plugin URI: https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
Description: Add clickable links to informative popups about movies with information extracted from the IMDb. Display data related to movies and people in a widget or inside your post. Fully customizable. The most comprehensive and simplest plugin if you write about movies.
Version: 3.4.1
Requires at least: 4.6
Text Domain: lumiere-movies
Domain Path: /languages
Author: psykonevro
Author URI: https://www.jcvignoli.com/blog
*/

// Stop direct call
if ( ! defined( 'ABSPATH' ) ) 
	wp_die(esc_html__("You are not allowed to call this page directly.", "lumiere-movies"));

# Bootstrap with requires
require_once ( plugin_dir_path( __FILE__ ) . 'bootstrap.php' );
require_once ( plugin_dir_path( __FILE__ ) . 'inc/admin_pages.php' );

### Lumiere Classes start
if (class_exists("\Lumiere\Core")) {
	$start = new \Lumiere\Core() ?? NULL;
}

# Executed upon plugin activation
register_activation_hook( __FILE__, [ $start , 'lumiere_on_activation' ] );

# Executed upon plugin deactivation
register_deactivation_hook( __FILE__, [ $start , 'lumiere_on_deactivation' ] );

?>
