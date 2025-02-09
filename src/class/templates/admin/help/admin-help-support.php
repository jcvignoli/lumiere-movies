<?php declare( strict_types = 1 );
/**
 * Template for the Support section in help
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;

/**
 * HTML allowed for use of wp_kses()
 */
$lumiere_escape_wp_kses = [
	'i' => [],
	'strong' => [],
	'div' => [ 'class' => [] ],
	'a' => [
		'id' => [],
		'href' => [],
		'title' => [],
		'data-*' => [],
	],
];

// Retrieve vars from calling class.
$lumiere_page_howto = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
$lumiere_page_faqs = get_transient( Admin_Menu::TRANSIENT_ADMIN )[1];
$lumiere_aknowledgefile = get_transient( Admin_Menu::TRANSIENT_ADMIN )[2];
?>

<div class="lumiere_wrap">
	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="layout" name="layout">
			<?php
			/* translators: %1$s and %2$s are HTML tags */
			echo wp_kses( wp_sprintf( __( 'Two ways to support %1$sLumiere Movies%2$s plugin development', 'lumiere-movies' ), '<i>', '</i>' ), [ 'i' => [] ] ); ?>
		</h3>
	</div>
	
	<div class="lumiere_border_shadow helpdiv">

		<div class="titresection"><?php esc_html_e( 'Be supported!', 'lumiere-movies' ); ?></div>
	
			<?php esc_html_e( 'You will never believe there is so many ways to be supported. You can:', 'lumiere-movies' ); ?><br>

	<strong>1</strong>. <?php esc_html_e( 'visit', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( Get_Options::LUM_BLOG_PLUGIN ); ?>">Lumière website</a> <?php esc_html_e( 'to ask for help. ', 'lumiere-movies' ); ?><br>

	<strong>2</strong>. <?php esc_html_e( 'check the', 'lumiere-movies' ); ?> <a href="<?php echo esc_url( $lumiere_page_faqs ) ?>"><?php esc_html_e( 'FAQs ', 'lumiere-movies' ); ?></a>.<br>

	<strong>3</strong>. <?php esc_html_e( 'check the', 'lumiere-movies' ); ?> <a href="<?php echo esc_url( $lumiere_page_howto ) ?>"><?php esc_html_e( 'how to', 'lumiere-movies' ); ?></a>.<br>


		<div class="titresection"><?php esc_html_e( 'Support me!', 'lumiere-movies' ); ?></div>

		<?php esc_html_e( 'You will never believe there is so many ways to thank me. Yes, you can:', 'lumiere-movies' ); ?><br>
		
		<strong>1</strong>. <?php esc_html_e( 'pay whatever you want on', 'lumiere-movies' ); ?> <a href="https://www.paypal.me/jcvignoli">paypal <img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'paypal-donate.png' ); ?>" width="40px" class="paypal lumiere_valign_middle" /></a>.<br>
		<strong>2</strong>. <?php esc_html_e( 'vote on', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( Get_Options::LUM_WORDPRESS_URL ); ?>"><?php esc_html_e( "WordPress' website", 'lumiere-movies' ); ?></a> <?php esc_html_e( 'for Lumière plugin', 'lumiere-movies' ); ?>.<br>
		<strong>3</strong>. <?php esc_html_e( 'send as many bugfixes and propositions as you can on Lumiere Movies website.', 'lumiere-movies' ); ?><br>
		<strong>4</strong>. <?php esc_html_e( 'translate the plugin into your own language.', 'lumiere-movies' ); ?><br>
		<strong>5</strong>. <?php esc_html_e( 'help me to improve the plugin.', 'lumiere-movies' ); ?> <?php esc_html_e( 'Report at the development', 'lumiere-movies' ); ?> <a href="<?php echo esc_attr( Get_Options::LUM_GIT_URL ); ?>">GIT</a>'s <?php esc_html_e( 'website', 'lumiere-movies' ); ?> <br>
		<strong>6</strong>. <?php esc_html_e( 'do a trackback, make some noise about this plugin!', 'lumiere-movies' ); ?><br>

		<div class="titresection"><?php esc_html_e( 'Credits:', 'lumiere-movies' ); ?></div>

		<ul>
		<?php

		$lumiere_count_aknowledgefile = count( $lumiere_aknowledgefile );

		// $lumiere_i starts at one in order to skip first line with "**Changelog**"
		for ( $lumiere_i = 1; $lumiere_i < $lumiere_count_aknowledgefile; $lumiere_i++ ) {

			$lumiere_texte_string = is_string( $lumiere_aknowledgefile[ $lumiere_i ] ) ? $lumiere_aknowledgefile[ $lumiere_i ] : null;

			// Returns '<br>' every few lines, take it out.
			if ( ! isset( $lumiere_texte_string ) || strlen( $lumiere_texte_string ) === 0 || $lumiere_texte_string === '<br>' ) {
				continue;
			} ?>
			
			<li><?php echo wp_kses( $lumiere_texte_string, $lumiere_escape_wp_kses ); ?></li>
			
			<?php
		}
		?>
		
		</ul>
	</div>
</div>

