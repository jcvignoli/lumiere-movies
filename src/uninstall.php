<?php declare( strict_types = 1 );
/**
 * Uninstall Class : Lumière gets blind forever
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

// If uninstall is not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

use \Lumiere\Settings;
use \Lumiere\Utils;
use \Lumiere\Logger;

class LumiereUninstall {

	/**
	 * Admin options
	 *
	 */
	private array $imdb_admin_values;

	/**
	 * Widget options
	 *
	 */
	private array $imdb_widget_values;

	/**
	 * \Lumiere\Settings class
	 *
	 */
	private Settings $config_class;

	/**
	 * \Lumière\Utils class
	 *
	 */
	private Utils $utils_class;

	/**
	 * constructor
	 *
	 */
	public function __construct() {

		include_once plugin_dir_path( __FILE__ ) . 'bootstrap.php';

		// Get options from database.
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		$this->imdb_widget_values = get_option( Settings::LUMIERE_WIDGET_OPTIONS );

		// Start Settings class.
		$this->config_class = new Settings();

		// Start Utils class.
		$this->utils_class = new Utils();

		// Start Logger class.
		$this->logger = new Logger( 'uninstallClass', false );

		// Run uninstall.
		$this->uninstall();

	}

	/**
	 * Remove ALL plugin's options
	 */
	private function uninstall(): void {

		global $wp_filesystem;

		// Start the logger.
		do_action( 'lumiere_logger' );

		$this->logger->log()->debug( '[Lumiere][uninstall] Processing uninstall' );

		/****** Below actions are executed for everybody */

		// Remove WP Cron shoud it exists.
		$timestamp = wp_next_scheduled( 'lumiere_cron_hook' );
		if ( $timestamp ) {

			wp_unschedule_event( $timestamp, 'lumiere_cron_hook' );
			$this->logger->log()->debug( '[Lumiere][uninstall] Cron deleted.' );

		}

		// Keep the settings if selected so.
		if ( ( isset( $this->imdb_admin_values['imdbkeepsettings'] ) ) && ( $this->imdb_admin_values['imdbkeepsettings'] === '1' ) ) {

			$this->logger->log()->info( '[Lumiere][uninstall] Lumière uninstall: keep settings selected, process finished.' );

			return;
		}

		/*** Following actions are not executed if the user selected to keep their settings ***/

		// Remove cache.
		$lumiere_cache_path = ABSPATH . 'wp-content/cache/lumiere/';
		$this->utils_class->lumiere_wp_filesystem_cred( $lumiere_cache_path );
		if ( $wp_filesystem->is_dir( $lumiere_cache_path ) ) {

			$wp_filesystem->delete( $lumiere_cache_path, true );

			$this->logger->log()->debug( '[Lumiere][uninstall] Cache files and folder deleted.' );

		} else {

			$this->logger->log()->warning( '[Lumiere][uninstall] Standard cache folder was not found. Could not delete ' . $lumiere_cache_path . '.' );

		}

		# Delete Taxonomy
		// Search for all imdbtaxonomy* in config array,
		// If a taxonomy is found, let's get related terms and delete them
		foreach ( $this->utils_class->lumiere_array_key_exists_wildcard( $this->imdb_widget_values, 'imdbtaxonomy*', 'key-value' ) as $key => $value ) {

			$filter_taxonomy = str_replace( 'imdbtaxonomy', '', $this->imdb_admin_values['imdburlstringtaxo'] . $key );

			$this->logger->log()->debug( '[Lumiere][uninstall] Process of deleting taxonomy ' . $filter_taxonomy . ' started' );

			# Register taxonomy: must be registered in order to delete its terms
			register_taxonomy(
				$filter_taxonomy,
				null,
				[
					'label' => false,
					'public' => false,
					'query_var' => false,
					'rewrite' => false,
				]
			);

			# Get all terms, even if empty
			$terms = get_terms(
				[
					'taxonomy' => $filter_taxonomy,
					'hide_empty' => false,
				]
			);

			# Delete taxonomy terms and unregister taxonomy
			foreach ( $terms as $term ) {

				// Sanitize terms
				$term_id = (int) $term->term_id;
				$term_name = (string) sanitize_text_field( $term->name );
				$term_taxonomy = (string) sanitize_text_field( $term->taxonomy );

				if ( ! empty( $term_id ) ) {

					wp_delete_term( $term_id, $filter_taxonomy );
					$this->logger->log()->debug( '[Lumiere][uninstall] Taxonomy: term ' . $term_name . ' in ' . $term_taxonomy . ' deleted.' );

				}

			}

			unregister_taxonomy( $filter_taxonomy );
			$this->logger->log()->debug( '[Lumiere][uninstall] Taxonomy ' . $filter_taxonomy . ' deleted.' );

		}

		// Delete Lumière options.
		delete_option( 'imdbAdminOptions' );
		delete_option( 'imdbWidgetOptions' );
		delete_option( 'imdbCacheOptions' );
		$this->logger->log()->debug( '[Lumiere][uninstall] Lumière options deleted.' );

		// Delete transients. Not yet utilised.
		// delete_transient( 'lumiere_tmp' );
		// $this->logger->log()->debug( '[Lumiere][uninstall] Lumière transients deleted.' );

	}

}

new LumiereUninstall();
