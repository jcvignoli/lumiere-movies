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

// Can't be certain that $this exists, creating $lumiere_that using transients sent by calling class
$lumiere_that = get_transient( 'admin_template_this' );
delete_transient( 'admin_template_this' );
?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="taxoorder" name="taxoorder"><?php esc_html_e( 'Position of data', 'lumiere-movies' ); ?></h3>
	</div>

	<br />

	<div class="imblt_border_shadow imdblt_align_webkit_center">

	<div class="lumiere_intro_options_small">
		<?php esc_html_e( 'You can select the order for the information selected in "display" section. Select first the movie detail you want to move, use "up" or "down" to reorder Lumiere Movies display. Once you are happy with the new order, click on "update settings" to keep it.', 'lumiere-movies' ); ?>
	</div>

	<div id="container_imdbwidgetorderContainer" class="imdblt_double_container imdblt_padding_top_twenty lumiere_align_center">

		<div class="imdblt_padding_ten imdblt_align_last_center imdblt_flex_auto">

			<input type="button" value="up" name="movemovieup" id="movemovieup" data-moveform="-1" /> 

			<input type="button" value="down" name="movemoviedown" id="movemoviedown" data-moveform="+1" />

			<div><?php esc_html_e( 'Move selected movie detail:', 'lumiere-movies' ); ?></div>

			<input type="hidden" name="imdb_imdbwidgetorder" id="imdb_imdbwidgetorder" value="" class="imdblt_hidden" />
		</div>

		<div class="imdblt_padding_ten imdblt_align_last_center imdblt_flex_auto">
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
