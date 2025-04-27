<?php declare( strict_types = 1 );
/**
 * Hooks Updates class
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Plugins\Logger;
use Lumiere\Updates;
use FilesystemIterator;

/**
 * All hooks for automatic or manual updates
 * Need for update is also checked on
 * @since 4.6.1
 */
class Hooks_Updates {

	/**
	 * Constructor
	 */
	public function __construct () {

		// On updating the plugin.
		add_action( 'automatic_updates_complete', [ $this, 'lum_on_plugin_autoupdate' ], 10, 1 );
		add_action( 'upgrader_process_complete', [ $this, 'lum_on_plugin_manualupdate' ], 10, 2 );

		// On any admin page, check if an update is needed. Extra opportunity for update.
		add_action( 'admin_init', [ $this, 'lum_update_needed' ] );
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
		$logger = new Logger( 'hooksUpdates', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		// If an update has taken place and the updated type is plugins and the plugins element exists.
		if ( $options['type'] === 'plugin' && $options['action'] === 'update' && isset( $options['plugins'] ) ) {

			// Iterate through the plugins being updated and check if ours is there.
			foreach ( $options['plugins'] as $plugin ) {

				// It is Lumière!, so run the functions.
				if ( $plugin === 'lumiere-movies/lumiere-movies.php' ) {

					$logger->log?->debug( '[hooksUpdates][manualupdate] Starting Lumière manual update' );
					$start_update_options = new Updates();
					$start_update_options->run_update_options();

					// Set up WP Cron exec once if it doesn't exist.
					if ( $this->lum_setup_cron_exec_once( $logger, 'manualupdate' ) === false ) {
						$logger->log?->error( '[hooksUpdates][autoupdate] Cron lumiere_exec_once_update was not set up (maybe an issue during activation?)' );
					}
					$logger->log?->debug( '[hooksUpdates][manualupdate] Lumière manual update processed.' );
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
		$logger = new Logger( 'hooksUpdates', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

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
				$logger->log?->debug( '[hooksUpdates][autoupdate] Starting Lumière automatic update' );
				$start_update_options = new Updates();
				$start_update_options->run_update_options();

				// Set up WP Cron exec once if it doesn't exist
				if ( $this->lum_setup_cron_exec_once( $logger, 'autoupdate' ) === false ) {
					$logger->log?->error( '[hooksUpdates][autoupdate] Cron lumiere_exec_once_update was not set up (maybe an issue during activation?)' );
				}
				$logger->log?->debug( '[hooksUpdates][autoupdate] Lumière autoupdate processed.' );
			}

		}
	}

	/**
	 * Check if an upate is needed on every WordPress admin page
	 * Check Lumière! version in database against the number of files in update folder
	 * @since 4.1.1
	 * @todo Find a better hook than admin_init
	 *
	 * @return void An update is run if Lumiere! version was lagging behind a new version
	 */
	public function lum_update_needed() {

		$current_admin = get_option( Get_Options::get_admin_tablename() );
		$files = new FilesystemIterator( LUM_WP_PATH . Get_Options::LUM_UPDATES_PATH, FilesystemIterator::SKIP_DOTS );
		$nb_of_files_in_updates_folder = iterator_count( $files );

		// Check if the number of updates in database is greater than the number of update files in updates folder
		if ( isset( $current_admin['imdbHowManyUpdates'] ) && $current_admin['imdbHowManyUpdates'] <= $nb_of_files_in_updates_folder ) {

			// Start Logger class.
			$logger = new Logger( 'hooksUpdates', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

			$logger->log?->info( '[hooksUpdates][is_plugin_updated] An update is needed, starting the update...' );
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
	 * @see \Lumiere\Core::lumiere_on_activation() Use this method
	 *
	 * @param null|Logger $logger Class log, make sure it is active
	 * @param string $log_string The string to append to the log
	 * @return bool True if the cron was successfuly installed
	 */
	protected function lum_setup_cron_exec_once( ?Logger $logger, string $log_string = 'install_exec_once_update' ): bool {

		if ( wp_next_scheduled( 'lumiere_exec_once_update' ) === false ) {
			// Cron to run once, in 2 minutes.
			wp_schedule_single_event( time() + 120, 'lumiere_exec_once_update' );
			$logger?->log?->debug( '[hooksUpdates][' . $log_string . '] Lumière cron lumiere_exec_once_update successfully set up.' );
			return true;
		}
		return false;
	}
}

