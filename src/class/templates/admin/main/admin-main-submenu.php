<?php declare( strict_types = 1 );
/**
 * Template for the submenu of main options pages
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

// Retrieve vars from calling class.
$lum_pics_url = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
$lum_admin_page_main_base = get_transient( Admin_Menu::TRANSIENT_ADMIN )[1];
$lum_admin_page_main_advanced = get_transient( Admin_Menu::TRANSIENT_ADMIN )[2];
?>

<div id="tabswrap" class="lumiere_wrap">
	<div class="lumiere_flex_container lumiere_padding_five">
		<div class="lumiere_flex_auto lumiere_align_center"><img alt="Paths & Layout" src="<?php echo esc_url( $lum_pics_url . 'menu/admin-main-path.png' ); ?>" align="absmiddle" width="16px">&nbsp;&nbsp;<a title="<?php esc_html_e( 'Paths & Layout', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lum_admin_page_main_base ); ?>"><?php esc_html_e( 'Layout', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img alt="Advanced" src="<?php echo esc_url( $lum_pics_url . 'menu/admin-main-advanced.png' ); ?>" align="absmiddle" width="16px">&nbsp;&nbsp;<a title="<?php esc_html_e( 'Advanced', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lum_admin_page_main_advanced ); ?>"><?php esc_html_e( 'Advanced', 'lumiere-movies' ); ?></a></div>
	</div>
</div>

