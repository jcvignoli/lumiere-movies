<?php declare( strict_types = 1 );

/**
 * Add conditionnally extra phpstan rules
 * Typically used for specific PHP versions
 * Must be called in main PHPStan neon config file
 */

$includes = [];
/** add this if smaller than PHP8.2 */
if (PHP_VERSION_ID < 80200) {
	$includes[] = __DIR__ . '/phpstan.ci.81.neon';
} 

$config = [];
$config['includes'] = $includes;
$config['parameters']['phpVersion'] = PHP_VERSION_ID;

return $config;
