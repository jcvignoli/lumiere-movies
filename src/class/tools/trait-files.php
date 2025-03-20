<?php declare( strict_types = 1 );
/**
 * Files Trait
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { // Don't check for Lumiere\Config\Settings class, the trait is called before loading it.
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Exception;

/**
 * Trait for files operations
 * @since 4.0.1 Trait created
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 */
trait Files {

	/**
	 * Include the template if it exists and pass to it as a/many variable/s using transient
	 * The transiant has a validity time of 30 seconds by default
	 *
	 * @param string $file_name Template file name
	 * @param array<array-key, mixed> $variables The variables transfered to the include
	 * @param string $transient_name The *maximum* time the transient is valid in seconds, 30 seconds by default
	 * @param int $validity_time_transient The *maximum* time the transient is valid in seconds, 30 seconds by default
	 * @void The file with vars has been included
	 */
	public function include_with_vars(
		string $file_name,
		array $variables = [],
		string $transient_name = 'admin_template_pass_vars',
		int $validity_time_transient = 30,
	): void {

		$full_file_path = $this->find_template_file( $file_name );

		if ( is_file( $full_file_path ) ) {
			// Send the variables to transients so they can be retrieved in the included pages.
			// Validity: XX seconds, but is deleted after the include.
			set_transient( $transient_name, $variables, $validity_time_transient );

			// Require with the full path built.
			require_once $full_file_path;

			delete_transient( $transient_name );
		}
	}

	/**
	 * Find a file in template folder
	 *
	 * @param string $file_name The name without php of the file to be include, ie: admin-menu-first-part
	 * @return string Full path of the file found in template folder
	 */
	private function find_template_file( string $file_name ): string {

		$templates_dir = LUM_WP_PATH . 'class/templates/';

		$folder_iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $templates_dir, RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $folder_iterator as $file ) {
			if ( str_contains( $file->getPathname(), $file_name ) ) {
				return $file->getPathname();
			}
		}

		throw new Exception( esc_html__( 'No template file found', 'lumiere-movies' ) . ' ' . esc_html( $file_name ) );
	}

	/**
	 * Format a given file size to bytes
	 * The size in bits would need to replace '1000' by '1024'
	 *
	 * @param int $size the unformatted number of the size
	 * @param int $precision how many numbers after comma, two by default
	 */
	public function lumiere_format_bytes( int $size, int $precision = 2 ): string {

		$units = [ 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];
		$power = $size > 0 ? (int) floor( log( $size, 1000 ) ) : 0;
		return number_format( $size / pow( 1000, $power ), $precision, '.', ',' ) . ' ' . $units[ $power ];
	}

	/**
	 * Request WP_Filesystem credentials if file doesn't have it.
	 * @param string $file The file with full path to ask the credentials form
	 *
	 * @since 3.9.7 Added extra require_once() if $wp_filesystem is null
	 */
	public function wp_filesystem_cred( string $file ): void {

		global $wp_filesystem;

		// On some environnements, $wp_filesystem sometimes is not correctly initialised through globals.
		$file_path = ABSPATH . 'wp-admin/includes/file.php';
		if ( $wp_filesystem === null && is_file( $file_path ) ) {
			require_once $file_path;
			WP_Filesystem();
		}

		/** WP: request_filesystem_credentials($form_post, $type, $error, $context, $extra_fields, $allow_relaxed_file_ownership); */
		$creds = request_filesystem_credentials( $file, '', false );

		if ( $creds === false ) {
			echo esc_html__( 'Credentials are required to edit this file: ', 'lumiere-movies' ) . esc_html( $file );
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
	 * Recursively delete a directory, keeping the directory path provided
	 *
	 * @param string $dir Directory path
	 * @return void true on success
	 */
	public function dir_unlink_recursive( string $dir ): void {

		global $wp_filesystem;
		$files = [];

		// Make sure we have the correct credentials.
		$this->wp_filesystem_cred( $dir );

		if ( $wp_filesystem->is_dir( $dir ) === false ) {
			return;
		}

		$files = $wp_filesystem->dirlist( $dir );

		foreach ( $files as $file ) {

			if ( $wp_filesystem->is_dir( $dir . $file['name'] ) === true ) {
				$wp_filesystem->delete( $dir . $file['name'], true );
				continue;
			}
			$wp_filesystem->delete( $dir . $file['name'] );
		}
	}

	/**
	 * Make sure debug log exists and is writable
	 * Create debug log if it doesn't exist
	 *
	 * @param array<string, string> $imdb_admin_values Log file with the full path
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @param bool $second_try Whether the function is called a second time
	 * @return null|string Null if log creation was unsuccessful, Log full path file if successfull
	 *
	 * @since 3.9.1 is a method, and using fopen and added error_log(), if file creation in wp-content fails try with Lumière plugin folder
	 * @since 4.1.2 rewriting with global $wp_filesystem, refactorized, update the database with the new path if using Lumière plugin folder
	 * @since 4.6 Moved from Logger class to here
	 * @see \Lumiere\Plugins\Logger::set_logger()
	 */
	public function maybe_create_log( array $imdb_admin_values, bool $second_try = false ): ?string {

		global $wp_filesystem;
		$log_file = $imdb_admin_values['imdbdebuglogpath'];
		$this->wp_filesystem_cred( $log_file ); // in trait Files.

		// Debug file doesn't exist, create it.
		if ( $wp_filesystem->is_file( $log_file ) === false ) {
			$wp_filesystem->put_contents( $log_file, '' );
			error_log( '***WP Lumiere Plugin***: Debug did not exist, created a debug file ' . $log_file ); // @phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		// Debug file permissions are wrong, change them.
		if ( $wp_filesystem->is_file( $log_file ) === true && $wp_filesystem->is_writable( $log_file ) === false ) {

			$fs_chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0770;

			// Permissions on the file are not correct, change them.
			if ( $wp_filesystem->chmod( $log_file, $fs_chmod ) === true ) {
				error_log( '***WP Lumiere Plugin***: changed chmod permissions debug file ' . $log_file ); // @phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				return $log_file;
			}
			error_log( '***WP Lumiere Plugin ERROR***: cannot change permission of debug file ' . $log_file ); // @phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		// If couldnt create debug file in wp-content, change the path to Lumière plugin folder.
		// This is run only on the first call of the method, using $second_try.
		if ( ( $wp_filesystem->is_file( $log_file ) === false || $wp_filesystem->is_writable( $log_file ) === false ) && $second_try === false ) {

			$log_file = $imdb_admin_values['imdbpluginpath'] . 'debug.log';

			// Update database with the new value for debug path.
			$new_options = get_option( Get_Options::get_admin_tablename() );
			$new_options['imdbdebuglogpath'] = $log_file;
			update_option( Get_Options::get_admin_tablename(), $new_options );

			error_log( '***WP Lumiere Plugin***: debug file could not be written in normal place, using plugin folder: ' . $log_file ); // @phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			$this->maybe_create_log( $imdb_admin_values, true );
		}

		// If this failed again, send an Apache error message and exit.
		if ( is_file( $log_file ) === false || $wp_filesystem->is_writable( $log_file ) === false ) {
			error_log( '***WP Lumiere Plugin ERROR***: Tried everything, cannot create any debug log both neither in wp-content nor in Lumiere plugin folder.' ); // @phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return null;
		}
		return $log_file;
	}
}

