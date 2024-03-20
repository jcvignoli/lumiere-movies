<?php declare( strict_types = 1 );
/**
 * Data Trait
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

/**
 * Trait for data operations, like string or array modifications
 * @since 4.0.3 Trait created
 */
trait Data {

	/**
	 * HTMLizing function
	 * transforms movie's name in a way to be able to be searchable (ie "ô" becomes "&ocirc;")
	 *
	 * @since 3.11.4 Added Limit the number of characters step
	 *
	 * @param string $link The string to be converted
	 */
	public function lumiere_name_htmlize( string $link ): string {

		// a. quotes escape
		$lienhtmlize = wp_slash( $link );

		// b. regular expression to convert all accents
		$lienhtmlize = preg_replace( '/&(?!#[0-9]+;)/s', '&amp;', $lienhtmlize ) ?? $lienhtmlize;

		// c. transforms spaces to "+", which allows titles with several words to work
		$lienhtmlize = str_replace( [ ' ' ], [ '+' ], $lienhtmlize );

		// d. Limit the number of characters, as the cache file path can't exceed the limit of 255 characters
		$lienhtmlize = substr( $lienhtmlize, 0, 100 );

		return $lienhtmlize;
	}
}

