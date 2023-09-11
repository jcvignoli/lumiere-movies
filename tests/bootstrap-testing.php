<?php
/**
 * Purpose: provide testing tools with all missing constants and variables not reachable
 * These files exists on webserver and local development, but not in git
 */
// @phpcs:ignoreFile

//ini_set( 'PHP_INI_PERDIR', true); -> expected behaviour on webserver, but static tools can't read .user.ini...

// General constants.
require_once 'configs_static_tools/constants.php';
require_once 'configs_static_tools/extras.php';

// Bootstrap
require_once 'src/vendor/autoload.php';

