<?php declare( strict_types = 1 );
/**
 * Text that will be displayed on frontend only
 */
namespace Lumiere;

use Lumiere\Frontend\Widget\Widget_Frontpage;

/**
 * Callback function to render the Lumière Widget Block dynamically
 *
 * @param array<string, string|'lumiere_input'> $attributes Block attributes from the editor
 * @return string Rendered HTML for the block
 */
function lum_render_block_widget( array $attributes ): string {
	ob_start();
	if ( class_exists( Widget_Frontpage::class ) ) {
		$widget_class = new Widget_Frontpage();
		echo wp_kses(
			$widget_class->lum_get_widget( esc_html( $attributes['lumiere_input'] ) ),
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
	}
	$end = ob_get_clean();
	return $end !== false ? $end : '';
}
