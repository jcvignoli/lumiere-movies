<?php declare( strict_types = 1 );
/**
 * Files Trait
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

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Trait for files operations
 * @TODO Pass most Utils class methods here
 */
trait Files {

	/**
	 * Include the template if it exists and pass to it as a/many variable/s using transient
	 * The transiant has a validity time of 30 seconds by default
	 *
	 * @param string $file_name Template file name
	 * @param array<int, object|string|int|array<\Imdb\Person|\Imdb\Title|string|bool|int|array<int|string>>> $variables The variables transfered to the include
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
	protected function find_template_file( string $file_name ): string {

		$templates_dir = plugin_dir_path( __DIR__ ) . 'templates/';
		
		$folder_iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $templates_dir, RecursiveDirectoryIterator::SKIP_DOTS )
		);
		
		foreach ( $folder_iterator as $file ) {
			if ( str_contains( $file->getPathname(), $file_name ) ) {
				return $file->getPathname();
			}
		}

		throw new \Exception( __( 'No template file found', 'lumiere-movies' ) . ' ' . $file_name );
	}
}

