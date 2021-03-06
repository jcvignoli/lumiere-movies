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

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Plugins\Logger;

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
	 * Personal signature for administration
	 *
	 */
	public function lumiere_admin_signature(): string {

		// Construct Global Settings trait.
		// Needed for Lumi??re version.
		$this->settings_open();

		// Authorise this html tags wp_kses()
		$allowed_html_for_esc_html_functions = [
			'a' => [
				'href' => [],
				'title' => [],
			],
		];

		$output = "\t\t<div class=\"soustitre\">\n";

		$output .= "\t\t\t" .
			/* translators: %1$s is replaced with an html link */
			wp_sprintf( wp_kses( __( '<strong>Licensing Info:</strong> Under a GPL licence, "Lumiere Movies" is based on <a href="%1$s" target="_blank">tboothman</a> classes. Nevertheless, a considerable amount of work was required to implement it in WordPress; check the support page for', 'lumiere-movies' ), $allowed_html_for_esc_html_functions ), \Lumiere\Settings::IMDBPHPGIT );

		$output .= '<a href="'
			. esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=help&helpsub=support' ) . '"> '
			. esc_html__( 'more', 'lumiere-movies' ) . '</a>.';

		$output .= "\t\t\t<br /><br /><div>\n\t\t\t\t<div> &copy; 2005-" . gmdate( 'Y' ) . ' <a href="' . \Lumiere\Settings::IMDBABOUTENGLISH . '" target="_blank">Lost Highway</a>, <a href="' . \Lumiere\Settings::IMDBHOMEPAGE . '" target="_blank">Lumi??re! WordPress plugin</a>, version ' . $this->config_class->lumiere_version . "\n</div>\n</div>";

		$output .= "\t\t</div>\n";

		return $output;

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
	 * @param array<string, array<string>|bool|int|string> $array The array to be searched in
	 * @param string $search The text that is searched for
	 * @param string $return text 'key-value' can be passed to get simpler array of results
	 *
	 * @return array<string, array<string>|bool|int|string>
	 *
	 * @credit: https://magp.ie/2013/04/17/search-associative-array-with-wildcard-in-php/
	 */
	public function lumiere_array_key_exists_wildcard ( array $array, string $search, string $return = '' ): array {

		$search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );

		$result_init = preg_grep( '/^' . $search . '$/i', array_keys( $array ) );
		$result = $result_init !== false ? $result_init : [];

		if ( $return === 'key-value' ) {
			return array_intersect_key( $array, array_flip( $result ) );
		}

		return $result;

	}

	/**
	 * HTMLizing function
	 * transforms movie's name in a way to be able to be searchable (ie "??" becomes "&ocirc;")
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

		return $lienhtmlize;
	}

	/**
	 * Function lumiere_formatBytes
	 * Returns in a proper format a size
	 *
	 * @param int $size the unformatted number of the size
	 * @param int $precision how many numbers after comma, two by default
	 */
	public static function lumiere_format_bytes( int $size, int $precision = 2 ): string {
		$base = log( $size, 1024 );
		$suffixes = [ 'bytes', 'Kb', 'Mb', 'Gb', 'Tb' ];
		return round( pow( 1024, $base - floor( $base ) ), $precision ) . ' ' . $suffixes[ floor( $base ) ];
	}

	/**
	 * Does a glob recursively
	 * Does not support flag GLOB_BRACE
	 *
	 * @param int $flags glob() flag
	 * @return array<string>|array<mixed, mixed>
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
			case 4: // warning error, yellow
				return '<div "notice notice-warning">' . $msg . '</div>';
		}

	}

	/**
	 * Returns if a string is contained in a value
	 * Introduced in PHP 8, here for compatibilty purpose
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle The substring to search for in the haystack.
	 * @return bool
	 */
	public static function str_contains( string $haystack, string $needle ): bool {

		return $needle !== '' && mb_strpos( $haystack, $needle ) !== false;

	}

	/**
	 * Return true/false if a term in an array is contained in a value
	 *
	 * @param array<string> $array_list the array to be searched in
	 * @param string $term the term searched for
	 * @return bool
	 */
	public static function lumiere_array_contains_term( array $array_list, string $term ): bool {

		if ( preg_match( '(' . implode( '|', $array_list ) . ')', $term ) === 1 ) {

			return true;

		}

		return false;

	}

	/**
	 * Activate debug on screen
	 *
	 * @since 3.5
	 *
	 * @param array<string, array<string>|bool|int|string> $options the array of admin/widget/cache settings options
	 * @param string $set_error set to 'no_var_dump' to avoid the call to var_dump function
	 * @param string $libxml_use set to 'libxml to call php function libxml_use_internal_errors(true)
	 * @param string $get_screen set to 'screen' to display wp function get_current_screen()
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

			// @phpstan-ignore-next-line 'Parameter #1 $callback of function set_error_handler expects (callable(int, string, string, int, array):  bool)|null, 'var_dump' given.' var_dump works well, thanks!
			set_error_handler('var_dump');

		}

		if ( $get_screen === 'screen' ) {
			$currentScreen = get_current_screen();
			echo '<div align="center"><strong>[WP current screen]</strong>';
			print_r( $currentScreen );
			echo '</div>';
		}

		// Exit if no Lumi??re option array requested to show
		if ( ( null !== $options ) && count( $options ) !== 0 ) {

			echo '<div><strong>[Lumi??re options]</strong><font size="-3"> ';
			print_r( $options );
			echo ' </font><strong>[/Lumi??re options]</strong></div>';

		}

	}
	// phpcs:enable

	/**
	 * Check if the block widget is active
	 * Use the current name by default
	 */
	public static function lumiere_block_widget_isactive( string $blockname = \Lumiere\Widget::BLOCK_WIDGET_NAME ): bool {
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
	 *
	 */
	public static function lumiere_wp_filesystem_cred ( string $file ): bool {

		global $wp_filesystem;

		// If the basic function doesn't exist, exit.
		if ( function_exists( 'request_filesystem_credentials' ) === false ) {

			return false;

		}

		/** WP: request_filesystem_credentials($form_post, $type, $error, $context, $extra_fields); */
		$creds = request_filesystem_credentials( $file, '', false );

		if ( false === ( $creds ) ) {

			return false;
		}

		// now we have some credentials, try to get the wp_filesystem running.
		if ( is_array( $creds ) === true && ( WP_Filesystem( $creds ) === false || WP_Filesystem( $creds ) === null ) ) {
			// our credentials were no good, ask the user for them again
			$creds_two = request_filesystem_credentials( $file, '', true, '', null );

			if ( false === ( $creds_two ) ) {

				return false;
			}

			return true;
		}

		return true;
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
	public static function lumiere_is_amp_page (): bool {
		global $pagenow;
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

		if ( did_action( 'wp' ) === 0 ) {
			return false;
		}

		return function_exists( 'amp_is_request' ) && amp_is_request()
		|| isset( $_GET ['wpamp'] )
		|| isset( $_GET ['amp'] );
	}

}

