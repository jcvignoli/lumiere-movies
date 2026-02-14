<?php declare( strict_types = 1 );
/**
 * Class for displaying Link_Maker layout.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Layout;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Layout\Output;

/**
 * Layouts for Link_Maker system
 *
 * @see \Lumiere\Frontend\Link_Maker\Implement_Link_Maker calling class
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @since 4.5
 * @since 4.6.1 Removed everything about bootstrap_modal() function, taken care in lumiere-bootstrap-links.js
 */
final class Output_Linkmaker extends Output {

	/**
	 * Display Linkmaker layouts
	 *
	 * @param string $selector Select which column to return
	 * @param string $text_one Optional, an extra text to use
	 * @param string $text_two Optional, an extra text to use
	 * @param string $text_three Optional, an extra text to use
	 * @return string
	 */
	public function main_layout( string $selector, string $text_one = '', string $text_two = '', string $text_three = '' ): string {
		$container = [
			'item_picture'            => "\n\t\t\t<div class=\"imdbelementPIC\">" . $text_one . "\n\t\t\t</div>",
		];
		return $container[ $selector ];
	}
}
