<?php declare( strict_types = 1 );
/**
 * Class of tools: general utilities available for any class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.1
 * @package lumiere-movies
 */

namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Plugins\Logger;

/**
 * Class of function tools
 *
 */

class Utils {

	/**
	 * Trait including the database settings.
	 * Not built in constructor
	 */
	use \Lumiere\Settings_Global;

	/**
	 * \Lumiere\Logger class
	 *
	 */
	private Logger $logger;

	/**
	 * Check if debug is active
	 *
	 */
	public bool $debug_is_active;

	/**
	 * Class constructor
	 *
	 */
	public function __construct () {

		// Start Logger class.
		$this->logger = new Logger( 'utilsClass' );

		$this->debug_is_active = false;
	}

	/**
	 * Recursively delete a directory, keeping the directory path provided
	 *
	 * @param string $dir Directory path
	 * @return bool true on success
	 */
	public static function lumiere_unlink_recursive( string $dir ): bool {

		global $wp_filesystem;
		$files = [];

		// Make sure we have the correct credentials
		self::lumiere_wp_filesystem_cred( $dir );

		if ( $wp_filesystem->is_dir( $dir ) === false && $wp_filesystem->is_file( $dir ) === false ) {
			return false;
		}

		$files = $wp_filesystem->dirlist( $dir );

		foreach ( $files as $file ) {

			if ( $wp_filesystem->is_dir( $dir . $file['name'] ) === true ) {

				$wp_filesystem->delete( $dir . $file['name'], true );
				continue;

			}

			$wp_filesystem->delete( $dir . $file['name'] );
		}
		return true;

	}

	/**
	 * Recursively scan a directory. Not currently in use.
	 *
	 * @param string $dir mandatory Directory name
	 * @param int $filesbydefault optional number of files contained in folder and to not take
	 *  into account for the count (usefull if there is a number of predetermined files/folders, like in cache)
	 *
	 * @return bool
	 */
	public static function lumiere_is_empty_dir( string $dir, int $filesbydefault = 0 ): bool {

		global $wp_filesystem;

		// Make sure we have the correct credentials
		self::lumiere_wp_filesystem_cred( $dir );

		if ( $wp_filesystem->is_dir( $dir ) === false && $wp_filesystem->is_file( $dir ) === false ) {

			return false;
		}

		$files = $wp_filesystem->dirlist( $dir );
		$count_files = count( array_count_values( array_column( $files, 'name' ) ) );

		if ( $count_files <= $filesbydefault ) {

			return true;

		}

		return false;

	}

