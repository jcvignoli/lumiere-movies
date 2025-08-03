<?php declare( strict_types = 1 );
/**
 * Template for the submenu of help pages
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
$lumiere_pics_url = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
$lumiere_admin_page_help_base = get_transient( 'admin_template_pass_vars' )[1];
$lumiere_admin_page_help_support = get_transient( 'admin_template_pass_vars' )[2];
$lumiere_admin_page_help_faqs = get_transient( 'admin_template_pass_vars' )[3];
$lumiere_admin_page_help_filters = get_transient( 'admin_template_pass_vars' )[4];
$lumiere_admin_page_help_compatibility = get_transient( 'admin_template_pass_vars' )[5];
$lumiere_admin_page_help_changelog = get_transient( 'admin_template_pass_vars' )[6];
?>

<div id="tabswrap" class="lumiere_wrap">
	<div class="lumiere_flex_container lumiere_padding_five">

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $lumiere_pics_url . 'menu/admin-help-howto.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'How to use Lumiere Movies', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_admin_page_help_base ); ?>"><?php esc_html_e( 'How to', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $lumiere_pics_url . 'menu/admin-help-faq.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Frequently asked questions', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_admin_page_help_faqs ); ?>"><?php esc_html_e( 'FAQs', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $lumiere_pics_url . 'menu/admin-help-changelog.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( "What's new", 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_admin_page_help_changelog ); ?>"><?php esc_html_e( 'Changelog', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $lumiere_pics_url . 'menu/admin-help-filters.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'WordPress filters', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_admin_page_help_filters ); ?>"><?php esc_html_e( 'Hooks', 'lumiere-movies' ); ?></a></div>
		
		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $lumiere_pics_url . 'menu/admin-cache-compatibility.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Plugins Lumière is compatible with', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_admin_page_help_compatibility ); ?>"><?php esc_html_e( 'Compatibility', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $lumiere_pics_url . 'menu/admin-help-support.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Get support and support me', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_admin_page_help_support ); ?>"><?php esc_html_e( 'Support, donate & credits', 'lumiere-movies' ); ?></a></div>

	</div>
</div>
