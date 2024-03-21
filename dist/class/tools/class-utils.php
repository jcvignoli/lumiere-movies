<?php declare( strict_types = 1 );
/**
 * Class of tools
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.5
 * @package lumiere-movies
 */

namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

/**
 * Various tools
 * @TODO pass these in traits or other ways
 */
class Utils {

	/**
	 * Does a glob recursively
	 * @TODO use RecursiveIteratorIterator PHP classes instead of glob
	 *
	 * @param string $pattern File searched for, such as /whatever/text.*
	 * @param 0|1|2|3|4|5|6|7|16|17|18|19|20|21|22|23|64|65|66|67|68|69|70|71|80|81|82|83|84|85|86|87|1024|1025|1026|1027|1028|1029|1030|1031|1040|1041|1042|1043|1044|1045|1046|1047|1088|1089|1090|1091|1092|1093|1094|1095|1104|1105|1106|1107|1108|1109|1110|1111|8192|8193|8194|8195|8196|8197|8198|8199|8208|8209|8210|8211|8212|8213|8214|8215|8256|8257|8258|8259|8260|8261|8262|8263|8272|8273|8274|8275|8276|8277|8278|8279|9216|9217|9218|9219|9220|9221|9222|9223|9232|9233|9234|9235|9236|9237|9238|9239|9280|9281|9282|9283|9284|9285|9286|9287|9296|9297|9298|9299|9300|9301|9302|9303 $flags glob() flag
	 * @return array<string>|array<int|string, mixed>
	 * @credits https://www.php.net/manual/fr/function.glob.php#106595
	 */
	public static function lumiere_glob_recursive( string $pattern, int $flags = 0 ): array {

		$files = glob( $pattern, $flags ) !== false ? glob( $pattern, $flags ) : [];

		// Avoid providing false value in foreach loop
		$folder_init = glob( dirname( $pattern ) . '/*', GLOB_ONLYDIR | GLOB_NOSORT );
		$folder = $folder_init !== false ? $folder_init : [];

		foreach ( $folder as $dir ) {

			$files = array_merge( $files, self::lumiere_glob_recursive( $dir . '/' . basename( $pattern ), $flags ) );
		}

		return $files;
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

