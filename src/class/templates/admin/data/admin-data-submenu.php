<?php declare( strict_types = 1 );
/**
 * Template for the submenu of data pages
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

use Lumiere\Config\Get_Options;

// Retrieve the vars from calling class.
$lumiere_that = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
?>

<div id="tabswrap" class="lumiere_wrap">
	<div class="lumiere_flex_container lumiere_padding_five">

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-widget-inside-whattodisplay.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'What to display', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_that->page_data ); ?>"><?php esc_html_e( 'Display', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-widget-inside-order.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'Display order', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_that->page_data_order ); ?>"><?php esc_html_e( 'Display order', 'lumiere-movies' ); ?></a></div>

			<?php if ( $lumiere_that->imdb_admin_values['imdbtaxonomy'] === '1' ) { ?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-widget-inside-whattotaxo.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'What to taxonomize', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_that->page_data_taxo ); ?>"><?php esc_html_e( 'Taxonomy', 'lumiere-movies' ); ?></a></div>
			<?php } else { ?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-widget-inside-whattotaxo.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<i><?php esc_html_e( 'Taxonomy unactivated', 'lumiere-movies' ); ?></i></div>
			<?php } ?>

	</div>
</div>
