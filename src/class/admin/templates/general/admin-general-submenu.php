<?php declare( strict_types = 1 );
/**
 * Template for the submenu of general pages
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

// Get var from caller
$lumiere_that = get_transient( 'admin_template_this' )[0];
?>

<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">
		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $lumiere_that->config_class->lumiere_pics_dir . 'menu/admin-general-path.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Paths & Layout', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_that->page_general_base ); ?>"><?php esc_html_e( 'Layout', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $lumiere_that->config_class->lumiere_pics_dir . 'menu/admin-general-advanced.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Advanced', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_that->page_general_advanced ); ?>"><?php esc_html_e( 'Advanced', 'lumiere-movies' ); ?></a></div>
	</div>
</div>

<div id="poststuff" class="metabox-holder">
</div>
