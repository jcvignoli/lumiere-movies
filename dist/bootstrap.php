<?php declare( strict_types = 1 );
/**
 * Bootstrap of selected files
 * @phpcs:disable PEAR.Files.IncludingFile
 */

#	Vendor Libraries with autoload (notably IMDbPHP)
include_once __DIR__ . '/vendor/autoload.php';

#	Lumiere Classes (no autoload, don't want all classes)
include_once __DIR__ . '/class/tools/class-utils.php';
include_once __DIR__ . '/class/core.php';
include_once __DIR__ . '/class/frontend/trait-frontend.php';
include_once __DIR__ . '/class/tools/class-logger.php';
include_once __DIR__ . '/class/settings.php';
include_once __DIR__ . '/class/frontend/class-movie.php';
include_once __DIR__ . '/class/metabox.php';
include_once __DIR__ . '/class/widget.php';

