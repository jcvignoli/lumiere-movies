<?php declare( strict_types = 1 );
/**
 * Core Class
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       4.1
 * @package       lumieremovies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Cache\Cache_Files_Management;
use Lumiere\Config\Get_Options;
use Lumiere\Hooks_Updates;
use Lumiere\Plugins\Logger;
use Lumiere\Tools\Files;

/**
 * Main WordPress actions happen here
 * Calling all actions and filters
 * Hooks for automatic and manual updates and cron_exec_once are available in Hooks_Updates class
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @since 4.1.2 WP Cli commands compatible
 * @since 4.6.1 Moved update-related hooks to a parent class Hooks_Updates
 */
final class Core extends Hooks_Updates {

	/**
	 * Traits
	 */
	use Files;

	/**
	 * Constructor
	 */
	public function __construct () {

		// Get updates hooks
		parent::__construct();

		/**
		 * Widgets fire at init hook equivalent to priority 0, so they must either be called here with 'widgets_init' or with 'init' priority 0
		 * https://developer.wordpress.org/reference/hooks/widgets_init/#comment-2643
		 * They're not only for admin area, since they're executed in the frontpage as well
		 */
		add_action( 'widgets_init', [ 'Lumiere\Admin\Widget_Selection', 'start' ] );

		/**
		 * Taxonomy, must be executed on the whole website
		 */
		add_action( 'init', [ 'Lumiere\Alteration\Taxonomy', 'start' ], 10, 0 ); // @since 4.3: No need to pass args.

		/**
		 * Rewrite rules, must be executed on the whole website
		 */
		add_action( 'init', [ 'Lumiere\Alteration\Rewrite_Rules', 'start' ] );

		/**
		 * Admin
		 */
		add_action( 'init', [ 'Lumiere\Admin\Admin', 'start' ], 9 ); // Priority must be below 10.

		/**
		 * Frontpage
		 */
		add_action( 'init', [ 'Lumiere\Frontend\Frontend', 'start' ] );

		/**
		 * Crons. Must be executed on the whole website
		 */
		add_action( 'init', [ 'Lumiere\Admin\Crons\Cron', 'start' ] );

		/**
		 * Gutenberg blocks, must be executed on the whole website
		 */
		add_action( 'init', [ $this, 'lum_enqueue_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'lum_execute_blocks' ] );

		// WP-CLI commands, use the cli class.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			add_action( 'cli_init', [ 'Lumiere\Tools\Cli_Commands', 'start' ] );
		}
	}

	/**
	 * Run on plugin activation
	 * @return void All activation functions have been executed
	 */
	public function lumiere_on_activation(): void {

		// Start Logger class.
		$logger = new Logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );
		$imdb_admin_values = get_option( Get_Options::get_cache_tablename() );

		/* First install, create everything that is required */
		if ( $imdb_admin_values === false ) {

			// Create the options in database.
			Get_Options::create_database_options();

			// Create the debug file if WP_DEBUG and 'imdbdebug' are defined.
			/** @psalm-var OPTIONS_ADMIN $imdb_admin_values */
			$imdb_admin_values = get_option( Get_Options::get_cache_tablename() );
			if (
				defined( 'WP_DEBUG' )
				&& isset( $imdb_admin_values['imdbdebug'] )
				&& $imdb_admin_values['imdbdebug'] === '1'
				&& isset( $imdb_admin_values['imdbdebuglogpath'] )
			) {
				$this->maybe_create_log( $imdb_admin_values ); // in Files trait
			}
			$logger->log?->info( '[coreClass][activation] Lumière options and log successfully created.' );
		} else {
			$logger->log?->info( '[coreClass][activation] Lumière options already exists.' );
		}

		/* Create the cache folders */

		// Make sure cache folder exists and is writable.
		$cache_mngmt_class = new Cache_Files_Management();

		if ( $cache_mngmt_class->lumiere_create_cache() === true ) {
			$logger->log?->info( '[coreClass][activation] Lumière cache successfully created.' );
		} else {
			$logger->log?->info( '[coreClass][activation] Lumière cache could not be created (check permissions?)' );
		}

		// Set up WP Cron exec once if it doesn't exist.
		if ( parent::lum_setup_cron_exec_once( $logger, 'activation' ) === false ) {
			$logger->log?->error( '[coreClass][activation] Cron lumiere_exec_once_update was not set up (maybe an issue during activation?)' );
		}

		$logger->log?->debug( '[coreClass][activation] Lumière plugin activated.' );
	}

	/**
	 * Run on plugin deactivation
	 * @return void All deactivation functions have been executed
	 */
	public function lumiere_on_deactivation(): void {

		// Start Logger class.
		$logger = new Logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		// Remove WP lumiere crons should they exist.
		$list_crons_available = [ 'lumiere_exec_once_update', 'lumiere_cron_deletecacheoversized', 'lumiere_cron_autofreshcache' ];
		foreach ( $list_crons_available as $cron_installed ) {
			wp_clear_scheduled_hook( $cron_installed );
			$logger->log?->info( '[coreClass][deactivation] Cron ' . $cron_installed . ' removed' );
		}

		// Reset options related to crons, since we removed them.
		$current_admin = get_option( Get_Options::get_cache_tablename() );
		$current_admin['imdbcacheautorefreshcron'] = '0';
		$current_admin['imdbcachekeepsizeunder'] = '0';
		update_option( Get_Options::get_cache_tablename(), $current_admin );

		$logger->log?->info( '[coreClass][deactivation] Lumière deactivated' );
	}

	/**
	 * Register gutenberg blocks, must be executed on the whole website
	 *
	 * @since 4.1   Using block.json, added script translation, added lumiere_scripts_admin_gutenberg script
	 * @since 4.7   Moved from Admin\Admin to Core, must be executed on the whole website
	 *      Using wp_register_block_types_from_metadata_collection() which is compatible with WP >= 6.8 only
	 *      Simplified the use of register_block_type() and removed render_callback as render.php is functionless
	 *
	 * @see \Lumiere\Admin\Widget_Selection::lumiere_register_widget_block() Widget block may have to be registered there, not included here
	 */
	public function lum_enqueue_blocks(): void {

		// Register gutenberg admin scripts.
		wp_register_script(
			'lumiere_scripts_admin_gutenberg',
			Get_Options::LUM_JS_URL . 'lumiere_scripts_admin_gutenberg.min.js',
			[ 'jquery' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere_scripts_admin_gutenberg.min.js' ) ),
			false
		);

		$block_dir = LUM_WP_PATH . 'assets/blocks/';

		// Use metadata manifest to register merged block.json files, WP >= 6.8
		if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) && file_exists( Get_Options::LUM_BLOCKS_MANIFEST ) ) {
			wp_register_block_types_from_metadata_collection(
				$block_dir,
				Get_Options::LUM_BLOCKS_MANIFEST
			);
			return;
		}

		// Pre-WP 6.8, don't register widget block
		$blocks = [ 'post', 'addlink', 'coming-soon', 'opensearch', 'widget-sidebar-options' ];
		foreach ( $blocks as $block ) {
			register_block_type( $block_dir . $block );
		}
	}

	/**
	 * Enqueue gutenberg javascripts
	 */
	public function lum_execute_blocks(): void {

			// Script for click on gutenberg block link to open a popup
			wp_enqueue_script( 'lumiere_scripts_admin_gutenberg' );
	}
}

