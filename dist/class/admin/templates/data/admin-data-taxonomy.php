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
$taxo_fields = get_transient( 'admin_taxo_fields' );
delete_transient( 'admin_taxo_fields' );
$escape_wp_kses = [
	'br' => [],
	'div' => [
		'id' => true,
		'class' => true,
	],
	'img' => [
		'alt' => true,
		'align' => true,
		'src' => true,
	],
	'i' => [],
	'input' => [
		'type' => true,
		'name' => true,
		'value' => true,
		'id' => true,
		'checked' => true,
	],
	'label' => [
		'for' => true,
	],
	'font' => [ 'color' => true ],
	'a' => [
		'href' => true,
		'title' => true,
	],
	'span' => [
		'class' => true,
	],
]
?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="taxodetails" name="taxodetails"><?php esc_html_e( 'Select details to use as taxonomy', 'lumiere-movies' ); ?></h3>
	</div>
	<br />

	<div class="imblt_border_shadow">

		<div class="lumiere_intro_options"><?php esc_html_e( "Use the checkbox to display the taxonomy tags. When activated, selected taxonomy will become blue if it is activated in the 'display' section and will turn red otherwise.", 'lumiere-movies' ); ?>
		<br /><br />
		<?php esc_html_e( 'Cautiously select the categories you want to display: it may have some unwanted effects, in particular if you display many movies in the same post at once. When selecting one of the following taxonomy options, it will supersede any other function or link created; for instance, you will not have access anymore to the popups for directors, if directors taxonomy is chosen. Taxonomy will always prevail over other Lumiere functionalities.', 'lumiere-movies' ); ?>

		<br /><br />
		<?php esc_html_e( 'Note: once activated, each taxonomy category will show a new option to copy a taxonomy template directy into your theme folder.', 'lumiere-movies' ); ?>
		</div>
		<br /><br />

		<div class="imdblt_double_container">
			<?php echo wp_kses( $taxo_fields, $escape_wp_kses ); ?>
			<div class="imdblt_double_container_content_third lumiere_padding_five"></div>
		</div>
	</div>
