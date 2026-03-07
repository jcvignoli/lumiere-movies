<?php declare( strict_types = 1 );
/**
 * Template for the Data admin - Common menu for data
 *
 * @copyright (c) 2026, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use \Lumiere\Config\Get_Options;
?>

	<div class="lumiere_title_options lumiere_border_shadow lumiere_flex_container lum_flex_space_evenly">
		<h3 id="taxodetails" name="taxodetails" class=""><?php esc_html_e( 'Movies items', 'lumiere-movies' ); ?></h3>

		<div class="">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-data-inside-display.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'Movie items selection', 'lumiere-movies' ); ?>" href="<?php
		/** @phpstan-ignore variable.undefined  */
		echo esc_url( $lumiere_calling_class->page_data_movie ); ?>#tabswrap"><?php esc_html_e( 'Items selection', 'lumiere-movies' ); ?></a></div>

		<div class="">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-data-inside-order.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'Movie items order', 'lumiere-movies' ); ?>" href="<?php
		/** @phpstan-ignore variable.undefined  */
		echo esc_url( $lumiere_calling_class->page_data_movie_order ); ?>#tabswrap"><?php esc_html_e( 'Items order', 'lumiere-movies' ); ?></a></div>

			<?php
			/** @phpstan-ignore variable.undefined  */
			if ( $lumiere_calling_class->settings->get_admin_option( 'imdbtaxonomy' ) === '1' ) { ?>
		<div class=" lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-data-inside-movie-taxonomy.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'Movie items to use as taxonomy', 'lumiere-movies' ); ?>" href="<?php
		/** @phpstan-ignore variable.undefined  */
		echo esc_url( $lumiere_calling_class->page_data_movie_taxo ); ?>#tabswrap"><?php esc_html_e( 'Taxonomy', 'lumiere-movies' ); ?></a></div>
			<?php } else { ?>
		<div class="lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-data-inside-movie-taxonomy.png' ); ?>#tabswrap" align="absmiddle" width="16px" />&nbsp;<i><?php esc_html_e( 'Taxonomy unactivated', 'lumiere-movies' ); ?></i></div>
			<?php } ?>

	</div>

