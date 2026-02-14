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
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

// Get vars from the calling class.
$lumiere_helpage = $variables['page_help_support']; /** @phpstan-ignore variable.undefined  */
$lumiere_current_year = wp_date( 'Y' );
$lumiere_esc_html = [
	'a' => [
		'href' => [],
	],
	'strong' => [],
];
?>

<div class="soustitre lumiere_wrap lumiere_signature">
	<div><?php
		/* translators: %1$s and %2$s are replaced with an html strong tag, %3$s and %4$s are html ahref tags */
		echo wp_kses( wp_sprintf( __( '%1$sLicensing Info:%2$s Under a GPL licence, based on various libraries. However, a lot of work is required to implement it in WordPress and maintain it; check the support page for %3$smore information%4$s.', 'lumiere-movies' ), '<strong>', '</strong>', '<a href="' . esc_url( $lumiere_helpage ) . '">', '</a>' ), $lumiere_esc_html );
	?></div>
	<br />
	<div>
		&copy; 2005-<?php
		echo $lumiere_current_year !== false ? esc_html( $lumiere_current_year ) : ''; ?> <a href="<?php echo esc_html( \Lumiere\Config\Get_Options::LUM_BLOG_PLUGIN_ABOUT ); ?>" target="_blank">Lost Highway</a>, <a href="<?php echo esc_html( \Lumiere\Config\Get_Options::LUM_BLOG_PLUGIN ); ?>" target="_blank">Lumière! WordPress plugin</a> version <?php echo esc_html( lum_get_version() ); ?>.
	</div>
</div>
