<?php declare( strict_types = 1 );
/**
 * Bootstrap of plugin files
 * @phpcs:disable PEAR.Files.IncludingFile
 */

namespace Lumiere\Link_Makers;

/**
 * Load all files included in class/plugins
 * Loaded in spl_autoload_register()
 *
 * @param string $class_name Class name automagically retrieved from spl_autoload_register()
 */
function lumiere_link_makers_loader( string $class_name ): void {

	$parts = explode( '\\', $class_name );
	$class = strtolower( array_pop( $parts ) );
	$class_cleaned = str_replace( '_', '-', $class );

	// Final path for inclusion
	$classpath = plugin_dir_path( __DIR__ ) . 'link_makers' . DIRECTORY_SEPARATOR . 'class-' . $class_cleaned . '.php';

	if ( file_exists( $classpath ) ) {

		require_once $classpath;

	}

}

// Load all classes in class/admin folder, will be loaded when needed
spl_autoload_register( __NAMESPACE__ . '\lumiere_link_makers_loader' );