	/**
	 * Sanitize an array
	 * Input can be either an array or a string
	 *
	 * @param mixed $array
	 * @return mixed
	 * @credit https://wordpress.stackexchange.com/a/255238/206323
	 */
	public static function lumiere_recursive_sanitize_text_field( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = self::lumiere_recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}
		return $array;
	}

	/**
	 * Text displayed when no result is found
	 * This text is logged if the debug logging is activated
	 *
	 * @param string $text: text to display/log. if no text provided, default text is provided
	 */
	public function lumiere_noresults_text( string $text = 'No result found for this query.' ): void {

		$this->logger->log()->debug( "[Lumiere] $text" );

		echo "\n" . '<div class="noresult" align="center" style="font-size:16px;color:red;padding:15px;">'
			. esc_html( $text )
			. "</div>\n";

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
	public function lumiere_array_key_exists_wildcard ( array $array, string $search, string $return = '' ): array {

		$search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );

		$result_init = preg_grep( '/^' . $search . '$/i', array_keys( $array ) );
		$result = is_array( $result_init ) === true ? $result_init : [];

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
	 * @param ?string $link either null or string to be converted
	 */
	public static function lumiere_name_htmlize ( ?string $link = null ): ?string {

		// If no string passed, exit
		if ( $link === null ) {
			return null;
		}

		// a. quotes escape
		$lienhtmlize = addslashes( $link );

		// b.converts db to html -> no more needed
		//$lienhtmlize = htmlentities($lienhtmlize,ENT_NOQUOTES,"UTF-8");

		// c. regular expression to convert all accents; weird function...
		$lienhtmlize = preg_replace( '/&(?!#[0-9]+;)/s', '&amp;', $lienhtmlize ) ?? $lienhtmlize;

		// d. turns spaces to "+", which allows titles including several words
		$lienhtmlize = str_replace( [ ' ' ], [ '+' ], $lienhtmlize );

		// Limit the number of characters, as the cache file path can exceed the limit of 255 characters
		// @since 3.11.4
		$lienhtmlize = substr( $lienhtmlize, 0, 100 );

		return $lienhtmlize;
	}

	/**
	 * Format filesize
	 * Should I want the size in bytes, replace '1000' by '1024'
	 *
	 * @param int $size the unformatted number of the size
	 * @param int $precision how many numbers after comma, two by default
	 */
	public static function lumiere_format_bytes( int $size, int $precision = 2 ): string {

		$units = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];
		$power = $size > 0 ? (int) floor( log( $size, 1000 ) ) : 0;
		return number_format( $size / pow( 1000, $power ), $precision, '.', ',' ) . ' ' . $units[ $power ];

	}

	/**
	 * Does a glob recursively
	 * Does not support flag GLOB_BRACE
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
	 * Function lumiere_notice
	 * Display a confirmation notice, such as "options saved"
	 *
	 * @param int $code type of message
	 * @param string $msg text to display
	 */
	public static function lumiere_notice( int $code, string $msg ): string {

		switch ( $code ) {
			default:
			case 1: // success notice, green
				return '<div class="notice notice-success"><p>' . $msg . '</p></div>';
			case 2: // info notice, blue
				return '<div class="notice notice-info"><p>' . $msg . '</p></div>';
			case 3: // simple error, red
				return '<div class="notice notice-error"><p>' . $msg . '</p></div>';
			case 4: // warning, yellow
				return '<div class="notice notice-warning"><p>' . $msg . '</p></div>';
			case 5: // success notice, green, dismissible
				return '<div class="notice notice-success is-dismissible"><p>' . $msg . '</p></div>';
			case 6: // info notice, blue, dismissible
				return '<div class="notice notice-info is-dismissible"><p>' . $msg . '</p></div>';
			case 7: // simple error, red, dismissible
				return '<div class="notice notice-error is-dismissible"><p>' . $msg . '</p></div>';
			case 8: // warning, yellow, dismissible
				return '<div class="notice notice-warning is-dismissible"><p>' . $msg . '</p></div>';
		}

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
	// phpcs:disable
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

			echo '<div><strong>[Lumière options]</strong><font size="-3"> ';
			print_r( $options );
			echo ' </font><strong>[/Lumière options]</strong></div>';
		}

	}
	// phpcs:enable

	/**
	 * Lumiere internal exception handler
	 *
	 * @see Utils::lumiere_activate_debug()
	 * @param \Throwable $exception The type of new exception
	 * @return void
	 */
	private function lumiere_exception_handler( \Throwable $exception ): void {
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
	 * Request WP_Filesystem credentials if file doesn't have it.
	 * @param string $file The file with full path to ask the credentials form
	 */
	public static function lumiere_wp_filesystem_cred( string $file ): void {

		global $wp_filesystem;

		// On some environnements, $wp_filesystem is sometimes not correctly initialised through globals.
		// @since 3.9.7
		if ( $wp_filesystem === null ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		/** WP: request_filesystem_credentials($form_post, $type, $error, $context, $extra_fields, $allow_relaxed_file_ownership); */
		$creds = request_filesystem_credentials( $file, '', false );

		if ( $creds === false ) {
			$escape_html = [
				'div' => [ 'class' => true ],
				'p' => [],
			];
			echo wp_kses( self::lumiere_notice( 3, __( 'Credentials are required to edit this file: ', 'lumiere-movies' ) . $file ), $escape_html );
			return;
		}

		$credit_open = is_array( $creds ) === true ? WP_Filesystem( $creds ) : false;

		// our credentials were no good, ask for them again.
		if ( $credit_open === false || $credit_open === null ) {

			$creds_two = request_filesystem_credentials( $file, '', true, '' );

			// If credentials succeeded or failed, don't pass them to WP_Filesystem.
			if ( is_bool( $creds_two ) === true ) {
				WP_Filesystem();
				return;
			}

			WP_Filesystem( $creds_two );
		}
	}

	/**
	 * Return if Lumiere plugin is installed
	 *
	 * @since 3.7.1
	 * @return bool always true
	 */
	public static function lumiere_is_active (): bool {
		return true;
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

		if ( is_admin()
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

