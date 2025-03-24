<?php
// Definition of WP_CLI, because error() needs @return never

namespace {
	class WP_CLI {
		 /**
		 * Show error message.
		 *
		 * @param string $error Error message.
		 *
		 * @return never
		 */
		public static function error($error)
		{
		}
		/**
		 * Register a command to WP-CLI.
		 *
		 * WP-CLI supports using any callable class, function, or closure as a
		 * command. `WP_CLI::add_command()` is used for both internal and
		 * third-party command registration.
		 *
		 * Command arguments are parsed from PHPDoc by default, but also can be
		 * supplied as an optional third argument during registration.
		 *
		 * ```
		 * # Register a custom 'foo' command to output a supplied positional param.
		 * #
		 * # $ wp foo bar --append=qux
		 * # Success: bar qux
		 *
		 * /**
		 *  * My awesome closure command
		 *  *
		 *  * <message>
		 *  * : An awesome message to display
		 *  *
		 *  * --append=<message>
		 *  * : An awesome message to append to the original message.
		 *  *
		 *  * @when before_wp_load
		 *  *\/
		 * $foo = function( $args, $assoc_args ) {
		 *     WP_CLI::success( $args[0] . ' ' . $assoc_args['append'] );
		 * };
		 * WP_CLI::add_command( 'foo', $foo );
		 * ```
		 *
		 * @access public
		 * @category Registration
		 *
		 * @param string   $name Name for the command (e.g. "post list" or "site empty").
		 * @param callable|object|string $callable Command implementation as a class, function or closure.
		 * @param array    $args {
		 *    Optional. An associative array with additional registration parameters.
		 *
		 *    @type callable $before_invoke Callback to execute before invoking the command.
		 *    @type callable $after_invoke  Callback to execute after invoking the command.
		 *    @type string   $shortdesc     Short description (80 char or less) for the command.
		 *    @type string   $longdesc      Description of arbitrary length for examples, etc.
		 *    @type string   $synopsis      The synopsis for the command (string or array).
		 *    @type string   $when          Execute callback on a named WP-CLI hook (e.g. before_wp_load).
		 *    @type bool     $is_deferred   Whether the command addition had already been deferred.
		 * }
		 * @return bool|void True on success, false if deferred, hard error if registration failed.
		 */
		public static function add_command($name, $callable, $args = [])
		{
		}
		/**
		 * Display informational message without prefix.
		 *
		 * Message is written to STDOUT, or discarded when `--quiet` flag is supplied.
		 *
		 * ```
		 * # `wp cli update` lets user know of each step in the update process.
		 * WP_CLI::log( sprintf( 'Downloading from %s...', $download_url ) );
		 * ```
		 *
		 * @access public
		 * @category Output
		 *
		 * @param string $message Message to write to STDOUT.
		 */
		public static function log($message)
		{
		}
		/**
		 * Display success message prefixed with "Success: ".
		 *
		 * Success message is written to STDOUT.
		 *
		 * Typically recommended to inform user of successful script conclusion.
		 *
		 * ```
		 * # wp rewrite flush expects 'rewrite_rules' option to be set after flush.
		 * flush_rewrite_rules( \WP_CLI\Utils\get_flag_value( $assoc_args, 'hard' ) );
		 * if ( ! get_option( 'rewrite_rules' ) ) {
		 *     WP_CLI::warning( "Rewrite rules are empty." );
		 * } else {
		 *     WP_CLI::success( 'Rewrite rules flushed.' );
		 * }
		 * ```
		 *
		 * @access public
		 * @category Output
		 *
		 * @param string $message Message to write to STDOUT.
		 * @return null
		 */
		public static function success($message)
		{
		}
	}
}
