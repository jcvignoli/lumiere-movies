<?php declare( strict_types = 1 );
/**
 * Render functions that will be used on the frontend only
 */
namespace Lumiere;

/**
 * Callback function to render the Lumière Widget Block dynamically.
 * It embeds the text (widget title) into [lumiereWidget][/lumiereWidget]
 * These tags will trigger the display of movie/person data in the widget when displaying in sidebars, according to Widget_Selection class
 *
 * @param array<string, string|'lumiere_input'> $attributes Block attributes from the editor.
 * @return string Rendered HTML for the block.
 *
 * @see Lumiere\Admin\Widget_Selection::lumiere_register_widget_block() Includes this file
 */
function lum_render_block_widget( array $attributes ): string {

	// Return the rendered HTML.
	return sprintf(
		'<div class="wp-block-lumiere-widget">%s</div>',
		'[lumiereWidget]' . esc_html( $attributes['lumiere_input'] ) . '[/lumiereWidget]'
	);
}
