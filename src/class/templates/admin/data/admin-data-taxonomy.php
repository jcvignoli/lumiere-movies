<?php declare( strict_types = 1 );
/**
 * Template for the Data admin - Taxonomy data part
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

// Retrieve the vars passed in calling class.
$lum_that = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
$lum_all_taxo_elements = get_transient( Admin_Menu::TRANSIENT_ADMIN )[1];

$lumiere_escape_wp_kses = [
	'br' => [],
	'div' => [
		'id' => [],
		'class' => [],
	],
	'img' => [
		'alt' => [],
		'align' => [],
		'src' => [],
	],
	'i' => [],
	'input' => [
		'type' => [],
		'name' => [],
		'value' => [],
		'id' => [],
		'checked' => [],
	],
	'label' => [
		'for' => [],
	],
	'font' => [ 'color' => [] ],
	'a' => [
		'href' => [],
		'title' => [],
	],
	'span' => [
		'class' => [],
	],
]
?>

<div class="lumiere_wrap">
	<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	
	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="taxodetails" name="taxodetails"><?php esc_html_e( 'Select details to use as taxonomy', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow">

		<div class="lumiere_options_intro_inblock"><?php esc_html_e( "Use the checkbox to display the taxonomy tags. When activated, selected taxonomy will become blue if it is activated in the 'display' section and will turn red otherwise.", 'lumiere-movies' ); ?>
		<br /><br />
		<?php esc_html_e( 'Cautiously select the categories you want to display: it may have some unwanted effects, in particular if you display many movies in the same post at once. When selecting one of the following taxonomy options, it will supersede any other function or link created; for instance, you will not have access anymore to the popups for directors, if directors taxonomy is chosen. Taxonomy will always prevail over other Lumiere functionalities.', 'lumiere-movies' ); ?>

		<br /><br />
		<?php esc_html_e( 'Note: once activated, each taxonomy category will show a new option to copy a taxonomy template directy into your theme folder.', 'lumiere-movies' ); ?>
		</div>
		<br /><br />

		<div class="lumiere_flex_container">
			<?php
			foreach ( $lum_all_taxo_elements as $lum_key => $lum_value ) { ?>

			<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

				<input type="hidden" id="imdb_imdbtaxonomy<?php echo esc_html( $lum_key ) ?>_no" name="imdb_imdbtaxonomy<?php echo esc_html( $lum_key ) ?>" value="0" />

				<input type="checkbox" id="imdb_imdbtaxonomy<?php echo esc_html( $lum_key ) ?>_yes" name="imdb_imdbtaxonomy<?php echo esc_html( $lum_key ) ?>" value="1"<?php
				if ( $lum_that->imdb_data_values[ 'imdbtaxonomy' . $lum_key ] === '1' ) {
					echo ' checked="checked"';
				}
				echo ' />';
				?> 
				<label for="imdb_imdbtaxonomy<?php echo esc_html( $lum_key ) ?>_yes">
				<?php
				if ( $lum_that->imdb_data_values[ 'imdbtaxonomy' . $lum_key ] === '1' ) {
					if ( $lum_that->imdb_data_values[ 'imdbwidget' . $lum_key ] === '1' ) {
						echo "\n\t\t<span class=\"lumiere-option-taxo-activated\">";
					} else {
						echo "\n\t\t<span class=\"lumiere-option-taxo-deactivated\">";
					}

					echo esc_html( ucfirst( $lum_value ) );
					echo '</span>';

				} else {
					echo esc_html( ucfirst( $lum_value ) ) . '&nbsp;&nbsp;';
				}
				echo "\n\t\t</label>";

				// If a new template is available, notify to to update.
				if ( $lum_that->imdb_data_values[ 'imdbtaxonomy' . $lum_key ] === '1' ) {
					echo wp_kses( $lum_that->display_new_taxo( $lum_key ), $lumiere_escape_wp_kses );
				}
				echo "\n\t</div>";

			}
			?>
			<div class="lumiere_flex_container_content_thirty lumiere_padding_five"></div>
		</div>
	</div>
	
	<div class="submit lumiere_sticky_boxshadow lumiere_align_center">
		<?php wp_nonce_field( 'lumiere_nonce_data_settings', '_nonce_data_settings' ); ?>
		<input type="submit" class="button-primary" id="lumiere_update_data_settings" name="lumiere_update_data_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
		<input type="submit" class="button-primary" id="lumiere_reset_data_settings" name="lumiere_reset_data_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
	</div>

	</form>	
</div>
