<?php declare( strict_types = 1 );

//require_once('../website/wp-load.php');

/**
 * This configuration will be read and overlaid on top of the
 * default configuration. Command line arguments will be applied
 * after this file is read.
 */
return [

	// Supported values: `'5.6'`, `'7.0'`, `'7.1'`, `'7.2'`, `'7.3'`, `'7.4'`,
	// '8.0', '8.1', '8.2', '8.3' `null`.
	// If this is set to `null`,
	// then Phan assumes the PHP version which is closest to the minor version
	// of the php executable used to execute Phan.
	// @info: never got this to work
	'target_php_version' => '8.1',

	// Issue::SEVERITY_LOW(0), Issue::SEVERITY_NORMAL(5), Issue::SEVERITY_CRITICAL(10)
	// Low is the highest level
	'minimum_severity' => 0,

	// Backwards Compatibility Checking. This is slow
	// and expensive, but you should consider running
	// it before upgrading your version of PHP to a
	// new version that has backward compatibility
	// breaks. (Also see target_php_version)
	'backward_compatibility_checks' => true,

	// If enabled, check all methods that override a
	// parent method to make sure its signature is
	// compatible with the parent's. This check
	// can add quite a bit of time to the analysis.
	'analyze_signature_compatibility' => true,

	// If true, missing properties will be created when
	// they are first seen. If false, we'll report an
	// error message if there is an attempt to write
	// to a class property that wasn't explicitly
	// defined.
	'allow_missing_properties' => false,

	// If enabled, scalars (int, float, bool, true, false, string, null)
	// are treated as if they can cast to each other.
	'scalar_implicit_cast' => false,

	// Set to true in order to attempt to detect dead (unreferenced) code.
	// Keep in mind that the results will only be a guess given that classes,
	// properties, constants and methods can be referenced as variables
	// (like $class->$property or $class->$method()) in ways that we're unable to make sense of.
	'dead_code_detection' => false,

	// If true, seemingly undeclared variables in the global
	// scope will be ignored. This is useful for projects
	// with complicated cross-file globals that you have no
	// hope of fixing.
	'ignore_undeclared_variables_in_global_scope' => false,

	// A list of directories that should be parsed for class and
	// method information. After excluding the directories
	// defined in exclude_analysis_directory_list, the remaining
	// files will be statically analyzed for errors.
	//
	// Thus, both first-party and third-party code being used by
	// your application should be included in this list.
	'directory_list' => [
		'src/',
	],

	// A list of files that should be parsed for class and
	// method information.
	// Perfect for getting bootstrap files
	'file_list' => [
		// 'tests/bootstrap-testing.php', // Doesn't work as a real bootstrap, not finding below declarations
		'tests/extra_statics_tools/phan/functions.php',
		'tests/extra_statics_tools/wpcli.php',
		'tests/extra_statics_tools/constants.php',
		'tests/extra_statics_tools/functions.php',
		'tests/extra_statics_tools/classes.php',
		'vendor/skaut/wordpress-stubs/stubs/WordPress/functions.php',
		'vendor/php-stubs/wordpress-stubs/wordpress-stubs.php',
		// 'vendor/php-stubs/wp-cli-stubs/wp-cli-stubs.php',
		'vendor/php-stubs/wp-cli-stubs/wp-cli-commands-stubs.php',
		'vendor/php-stubs/wp-cli-stubs/wp-cli-i18n-stubs.php',
	],

	// A directory list that defines files that will be excluded
	// from static analysis, but whose class and method
	// information should be included.
	//
	// Generally, you'll want to include the directories for
	// third-party code (such as "vendor/") in this list.
	//
	// n.b.: If you'd like to parse but not analyze 3rd
	//       party code, directories containing that code
	//       should be added to the `directory_list` as
	//       to `exclude_analysis_directory_list`.
	'exclude_analysis_directory_list' => [
		'tests/bootstrap-testing.php',
		'tests/extra_statics_tools/constants.php',
		'tests/extra_statics_tools/functions.php',
		'tests/extra_statics_tools/classes.php',
		'tests/extra_statics_tools/phan/functions.php',
		'src/vendor/',
		'assets/js/highslide/',
		'vendor/php-stubs/wordpress-stubs/wordpress-stubs.php',
		'vendor/php-stubs/wp-cli-stubs/wp-cli-stubs.php',
		'vendor/php-stubs/wp-cli-stubs/wp-cli-commands-stubs.php',
		'vendor/php-stubs/wp-cli-stubs/wp-cli-i18n-stubs.php',
		'vendor/skaut/wordpress-stubs/stubs',
	],

	// No need to analyse.
	'exclude_file_list' => [ 'src/vendor/duck7000/imdb-graphql-php/src/Imdb/Calendar.php' ],

	// Remove this types of errors.
	'suppress_issue_types' => [
		'PhanPluginPrintfVariableFormatString', // Phan doesn't detect correct behaviour for __(), _n(), "has a dynamic format string that could not be inferred by Phan"
	],

	// A list of plugin files to execute.
	// Plugins which are bundled with Phan can be added here by providing their name
	// (e.g. 'AlwaysReturnPlugin')
	//
	// Documentation about available bundled plugins can be found
	// at https://github.com/phan/phan/tree/v5/.phan/plugins
	//
	// Alternately, you can pass in the full path to a PHP file
	// with the plugin's implementation.
	// (e.g. 'vendor/phan/phan/.phan/plugins/AlwaysReturnPlugin.php')
	'plugins' => [
		// checks if a function, closure or method unconditionally returns.
		// can also be written as 'vendor/phan/phan/.phan/plugins/AlwaysReturnPlugin.php'
		'AlwaysReturnPlugin',
		'DollarDollarPlugin',
		'DuplicateArrayKeyPlugin',
		'DuplicateExpressionPlugin',
		'PregRegexCheckerPlugin',
		'PreferNamespaceUsePlugin',
		'UnusedSuppressionPlugin',
		'PrintfCheckerPlugin',
		'SleepCheckerPlugin',

		// Checks for syntactically unreachable statements in
		// the global scope or function bodies.
		'UnreachableCodePlugin',
		'UseReturnValuePlugin',
		'EmptyStatementListPlugin',
		'LoopVariableReusePlugin',
	],
];
