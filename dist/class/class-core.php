<?php declare( strict_types = 1 );
/**
 * Core Class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       4.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
lum_check_display();

use Lumiere\Settings;
use Lumiere\Admin\Cache_Tools;
use Lumiere\Plugins\Logger;
use Lumiere\Updates;
use FilesystemIterator;

/**
 * Main WordPress actions happen here
 * Calling all actions and filters
 * Since WP 6.7, getting "Notice: Function _load_textdomain_just_in_time was called incorrectly." if Logger class is executed before init hook
 * -> removed a property that save it, each method initiates the class itself now. In order to get the log, it must be executed before the init,
 * but as a result the notice may be thrown for each method. No solution yet.
 *
 * @since 4.1.2 WP Cli commands compatible
 * @TODO Since 4.1.1 an update version check is now executed on every admin page, find a better hook
 */
class Core {

	/**
	 * Constructor
	 */
	public function __construct () {

		/**
		 * Widgets fire at init priority 0, so they must either be called here with 'widgets_init' or with 'init' priority 0
		 * https://developer.wordpress.org/reference/hooks/widgets_init/#comment-2643
		 * They're not only for admin area, since they're executed in the frontpage as well
		 */
		add_action( 'widgets_init', fn() => Admin\Widget_Selection::lumiere_static_start() );

		/**
		 * Taxonomy, must be executed on the whole website
		 */
		add_action( 'registered_taxonomy', fn() => Alteration\Taxonomy::lumiere_static_start() );

		/**
		 * Rewrite rules, must be executed on the whole website
		 */
		add_action( 'init', fn() => Alteration\Rewrite_Rules::lumiere_static_start() );

		/**
		 * Gutenberg blocks, must be executed on the whole website
		 */
		add_action( 'enqueue_block_editor_assets', [ $this, 'lum_enqueue_blocks' ] );

		/**
		 * Admin
		 */
		add_action( 'init', fn() => Admin\Admin::lumiere_static_start(), 9 ); // Priority must be below 10.

		/**
		 * Frontpage
		 */
		add_action( 'init', fn() => Frontend\Frontend::lumiere_static_start() );

		/**
		 * Updates. Must be free of any conditions.
		 */
		// On updating the plugin.
		add_action( 'automatic_updates_complete', [ $this, 'lum_on_plugin_autoupdate' ], 10, 1 );
		add_action( 'upgrader_process_complete', [ $this, 'lum_on_plugin_manualupdate' ], 10, 2 );

		// On any admin page, check if an update is needed. Extra opportunity for update. @todo Find a better hook
		add_action( 'admin_init', [ $this, 'lum_update_needed' ] );

		/**
		 * Crons. Must be free of any conditions.
		 */
		// Crons schedules.
		add_action( 'init', fn() => Admin\Cron::lumiere_cron_start() );

		// Call the translation.
		add_action( 'init', [ $this, 'lum_load_translation' ] );

		// WP-CLI commands, use the cli class and stop the execution.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			add_action( 'cli_init', fn() => Tools\Cli_Commands::lumiere_static_start() );
		}
	}

	/**
	 * Load language textdomain
	 * This function allows translate all English strings
	 *
	 * @since 4.1.9 Supposedly not needed https://make.wordpress.org/core/2016/07/06/i18n-improvements-in-4-6/ but actually it is!
	 */
	public function lum_load_translation(): void {
		load_plugin_textdomain( 'lumiere-movies', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Register and enqueue gutenberg blocks, must be executed on the whole website
	 *
	 * @since 4.1 Using block.json, added script translation, added lumiere_scripts_admin_gutenberg script
	 * @see \Lumiere\Admin\Widget_Selection::lumiere_register_widget_block() which registers gutenberg widget blocks
	 */
	public function lum_enqueue_blocks(): void {
		$blocks = [ 'movie', 'addlink', 'opensearch' ];
		$block_dir = LUMIERE_WP_PATH . 'assets/blocks';

		foreach ( $blocks as $block ) {
			register_block_type( $block_dir . '/' . $block );
			add_action(
				'init',
				function( string $block ) {
					wp_set_script_translations( 'lumiere-' . $block . '-editor-script', 'lumiere-movies', LUMIERE_WP_PATH . 'languages/' );
				}
			);
		}

		// Javascript for Gutenberg blocks only.
		wp_register_script(
			'lumiere_scripts_admin_gutenberg',
			LUMIERE_WP_URL . 'assets/js/lumiere_scripts_admin_gutenberg.min.js',
			[],
			strval( filemtime( LUMIERE_WP_PATH . 'assets/js/lumiere_scripts_admin_gutenberg.min.js' ) ),
			true
		);
		wp_enqueue_script( 'lumiere_scripts_admin_gutenberg' );
	}

	/**
	 * Run on lumiere WordPress manual upgrade (not waiting for the regular update, but clicking on update now)
	 * @since 4.1.1 Added an extra cron exec once that executes the latest update
	 *
	 * @param \WP_Upgrader $upgrader_object Upgrader class, not in use
	 * @param mixed[] $options Type of update process, such as 'plugin', 'theme', 'translation' or 'core'
	 */
	public function lum_on_plugin_manualupdate( \WP_Upgrader $upgrader_object, array $options ): void {

		// Start Logger class.
		$logger = new Logger( 'coreClass' );

		// Start the logger.
		$logger->lumiere_start_logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		// If an update has taken place and the updated type is plugins and the plugins element exists.
		if ( $options['type'] === 'plugin' && $options['action'] === 'update' && isset( $options['plugins'] ) ) {

			// Iterate through the plugins being updated and check if ours is there.
			foreach ( $options['plugins'] as $plugin ) {

				// It is Lumière!, so run the functions.
				if ( $plugin === 'lumiere-movies/lumiere-movies.php' ) {

					$logger->log()->debug( '[Lumiere][coreClass][manualupdate] Starting Lumière manual update' );
					$start_update_options = new Updates();
					$start_update_options->run_update_options();

					// Set up WP Cron exec once if it doesn't exist.
					if ( $this->lum_setup_cron_exec_once( $logger, 'manualupdate' ) === false ) {
						$logger->log()->error( '[Lumiere][coreClass][autoupdate] Cron lumiere_exec_once_update was not set up (maybe an issue during activation?)' );
					}
					$logger->log()->debug( '[Lumiere][coreClass][manualupdate] Lumière manual update processed.' );
				}
			}
		}
	}

	/**
	 * Run on Lumiere! WordPress auto update
	 * @since 4.1.1 Added an extra cron exec once that executes the latest update
	 *
	 * @param array<string, array<int, object>> $results Array of plugins updated
	 * @return void Plugin updated, log about success or not
	 */
	public function lum_on_plugin_autoupdate( array $results ): void {

		// Start Logger class.
		$logger = new Logger( 'coreClass' );

		// Start the logger.
		$logger->lumiere_start_logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		// Exit if not exist.
		if ( ! isset( $results['plugin'] ) ) {
			return;
		}

		// Iterate through the plugins being updated and check if ours is there.
		foreach ( $results['plugin'] as $plugin ) {

			if (
				// @phpstan-ignore-next-line Access to an undefined property object::$item
				isset( $plugin->item->slug )
				&& strlen( $plugin->item->slug ) > 0
				&& $plugin->item->slug === 'lumiere-movies'
			) {

				// It is Lumière!, so run the functions.
				$logger->log()->debug( '[Lumiere][coreClass][autoupdate] Starting Lumière automatic update' );
				$start_update_options = new Updates();
				$start_update_options->run_update_options();

				// Set up WP Cron exec once if it doesn't exist
				if ( $this->lum_setup_cron_exec_once( $logger, 'autoupdate' ) === false ) {
					$logger->log()->error( '[Lumiere][coreClass][autoupdate] Cron lumiere_exec_once_update was not set up (maybe an issue during activation?)' );
				}
				$logger->log()->debug( '[Lumiere][coreClass][autoupdate] Lumière autoupdate processed.' );
			}

		}
	}

	/**
	 * Run on plugin activation
	 * @return void all activation functions have been executed
	 */
	public function lumiere_on_activation(): void {

		// Start Logger class.
		$logger = new Logger( 'coreClass' );

		// Start the logger.
		$logger->lumiere_start_logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		$current_admin = get_option( Settings::get_cache_tablename() );

		/* Create the value of number of updates on first install */
		if ( ! isset( $current_admin['imdbHowManyUpdates'] ) ) {
			// Start Settings class to create it. Not optimal.
			$settings_class = new Settings();
			$logger->log()->info( "[Lumiere][coreClass][activation] Lumière option 'imdbHowManyUpdates' successfully created." );
		} else {
			$logger->log()->info( "[Lumiere][coreClass][activation] Lumière option 'imdbHowManyUpdates' already exists." );
		}

		/* Create the cache folders */

		// Make sure cache folder exists and is writable.
		$cache_tools_class = new Cache_Tools();

		if ( $cache_tools_class->lumiere_create_cache() === true ) {
			$logger->log()->info( '[Lumiere][coreClass][activation] Lumière cache successfully created.' );
		} else {
			$logger->log()->info( '[Lumiere][coreClass][activation] Lumière cache could not be created (check permissions?)' );
		}

		// Set up WP Cron exec once if it doesn't exist.
		if ( $this->lum_setup_cron_exec_once( $logger, 'activation' ) === false ) {
			$logger->log()->error( '[Lumiere][coreClass][activation] Cron lumiere_exec_once_update was not set up (maybe an issue during activation?)' );
		}

		$logger->log()->debug( '[Lumiere][coreClass][activation] Lumière plugin activated.' );
	}

	/**
	 * Run on plugin deactivation
	 * @return void all deactivation functions have been executed
	 */
	public function lumiere_on_deactivation(): void {

		// Start Logger class.
		$logger = new Logger( 'coreClass' );

		// Start the logger.
		$logger->lumiere_start_logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		// Remove WP lumiere crons should they exist.
		$list_crons_available = [ 'lumiere_exec_once_update', 'lumiere_cron_deletecacheoversized', 'lumiere_cron_autofreshcache' ];
		foreach ( $list_crons_available as $cron_installed ) {
			wp_clear_scheduled_hook( $cron_installed );
			$logger->log()->info( '[Lumiere][coreClass][deactivation] Cron ' . $cron_installed . ' removed' );
		}

		// Reset options related to crons, since we removed them.
		$current_admin = get_option( Settings::get_cache_tablename() );
		$current_admin['imdbcacheautorefreshcron'] = '0';
		$current_admin['imdbcachekeepsizeunder'] = '0';
		update_option( Settings::get_cache_tablename(), $current_admin );

		$logger->log()->info( '[Lumiere][coreClass][deactivation] Lumière deactivated' );
	}

	/**
	 * Check if an upate is needed on every WordPress admin page
	 * Check Lumière! version in database against the number of files in update folder
	 * @since 4.1.1
	 *
	 * @return void An update is run if Lumiere! version was lagging behind a new version
	 */
	public function lum_update_needed() {

		$current_admin = get_option( Settings::get_admin_tablename() );
		$files = new FilesystemIterator( LUMIERE_WP_PATH . 'class/updates/', FilesystemIterator::SKIP_DOTS );
		$nb_of_files_in_updates_folder = iterator_count( $files );

		// Check if the number of updates in database is greater than the number of update files in updates folder
		if ( isset( $current_admin['imdbHowManyUpdates'] ) && $current_admin['imdbHowManyUpdates'] <= $nb_of_files_in_updates_folder ) {

			// Start Logger class.
			$logger = new Logger( 'coreClass' );

			$logger->log()->debug( '[Lumiere][coreClass][is_plugin_updated] An update is needed, starting the update...' );
			$start_update_options = new Updates();
			$start_update_options->run_update_options();

			set_transient( 'notice_lumiere_msg', 'lum_plugin_updated', 2 );
			add_action( 'admin_notices', [ '\Lumiere\Admin\Admin_Notifications', 'lumiere_static_start' ] );
			delete_transient( 'lum_plugin_updated' );
		}

	}

	/**
	 * Set up WP Cron exec once if it doesn't exist
	 * It is recommended to add this to the update processes, as adding a cron ensure the previous update is run but also the new one
	 * @since 4.1.1
	 *
	 * @param Logger $logger Class log, make sure it is active
	 * @param string $log_string The string to append to the log
	 * @return bool True if the cron was successfuly installed
	 */
	private function lum_setup_cron_exec_once( Logger $logger, string $log_string = 'install_exec_once_update' ): bool {

		if ( wp_next_scheduled( 'lumiere_exec_once_update' ) === false ) {
			// Cron to run once, in 2 minutes.
			wp_schedule_single_event( time() + 120, 'lumiere_exec_once_update' );
			$logger->log()->debug( '[Lumiere][coreClass][' . $log_string . '] Lumière cron lumiere_exec_once_update successfully set up.' );
			return true;
		}
		return false;
	}
}

