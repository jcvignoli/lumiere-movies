<?php declare( strict_types = 1 );
/**
 * Bootstrap of plugin files
 * @phpcs:disable PEAR.Files.IncludingFile
 */

namespace Lumiere\Plugins;

#	Lumiere Plugin Classes
#include_once __DIR__ . '/class-highslide.php';
#include_once __DIR__ . '/class-logger.php';
#include_once __DIR__ . '/class-imdbphp.php';

/**
 * Load all files included in class/plugins
 * Loaded in spl_autoload_register()
 *
 * @param string $class_name Class name automagically retrieved from spl_autoload_register()
 */
function lumiere_plugin_loader( string $class_name ): void {

	$parts = explode( '\\', $class_name );
	$class = strtolower( array_pop( $parts ) );
	$folder = strtolower( implode( DIRECTORY_SEPARATOR, $parts ) );

	// Final path for inclusion
	$classpath = plugin_dir_path( __DIR__ ) . 'plugins' . DIRECTORY_SEPARATOR . 'class-' . $class . '.php';

	if ( file_exists( $classpath ) ) {

		require_once $classpath;

	}

}

// Load all classes in class/admin folder, will be loaded when needed
spl_autoload_register( __NAMESPACE__ . '\lumiere_plugin_loader' );

