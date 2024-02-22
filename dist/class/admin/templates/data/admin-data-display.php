<?php declare( strict_types = 1 );
/**
 * Template for the data taxonomy page
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

// Getting the result of a method.
$lumiere_that = get_transient( 'admin_template_this' )[0];
$lumiere_items_people = get_transient( 'admin_template_this' )[1];
$lumiere_comments_fields = get_transient( 'admin_template_this' )[2];
$lumiere_details_with_numbers = get_transient( 'admin_template_this' )[3];
?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="taxodetails" name="taxodetails"><?php esc_html__( 'What to display', 'lumiere-movies' ); ?></h3>
	</div>
	<br />

	<div class="imblt_border_shadow">

	<div class="lumiere_flex_container lumiere_align_center">
	
	<?php
	foreach ( $lumiere_items_people as $lumiere_item => $lumiere_item_translated ) {

		echo "\n\t\t\t\t" . '<div class="lumiere_flex_container_content_third lumiere_padding_ten lumiere_align_center">';

		// Add extra color through span if the item is selected
		if ( $lumiere_that->imdb_widget_values[ 'imdbwidget' . $lumiere_item ] === '1' ) {

			echo "\n\t\t\t\t\t" . '<span class="admin-option-selected">' . esc_html( ucfirst( $lumiere_item_translated ) ) . '</span>';

		} else {

			echo esc_html( ucfirst( $lumiere_item_translated ) );
			echo '&nbsp;&nbsp;';
		}

		echo "\n\t\t\t\t\t"
			. '<input type="hidden" id="imdb_imdbwidget' . esc_attr( $lumiere_item ) . '_no"'
			. ' name="imdb_imdbwidget' . esc_attr( $lumiere_item ) . '" value="0">';

		echo "\n\t\t\t\t\t" . '<input type="checkbox" id="imdb_imdbwidget' . esc_attr( $lumiere_item ) . '_yes"' .
			' name="imdb_imdbwidget' . esc_attr( $lumiere_item ) . '" value="1"';

		// Add checked if the item is selected
		if ( $lumiere_that->imdb_widget_values[ 'imdbwidget' . $lumiere_item ] === '1' ) {
			echo ' checked="checked"';
		}

		// If item is in list of $details_with_numbers, add special section
		if ( array_key_exists( $lumiere_item, $lumiere_details_with_numbers ) ) {
			echo ' data-checkbox_activate="imdb_imdbwidget' . esc_attr( $lumiere_item ) . 'number_div" />';

			echo "\n\t\t\t\t\t" . '<div id="imdb_imdbwidget' . esc_attr( $lumiere_item ) . 'number_div" class="lumiere_flex_container lumiere_padding_five">';

			echo "\n\t\t\t\t\t\t" . '<div class="lumiere_flex_container_content_seventy lumiere_font_ten">' . esc_html__( 'Enter the maximum of items you want to display', 'lumiere-movies' ) . '<br /></div>';

			echo "\n\t\t\t\t\t\t" . '<div class="lumiere_flex_container_content_twenty">';
			echo "\n\t\t\t\t\t\t\t" . '<input type="text" class="lumiere_width_two_em" name="imdb_imdbwidget' . esc_html( $lumiere_item ) . 'number" id="imdb_imdbwidget' . esc_html( $lumiere_item ) . 'number" size="3"';
			$lumiere_imdb_widget_item = $lumiere_that->imdb_widget_values[ 'imdbwidget' . $lumiere_item . 'number' ];
			echo is_string( $lumiere_imdb_widget_item ) ? ' value="' . esc_attr( $lumiere_imdb_widget_item ) . '" ' : ' value="" ';
			if ( $lumiere_that->imdb_widget_values[ 'imdbwidget' . $lumiere_item ] === 0 ) {
				echo 'disabled="disabled"';
			};

			echo ' />';
			echo "\n\t\t\t\t\t\t" . '</div>';

			echo "\n\t\t\t\t\t" . '</div>';

			// item is not in list of $details_with_numbers
		} else {

			echo ' >';

		}

		echo "\n\t\t\t\t\t" . '<div class="explain">' . esc_html( $lumiere_comments_fields[ $lumiere_item ] ) . '</div>';

		echo "\n\t\t\t\t" . '</div>';
	}

	// Reach a multiple of three for layout
	// Include extra lines if not multiple of three
	$lumiere_operand = ( count( $lumiere_items_people ) / ( count( $lumiere_items_people ) / 3 ) );
	for ( $lumiere_i = 1; $lumiere_i < $lumiere_operand; $lumiere_i++ ) {
		if ( $lumiere_i % 3 !== 0 ) {
			echo "\n\t\t\t\t" . '<div class="lumiere_flex_container_content_third lumiere_padding_ten lumiere_align_center"></div>';
		}
	}
	?>

			</div>
		</div>
	</div>
