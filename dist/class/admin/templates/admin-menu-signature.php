<?php declare( strict_types = 1 );
/**
 * Template for the first part of the admin menu
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

// Authorise this html tags wp_kses()
$lumiere_esc_html = [
	'a' => [
		'href' => [],
		'title' => [],
	],
	'strong' => [],
];

// Get transients vars from the calling class.
$lumiere_helpage = get_transient( 'admin_template_pass_vars' )[0];
?>

<div class="soustitre lumiere_signature">
	<div><?php
	/* translators: %1$s and %1$s are replaced with an html strong tag */
	echo wp_kses( wp_sprintf( __( '%1$sLicensing Info:%2$s Under a GPL licence, on various libraries. However, much work was required to implement it in WordPress and maintain it; check the support page for %3$smore information%4$s.', 'lumiere-movies' ), '<strong>', '</strong>', '<a href="' . esc_url( $lumiere_helpage ) . '">', '</a>' ), $lumiere_esc_html );
	?></div>
	<br />
	<div> &copy; 2005-<?php echo esc_html( gmdate( 'Y' ) ); ?> <a href="<?php echo esc_html( Settings::IMDBABOUTENGLISH ); ?>" target="_blank">Lost Highway</a>, <a href="<?php echo esc_html( Settings::IMDBHOMEPAGE ); ?>" target="_blank">Lumière! WordPress plugin</a>
	</div>
</div>
