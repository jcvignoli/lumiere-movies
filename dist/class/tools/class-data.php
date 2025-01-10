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
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Trait for data operations, like string or array modifications
 * @since 4.1 Trait created
 * @since 4.3 It is a class and all methods are static
 */
class Data {

	/**
	 * HTMLizing function
	 * transforms movie's name in a way to be able to be searchable (ie "ô" becomes "&ocirc;")
	 *
	 * @since 3.11.4 Added Limit the number of characters step
	 *
	 * @param string $link The string to be converted
	 */
	public static function lumiere_name_htmlize( string $link ): string {

		// a. quotes escape
		$lienhtmlize = wp_slash( $link );

		// b. regular expression to convert all accents
		$lienhtmlize = preg_replace( '/&(?!#[0-9]+;)/s', '&amp;', $lienhtmlize ) ?? $lienhtmlize;

		// c. transforms spaces to "+", which allows titles with several words to work
		$lienhtmlize = str_replace( ' ', '+', $lienhtmlize );

		// d. Limit the number of characters, as the cache file path can't exceed the limit of 255 characters
		/** @psalm-suppress PossiblyInvalidArgument (according to PHPStan, alwsays string, no futher check */
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
	public static function lumiere_array_key_exists_wildcard( array $array, string $search, string $return = '' ): array {

		$search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );

		$result_init = preg_grep( '/^' . $search . '$/i', array_keys( $array ) );
		/** @psalm-suppress RedundantConditionGivenDocblockType, DocblockTypeContradiction -- Docblock-defined type array<int<0, max>, string> can never contain false -- PHPStan says otherwise */
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
	public static function get_current_classname(): string {
		$get_class = strrchr( __CLASS__, '\\' );
		$classname = $get_class !== false ? substr( $get_class, 1 ) : false;
		return $classname !== false ? $classname : 'unknowClass';
	}

	/**
	 * Return true/false if a term in an array is contained in a value
	 * @since 3.9.2 Added escape special chara
	 *
	 * @param array<string> $array_list the array to be searched in
	 * @param string $term the term searched for
	 * @return bool
	 */
	public static function lumiere_array_contains_term( array $array_list, string $term ): bool {

		// Escape special url string characters for following regex
		$array_list_escaped = str_replace( [ '?', '&', '#' ], [ '\?', '\&', '\#' ], $array_list );

		if ( preg_match( '~(' . implode( '|', $array_list_escaped ) . ')~', $term ) === 1 ) {
			return true;
		}

		return false;
	}
}
