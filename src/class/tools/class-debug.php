<?php declare( strict_types = 1 );
/**
 * Debug static class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
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
 * Class for debuging operations
 * Those methods shouldn't be permanentely included in code, they're temporary in nature
 */
class Debug {

	/**
	 * Return the hooks currently used
	 */
	public static function get_hooks(): string {
		self::trigger_wp_error( __METHOD__, 'This is a debugging function, should not permanently added' );
		global $wp_filter;
		return '<pre>' . self::colorise_output( array_keys( $wp_filter ) ) . '</pre>';
	}

	/**
	 * Internal function to colorise the output
	 * Allows to avoid print_r() or var_dump()
	 * @param array<int<0, max>|string, array<string, string>|int|string> $array
	 */
	private static function colorise_output( array $array ): string {
		$output = '<ul>';

		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) ) {
				$output .= '<li><span style="color:red;">' . $key . '</span><b> => </b><span style="color:blue;">' . self::colorise_output( $val ) . '</span></li>';
				continue;
			}
			$output .= '<li><span style="color:red;">' . $key . '</span><b> => </b><span style="color:blue;">' . $val . '</span></li>';
		}

		return $output . '</ul>';
	}

	/**
	 * Internal function to display a wp error
	 * @param string $method
	 */
	private static function trigger_wp_error( string $method, string $text ): void {
		wp_trigger_error( $method, $text );
	}
}

