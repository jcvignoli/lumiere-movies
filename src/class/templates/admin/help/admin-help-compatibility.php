<?php declare( strict_types = 1 );
/**
 * Template for the Compatibility in help
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

/**
 * HTML allowed for use of wp_kses()
 */
$lumiere_escape_wp_kses = [
	'i' => [],
	'strong' => [],
	'div' => [ 'class' => [] ],
	'b' => [],
	'a' => [
		'id' => [],
		'href' => [],
		'title' => [],
		'data-*' => [],
	],
	'font' => [
		'size' => [],
	],
	'blockquote' => [ 'class' => [] ],
	'br' => [],
];

// Get vars from the calling class.
$lumiere_compatsection_processed = $variables['compat_section']; /** @phpstan-ignore variable.undefined  */

?>

<div class="lumiere_wrap">

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="layout" name="layout"><?php esc_html_e( 'Plugin compatibility list', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow helpdiv">
		<?php
		$lumiere_count_compat = count( $lumiere_compatsection_processed );

		// $lumiere_i starts at one in order to skip first line with "**Changelog**"
		for ( $lumiere_i = 1; $lumiere_i < $lumiere_count_compat; $lumiere_i++ ) {  ?>

		<div><?php
		$lumiere_compat_text = is_string( $lumiere_compatsection_processed[ $lumiere_i ] ) ? $lumiere_compatsection_processed[ $lumiere_i ] : '';
		echo wp_kses( str_replace( "\n", '', $lumiere_compat_text ), $lumiere_escape_wp_kses );
		?></div>

			<?php
		}
		?>
	</div>
</div>
