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
use Lumiere\Admin\Cache;
use Lumiere\Plugins\Logger;
use Lumiere\Updates;

/**
 * Manage crons
 * Called with init hook
 * @phpstan-import-type OPTIONS_CACHE from Settings
 */
class Cron {

	/**
	 * @phpstan-var OPTIONS_CACHE $imdb_cache_values
	 */
	private array $imdb_cache_values;

	private Logger $logger;

	/**
	 * Return the default suggested privacy policy content.
	 *
	 * @return void The default policy content has been added to WP policy page
	 */
	public function __construct() {

		$this->logger = new Logger( 'cronClass' );

		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

		// Add cron schedules.
		add_action( 'lumiere_cron_hook', [ $this, 'lumiere_cron_exec_once' ], 0 );

		// Add cron Cache delete action hook. Must be outside of the calling cache class.
		add_action( 'lumiere_cron_deletecacheoversized', [ $this, 'lumiere_cron_exec_cache' ], 0 );
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

		$cache_class = new Cache();
		$cache_class->lumiere_cache_delete_files_over_limit(
			intval( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] )
		);
	}
}
