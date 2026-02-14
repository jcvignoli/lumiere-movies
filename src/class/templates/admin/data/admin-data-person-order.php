<?php declare( strict_types = 1 );
/**
 * Template for the Data admin - Order of the data part
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

// Get vars from the calling class.
$lumiere_that = $variables['lum_that']; /** @phpstan-ignore variable.undefined  */
$lumiere_perso_list = Get_Options_Person::get_all_person_fields();
?>
<div class="lumiere_wrap">
	<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	
	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="taxoorder" name="taxoorder"><?php esc_html_e( 'Position of person data', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow lumiere_align_webkit_center">

		<div class="lumiere_options_intro_inblock">
			<?php esc_html_e( 'You can select the order for the information selected in "display" section. Select first the movie detail you want to move, use "up" or "down" to reorder Lumiere Movies display. Once you are happy with the new order, click on "update settings" to keep it.', 'lumiere-movies' ); ?>
		</div>

		<div id="container_imdbwidgetorderContainer" class="lumiere_flex_container lum_padding_top_twenty lumiere_align_center">
			<div class="lumiere_padding_ten lum_align_last_center lumiere_flex_auto">
				<div><?php esc_html_e( 'Move selected movie detail:', 'lumiere-movies' ); ?></div>
				<input type="button" value="<?php esc_html_e( 'up', 'lumiere-movies' ); ?>" data-container-id="person_order" name="movemovieup" id="movemovieup" data-moveform="-1" /> 
				<input type="button" value="<?php esc_html_e( 'down', 'lumiere-movies' ); ?>" data-container-id="person_order" name="movemoviedown" id="movemoviedown" data-moveform="+1" />

			</div>

			<div class="lumiere_padding_ten lum_align_last_center lumiere_flex_auto">
				<select id="person_order" name="person_order[]" class="person_order" size="<?php echo ( count( $lumiere_that->imdb_data_person_values['order'] ) / 2 ); ?>" multiple><?php

				foreach ( $lumiere_that->imdb_data_person_values['order'] as $lumiere_key => $lumiere_value ) {
					// Do not use unactivated functions. Those methods do not exists in \IMDB\Name, but exist as modules.
					if ( in_array( $lumiere_key, Get_Options_Person::LUM_DATA_PERSON_NO_METHOD, true ) ) {
						continue;
					}
					echo "\n\t\t\t\t<option value='" . esc_attr( $lumiere_key ) . "'";

					if ( $lumiere_that->imdb_data_person_values['activated'][ $lumiere_key . '_active' ] !== '1' ) {
						echo ' label="' . esc_attr( $lumiere_key ) . ' (' . esc_html__( 'unactivated', 'lumiere-movies' ) . ')">' . esc_html( $lumiere_key );
					} else {
						echo ' label="' . esc_attr( $lumiere_perso_list [ $lumiere_key ] ) . '">' . esc_html( $lumiere_key );
					}
					echo '</option>';
				}
				?>

				</select>
			</div>
		</div>
	</div>
	
	<div class="submit lumiere_sticky_boxshadow lumiere_align_center">
		<?php wp_nonce_field( 'lumiere_nonce_data_person_settings', '_nonce_data_person_settings' ); ?>
		<input type="submit" class="button-primary" id="lumiere_update_data_person_settings" name="lumiere_update_data_person_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
		<input type="submit" class="button-primary" id="lumiere_reset_data_person_settings" name="lumiere_reset_data_person_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
	</div>

	</form>	
</div>
