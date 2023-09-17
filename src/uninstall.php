<?php declare( strict_types = 1 );
/**
 * Uninstall Class : Lumière gets blind forever
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 * @phpcs:disable WordPress.Files.FileName
 */

namespace Lumiere;

// If uninstall is not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}
// Include composer bootstrap.
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use Lumiere\Settings;
use Lumiere\Tools\Utils;
use Lumiere\Plugins\Logger;

class Uninstall {

	/**
	 * Admin options
	 * @var array<string> $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 * Widget options
	 * @var array<string> $imdb_widget_values
	 */
	private array $imdb_widget_values;

	/**
	 * Cache options
	 * @var array<string> $imdb_cache_values
	 */
	private array $imdb_cache_values;

	/**
	 * \Lumiere\Logger class
	 *
	 */
	private Logger $logger;

	/**
	 * \Lumière\Utils class
	 *
	 */
	private Utils $utils_class;

	/**
	 * \Lumière\Settings class
	 */
	private Settings $config_class; // @phpstan-ignore-line -- Property Lumiere\Uninstall::$config_class is never read, only written. -- it's to setup the config, can't use $this->imdb_admin_values (etc) otherwise

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Needed to access the options
		$this->config_class = new Settings();

		// Get options from database.
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		$this->imdb_widget_values = get_option( Settings::LUMIERE_WIDGET_OPTIONS );
		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

		// Start Utils class.
		$this->utils_class = new Utils();

		// Start Logger class.
		$this->logger = new Logger( 'uninstallClass', false );

	}

	/**
	 * Remove ALL plugin's options
	 */
	public function uninstall(): void {

		global $wp_filesystem;

		// Start the logger.
		do_action( 'lumiere_logger' );

		$this->logger->log()->debug( '[Lumiere][uninstall] Processing uninstall' );

		/** Below actions are executed for everybody */

		// Remove WP Cron shoud they exist.
		$wp_cron_list = count( _get_cron_array() ) > 0 ? _get_cron_array() : [];
		foreach ( $wp_cron_list as $time => $hook ) {
			if ( isset( $hook['lumiere_cron_hook'] ) ) {
				$timestamp = (int) wp_next_scheduled( 'lumiere_cron_hook' );
				wp_unschedule_event( $timestamp, 'lumiere_cron_hook' );
				$this->logger->log()->info( '[Lumiere][uninstall] Cron lumiere_cron_hook removed' );
			}
			if ( isset( $hook['lumiere_cron_deletecacheoversized'] ) ) {
				$timestamp = (int) wp_next_scheduled( 'lumiere_cron_deletecacheoversized' );
				wp_unschedule_event( $timestamp, 'lumiere_cron_deletecacheoversized' );
				$this->logger->log()->info( '[Lumiere][uninstall] Cron lumiere_cron_deletecacheoversized removed' );
			}
		}

		// Keep the settings if selected so.
		if ( isset( $this->imdb_admin_values ) && ( array_key_exists( 'imdbkeepsettings', $this->imdb_admin_values ) ) && ( $this->imdb_admin_values['imdbkeepsettings'] === '1' ) ) {

			$this->logger->log()->info( '[Lumiere][uninstall] Lumière uninstall: keep settings selected, process finished.' );

			return;
		}

		/** Following actions are executed only if the user selected to not keep their settings */

		// Remove cache.
		$lumiere_cache_path = $this->imdb_cache_values['imdbcachedir'];
		Utils::lumiere_wp_filesystem_cred( $lumiere_cache_path );
		if ( $wp_filesystem->is_dir( $lumiere_cache_path ) ) {

			$wp_filesystem->delete( $lumiere_cache_path, true );

			$this->logger->log()->debug( '[Lumiere][uninstall] Cache files and folder deleted.' );

		} else {

			$this->logger->log()->warning( '[Lumiere][uninstall] Standard cache folder was not found. Could not delete ' . $lumiere_cache_path . '.' );

		}

		// Delete Taxonomy.
		// Search for all imdbtaxonomy* in config array.
		// If a taxonomy is found, let's get related terms and delete them.
		foreach ( $this->utils_class->lumiere_array_key_exists_wildcard( $this->imdb_widget_values, 'imdbtaxonomy*', 'key-value' ) as $key => $value ) {

			$filter_taxonomy = str_replace( 'imdbtaxonomy', '', $this->imdb_admin_values['imdburlstringtaxo'] . $key );

			$this->logger->log()->debug( '[Lumiere][uninstall] Process of deleting taxonomy ' . $filter_taxonomy . ' started' );

			// Register taxonomy: must be registered in order to delete its terms.
			register_taxonomy(
				$filter_taxonomy,
				[ 'page', 'post' ],
				[
					'label' => false,
					'public' => false,
					'query_var' => false,
					'rewrite' => false,
				]
			);

			// Get all terms, even if empty.
			$terms = get_terms(
				[
					'taxonomy' => $filter_taxonomy,
					'hide_empty' => false,
				]
			);

			// Filer: Get rid of errors, keep arrays only.
			if ( is_wp_error( $terms ) === true ) {
				$this->logger->log()->error( '[Lumiere][uninstall] Invalid terms: ' . wp_json_encode( $terms ) );
				continue;
			}

			foreach ( $terms as $term ) {

				// Filter: Get rid of integers and strings, keep objects only.
				/* removed, PHPStan says it's useless, kept for the logic
				if ( $term instanceof \WP_Term === false ) {
					$this->logger->log()->error( '[Lumiere][uninstall] Invalid term: ' . $term );
					continue;
				}
				*/

				// Retrieve and sanitize the term object vars.
				$term_id = intval( $term->term_id );
				$term_name = sanitize_text_field( $term->name );
				$term_taxonomy = sanitize_text_field( $term->taxonomy );

				// Delete the term.
				if ( wp_delete_term( $term_id, $filter_taxonomy ) !== true ) {
					$this->logger->log()->error( '[Lumiere][uninstall] Taxonomy: failed to delete ' . $term_name . ' in ' . $term_taxonomy );
					continue;
				}

				// Confirm success.
				$this->logger->log()->debug( '[Lumiere][uninstall] Taxonomy: term ' . $term_name . ' in ' . $term_taxonomy . ' deleted.' );
			}

			unregister_taxonomy( $filter_taxonomy );
			$this->logger->log()->debug( '[Lumiere][uninstall] Taxonomy ' . $filter_taxonomy . ' deleted.' );

		}

		// Delete Lumière options.
		if ( delete_option( Settings::LUMIERE_ADMIN_OPTIONS ) === false ) {
			$this->logger->log()->error( '[Lumiere][uninstall] Could not delete ' . Settings::LUMIERE_ADMIN_OPTIONS );
		}
		if ( delete_option( Settings::LUMIERE_WIDGET_OPTIONS ) === false ) {
			$this->logger->log()->error( '[Lumiere][uninstall] Could not delete ' . Settings::LUMIERE_WIDGET_OPTIONS );
		}
		if ( delete_option( Settings::LUMIERE_CACHE_OPTIONS ) === false ) {
			$this->logger->log()->error( '[Lumiere][uninstall] Could not delete ' . Settings::LUMIERE_CACHE_OPTIONS );
		}
		$this->logger->log()->debug( '[Lumiere][uninstall] Lumière options deleted.' );

		// Delete transients. Not yet utilised.
		// delete_transient( 'lumiere_tmp' );
		// $this->logger->log()->debug( '[Lumiere][uninstall] Lumière transients deleted.' );

	}

}

// Run uninstall.
$lumiere_uninstall_class = new Uninstall();
$lumiere_uninstall_class->uninstall();
