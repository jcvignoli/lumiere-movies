<?php declare( strict_types = 1 );
/**
 * Template for the Data admin - Display data part
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

use \Lumiere\Config\Get_Options_Person;
use \Lumiere\Config\Get_Options;

// Get vars from the calling class.
$lumiere_calling_class = $variables['lum_calling_class']; /** @phpstan-ignore variable.undefined  */
$lumiere_imdb_data_values = $lumiere_calling_class->imdb_data_person_values;
$lumiere_perso_list = Get_Options_Person::get_all_person_fields();
asort( $lumiere_perso_list );
$lumiere_comments_fields = Get_Options_Person::get_items_person_details_comments();
?>
<div class="lumiere_wrap">
	<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

	<div class="lumiere_title_options lumiere_border_shadow lumiere_flex_container lum_flex_space_evenly">
		<h3 id="person_display" name="person_display" class=""><?php esc_html_e( 'Persons items to display', 'lumiere-movies' ); ?></h3>

		<div class="">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-widget-inside-order.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'Movie items order', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_calling_class->page_data_person_order ); ?>"><?php esc_html_e( 'Items order', 'lumiere-movies' ); ?></a></div>
<!-- not yet active 
			<?php if ( $lumiere_calling_class->imdb_admin_values['imdbtaxonomy'] === '1' ) { ?>
		<div class=" lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-widget-inside-movie-taxonomy.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'Movie items to use as taxonomy', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $lumiere_calling_class->page_data_movie_taxo ); ?>"><?php esc_html_e( 'Taxonomy', 'lumiere-movies' ); ?></a></div>
			<?php } else { ?>
		<div class="lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-widget-inside-movie-taxonomy.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<i><?php esc_html_e( 'Taxonomy unactivated', 'lumiere-movies' ); ?></i></div>
			<?php } ?>
-->
	</div>

	<div class="lumiere_border_shadow">

		<div class="lumiere_flex_container lumiere_align_center">
		
		<?php

		foreach ( $lumiere_perso_list as $lumiere_item => $lumiere_item_translated ) {

			// Do not display in the selection neither title nor pic
			if ( in_array( $lumiere_item, Get_Options_Person::LUM_DATA_PERSON_NO_METHOD, true ) === true  ) {
				continue;
			}

			echo "\n\t\t\t\t" . '<div class="lumiere_flex_container_content_thirty lumiere_padding_ten lumiere_align_center">';

			// Add extra color through span if the item is selected
			if ( isset( $lumiere_imdb_data_values['activated'][ $lumiere_item . '_active' ] ) && $lumiere_imdb_data_values['activated'][ $lumiere_item . '_active' ] === '1' ) {
				echo "\n\t\t\t\t\t" . '<span class="admin-option-selected">' . esc_html( ucfirst( $lumiere_item_translated ) ) . '</span>';
			} else {
				echo esc_html( ucfirst( $lumiere_item_translated ) );
				echo '&nbsp;&nbsp;';
			}

			echo "\n\t\t\t\t\t"
				. '<input type="hidden" id="' . esc_attr( $lumiere_item ) . '_active_no"'
				. ' name="' . esc_attr( $lumiere_item ) . '_active" value="0">';

			echo "\n\t\t\t\t\t" . '<input type="checkbox" id="' . esc_attr( $lumiere_item ) . '_active_yes"' .
				' name="' . esc_attr( $lumiere_item ) . '_active" value="1"';

			// Add checked if the item is selected
			if ( isset( $lumiere_imdb_data_values['activated'][ $lumiere_item . '_active' ] ) && $lumiere_imdb_data_values['activated'][ $lumiere_item . '_active' ] === '1' ) {
				echo ' checked="checked"';
			}

			// If item is in list of $details_with_numbers, add special section
			if ( in_array( $lumiere_item, array_keys( Get_Options_Person::LUM_DATA_PERSON_DEFAULT_WITHNUMBER ), true ) ) {
				echo ' data-checkbox_activate="' . esc_attr( $lumiere_item ) . 'number_div" />';

				echo "\n\t\t\t\t\t" . '<div id="' . esc_attr( $lumiere_item ) . 'number_div" class="lumiere_flex_container lumiere_padding_five">';

				$lumiere_isset_items_trans_plural = Get_Options_Person::get_all_person_fields( 2 );
				$lumiere_items_trans_plural = $lumiere_isset_items_trans_plural[ $lumiere_item ] ?? '';
				echo "\n\t\t\t\t\t\t" . '<div class="lumiere_flex_container_content_seventy lumiere_font_ten_proportional lum_align_right">'
				/* translators: %s is a movie items like 'directors' or 'colors' => always plural */
				. wp_sprintf( esc_html__( 'Enter the maximum number of %s you want to display', 'lumiere-movies' ), esc_html( $lumiere_items_trans_plural ) ) . '<br /></div>';

				echo "\n\t\t\t\t\t\t" . '<div class="lumiere_flex_container_content_twenty">';
				echo "\n\t\t\t\t\t\t\t" . '<input type="text" class="lumiere_width_two_em" name="' . esc_html( $lumiere_item ) . '_number" id="' . esc_html( $lumiere_item ) . '_number" size="3"';
				$lumiere_imdb_data_item = $lumiere_imdb_data_values['number'][ $lumiere_item . '_number' ];
				echo is_string( $lumiere_imdb_data_item ) ? ' value="' . esc_attr( $lumiere_imdb_data_item ) . '" ' : ' value="" ';
				if ( $lumiere_imdb_data_values['number'][ $lumiere_item . '_number' ] === 0 ) {
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
		$lumiere_operand = ( (float) count( $lumiere_perso_list ) / ( (float) count( $lumiere_perso_list ) / (float) 3 ) );
		for ( $lumiere_i = 1; $lumiere_i < $lumiere_operand; $lumiere_i++ ) {
			if ( $lumiere_i % 3 !== 0 ) {
				echo "\n\t\t\t\t" . '<div class="lumiere_flex_container_content_thirty lumiere_padding_ten lumiere_align_center"></div>';
			}
		}?>

		</div>
	</div>

	<div class="submit lumiere_sticky_boxshadow lumiere_align_center">
		<?php wp_nonce_field( 'lumiere_nonce_data_person_settings', '_nonce_data_person_settings' ); ?>
		<input type="submit" class="button-primary" id="lumiere_update_data_person_settings" name="lumiere_update_data_person_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
		<input type="submit" class="button-primary" id="lumiere_reset_data_person_settings" name="lumiere_reset_data_person_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
	</div>
	
	</form>	
</div>
