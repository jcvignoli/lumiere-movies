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

// Extra classes
//require_once 'extra_classes/irp-core.php'; // add irp extra classes -> breaks Psalm that can't find add_filter() functions

// Bootstrap
require_once 'src/vendor/autoload.php';

// Global functions
require_once 'src/functions.php';

