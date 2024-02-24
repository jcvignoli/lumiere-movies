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

/**
 * Uninstall plugin
 * If imdbkeepsettings is set (advanced admin options), exit earlier to keep database settings
 */
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
	 * Constructor
	 *
	 */
	public function __construct() {

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

		// Start the logger.
		do_action( 'lumiere_logger' );

		$this->logger->log()->debug( '[Lumiere][uninstall] Processing uninstall' );

		/** Below actions are executed for everybody */

		// Remove WP Cron should they exist.
		$this->lumiere_delete_crons();

		// Keep the settings if selected so.
		if ( count( $this->imdb_admin_values ) > 0 && ( array_key_exists( 'imdbkeepsettings', $this->imdb_admin_values ) ) && ( $this->imdb_admin_values['imdbkeepsettings'] === '1' ) ) {

			$this->logger->log()->info( '[Lumiere][uninstall] Lumière uninstall: keep settings selected, process finished.' );

			return;
		}

		/** Following actions are executed only if the user selected to not keep their settings */

		$this->lumiere_delete_cache();

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
					'labels' => [ 'name' => 'Lumière ' . $filter_taxonomy . 's' ],
					'public' => false,
					'query_var' => false,
					'rewrite' => false,
				]
			);

			// Get all terms, even if empty.
			// @phan-suppress-next-line PhanAccessMethodInternal -- Cannot access internal method \get_terms() of namespace \ defined at vendor/php-stubs/wordpress-stubs/wordpress-stubs.php:133181 from namespace \Lumiere\Plugins -> PHAN got creazy with get_terms()!
			$terms = get_terms(
				[
					'taxonomy' => $filter_taxonomy,
					'hide_empty' => false,
				]
			);

			// Filer: Get rid of errors, keep arrays only.
			if ( $terms instanceof \WP_Error ) {
				$this->logger->log()->error( '[Lumiere][uninstall] Invalid terms: ' . $terms->get_error_message() );
				continue;
			}

			/** @psalm-suppress PossiblyInvalidIterator -- Cannot iterate over string -- this is the old WordPress way to have get_terms() return strings */
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
		$this->logger->log()->debug( '[Lumiere][uninstall] Lumière options deletion processed.' );

		// Delete transients.
		if ( delete_transient( 'cron_settings_updated' ) ) {
			$this->logger->log()->debug( '[Lumiere][uninstall] Lumière cron_settings_updated transients deleted.' );
		}
		if ( delete_transient( 'notice_lumiere_msg' ) ) {
			$this->logger->log()->debug( '[Lumiere][uninstall] Lumière notice_lumiere_msg transients deleted.' );
		}
		if ( delete_transient( 'admin_template_this' ) ) {
			$this->logger->log()->debug( '[Lumiere][uninstall] Lumière admin_template_this transients deleted.' );
		}
	}

	/**
	 * Delete cache
	 */
	private function lumiere_delete_cache(): void {
		global $wp_filesystem;

		// Remove cache.
		$lumiere_cache_path = $this->imdb_cache_values['imdbcachedir'];
		Utils::lumiere_wp_filesystem_cred( $lumiere_cache_path );
		if ( $wp_filesystem->is_dir( $lumiere_cache_path ) ) {

			$wp_filesystem->delete( $lumiere_cache_path, true );
			$this->logger->log()->debug( '[Lumiere][uninstall] Cache files and folder deleted.' );
		} else {

			$this->logger->log()->warning( '[Lumiere][uninstall] Standard cache folder was not found. Could not delete ' . $lumiere_cache_path . '.' );
		}
	}

	/**
	 * Delete crons
	 */
	private function lumiere_delete_crons(): void {
		// Remove WP lumiere crons should they exist.
		$list_crons_available = [ 'lumiere_cron_exec_once', 'lumiere_cron_deletecacheoversized', 'lumiere_cron_autofreshcache' ];
		foreach ( $list_crons_available as $cron_installed ) {
			wp_clear_scheduled_hook( $cron_installed );
		}
	}
}

// Run uninstall.
$lumiere_uninstall_class = new Uninstall();
$lumiere_uninstall_class->uninstall();
