<?php
/**
 * Purpose: provide testing tools with all missing constants and variables not reachable
 * These files exists on webserver and local development, but not in git
 * Currently in use by Psalm and PHPStan has these in its config
 * Phan is not using it
 */
// @phpcs:ignoreFile

//ini_set( 'PHP_INI_PERDIR', true); -> expected behaviour on webserver, but static tools can't read .user.ini...

// General constants.
require_once 'extra_statics_tools/constants.php';
require_once 'extra_statics_tools/functions.php';
require_once 'extra_statics_tools/classes.php';

/** phan must not know this, so define it here */
function lum_protect_direct_call(){}

// Bootstrap
require_once 'src/vendor/autoload.php';

// Global functions
// deactivated 01/2026
//require_once 'src/functions.php';
