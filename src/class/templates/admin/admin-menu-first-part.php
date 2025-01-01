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
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Widget_Selection;

// Retrieve the vars from calling class.
$lum_that = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
?>

	<div class="lumiere_wrap">

		<h2 class="lum_padding_bottom_right_fifteen"><img src="<?php echo esc_url( $lum_that->config_class->lumiere_pics_dir . 'lumiere-ico80x80.png' ); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;<i>Lumière!</i>&nbsp;<?php esc_html_e( 'admin options', 'lumiere-movies' ); ?></h2>

		<div class="subpage">
			<div align="left" class="lumiere_flex_container">

				<div class="lumiere_padding_five lumiere_flex_auto">
					<img src="<?php echo esc_url( $lum_that->config_class->lumiere_pics_dir . 'menu/admin-general.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'General Options', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lum_that->page_general_base ); ?>"> <?php esc_html_e( 'General Options', 'lumiere-movies' ); ?></a>
				</div>

				<?php // Data subpage is relative to what is activated. ?>

				<div class="lumiere_padding_five lumiere_flex_auto">
					<img src="<?php echo esc_url( $lum_that->config_class->lumiere_pics_dir . 'menu/admin-widget-inside.png' ); ?>" align="absmiddle" width="16px" />&nbsp;


					<a title="<?php esc_html_e( 'Data Management', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lum_that->page_data ); ?>"><?php esc_html_e( 'Data Management', 'lumiere-movies' ); ?></a>

		<?php
		// Check if both widgets is are inactive (pre/post-5.8, aka block & legacy blocks)
		if (
			Widget_Selection::lumiere_block_widget_isactive( Widget_Selection::BLOCK_WIDGET_NAME ) === false
			&& is_active_widget( false, false, Widget_Selection::WIDGET_NAME, false ) === false
		) { ?>

			- <em><font size=-2><a href="<?php echo esc_url( admin_url() . 'widgets.php' ); ?>"><?php esc_html_e( 'Widget unactivated', 'lumiere-movies' ); ?></a></font></em>
			
			<?php
		}
		if ( $lum_that->imdb_admin_values['imdbtaxonomy'] === '0' ) {

			?> - <em><font size=-2><a href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=advanced#imdb_imdbtaxonomy_yes' ); ?>"><?php esc_html_e( 'Taxonomy unactivated', 'lumiere-movies' ); ?></font></em>

	<?php } ?>

				</div>

				<div class="lumiere_padding_five lumiere_flex_auto">			
					<img src="<?php echo esc_url( $lum_that->config_class->lumiere_pics_dir . 'menu/admin-cache.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'Cache management', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lum_that->page_cache_option ); ?>"><?php esc_html_e( 'Cache management', 'lumiere-movies' ); ?></a>
				</div>

				<div align="right" class="lumiere_padding_five lumiere_flex_auto" >
					<img src="<?php echo esc_url( $lum_that->config_class->lumiere_pics_dir . 'menu/admin-help.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'How to use Lumière!, check FAQs & changelog', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lum_that->page_help ); ?>">
						<i>Lumière!</i> <?php esc_html_e( 'help', 'lumiere-movies' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
