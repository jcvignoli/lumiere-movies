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
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

// Get vars from the calling class.
$lumiere_pics_url = $variables['lum_pics_url']; /** @phpstan-ignore variable.undefined  */
$lumiere_admin_page_main_base = $variables['page_main_base']; /** @phpstan-ignore variable.undefined  */
$lumiere_admin_page_main_advanced = $variables['page_main_advanced']; /** @phpstan-ignore variable.undefined  */
?>

<div id="tabswrap" class="lumiere_wrap">
	<div class="lumiere_flex_container lumiere_padding_five">
		<div class="lumiere_flex_auto lumiere_align_center"><img alt="Paths & Layout" src="<?php echo esc_url( $lumiere_pics_url . 'menu/admin-main-path.png' ); ?>" align="absmiddle" width="16px">&nbsp;&nbsp;<a title="<?php esc_html_e( 'Paths & Layout', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_admin_page_main_base ); ?>"><?php esc_html_e( 'Layout', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img alt="Advanced" src="<?php echo esc_url( $lumiere_pics_url . 'menu/admin-main-advanced.png' ); ?>" align="absmiddle" width="16px">&nbsp;&nbsp;<a title="<?php esc_html_e( 'Advanced', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_admin_page_main_advanced ); ?>"><?php esc_html_e( 'Advanced', 'lumiere-movies' ); ?></a></div>
	</div>
</div>

