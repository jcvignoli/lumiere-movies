<?php declare( strict_types = 1 );
/**
 * Admin Trait
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Tools\Files;

/**
 * Trait for general function
 *
 * @since 4.1
 */
trait Admin_General {

	/**
	 * Trais
	 */
	use Files;

	/**
	 * Get the current URL
	 *
	 * @return string
	 */
	public function lumiere_get_current_admin_url() {
		$current = admin_url( str_replace( site_url( '', 'relative' ) . '/wp-admin', '', esc_url( $_SERVER['REQUEST_URI'] ?? '' ) ) );
		return $current;
	}

	/**
	 * Recursively delete a directory, keeping the directory path provided
	 *
	 * @param string $dir Directory path
	 * @return bool true on success
	 * @see \Lumiere\Tools\Files::lumiere_wp_filesystem_cred() used to make sure the permissions for deleting files are ok
	 */
	public function lumiere_unlink_recursive( string $dir ): bool {

		global $wp_filesystem;
		$files = [];

		// Make sure we have the correct credentials.
		$this->lumiere_wp_filesystem_cred( $dir ); // in trait Files.

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
	public function lumiere_display_vars( ?array $options = null, ?string $set_error = null, ?string $libxml_use = null, ?string $get_screen = null ): void {

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
			set_exception_handler( [ $this, 'lumiere_exception_handler' ] );
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
	 * Lumiere internal exception handler
	 *
	 * @see Utils::lumiere_activate_debug()
	 * @param \Throwable $exception The type of new exception
	 * @return void
	 */
	public function lumiere_exception_handler( \Throwable $exception ): void {
		throw $exception;
	}

}

