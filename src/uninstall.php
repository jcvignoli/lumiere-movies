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
use Lumiere\Admin\Admin_General;
use Lumiere\Plugins\Logger;

/**
 * Uninstall plugin
 * If imdbkeepsettings is set (advanced admin options), exit earlier to keep database settings
 *
 * @since 4.0 option properties can be null and construct is different.
 */
class Uninstall {

	/**
	 * Traits
	 */
	use Admin_General;

	/**
	 * Admin options
	 * @var null|array<string> $imdb_admin_values
	 */
	private ?array $imdb_admin_values;

	/**
	 * Data options
	 * @var null|array<string> $imdb_data_values
	 */
	private ?array $imdb_data_values;

	/**
	 * Cache options
	 * @var null|array<string> $imdb_cache_values
	 */
	private ?array $imdb_cache_values;

	/**
	 * \Lumiere\Logger class
	 *
	 */
	private Logger $logger;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get options from database.
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS ) !== false ? get_option( Settings::LUMIERE_ADMIN_OPTIONS ) : null;
		$this->imdb_data_values = get_option( Settings::LUMIERE_DATA_OPTIONS ) !== false ? get_option( Settings::LUMIERE_DATA_OPTIONS ) : null;
		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS ) !== false ? get_option( Settings::LUMIERE_CACHE_OPTIONS ) : null;

		// Start Logger class.
		$this->logger = new Logger( 'uninstallClass', false );

	}

	/**
	 * Clean Plugin's traces
	 *
	 * @since 4.0 created private methods to deal with processes, added precheck of database exists
	 */
	public function uninstall(): bool {

		// Start the logger.
		do_action( 'lumiere_logger' );

		// If databases were not created, exit as the plugin was not installed
		if ( ! isset( $this->imdb_admin_values ) || ! isset( $this->imdb_data_values ) || ! isset( $this->imdb_cache_values ) ) {
			$this->logger->log()->debug( '[Lumiere][uninstall] Lumiere was not installed, exiting' );
			return false;
		}

		$this->logger->log()->debug( '[Lumiere][uninstall] Processing uninstall' );

		/********* Below actions are executed for everybody */

		// Remove WP Cron should they exist.
		$this->lumiere_delete_crons();

		// Keep the settings, exit at this point if 'imdbkeepsettings' is selected.
		/** @psalm-suppress RedundantCondition -- Type array<array-key, string> for $this->imdb_admin_values is always isset -- wrong, it may not exist */
		if (
			isset( $this->imdb_admin_values )
			&& count( $this->imdb_admin_values ) > 0
			&& ( array_key_exists( 'imdbkeepsettings', $this->imdb_admin_values ) )
			&& ( $this->imdb_admin_values['imdbkeepsettings'] === '1' )
		) {
			$this->logger->log()->info( '[Lumiere][uninstall] Lumière uninstall: keep settings selected, process finished.' );
			return true;
		}

		//********* Next actions are executed only if the user selected to not keep their settings */

		// Delete cache folder.
		$this->lumiere_delete_cache();

		// Delete Taxonomy.
		$this->lumiere_delete_taxonomy();
		$this->lumiere_delete_taxonomy_templates();

		// Delete transients.
		$this->lumiere_delete_transients();

		// Delete Lumière options.
		$this->lumiere_delete_options();

		return true;
	}

	/**
	 * Delete cache
	 */
	private function lumiere_delete_cache(): bool {

		global $wp_filesystem;

		if ( ! isset( $this->imdb_cache_values ) ) {
			return false;
		}

		// Remove cache.
		$lumiere_cache_path = $this->imdb_cache_values['imdbcachedir'];
		$this->lumiere_wp_filesystem_cred( $lumiere_cache_path ); // in trait Admin_General.

		if ( strlen( $lumiere_cache_path ) === 0 || $wp_filesystem->is_dir( $lumiere_cache_path ) === false ) {
			$this->logger->log()->warning( '[Lumiere][uninstall][Cache] Standard cache folder was not found. Could not delete ' . $lumiere_cache_path . '.' );
			return false;
		}

		if ( $wp_filesystem->delete( $lumiere_cache_path, true ) === true ) {
			$this->logger->log()->debug( '[Lumiere][uninstall][Cache] Cache files and folder deleted.' );
		}

		$this->logger->log()->debug( '[Lumiere][uninstall][Cache] Lumière cache deletion processed.' );

		return true;
	}

	/**
	 * Delete taxonomy
	 * Search for all imdbtaxonomy* in config array.
	 * If a taxonomy is found, let's get related terms and delete them.
	 */
	private function lumiere_delete_taxonomy(): bool {

		if ( ! isset( $this->imdb_data_values ) || ! isset( $this->imdb_admin_values ) ) {
			return false;
		}

		foreach ( Utils::lumiere_array_key_exists_wildcard( $this->imdb_data_values, 'imdbtaxonomy*', 'key-value' ) as $key => $value ) {

			$filter_taxonomy = str_replace( 'imdbtaxonomy', '', $this->imdb_admin_values['imdburlstringtaxo'] . $key );

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
			// @phan-suppress-next-line PhanAccessMethodInternal -- Cannot access internal method \get_terms() of namespace \ defined at vendor/php-stubs/wordpress-stubs/wordpress-stubs.php:133181 from namespace \Lumiere\Plugins -> PHAN gets crazy with get_terms()!
			$terms = get_terms(
				[
					'taxonomy' => $filter_taxonomy,
					'hide_empty' => false,
				]
			);

			// Filer: Get rid of errors, keep arrays only.
			if ( $terms instanceof \WP_Error ) {
				$this->logger->log()->error( '[Lumiere][uninstall][Taxonomy terms] Invalid terms: ' . $terms->get_error_message() );
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
					$this->logger->log()->error( '[Lumiere][uninstall][Taxonomy terms] Taxonomy: failed to delete ' . $term_name . ' in ' . $term_taxonomy );
					continue;
				}

				// Confirm success.
				$this->logger->log()->debug( '[Lumiere][uninstall][Taxonomy terms] Taxonomy: term ' . $term_name . ' in ' . $term_taxonomy . ' deleted.' );
			}

			unregister_taxonomy( $filter_taxonomy );
			$this->logger->log()->debug( '[Lumiere][uninstall][Taxonomy terms] Taxonomy ' . $filter_taxonomy . ' deleted.' );
		}
		$this->logger->log()->debug( '[Lumiere][uninstall][Taxonomy terms] Lumière taxonomy terms deletion processed.' );
		return true;
	}

	/**
	 * Delete taxonomy templates
	 */
	private function lumiere_delete_taxonomy_templates(): bool {

		if ( ! isset( $this->imdb_admin_values ) ) {
			return false;
		}

		global $wp_filesystem;

		$get_taxo_templates = glob( get_stylesheet_directory() . '/taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . '*' );

		// No taxo files found
		if ( $get_taxo_templates === false || count( $get_taxo_templates ) === 0 ) {
			$this->logger->log()->debug( '[Lumiere][uninstall][Taxonomy template] No taxonomy files found in the template folder ' . get_stylesheet_directory() );
			return false;
		}

		foreach ( $get_taxo_templates as $tax_file ) {
			$this->lumiere_wp_filesystem_cred( $tax_file ); // in trait Admin_General.
			$wp_filesystem->delete( $tax_file );
			$this->logger->log()->debug( '[Lumiere][uninstall][Taxonomy template] File ' . $tax_file . ' deleted' );
		}
		$this->logger->log()->debug( '[Lumiere][uninstall][Taxonomy template] Lumière taxonomy templates deletion processed.' );

		return true;
	}

	/**
	 * Delete crons
	 */
	private function lumiere_delete_crons(): bool {

		$processed = false;

		// Remove WP lumiere crons should they exist.
		$list_crons_available = [ 'lumiere_cron_exec_once', 'lumiere_cron_deletecacheoversized', 'lumiere_cron_autofreshcache' ];
		foreach ( $list_crons_available as $cron_installed ) {
			if ( wp_clear_scheduled_hook( $cron_installed ) > 0 ) {
				$processed = true;
				$this->logger->log()->debug( '[Lumiere][uninstall][Crons] Cron ' . $cron_installed . ' deleted.' );
			}
		}
		$this->logger->log()->debug( '[Lumiere][uninstall][Crons] Lumière crons deletion processed.' );
		return $processed;
	}

	/**
	 * Delete transients
	 */
	private function lumiere_delete_transients(): bool {

		$processed = false;
		if ( delete_transient( 'cron_settings_updated' ) ) {
			$processed = true;
			$this->logger->log()->debug( '[Lumiere][uninstall][Transients] Lumière cron_settings_updated transients deleted.' );
		}
		if ( delete_transient( 'notice_lumiere_msg' ) ) {
			$processed = true;
			$this->logger->log()->debug( '[Lumiere][uninstall][Transients]  Lumière notice_lumiere_msg transients deleted.' );
		}
		if ( delete_transient( 'admin_template_pass_vars' ) ) {
			$processed = true;
			$this->logger->log()->debug( '[Lumiere][uninstall][Transients]  Lumière admin_template_pass_vars transients deleted.' );
		}
		$this->logger->log()->debug( '[Lumiere][uninstall][Transients] Lumière transients deletion processed.' );
		return $processed;
	}

	/**
	 * Delete options
	 */
	private function lumiere_delete_options(): bool {

		$processed = false;

		if ( delete_option( Settings::LUMIERE_ADMIN_OPTIONS ) === true ) {
			$processed = true;
			$this->logger->log()->error( '[Lumiere][uninstall][Options] Successfully deleted ' . Settings::LUMIERE_ADMIN_OPTIONS );
		}
		if ( delete_option( Settings::LUMIERE_DATA_OPTIONS ) === true ) {
			$processed = true;
			$this->logger->log()->error( '[Lumiere][uninstall][Options] Successfully deleted ' . Settings::LUMIERE_DATA_OPTIONS );
		}
		if ( delete_option( Settings::LUMIERE_CACHE_OPTIONS ) === true ) {
			$processed = true;
			$this->logger->log()->error( '[Lumiere][uninstall][Options] Successfully deleted ' . Settings::LUMIERE_CACHE_OPTIONS );
		}
		$this->logger->log()->debug( '[Lumiere][uninstall][Delete options] Lumière options deletion processed.' );
		return $processed;

	}
}

// Run uninstall.
$lumiere_uninstall_class = new Uninstall();
$lumiere_uninstall_class->uninstall();
