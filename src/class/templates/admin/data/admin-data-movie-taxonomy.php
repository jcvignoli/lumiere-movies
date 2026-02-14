<?php declare( strict_types = 1 );
/**
 * Template for the Data admin - Taxonomy data part
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

use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;

// Get vars from the calling class.
$lumiere_that = $variables['lum_that']; /** @phpstan-ignore variable.undefined  */
$lumiere_all_taxo_elements = $variables['lum_all_taxo_elements']; /** @phpstan-ignore variable.undefined  */
$lumiere_fields_updated = $variables['lum_fields_updated']; /** @phpstan-ignore variable.undefined  */
$lumiere_current_admin_page = $variables['lum_current_admin_page']; /** @phpstan-ignore variable.undefined  */

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
];

// taxonomy is disabled
if ( $lumiere_that->imdb_admin_values['imdbtaxonomy'] !== '1' ) {
	?><br><br><div align="center" class="accesstaxo"><?php
		echo wp_kses(
			wp_sprintf(
				/* translators: %1$s and %2$s are replaced with html ahref tags */
				__( 'Please %1$sactivate taxonomy%2$s before accessing to taxonomy options.', 'lumiere-movies' ),
				'<a href="' . esc_url( $lumiere_that->page_main_advanced ) . '#imdb_imdbtaxonomy_yes">',
				'</a>'
			),
			[ 'a' => [ 'href' => [] ] ]
		);

	?></div><?php
	return;
} ?>

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
			foreach ( $lumiere_all_taxo_elements as $lumiere_key => $lumiere_value ) { ?>

			<div class="lumiere_flex_container_content_thirty lumiere_padding_five">
				<input type="hidden" id="imdb_imdbtaxonomy<?php echo esc_html( $lumiere_key ) ?>_no" name="imdb_imdbtaxonomy<?php echo esc_html( $lumiere_key ) ?>" value="0" />
				<input type="checkbox" id="imdb_imdbtaxonomy<?php echo esc_html( $lumiere_key ) ?>_yes" name="imdb_imdbtaxonomy<?php echo esc_html( $lumiere_key ) ?>" value="1"<?php
				if ( $lumiere_that->imdb_data_values[ 'imdbtaxonomy' . $lumiere_key ] === '1' ) {
					echo ' checked="checked"';
				}
				echo ' />';

				?>
				
				<label for="imdb_imdbtaxonomy<?php echo esc_html( $lumiere_key ) ?>_yes"><?php

				if ( $lumiere_that->imdb_data_values[ 'imdbtaxonomy' . $lumiere_key ] === '1' ) {
					if ( $lumiere_that->imdb_data_values[ 'imdbwidget' . $lumiere_key ] === '1' ) {
						?><span class="lumiere-option-taxo-activated"><?php
					} else {
						?><span class="lumiere-option-taxo-deactivated"><?php
					}
					echo esc_html( ucfirst( $lumiere_value ) );
					?></span>

				<?php } else {
					echo "\t" . esc_html( ucfirst( $lumiere_value ) ) . '&nbsp;&nbsp;';
				}

				?></label><?php


				// If a new template is available, notify to to update.
if ( $lumiere_that->imdb_data_values[ 'imdbtaxonomy' . $lumiere_key ] === '1' ) {
	$lumiere_link_taxo_copy = add_query_arg( '_wpnonce_linkcopytaxo', wp_create_nonce( 'linkcopytaxo' ), $lumiere_current_admin_page . $lumiere_key );
	$lumiere_file_in_stylesheet_path = get_stylesheet_directory() . '/' . Get_Options::LUM_THEME_TAXO_FILENAME_START . $lumiere_that->imdb_admin_values['imdburlstringtaxo'] . $lumiere_key . '.php';
	$lumiere_translated_item = Get_Options_Movie::get_all_fields()[ $lumiere_key ];

	// No field to update found and no template to be updated found, offer to copy .
	if ( count( $lumiere_fields_updated ) === 0 && is_file( $lumiere_file_in_stylesheet_path ) === false ) {

		?><br>
						<div id="lumiere_copy_<?php echo esc_html( $lumiere_key ); ?>">
						<a href="<?php echo esc_html( $lumiere_link_taxo_copy ); ?>" title="<?php esc_html_e( 'Create a taxonomy template into your theme folder.', 'lumiere-movies' ); ?>"><img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-widget-copy-theme.png' ); ?>" alt="copy the taxonomy template" align="absmiddle" /><?php esc_html_e( 'Copy template', 'lumiere-movies' ); ?></a>
						<div><span class="lum_color_red"><?php

						/* translators: %s is replaced with a movie item name, ie 'director' */
						echo wp_sprintf( esc_html__( 'No %s template found', 'lumiere-movies' ), esc_html( $lumiere_translated_item ) );
						?>
						</span></div>
						</div>
					<?php
					// Template file exists and need to be updated, notify there is a new version of the template and exit.
	} elseif ( count( $lumiere_fields_updated ) > 0 && in_array( $lumiere_key, $lumiere_fields_updated, true ) && is_file( $lumiere_file_in_stylesheet_path ) === true ) { ?>

				<br>
				<div id="lumiere_copy__<?php echo esc_html( $lumiere_key ); ?>">
				<a href="<?php echo esc_html( $lumiere_link_taxo_copy ); ?>" title="<?php esc_html_e( 'Update your taxonomy template in your theme folder.', 'lumiere-movies' ); ?>"><img src="<?php echo esc_url( Get_Options::LUM_PICS_URL . 'menu/admin-widget-copy-theme.png' ); ?>" alt="copy the taxonomy template" align="absmiddle"><?php esc_html_e( 'Update template', 'lumiere-movies' ); ?></a>
				<div>
					<span class="lum_color_red"><?php
					/* translators: %s is replaced with a movie item name, ie 'director' */
					echo wp_sprintf( esc_html__( 'New %s template version available', 'lumiere-movies' ), esc_html( $lumiere_translated_item ) ); ?>
				
					</span>
				</div>
			</div>
					<?php
					// No template updated, template file exists, so it is up-to-date, notify.
	} elseif ( is_file( $lumiere_file_in_stylesheet_path ) === true ) {
		?>
						
				<br />
				<div>
					<i><?php echo /* translators: %s is replaced with a movie item name, ie 'director' */
					wp_sprintf( esc_html__( 'Template %s up-to-date', 'lumiere-movies' ), esc_html( $lumiere_translated_item ) ); ?></i>
				</div><?php

	}

} ?>

			</div><?php
			} ?>

			<div class="lumiere_flex_container_content_thirty lumiere_padding_five"></div>
		</div>
	</div>
	
	<div class="submit lumiere_sticky_boxshadow lumiere_align_center">
		<?php wp_nonce_field( 'lumiere_nonce_data_settings', '_nonce_data_settings' ); ?>
		<input type="submit" class="button-primary" id="lumiere_update_data_movie_settings" name="lumiere_update_data_movie_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
		<input type="submit" class="button-primary" id="lumiere_reset_data_movie_settings" name="lumiere_reset_data_movie_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
	</div>

	</form>	
</div>
