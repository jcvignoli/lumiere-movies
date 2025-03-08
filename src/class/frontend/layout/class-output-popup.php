<?php declare( strict_types = 1 );
/**
 * Class for displaying popups' layout.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Layout;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Layout\Output;

/**
 * Layouts for popups
 *
 * @since 4.4.3
 */
class Output_Popup extends Output {

	/**
	 * Embed the elements
	 * @see \Lumiere\Frontend\Popup\Popup_Film in Modules Movie
	 *
	 * @param string $text The text to be embeded in the layout
	 * @param string $item The name of the item, ie 'director'
	 */
	public function movie_element_embeded( string $text, string $item ): string {
		return "\n\t\t\t\t\t\t\t\t\t\t<!-- " . ucfirst( $item ) . ' -->'
			. "\n\t<div>" . $text . "\n\t</div>";
	}

	/**
	 * Embed the titles
	 * @see \Lumiere\Frontend\Popup\Popup_Person in Modules Person
	 *
	 * @param string $text The text to be embeded in the layout
	 * @param string $item The name of the item, ie 'spouse'
	 * @return string
	 */
	public function person_element_embeded( string $text, string $item ): string {
		return "\n\t\t\t\t\t\t\t <!-- " . ucfirst( $item ) . ' -->'
			. "\n\t\t<div id=\"lumiere_popup_" . strtolower( $item ) . '">' . $text . "\n\t\t</div>";
	}
}
