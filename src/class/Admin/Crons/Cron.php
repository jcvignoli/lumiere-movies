<?php declare( strict_types = 1 );
/**
 * Crons
 *
 * @copyright   2023, Lost Highway
 *
 * @version     1.0
 * @package     lumieremovies
 */

namespace Lumiere\Admin\Crons;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Admin\Cache\Cache_Files_Management;
use Lumiere\Admin\Copy_Templates\Auto_Update_Theme;
use Lumiere\Plugins\Logger;
use Lumiere\Updates;

/**
 * Manage crons
 *
 * @see \Lumiere\Core This class is called in a hook
 * @since 4.0 add/remove cache cron moved from class cache to here
 *
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Config\Settings
 */
final class Cron {

	/**
	 * Number of day before the autorefresh of cache starts a new round of cache refreshing
	 */
	public const CACHE_DAYS_AUTO_REFRESH_ROUND = 1209600; // 14 * 24 * 60 * 60

	/**
	 * @var array<string, string>
	 * @phpstan-var OPTIONS_CACHE
	 */
	private array $imdb_cache_values;

	/**
	 * Return the default suggested privacy policy content.
	 *
	 * @return void The default policy content has been added to WP policy page
	 */
	public function __construct(
		private Logger $logger = new Logger( 'cronClass' ),
	) {

		$this->imdb_cache_values = get_option( Get_Options::get_cache_tablename() );

		// When 'lumiere_exec_once_update' cron is scheduled, execute the following.
		add_action( 'lumiere_exec_once_update', [ $this, 'exec_once_update' ] );

		// When 'lumiere_cron_deletecacheoversized' cron is scheduled, execute the following.
		add_action( 'lumiere_cron_deletecacheoversized', [ $this, 'delete_cache_oversized' ] );

		// When 'lumiere_cron_autofreshcache' cron is scheduled, execute the following.
		add_action( 'lumiere_cron_autofreshcache', [ $this, 'cache_auto_refresh' ] );

		// Add or remove crons.
		add_action( 'init', [ $this, 'add_remove_crons_cache' ], 11 );

		// Add new schedules in cron recurrences.
		add_filter( 'cron_schedules', [ $this, 'add_custom_job_recurrence' ] );
	}

	/**
	 * Static instanciation of the class
	 * Needed to be called in add_actions()
	 *
	 * @return void The class is instanciated
	 */
	public static function start(): void {
		$cron_class = new self();
	}

	/**
	 * Add new schedule
	 *
	 * @since 4.0 Method added
	 *
	 * @param array<int|string, array<string, int|string>|string> $schedules
	 * @return array<int|string, array<string, int|string>|string> The new schedule is added
	 */
	public function add_custom_job_recurrence( array $schedules ): array {
		$schedules['everytwoweeks'] = [
			'display' => __( 'Every two weeks', 'lumiere-movies' ),
			'interval' => 1317600,
		];
		return $schedules;
	}

