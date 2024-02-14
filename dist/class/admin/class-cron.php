<?php declare( strict_types = 1 );
/**
 * Crons
 *
 * @author      Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright   2023, Lost Highway
 *
 * @version     1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Settings;
use Lumiere\Admin\Cache_Tools;
use Lumiere\Plugins\Logger;
use Lumiere\Updates;

/**
 * Manage crons
 * Called with init hook in class core
 *
 * @since 3.12 add/remove cache cron moved from class cache to here
 *
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Settings
 */
class Cron {

	/**
	 * @var array<string, string> $imdb_cache_values
	 * @phpstan-var OPTIONS_CACHE $imdb_cache_values
	 */
	private array $imdb_cache_values;

	/**
	 * @var Logger $logger Logger class
	 */
	private Logger $logger;

	/**
	 * Return the default suggested privacy policy content.
	 *
	 * @return void The default policy content has been added to WP policy page
	 */
	public function __construct() {

		$this->logger = new Logger( 'cronClass' );

		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

		// When 'lumiere_cron_hook' cron is scheduled, this is what is executed.
		add_action( 'lumiere_cron_hook', [ $this, 'lumiere_cron_exec_once' ], 0 );

		// When 'lumiere_cron_deletecacheoversized' cron is scheduled, this is what is executed.
		add_action( 'lumiere_cron_deletecacheoversized', [ $this, 'lumiere_cron_exec_cache' ], 0 );

		// Add or remove crons.
		add_action( 'init', [ $this, 'lumiere_add_remove_crons_schedule' ] );
	}

	/**
	 * Static instanciation of the class
	 * Needed to be called in add_actions()
	 *
	 * @return void The class was instanciated
	 */
	public static function lumiere_cron_start(): void {
		$rewrite_class = new self();
	}

	/**
	 * Cron that runs once
	 * Perfect for updates, runs once after install
	 */
	public function lumiere_cron_exec_once(): void {

		$this->logger->log()->debug( '[Lumiere][cronClass] Cron running once started...' );

		// Update class.
		// this udpate is also run in upgrader_process_complete, but the process is not always reliable.
		$start_update_options = new Updates();
		$start_update_options->run_update_options();

	}

	/**
	 * Cache Cron to run execute weekly cache
	 */
	public function lumiere_cron_exec_cache(): void {

		$this->logger->log()->debug( '[Lumiere][cronClass] Cron deletint cache running...' );

		$cache_class = new Cache_Tools();
		$cache_class->lumiere_cache_delete_files_over_limit(
			intval( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] )
		);
	}

	/**
	 * Depending on the settings and if there is the correct transient passed from class cache, add or remove crons schedule
	 *
	 * @return void Crons schedules have been added or removed, or exit if no update was selected in class cache
	 */
	public function lumiere_add_remove_crons_schedule(): void {

		// No update was thrown from class cache, exit.
		if ( get_transient( 'cron_settings_updated' ) === false ) {
			return;
		}

		// Set up cron
		if (
			$this->imdb_cache_values['imdbcachekeepsizeunder'] === '1'
			&& intval( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] ) > 0
		) {
			// Add WP cron if not already registred.
			$this->lumiere_add_cron_deleteoversizedfolder();

			// Remove cron
		} elseif (
			$this->imdb_cache_values['imdbcachekeepsizeunder'] === '0'
		) {
			// Add WP cron if not already registred.
			$this->lumiere_remove_cron_deleteoversizedfolder();
		}
	}

	/**
	 * Add WP Cron to delete files that are over a given limit
	 *
	 * @return void Files exceeding provided limited are deleted
	 */
	private function lumiere_add_cron_deleteoversizedfolder(): void {

		/* Set up WP Cron if it doesn't exist */
		if ( wp_next_scheduled( 'lumiere_cron_deletecacheoversized' ) === false ) {
			// Cron to run Daily, first time in 1 minute
			wp_schedule_event( time() + 60, 'daily', 'lumiere_cron_deletecacheoversized' );
			$this->logger->log()->info( '[Lumiere] Cron lumiere_cron_deletecacheoversized added' );

		}
	}

	/**
	 * Remove WP Cron that deletes files that are over a given limit
	 *
	 * @return void Files exceeding provided limited are deleted
	 */
	private function lumiere_remove_cron_deleteoversizedfolder(): void {
		$wp_cron_list = count( _get_cron_array() ) > 0 ? _get_cron_array() : [];
		foreach ( $wp_cron_list as $time => $hook ) {
			if ( isset( $hook['lumiere_cron_deletecacheoversized'] ) ) {
				$timestamp = wp_next_scheduled( 'lumiere_cron_deletecacheoversized' );
				if ( $timestamp !== false ) {
					wp_unschedule_event( $timestamp, 'lumiere_cron_deletecacheoversized' );
					$this->logger->log()->info( '[Lumiere] Cron lumiere_cron_deletecacheoversized removed' );
				}
			}
		}
	}
}
