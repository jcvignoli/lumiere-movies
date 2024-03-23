<?php declare( strict_types = 1 );
/**
 * Template for the submenu of cache pages
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
$lumiere_that = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
?>

<div id="tabswrap" class="lumiere_wrap">

	<div class="lumiere_flex_container lumiere_padding_five">
		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $lumiere_that->config_class->lumiere_pics_dir . 'menu/admin-cache-options.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Cache options', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_that->page_cache_option ); ?>"><?php esc_html_e( 'Cache options', 'lumiere-movies' ); ?></a></div>
		<?php
		if ( $lumiere_that->imdb_cache_values['imdbusecache'] === '1' ) {
			?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $lumiere_that->config_class->lumiere_pics_dir . 'menu/admin-cache-management.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Manage Cache', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_that->page_cache_manage ); ?>"><?php esc_html_e( 'Manage Cache', 'lumiere-movies' ); ?></a></div>
			<?php
		};
		?>
	</div>
</div>

<div class="lumiere_intro_title_options lumiere_wrap">
	<?php esc_html_e( 'Cache is crucial for Lumière! operations. Initial IMDb queries are quite time consuming, so if you do not want to kill your server and look for a smooth experience for your users, do not delete often your cache.', 'lumiere-movies' ); ?>
</div>
