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
 * They should all be static
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
	 * Colorise the output
	 * Allows to avoid print_r() or var_dump()
	 * @param array<int<0, max>|string, array<string, string>|int|string> $array
	 */
	public static function colorise_output( array $array ): string {
		$output = '<ul>';

		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) ) {
				$output .= '<li><span class="lum_color_red">' . $key . '</span><b> => </b><span class="lum_color_blue">'
					. self::colorise_output( $val )
					. '</span></li>';
				continue;
			}
			$output .= '<li><span class="lum_color_red">' . $key . '</span><b> => </b><span class="lum_color_blue">' . $val . '</span></li>';
		}

		return $output . '</ul>';
	}

	/**
	 * Internal function to display a wp error
	 *
	 * @param string $method Calling method name
	 * @param string $text Display this text
	 */
	private static function trigger_wp_error( string $method, string $text ): void {
		wp_trigger_error( $method, $text );
	}

	/**
	 * Display Lumière variables
	 *
	 * @since 3.5
	 *
	 * @param null|array<string, array<int|string>|bool|int|string> $options the array of admin/widget/cache settings options
	 * @param null|string $set_error set to 'no_var_dump' to avoid the call to var_dump function
	 * @param null|string $libxml_use set to 'libxml to call php function libxml_use_internal_errors(true)
	 * @param null|string $get_screen set to 'screen' to display wp function get_current_screen()
	 * @return void Returns optionaly an array of the options passed in $options
	 */
	public static function display_lum_vars( ?array $options = null, ?string $set_error = null, ?string $libxml_use = null, ?string $get_screen = null ): void {

		// If the user can't manage options and it's not a cron, exit.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Set the highest level of debug reporting.
		error_reporting( E_ALL ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting -- it's debugging
		ini_set( 'display_errors', '1' ); // @phpcs:ignore WordPress.PHP.IniSet.display_errors_Blacklisted, Squiz.PHP.DiscouragedFunctions.Discouraged -- it's debugging!

		// avoid endless loops with imdbphp parsing errors.
		if ( ( isset( $libxml_use ) ) && ( $libxml_use === 'libxml' ) ) {
			libxml_use_internal_errors( true );
		}

		if ( $set_error !== 'no_var_dump' ) {
			set_exception_handler( [ self::class, 'exception_handler' ] );
		}

		if ( $get_screen === 'screen' ) {
			$current_screen = get_current_screen();
			echo '<div align="center"><strong>[WP current screen]</strong>';
			echo wp_json_encode( $current_screen );
			echo '</div>';
		}

		// Print the options.
		if ( ( null !== $options ) && count( $options ) > 0 ) {
			echo '<div class="lumiere_wrap"><strong>[Lumière options]</strong><font size="-2"> ';
			$json_options = wp_json_encode( $options );
			echo $json_options !== false ? esc_html( str_replace( [ '\\', '{"', '"}', '":"', '","' ], [ '', '["', '" ]', '" => "', '" ], [ "' ], $json_options ) ) : '';
			echo ' </font><strong>[/Lumière options]</strong></div>';
		}
	}

	/**
	 * Internal exception handler
	 *
	 * @param \Throwable $exception The type of new exception
	 * @return void
	 */
	public static function exception_handler( \Throwable $exception ): void {
		throw $exception;
	}
}

