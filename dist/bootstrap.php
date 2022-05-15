<?php declare( strict_types = 1 );
/**
 * Bootstrap of selected files
 * @phpcs:disable PEAR.Files.IncludingFile
 */

/**
 * Note: Classes class/class-updates.php, class/class-admin.php, vendor/autoload.php have a spl_autoload_register()
 * Can't make it general, the class called in each file is different
 */

#   Vendor Libraries
include_once __DIR__ . '/vendor/autoload.php'; // with spl_autoload_register().

#	Lumiere Classes (no autoload, don't want all classes)
include_once __DIR__ . '/class/class-settings.php';
include_once __DIR__ . '/class/trait-settings-global.php';
include_once __DIR__ . '/class/tools/class-utils.php';
include_once __DIR__ . '/class/plugins/autoload.php';
include_once __DIR__ . '/class/class-core.php'; // activated in lumiere-movies.php
include_once __DIR__ . '/class/frontend/trait-frontend.php';
require_once __DIR__ . '/class/frontend/link_makers/autoload.php';
include_once __DIR__ . '/class/tools/class-pluginsdetect.php';
include_once __DIR__ . '/class/frontend/class-movie.php';
include_once __DIR__ . '/class/class-widget.php';