	/**
	 * Cron that runs once
	 * Runs once after plugin activation or update
	 * @see \Lumiere\Core
	 */
	public function exec_once_update(): void {

		$this->logger->log?->debug( '[Cron] Cron run once started at ' . (string) wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), time() ) );

		// Run updating process.
		$start_update_options = new Updates();
		$start_update_options->run_update_options();

		// Auto update templates file in user template folder.
		( new Auto_Update_Theme() )->update_auto_dest_theme();

		$this->logger->log?->debug( '[Cron] Cron run once finished at ' . (string) wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), time() ) );
	}

	/**
	 * Cache Cron to run delete oversized cache
	 * Relevant log executed in cache class
	 *
	 * @see \Lumiere\Admin\Cache\Cache_Files_Management::lumiere_cache_delete_files_over_limit()
	 */
	public function delete_cache_oversized(): void {
		$cache_class = new Cache_Files_Management();
		$cache_class->lumiere_cache_delete_files_over_limit(
			intval( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] )
		);
	}

	/**
	 * Cache Cron to run autorefresh
	 * It is bound to the custom hook 'lumiere_cron_autofreshcache', which runs x times per day/week
	 * But all_cache_refresh() will refresh only once per overall refresh (in second parameter of the function)
	 *
	 * @see \Lumiere\Admin\Cache\Cache_Files_Management::all_cache_refresh()
	 * @since 4.0 Added method
	 */
	public function cache_auto_refresh(): void {

		$this->logger->log?->debug( '[Cron] Cron refreshing cache started at ' . (string) wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), time() ) );

		$cache_class = new Cache_Files_Management();
		$cache_class->cron_all_cache_refresh(
			5, /* nb of files refreshed per cron call, lowered to 5 since 4.4 as connection method is resource intensive */
			self::CACHE_DAYS_AUTO_REFRESH_ROUND /* nb of days before having a new overall refresh */
		);

		$this->logger->log?->debug( '[Cron] Cron refreshing cache ended at ' . (string) wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), time() ) );
	}

	/**
	 * Depending on the settings and if there is the correct transient passed from class cache, add or remove crons schedule
	 *
	 * @since 4.0 Added method which uses transients to validate the execution of the relevant method. Transient are sent from {@see Lumiere\Admin\Save_Options::lumiere_cache_options_save()} and make sure it was an intended action to update the crons
	 *
	 * @return void Crons schedules have been added or removed
	 */
	public function add_remove_crons_cache(): void {

		// Set up/remove cron imdbcachekeepsizeunder
		if ( get_transient( 'cron_settings_imdbcachekeepsizeunder_updated' ) === 'imdbcachekeepsizeunder' ) {
			delete_transient( 'cron_settings_imdbcachekeepsizeunder_updated' );
			$this->cron_add_delete_oversize();
		}
		// Set up/remove cron imdbcacheautorefreshcron
		if ( get_transient( 'cron_settings_imdbcacheautorefreshcron_updated' ) === 'imdbcacheautorefreshcron' ) {
			delete_transient( 'cron_settings_imdbcacheautorefreshcron_updated' );
			$this->cron_add_delete_cache();
		}
	}

	/**
	 * Add or Remove WP Cron a daily cron that deletes files that are over a given limit
	 *
	 * @since 4.0 Merged here the two add/remove previously separated functions
	 * @see \Lumiere\Tools\Cli_Commands::sub_update_options() Use this method to update the cron
	 *
	 * @return void Files exceeding provided limited are deleted
	 */
	public function cron_add_delete_oversize(): void {

		if (
			$this->imdb_cache_values['imdbcachekeepsizeunder'] === '1'
			&& intval( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] ) > 0
			// Add WP cron if not already registred.
			&& wp_next_scheduled( 'lumiere_cron_deletecacheoversized' ) === false
		) {
			// Cron to run twice Daily, first time in 1 minute
			wp_schedule_event( time() + 1, 'hourly', 'lumiere_cron_deletecacheoversized' );
			$this->logger->log?->debug( '[Cron] Cron lumiere_cron_deletecacheoversized added' );

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
					$this->logger->log?->debug( '[Cron] Cron lumiere_cron_deletecacheoversized removed' );
				}
			}
		}
	}

	/**
	 * Add or Remove WP Cron a monthly cron that refresh cache files
	 *
	 * @since 4.0 Added method
	 * @since 4.3.3 Runs twice a day instead of every two weeks, now processing the refresh by batches + added transient to know the rounds lenght
	 * @see \Lumiere\Tools\Cli_Commands::sub_update_options() Use this method to update the cron
	 *
	 * @return void Files exceeding provided limited are deleted
	 */
	public function cron_add_delete_cache(): void {

		if (
			$this->imdb_cache_values['imdbcacheautorefreshcron'] === '1'
			// Add WP cron if not already registred.
			&& wp_get_scheduled_event( 'lumiere_cron_autofreshcache' ) === false
		) {
			// Cron running every day
			$starting_time = strtotime( '+1 hours', time() );
			/** @psalm-suppress InvalidArgument -- With time(), it's always int! */
			wp_schedule_event( $starting_time, 'hourly', 'lumiere_cron_autofreshcache' );
			$this->logger->log?->debug( '[Cron] Cron lumiere_cron_autofreshcache added' );

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
					$this->logger->log?->debug( '[Cron] Cron lumiere_cron_autofreshcache removed' );
				}
			}
			delete_transient( 'lum_cache_cron_refresh_time_started' );
			delete_transient( 'lum_cache_cron_refresh_store_movie' ); // In class Cache_Files_Management.
			delete_transient( 'lum_cache_cron_refresh_store_people' ); // In class Cache_Files_Management.
		}
	}
}
