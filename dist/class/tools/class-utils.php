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
 */
class Utils {

	/**
	 * Check if debug is active
	 */
	public bool $debug_is_active;

	/**
	 * Constructor
	 */
	public function __construct () {

		$this->debug_is_active = false;
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
		/** @psalm-suppress RedundantConditionGivenDocblockType -- Docblock-defined type array<int<0, max>, string> can never contain false -- PHPStan says otherwise */
		$result = is_array( $result_init ) && count( $result_init ) > 0 ? $result_init : [];

		if ( $return === 'key-value' ) {
			return array_intersect_key( $array, array_flip( $result ) );
		}

		return $result;
	}

	/**
	 * HTMLizing function
	 * transforms movie's name in a way to be able to be searchable (ie "ô" becomes "&ocirc;")
	 * ----> should use a WordPress dedicated function instead, like esc_url() ?
	 *
	 * @since 3.11.4 Added Limit the number of characters step
	 *
	 * @param ?string $link either null or string to be converted
	 */
	public static function lumiere_name_htmlize( ?string $link = null ): ?string {

		// If no string passed, exit
		if ( $link === null ) {
			return null;
		}

		// a. quotes escape
		$lienhtmlize = addslashes( $link );

		// b. regular expression to convert all accents; weird function...
		$lienhtmlize = preg_replace( '/&(?!#[0-9]+;)/s', '&amp;', $lienhtmlize ) ?? $lienhtmlize;

		// c. turns spaces to "+", which allows titles including several words
		$lienhtmlize = str_replace( [ ' ' ], [ '+' ], $lienhtmlize );

		// d. Limit the number of characters, as the cache file path can't exceed the limit of 255 characters
		$lienhtmlize = substr( $lienhtmlize, 0, 100 );

		return $lienhtmlize;
	}

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

	/**
	 * Activate debug on screen
	 *
	 * @since 3.5
	 *
	 * @param null|array<string, array<int|string>|bool|int|string> $options the array of admin/widget/cache settings options
	 * @param null|string $set_error set to 'no_var_dump' to avoid the call to var_dump function
	 * @param null|string $libxml_use set to 'libxml to call php function libxml_use_internal_errors(true)
	 * @param null|string $get_screen set to 'screen' to display wp function get_current_screen()
	 *
	 * @return void Returns optionaly an array of the options passed in $options
	 */
	// @phpcs:disable
	public function lumiere_activate_debug( ?array $options = null, string $set_error = null, string $libxml_use = null, string $get_screen = null ): void {

		// Set on true to show debug is active if called again.
		$this->debug_is_active = true;

		// If the user can't manage options and it's not a cron, exit.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Set the highest level of debug reporting.
		error_reporting( E_ALL );
		ini_set( 'display_errors', '1' );

		// avoid endless loops with imdbphp parsing errors.
		if ( ( isset( $libxml_use ) ) && ( $libxml_use === 'libxml' ) ) {
			libxml_use_internal_errors( true );
		}

		if ( $set_error !== 'no_var_dump' ) {
			set_exception_handler( [ $this, 'lumiere_exception_handler' ] );
		}

		if ( $get_screen === 'screen' ) {
			$currentScreen = get_current_screen();
			echo '<div align="center"><strong>[WP current screen]</strong>';
			print_r( $currentScreen );
			echo '</div>';
		}

		// Exit if no Lumière option array requested to show
		if ( ( null !== $options ) && count( $options ) > 0 ) {

			echo '<div class="lumiere_wrap"><strong>[Lumière options]</strong><font size="-3"> ';
			print_r( $options );
			echo ' </font><strong>[/Lumière options]</strong></div>';
		}
	}
	// @phpcs:enable

	/**
	 * Lumiere internal exception handler
	 *
	 * @see Utils::lumiere_activate_debug()
	 * @param \Throwable $exception The type of new exception
	 * @return void
	 */
	public function lumiere_exception_handler( \Throwable $exception ): void {
		throw $exception;
	}

	/**
	 * Check if a block widget is active
	 *
	 * @param string $blockname Name of the block to look for
	 * @return bool True if found
	 */
	public static function lumiere_block_widget_isactive( string $blockname ): bool {
		$widget_blocks = get_option( 'widget_block' );
		foreach ( $widget_blocks as $widget_block ) {
			if ( ( isset( $widget_block['content'] ) && strlen( $widget_block['content'] ) !== 0 )
			&& has_block( $blockname, $widget_block['content'] )
			) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Are we currently on an AMP URL?
	 * Will always return `false` and show PHP Notice if called before the `wp` hook.
	 *
	 * @since 3.7.1
	 * @return bool true if amp url, false otherwise
	 */
	public static function lumiere_is_amp_page(): bool {
		global $pagenow;

		// If url contains ?amp, it must be an AMP page
		if ( str_contains( $_SERVER['REQUEST_URI'] ?? '', '?amp' )
		|| isset( $_GET ['wpamp'] )
		|| isset( $_GET ['amp'] )
		) {
			return true;
		}

		if (
			is_admin()
			/**
			 * If kept, breaks blog pages these functions can be executed very early
				|| is_embed()
				|| is_feed()
			*/
			|| ( isset( $pagenow ) && in_array( $pagenow, [ 'wp-login.php', 'wp-signup.php', 'wp-activate.php' ], true ) )
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			|| ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
		) {
			return false;
		}

		// Since we are checking later (amp_is_request()) a function that execute late, make sure we can execute it
		if ( did_action( 'wp' ) === 0 ) {
			return false;
		}
		return function_exists( 'amp_is_request' ) && amp_is_request();
	}
}

