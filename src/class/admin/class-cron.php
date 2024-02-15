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

		// When 'lumiere_cron_exec_once' cron is scheduled, this is what is executed.
		add_action( 'lumiere_cron_exec_once', [ $this, 'lumiere_cron_exec_once' ], 0 );

		// When 'lumiere_cron_deletecacheoversized' cron is scheduled, this is what is executed.
		add_action( 'lumiere_cron_deletecacheoversized', [ $this, 'lumiere_cron_exec_cache' ], 0 );

		// When 'lumiere_cron_autofreshcache' cron is scheduled, this is what is executed.
		add_action( 'lumiere_cron_autofreshcache', [ $this, 'lumiere_cron_exec_autorefresh' ], 0 );

		// Add or remove crons.
		add_action( 'init', [ $this, 'lumiere_add_remove_crons_cache' ] );

		// Add new schedules in cron recurrences.
		add_filter( 'cron_schedules', [ $this, 'lumiere_add_cron_job_recurrence' ] );
	}

	/**
	 * Static instanciation of the class
	 * Needed to be called in add_actions()
	 *
	 * @return void The class is instanciated
	 */
	public static function lumiere_cron_start(): void {
		$rewrite_class = new self();
	}

	/**
	 * Add new schedules
	 *
	 * @since 3.12 Added method
	 *
	 * @param array<int|string, array<string, int|string>|string> $schedules
	 * @return array<int|string, array<string, int|string>|string> The new schedule is added
	 */
	public function lumiere_add_cron_job_recurrence( array $schedules ): array {
		$schedules['everytwoweeks'] = [
			'display' => __( 'Every two weeks', 'lumiere-movies' ),
			'interval' => 1317600,
		];
		return $schedules;
	}

	/**
	 * Cron that runs once
	 * Perfect for updates, runs once after install
	 */
	public function lumiere_cron_exec_once(): void {

		$this->logger->log()->debug( '[Lumiere][cronClass] Cron run once started...' );

		// Update class.
		// this udpate is also run in upgrader_process_complete, but the process is not always reliable.
		$start_update_options = new Updates();
		$start_update_options->run_update_options();

	}

	/**
	 * Cache Cron to run delete oversized cache
	 */
	public function lumiere_cron_exec_cache(): void {

		$this->logger->log()->debug( '[Lumiere][cronClass] Cron delete oversized cache running' );

		$cache_class = new Cache_Tools();
		$cache_class->lumiere_cache_delete_files_over_limit(
			intval( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] )
		);
	}

	/**
	 * Cache Cron to run autorefresh
	 *
	 * @since 3.12 Added method
	 * @return void
	 */
	public function lumiere_cron_exec_autorefresh(): void {

		$this->logger->log()->debug( '[Lumiere][cronClass] Cron refreshing cache started' );

		$cache_class = new Cache_Tools();
		$cache_class->lumiere_all_cache_refresh();
	}

	/**
	 * Depending on the settings and if there is the correct transient passed from class cache, add or remove crons schedule
	 *
	 * @since 3.12 Added method which uses transients to validate the execution of the relevant method
	 *
	 * @return void Crons schedules have been added or removed, or exit if no update was selected in class cache
	 */
	public function lumiere_add_remove_crons_cache(): void {

		// Set up cron imdbcachekeepsizeunder
		if ( get_transient( 'cron_settings_updated' ) === 'imdbcachekeepsizeunder' ) {
			$this->lumiere_edit_cron_deleteoversizedfolder();
		}
		// Set up cron imdbcachekeepsizeunder
		if ( get_transient( 'cron_settings_updated' ) === 'imdbcacheautorefreshcron' ) {
			$this->lumiere_edit_cron_refresh_cache();
		}
	}

	/**
	 * Add or Remove WP Cron a daily cron that deletes files that are over a given limit
	 *
	 * @since 3.12 Merged here the two add/remove previously separated functions
	 *
	 * @return void Files exceeding provided limited are deleted
	 */
	private function lumiere_edit_cron_deleteoversizedfolder(): void {

		if (
			$this->imdb_cache_values['imdbcachekeepsizeunder'] === '1'
			&& intval( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] ) > 0
			// Add WP cron if not already registred.
			&& wp_next_scheduled( 'lumiere_cron_deletecacheoversized' ) === false
		) {
			// Cron to run twice Daily, first time in 1 minute
			wp_schedule_event( time() + 1, 'twicedaily', 'lumiere_cron_deletecacheoversized' );
			$this->logger->log()->debug( '[Lumiere] Cron lumiere_cron_deletecacheoversized added' );

			// Remove cron imdbcachekeepsizeunder.
		} elseif (
			$this->imdb_cache_values['imdbcachekeepsizeunder'] === '0'
			&& wp_next_scheduled( 'lumiere_cron_deletecacheoversized' ) !== false
		) {
			$wp_cron_list = count( _get_cron_array() ) > 0 ? _get_cron_array() : [];
			foreach ( $wp_cron_list as $time => $hook ) {
				if ( isset( $hook['lumiere_cron_deletecacheoversized'] ) ) {
					$timestamp = wp_next_scheduled( 'lumiere_cron_deletecacheoversized' );
					wp_unschedule_event( $timestamp, 'lumiere_cron_deletecacheoversized' );
					$this->logger->log()->debug( '[Lumiere] Cron lumiere_cron_deletecacheoversized removed' );
				}
			}
		}
	}

	/**
	 * Add or Remove WP Cron a monthly cron that refresh cache files
	 *
	 * @since 3.12 Added method
	 *
	 * @return void Files exceeding provided limited are deleted
	 */
	private function lumiere_edit_cron_refresh_cache(): void {

		if (
			$this->imdb_cache_values['imdbcacheautorefreshcron'] === '1'
			// Add WP cron if not already registred.
			&& wp_get_scheduled_event( 'lumiere_cron_autofreshcache' ) === false
		) {

			// Cron to run Every Two Weeks, will run for the first time next Monday.
			$next_monday = strtotime( 'next monday', time() );
			wp_schedule_event( $next_monday, 'everytwoweeks', 'lumiere_cron_autofreshcache' );
			$this->logger->log()->debug( '[Lumiere] Cron lumiere_cron_autofreshcache added' );

			// Remove cron imdbcacheautorefreshcron.
		} elseif (
			$this->imdb_cache_values['imdbcacheautorefreshcron'] === '0'
			&& wp_next_scheduled( 'lumiere_cron_autofreshcache' ) !== false
		) {
			$wp_cron_list = count( _get_cron_array() ) > 0 ? _get_cron_array() : [];
			foreach ( $wp_cron_list as $time => $hook ) {
				if ( isset( $hook['lumiere_cron_autofreshcache'] ) ) {
					$timestamp = wp_next_scheduled( 'lumiere_cron_autofreshcache' );
					wp_unschedule_event( $timestamp, 'lumiere_cron_autofreshcache' );
					$this->logger->log()->debug( '[Lumiere] Cron lumiere_cron_autofreshcache removed' );
				}
			}
		}
	}
}
