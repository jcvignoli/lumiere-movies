<?php declare( strict_types = 1 );
/**
 * Template for the first part of the admin menu
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

use Lumiere\Admin\Widget_Selection;
use Lumiere\Config\Get_Options;

// Retrieve the vars from calling class.
$lum_that = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
?>

	<div class="lumiere_wrap">

		<h2 class="lum_padding_bottom_right_fifteen"><img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'lumiere-ico80x80.png' ); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;<i>Lumière!</i>&nbsp;<?php esc_html_e( 'admin options', 'lumiere-movies' ); ?></h2>

		<div class="subpage">
			<div align="left" class="lumiere_flex_container">

				<div class="lumiere_padding_five lumiere_flex_auto">
					<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-main.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'Main Options', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lum_that->page_main_base ); ?>"> <?php esc_html_e( 'Main Options', 'lumiere-movies' ); ?></a>
				</div>

				<?php // Data subpage is relative to what is activated. ?>

				<div class="lumiere_padding_five lumiere_flex_auto">
					<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-widget-inside-movie-items.png' ); ?>" align="absmiddle" width="16px" />&nbsp;


					<a title="<?php esc_html_e( 'Data Management', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lum_that->page_data_movie ); ?>"><?php esc_html_e( 'Data Management', 'lumiere-movies' ); ?></a>

		<?php
		/**
		 * Check if both widgets is are inactive (pre/post-5.8, aka block & legacy blocks)
		 * @psalm-suppress UndefinedClass -- it's defined above! how come it's undefined? Bug, if refreshing cache, the class is found
		 */
		if (
			Widget_Selection::lumiere_block_widget_isactive( Widget_Selection::BLOCK_WIDGET_NAME ) === false
			&& is_active_widget( false, false, Widget_Selection::WIDGET_NAME, false ) === false
		) { ?>

			- <em><font size=-2><a href="<?php echo esc_url( admin_url() . 'widgets.php' ); ?>"><?php esc_html_e( 'Widget unactivated', 'lumiere-movies' ); ?></a></font></em>
			
			<?php
		}
		if ( $lum_that->imdb_admin_values['imdbtaxonomy'] === '0' ) {

			?> - <em><font size=-2><a href="<?php echo esc_url( admin_url() . $lum_that->page_main_advanced . '#imdb_imdbtaxonomy_yes' ); ?>"><?php esc_html_e( 'Taxonomy unactivated', 'lumiere-movies' ); ?></font></em>

	<?php } ?>

				</div>

				<div class="lumiere_padding_five lumiere_flex_auto">			
					<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-cache.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'Cache management', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lum_that->page_cache_option ); ?>"><?php esc_html_e( 'Cache management', 'lumiere-movies' ); ?></a>
				</div>

				<div align="right" class="lumiere_padding_five lumiere_flex_auto" >
					<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-help.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'How to use Lumière!, check FAQs & changelog', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lum_that->page_help ); ?>">
						<i>Lumière!</i> <?php esc_html_e( 'help', 'lumiere-movies' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
