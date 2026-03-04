<?php declare( strict_types = 1 );

/**
 * Add conditionnally extra phpstan rules
 * Typically used for specific PHP versions
 */

$includes = [];
/** add this if smaller than PHP8.2 */
if ( version_compare( phpversion(), '8.2', '<' ) ) {
	/** remove not report an error */
	$includes[] = __DIR__ . '/phpstan.noerror.neon';
} 

$config = [];
$config['includes'] = $includes;
$config['parameters']['phpVersion'] = PHP_VERSION_ID;

return $config;
