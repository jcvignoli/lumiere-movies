<?php declare( strict_types = 1 );
/**
 * Template for the management of cache
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Settings;

$lumiere_imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

// Retrieve the vars from calling class.
$lumiere_size_cache_folder = get_transient( 'admin_template_pass_vars' )[0];


