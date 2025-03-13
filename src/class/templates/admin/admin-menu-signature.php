<?php declare( strict_types = 1 );
/**
 * Template for the first part of the admin menu
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

$lumiere_esc_html = [
	'a' => [
		'href' => [],
	],
	'strong' => [],
];

// Get transients vars from the calling class.
$lumiere_helpage = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
?>

<div class="soustitre lumiere_wrap lumiere_signature">
	<div><?php
		/* translators: %1$s and %2$s are replaced with an html strong tag, %3$s and %4$s are html ahref tags */
		echo wp_kses( wp_sprintf( __( '%1$sLicensing Info:%2$s Under a GPL licence, based on various libraries. However, a lot of work is required to implement it in WordPress and maintain it; check the support page for %3$smore information%4$s.', 'lumiere-movies' ), '<strong>', '</strong>', '<a href="' . esc_url( $lumiere_helpage ) . '">', '</a>' ), $lumiere_esc_html );
	?></div>
	<br />
	<div>
		&copy; 2005-<?php echo esc_html( gmdate( 'Y' ) ); ?> <a href="<?php echo esc_html( \Lumiere\Config\Get_Options::LUM_BLOG_PLUGIN_ABOUT ); ?>" target="_blank">Lost Highway</a>, <a href="<?php echo esc_html( \Lumiere\Config\Get_Options::LUM_BLOG_PLUGIN ); ?>" target="_blank">Lumière! WordPress plugin</a> version <?php echo esc_html( lum_get_version() ); ?>.
	</div>
</div>
