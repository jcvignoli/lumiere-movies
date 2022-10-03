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
require_once __DIR__ . '/vendor/autoload.php';

# Lumiere Classes
require_once __DIR__ . '/class/class-settings.php';
require_once __DIR__ . '/class/trait-settings-global.php';
require_once __DIR__ . '/class/tools/class-pluginsdetect.php';
require_once __DIR__ . '/class/tools/class-utils.php';
require_once __DIR__ . '/class/plugins/autoload.php';
require_once __DIR__ . '/class/class-core.php'; // activated in lumiere-movies.php
