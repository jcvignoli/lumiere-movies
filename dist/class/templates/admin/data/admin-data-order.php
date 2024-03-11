<?php declare( strict_types = 1 );
/**
 * Template for the Data admin - Order of the data part
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
<div class="lumiere_wrap">
	<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	
	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="taxoorder" name="taxoorder"><?php esc_html_e( 'Position of data', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow lumiere_align_webkit_center">

		<div class="lumiere_options_intro_inblock">
			<?php esc_html_e( 'You can select the order for the information selected in "display" section. Select first the movie detail you want to move, use "up" or "down" to reorder Lumiere Movies display. Once you are happy with the new order, click on "update settings" to keep it.', 'lumiere-movies' ); ?>
		</div>

		<div id="container_imdbwidgetorderContainer" class="imdblt_double_container imdblt_padding_top_twenty lumiere_align_center">

			<div class="lumiere_padding_ten imdblt_align_last_center lumiere_flex_auto">

				<input type="button" value="up" name="movemovieup" id="movemovieup" data-moveform="-1" /> 

				<input type="button" value="down" name="movemoviedown" id="movemoviedown" data-moveform="+1" />

				<div><?php esc_html_e( 'Move selected movie detail:', 'lumiere-movies' ); ?></div>

				<input type="hidden" name="imdb_imdbwidgetorder" id="imdb_imdbwidgetorder" value="" class="lumiere_hidden" />
			</div>

			<div class="lumiere_padding_ten imdblt_align_last_center lumiere_flex_auto">
				<select id="imdbwidgetorderContainer" name="imdbwidgetorderContainer[]" class="imdbwidgetorderContainer" size="<?php echo ( count( $lumiere_that->imdb_widget_values['imdbwidgetorder'] ) / 2 ); ?>" multiple><?php

				foreach ( $lumiere_that->imdb_widget_values['imdbwidgetorder'] as $lumiere_key => $lumiere_value ) {

					echo "\n\t\t\t\t<option value='" . esc_attr( $lumiere_key ) . "'";

					// search if "imdbwidget'title'" (ie) is activated
					if ( $lumiere_that->imdb_widget_values[ "imdbwidget$lumiere_key" ] !== '1' ) {

						echo ' label="' . esc_attr( $lumiere_key ) . ' (unactivated)">' . esc_html( $lumiere_key );

					} else {

						echo ' label="' . esc_attr( $lumiere_key ) . '">' . esc_html( $lumiere_key );

					}
					echo '</option>';
				}
				?>

				</select>
			</div>

		</div>
	</div>
	
	<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">
	
		<?php wp_nonce_field( 'lumiere_nonce_data_settings', '_nonce_data_settings' ); ?>
		
		<input type="submit" class="button-primary" name="lumiere_reset_data_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
		<input type="submit" class="button-primary" id="lumiere_update_data_settings" name="lumiere_update_data_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />
	</div>

	</form>	
</div>
