<?php
/**
 * Purpose: provide testing tools with all missing constants and variables not reachable
 * These files exists on webserver and local development, but not in git
 * Currently in use by Psalm and PHPStan, Phan has these in its config
 */
// @phpcs:ignoreFile

//ini_set( 'PHP_INI_PERDIR', true); -> expected behaviour on webserver, but static tools can't read .user.ini...

// General constants.
require_once 'extra_statics_tools/constants.php';
require_once 'extra_statics_tools/classes.php';
require_once 'extra_statics_tools/functions.php';

// Extra classes
//require_once 'extra_statics_tools/phan/irp-core.php'; // add irp extra classes -> breaks Psalm that can't find add_filter() functions


