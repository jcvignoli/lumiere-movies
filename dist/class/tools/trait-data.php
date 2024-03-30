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
 * @since 4.1 Trait created
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

	/**
	 * Function lumiere_array_key_exists_wildcard
	 * Search with a wildcard in $keys of an array
	 *
	 * @param array<string, array<int|string>|bool|int|string> $array The array to be searched in
	 * @param string $search The text that is searched for
	 * @param string $return text 'key-value' can be passed to get simpler array of results
	 *
	 * @return array<int<0, max>|string, array<array-key, int|string>|bool|int|string>
	 *
	 * @credit: https://magp.ie/2013/04/17/search-associative-array-with-wildcard-in-php/
	 */
	public function lumiere_array_key_exists_wildcard( array $array, string $search, string $return = '' ): array {

		$search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );

		$result_init = preg_grep( '/^' . $search . '$/i', array_keys( $array ) );
		/** @psalm-suppress RedundantConditionGivenDocblockType -- Docblock-defined type array<int<0, max>, string> can never contain false -- PHPStan says otherwise */
		$result = is_array( $result_init ) && count( $result_init ) > 0 ? $result_init : [];

		if ( $return === 'key-value' ) {
			return array_intersect_key( $array, array_flip( $result ) );
		}

		return $result;
	}

	/**
	 * Get the Class name currently in use
	 * Mainly utlised in $log() so they can provide their class of origin
	 *
	 * @since 4.1
	 * @return string The classname currently in use, 'unknowClass' if not found
	 */
	public function get_current_classname(): string {
		$get_class = strrchr( __CLASS__, '\\' );
		$classname = $get_class !== false ? substr( $get_class, 1 ) : false;
		return $classname !== false ? $classname : 'unknowClass';
	}
}

