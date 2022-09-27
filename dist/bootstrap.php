<?php declare( strict_types = 1 );
/**
 * Bootstrap of selected files
 * @phpcs:disable PEAR.Files.IncludingFile
 */

/**
 * Note: Classes with spl_autoload_register():
 * class/class-updates.php,
 * class/plugins/autoload.php,
 * class/class-admin.php,
 * vendor/autoload.php
 * Don't want to make an autoload for all classes, some will be called only under certain circonstances (admin, etc)
 */

# Vendor Libraries
include_once __DIR__ . '/vendor/autoload.php';

# Lumiere Classes
include_once __DIR__ . '/class/class-settings.php';
include_once __DIR__ . '/class/trait-settings-global.php';
include_once __DIR__ . '/class/tools/class-pluginsdetect.php';
include_once __DIR__ . '/class/tools/class-utils.php';
include_once __DIR__ . '/class/plugins/autoload.php';
include_once __DIR__ . '/class/class-core.php'; // activated in lumiere-movies.php
