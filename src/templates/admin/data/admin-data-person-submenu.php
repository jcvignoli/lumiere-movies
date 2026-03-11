<?php declare( strict_types = 1 );
/**
 * Template for the Data person admin - Common menu for data person
 *
 * @copyright (c) 2026, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use \Lumiere\Config\Get_Options;

?>

	<div class="lumiere_title_options lumiere_border_shadow lumiere_flex_container lum_flex_space_evenly">
		<h3 id="person_display" name="person_display" class=""><?php esc_html_e( 'Persons items', 'lumiere-movies' ); ?></h3>

		<div class="">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-data-inside-display.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'Items selection', 'lumiere-movies' ); ?>" href="<?php
		/** @phpstan-ignore variable.undefined  */
		echo esc_url( $lumiere_calling_class->page_data_person ); ?>#tabswrap"><?php esc_html_e( 'Items selection', 'lumiere-movies' ); ?></a></div>

		<div class="">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-data-inside-order.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'Items order', 'lumiere-movies' ); ?>" href="<?php
		/** @phpstan-ignore variable.undefined  */
		echo esc_url( $lumiere_calling_class->page_data_person_order ); ?>#tabswrap"><?php esc_html_e( 'Items order', 'lumiere-movies' ); ?></a></div>
	</div>

