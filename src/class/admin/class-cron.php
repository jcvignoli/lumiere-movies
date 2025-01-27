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
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Tools\Get_Options;
use Lumiere\Admin\Cache_Tools;
use Lumiere\Plugins\Logger;
use Lumiere\Updates;

/**
 * Manage crons
 *
 * @see \Lumiere\Core This class is called in a hook
 * @since 4.0 add/remove cache cron moved from class cache to here
 *
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Tools\Settings_Global
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

		$this->imdb_cache_values = get_option( Get_Options::get_cache_tablename() );

		// When 'lumiere_exec_once_update' cron is scheduled, execute the following.
		add_action( 'lumiere_exec_once_update', [ $this, 'lumiere_exec_once_update' ] );

		// When 'lumiere_cron_deletecacheoversized' cron is scheduled, execute the following.
		add_action( 'lumiere_cron_deletecacheoversized', [ $this, 'lumiere_cron_exec_cache' ], 0 );

		// When 'lumiere_cron_autofreshcache' cron is scheduled, execute the following.
		add_action( 'lumiere_cron_autofreshcache', [ $this, 'lumiere_cron_exec_autorefresh' ], 0 );

		// Add or remove crons.
		add_action( 'init', [ $this, 'lumiere_add_remove_crons_cache' ], 11 );

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
	 * Add new schedule
	 *
	 * @since 4.0 Method added
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
	 * Cron that runs once the update
	 * Runs once after activation or updates
	 * @see \Lumiere\Core
	 */
	public function lumiere_exec_once_update(): void {

		$this->logger->log()->debug( '[Lumiere][cronClass] Cron run once started at ' . gmdate( 'd/m/Y h:i:s a', time() ) );

		// Run updating process.
		$start_update_options = new Updates();
		$start_update_options->run_update_options();

		// Auto update templates file in user template folder.
		( new \Lumiere\Admin\Taxo\Auto_Update_Template_Taxonomy() )->update_auto_dest_theme();
	}

	/**
	 * Cache Cron to run delete oversized cache
	 */
	public function lumiere_cron_exec_cache(): void {

		// $this->logger->log()->debug( '[Lumiere][cronClass] Cron delete oversized cache started at ' . gmdate( 'd/m/Y h:i:s a', time() ) );

		$cache_class = new Cache_Tools();
		$cache_class->lumiere_cache_delete_files_over_limit(
			intval( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] )
		);
	}

	/**
	 * Cache Cron to run autorefresh
	 *
	 * @since 4.0 Added method
	 * @return void
	 */
	public function lumiere_cron_exec_autorefresh(): void {

		$cache_class = new Cache_Tools();
		$cache_class->lumiere_all_cache_refresh();

		$this->logger->log()->debug( '[Lumiere][cronClass] Cron refreshing cache ended at ' . gmdate( 'd/m/Y h:i:s a', time() ) );
	}

	/**
	 * Depending on the settings and if there is the correct transient passed from class cache, add or remove crons schedule
	 *
	 * @since 4.0 Added method which uses transients to validate the execution of the relevant method. Transient are sent from {@see Lumiere\Admin\Save_Options::lumiere_cache_options_save()} and make sure it was an intended action to update the crons
	 *
	 * @return void Crons schedules have been added or removed
	 */
	public function lumiere_add_remove_crons_cache(): void {

		// Set up/remove cron imdbcachekeepsizeunder
		if ( get_transient( 'cron_settings_imdbcachekeepsizeunder_updated' ) === 'imdbcachekeepsizeunder' ) {
			delete_transient( 'cron_settings_imdbcachekeepsizeunder_updated' );
			$this->lumiere_edit_cron_deleteoversizedfolder();
		}
		// Set up/remove cron imdbcachekeepsizeunder
		if ( get_transient( 'cron_settings_imdbcacheautorefreshcron_updated' ) === 'imdbcacheautorefreshcron' ) {
			delete_transient( 'cron_settings_imdbcacheautorefreshcron_updated' );
			$this->lumiere_edit_cron_refresh_cache();
		}
	}

	/**
	 * Add or Remove WP Cron a daily cron that deletes files that are over a given limit
	 *
	 * @since 4.0 Merged here the two add/remove previously separated functions
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
			wp_schedule_event( time() + 1, 'hourly', 'lumiere_cron_deletecacheoversized' );
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
					/** @psalm-suppress PossiblyFalseArgument -- False can't happend, checked through the $hook, always exists */
					wp_unschedule_event( $timestamp, 'lumiere_cron_deletecacheoversized' );
					$this->logger->log()->debug( '[Lumiere] Cron lumiere_cron_deletecacheoversized removed' );
				}
			}
		}
	}

	/**
	 * Add or Remove WP Cron a monthly cron that refresh cache files
	 *
	 * @since 4.0 Added method
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
			/** @psalm-suppress InvalidArgument -- With time(), it's always int! */
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
					/** @psalm-suppress PossiblyFalseArgument -- False can't happend, checked through the $hook, always exists */
					wp_unschedule_event( $timestamp, 'lumiere_cron_autofreshcache' );
					$this->logger->log()->debug( '[Lumiere] Cron lumiere_cron_autofreshcache removed' );
				}
			}
		}
	}
}
