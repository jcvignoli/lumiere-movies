<?php

/* Load automatically any class located in /lib/* whenever needed
 *
 */ 

function lumiere_lib_class_loader($class_name) {

	// Transforms '\' into '/'
	$class_name = str_replace('\\', '/', ltrim($class_name, '\\'));

	// Path for inclusion
	$path_to_file = plugin_dir_path( __DIR__ ) . 'vendor/lib/' .$class_name . '.php';

	if (file_exists($path_to_file)) {

		require $path_to_file;

	}

}
// Load all classes in class/Admin folder, will be loaded when needed
spl_autoload_register( 'lumiere_lib_class_loader' );

