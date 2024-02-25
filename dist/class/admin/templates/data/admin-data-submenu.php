<?php declare( strict_types = 1 );
/**
 * Template for the submenu of data pages
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

// Retrieve the vars from calling class.
$lumiere_that = get_transient( 'admin_template_pass_vars' )[0];
?>

<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $lumiere_that->config_class->lumiere_pics_dir . 'menu/admin-widget-inside-whattodisplay.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'What to display', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_that->page_data ); ?>"><?php esc_html_e( 'Display', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $lumiere_that->config_class->lumiere_pics_dir . 'menu/admin-widget-inside-order.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'Display order', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_that->page_data . '&widgetoption=order' ); ?>"><?php esc_html_e( 'Display order', 'lumiere-movies' ); ?></a></div>

			<?php if ( $lumiere_that->imdb_admin_values['imdbtaxonomy'] === '1' ) { ?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $lumiere_that->config_class->lumiere_pics_dir . 'menu/admin-widget-inside-whattotaxo.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'What to taxonomize', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_that->page_data . '&widgetoption=taxo' ); ?>"><?php esc_html_e( 'Taxonomy', 'lumiere-movies' ); ?></a></div>
			<?php } else { ?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $lumiere_that->config_class->lumiere_pics_dir . 'menu/admin-widget-inside-whattotaxo.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<i><?php esc_html_e( 'Taxonomy unactivated', 'lumiere-movies' ); ?></i></div>
			<?php } ?>

	</div>
</div>
