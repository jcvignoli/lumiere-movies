<?php declare( strict_types = 1 );
/**
 * Text that will be displayed on frontend only
 * @since 4.7 New file
 */
namespace Lumiere;

use Lumiere\Frontend\Calendar\Coming_Soon;

if ( ! function_exists( 'lum_render_block_coming_soon' ) ) {
	/**
	 * Callback function to render the Lumière ComingSoon block dynamically
	 * Using register_block_type() and block.json
	 *
	 * @param array<string, int|string> $attributes Block attributes from the editor
	 * @phpstan-param array{ region: string, type: string, startDateOverride: int, endDateOverride: int } $attributes
	 * @return string Rendered HTML for the block
	 */
	function lum_render_block_coming_soon( array $attributes ): string {
		ob_start();
		if ( class_exists( Coming_Soon::class ) ) {
			Coming_Soon::init(
				$attributes['region'],
				$attributes['type'],
				$attributes['startDateOverride'],
				$attributes['endDateOverride']
			);
		}
		$end = ob_get_clean();
		return $end !== false ? $end : '';
	};
}
