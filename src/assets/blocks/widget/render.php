<?php declare( strict_types = 1 );
/**
 * Text that will be displayed on frontend only
 * @since 4.7 Display the code without function (as simplified the use of register_block_type() and removed render_callback)
 */
namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Widget\Widget_Frontpage;

$lumiere_widget_class = new Widget_Frontpage();
echo wp_kses(
	$lumiere_widget_class->lum_get_widget( esc_html( $attributes['lumiere_input'] ?? 'Lumière Movies' ) ),
	[
		'a' => [
			'data-*' => true,
			'id' => [],
			'class' => [],
			'href' => [],
			'title' => [],
		],
		'div' => [
			'id' => [],
			'class' => [],
		],
		'span' => [
			'id' => [],
			'class' => [],
		],
		'h4' => [
			'id' => [],
			'class' => [],
		],
		'img' => [
			'decoding' => [],
			'loading' => [],
			'class' => [],
			'alt' => [],
			'src' => [],
			'width' => [],
		],
	]
);

