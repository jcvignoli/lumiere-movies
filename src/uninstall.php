<?php declare( strict_types = 1 );
/**
 * Uninstall Class : Lumière gets blind forever
 *
 * @version       1.0
 * @package       lumieremovies
 * @copyright (c) 2021, Lost Highway
 * @phpcs:disable WordPress.Files.FileName
 */

namespace Lumiere;

// If uninstall is not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}
// Include composer bootstrap.
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Config\Get_Options_Person;
use Lumiere\Plugins\Logger;
use Lumiere\Tools\Data;
use Lumiere\Tools\Files;

/**
 * Uninstall plugin
 * If imdbkeepsettings is set (advanced admin options), exit earlier to keep database settings
 *
 * @since 4.0 option properties can be null and construct is different.
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Config\Settings
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Config\Settings_Movie
 * @phpstan-import-type OPTIONS_DATA_PSALM from \Lumiere\Config\Settings_Movie
 * @phpstan-import-type OPTIONS_DATA_PERSON from \Lumiere\Config\Settings_Person
 * @phpstan-import-type OPTIONS_DATA_PERSON_PSALM from \Lumiere\Config\Settings_Person
 */
class Uninstall {

	/**
	 * Traits
	 */
	use Files;

	/**
	 * Admin options
	 * @phpstan-var null|OPTIONS_ADMIN
	 */
	private ?array $imdb_admin_values;

	/**
	 * Data movie options
	 * @phpstan-var null|OPTIONS_DATA
	 * @psalm-var null|OPTIONS_DATA_PSALM
	 */
	private ?array $imdb_data_values;

	/**
	 * Data options
	 * @phpstan-var null|OPTIONS_DATA
	 * @psalm-var null|OPTIONS_DATA_PSALM
	 */
	private ?array $imdb_data_person_values;

	/**
	 * Cache options
	 * @phpstan-var null|OPTIONS_CACHE
	 */
	private ?array $imdb_cache_values;

	/**
	 * Constructor
	 */
	public function __construct(
		private Logger $logger = new Logger( 'uninstallClass', false )
	) {
		$this->imdb_admin_values = get_option( Get_Options::get_admin_tablename(), null );
		$this->imdb_data_values = get_option( Get_Options_Movie::get_data_tablename(), null );
		$this->imdb_data_person_values = get_option( Get_Options_Person::get_data_person_tablename(), null );
		$this->imdb_cache_values = get_option( Get_Options::get_cache_tablename(), null );
	}

	/**
	 * Clean Plugin's traces
	 *
	 * @since 4.0 created private methods to deal with processes, added precheck of database exists
	 */
	public function run_uninstall(): void {

		// If databases were not created, exit as the plugin was not installed
		if ( ! isset( $this->imdb_admin_values ) || ! isset( $this->imdb_data_values ) || ! isset( $this->imdb_cache_values ) || ! isset( $this->imdb_data_person_values ) ) {
			$this->logger->log?->debug( '[uninstall] Lumiere was not installed, exiting' );
			return;
		}

		$this->logger->log?->debug( '[uninstall] Processing uninstall' );

		/********* Below actions are executed for everybody */

		// Remove WP Cron should they exist.
		$this->lumiere_delete_crons();

		// Keep the settings, exit at this point if 'imdbkeepsettings' is selected.
		if (
			/** @phpstan-ignore argument.type (Parameter #2 $array of function array_key_exists expects array, array<string, string>|null given) */
			array_key_exists( 'imdbkeepsettings', $this->imdb_admin_values )
			&& ( $this->imdb_admin_values['imdbkeepsettings'] === '1' )
		) {
			$this->logger->log?->info( '[uninstall] Lumière uninstall: keep settings selected, process finished.' );
			return;
		}

		//********* Next actions are executed only if the user selected to not keep their settings */

		// Delete cache folder.
		$this->delete_cache();

		// Delete Taxonomy.
		$this->delete_taxonomy();
		$this->delete_taxonomy_templates();

		// Delete transients.
		$this->lumiere_delete_transients();

		// Delete Lumière options.
		$this->delete_options();
	}

	/**
	 * Delete cache
	 */
	private function delete_cache(): void {

		global $wp_filesystem;

		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) {
			$this->logger->log?->warning( '[uninstall][Cache] Lumière Cache Options unavailable' );
			return;
		}

		// Remove cache.
		$lumiere_cache_path = $this->imdb_cache_values['imdbcachedir'];
		$this->wp_filesystem_cred( $lumiere_cache_path ); // in trait Files.

		if ( strlen( $lumiere_cache_path ) === 0 || $wp_filesystem->is_dir( $lumiere_cache_path ) === false ) {
			$this->logger->log?->warning( '[uninstall][Cache] Standard cache folder was not found. Could not delete ' . $lumiere_cache_path );
			return;
		}

		if ( $wp_filesystem->delete( $lumiere_cache_path, true ) === true ) {
			$this->logger->log?->debug( '[uninstall][Cache] Cache files and folder deleted' );
		}
		$this->logger->log?->debug( '[uninstall][Cache] Lumière cache deletion processed' );
	}

	/**
	 * Delete taxonomy
	 * Search for all imdbtaxonomy* in config array.
	 * If a taxonomy is found, let's get related terms and delete them.
	 */
	private function delete_taxonomy(): void {

		if ( ! isset( $this->imdb_data_values ) || ! isset( $this->imdb_admin_values['imdburlstringtaxo'] ) ) {
			$this->logger->log?->warning( '[uninstall][Taxonomy terms] Lumière Options unavailable' );
			return;
		}

		foreach ( Data::lumiere_array_key_exists_wildcard( $this->imdb_data_values, 'imdbtaxonomy*', 'key-value' ) as $key => $value ) {

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
			$terms = get_terms(
				[
					'taxonomy' => $filter_taxonomy,
					'hide_empty' => false,
				]
			);

			// Filer: Get rid of errors, keep arrays only.
			if ( $terms instanceof \WP_Error ) {
				$this->logger->log?->error( '[uninstall][Taxonomy terms] Invalid terms: ' . $terms->get_error_message() );
				continue;
			}

			/** @psalm-suppress PossiblyInvalidIterator -- Cannot iterate over string -- this is the old WordPress way to have get_terms() return strings */
			foreach ( $terms as $term ) {

				// Filter: Get rid of integers and strings, keep objects only.
				/* removed, PHPStan says it's useless, kept for the logic
				if ( $term instanceof \WP_Term === false ) {
					$this->logger->log?->error( '[uninstall] Invalid term: ' . $term );
					continue;
				}
				*/

				// Retrieve and sanitize the term object vars.
				$term_id = intval( $term->term_id );
				$term_name = sanitize_text_field( $term->name );
				$term_taxonomy = sanitize_text_field( $term->taxonomy );

				// Delete the term.
				if ( wp_delete_term( $term_id, $filter_taxonomy ) !== true ) {
					$this->logger->log?->error( '[uninstall][Taxonomy terms] Taxonomy: failed to delete ' . $term_name . ' in ' . $term_taxonomy );
					continue;
				}

				// Confirm success.
				$this->logger->log?->debug( '[uninstall][Taxonomy terms] Taxonomy: term ' . $term_name . ' in ' . $term_taxonomy . ' deleted.' );
			}

			unregister_taxonomy( $filter_taxonomy );
			$this->logger->log?->debug( '[uninstall][Taxonomy terms] Taxonomy ' . $filter_taxonomy . ' deleted.' );
		}
		$this->logger->log?->debug( '[uninstall][Taxonomy terms] Lumière taxonomy terms deletion processed.' );
	}

	/**
	 * Delete taxonomy templates
	 */
	private function delete_taxonomy_templates(): void {

		global $wp_filesystem;

		if ( ! isset( $this->imdb_admin_values['imdburlstringtaxo'] ) ) {
			$this->logger->log?->warning( '[uninstall][Taxonomy template] Lumière Admin Options unavailable' );
			return;
		}

		$get_taxo_templates = glob( get_stylesheet_directory() . '/' . Get_Options::LUM_THEME_TAXO_FILENAME_START . $this->imdb_admin_values['imdburlstringtaxo'] . '*' );

		// No taxo files found
		if ( $get_taxo_templates === false || count( $get_taxo_templates ) === 0 ) {
			$this->logger->log?->debug( '[uninstall][Taxonomy template] No taxonomy files found in the template folder ' . get_stylesheet_directory() );
			return;
		}

		foreach ( $get_taxo_templates as $tax_file ) {
			$this->wp_filesystem_cred( $tax_file ); // in trait Files.
			$wp_filesystem->delete( $tax_file );
			$this->logger->log?->debug( '[uninstall][Taxonomy template] File ' . $tax_file . ' deleted' );
		}
		$this->logger->log?->debug( '[uninstall][Taxonomy template] Lumière taxonomy templates deletion processed.' );
	}

	/**
	 * Delete crons
	 */
	private function lumiere_delete_crons(): void {

		// Remove WP lumiere crons should they exist.
		$list_crons_available = [ 'lumiere_exec_once_update', 'lumiere_cron_deletecacheoversized', 'lumiere_cron_autofreshcache' ];
		foreach ( $list_crons_available as $cron_installed ) {
			if ( wp_clear_scheduled_hook( $cron_installed ) > 0 ) {
				$this->logger->log?->debug( '[uninstall][Crons] Cron ' . $cron_installed . ' deleted.' );
			}
		}
		$this->logger->log?->debug( '[uninstall][Crons] Lumière crons deletion processed.' );
	}

	/**
	 * Delete transients
	 * Probably too much, who deletes transients anyway? Wordpress does it alone.
	 * @return void Transient deleted
	 */
	private function lumiere_delete_transients(): void {

		$list_transients = [ 'cron_settings_updated', 'notice_lumiere_msg', 'admin_template_pass_vars', 'lum_cache_cron_refresh_store_movie', 'lum_cache_cron_refresh_store_people', 'lum_cache_cron_refresh_time_started' ];

		foreach ( $list_transients as $transient ) {
			if ( delete_transient( $transient ) ) {
				$this->logger->log?->debug( '[uninstall][Transients] Lumière ' . $transient . ' transients deleted.' );
			}
		}

		$this->logger->log?->debug( '[uninstall][Transients] Lumière transients deletion processed.' );
	}

	/**
	 * Delete options
	 */
	private function delete_options(): void {

		if ( delete_option( Get_Options::get_admin_tablename() ) === true ) {
			$this->logger->log?->error( '[uninstall][Options] Successfully deleted ' . Get_Options::get_admin_tablename() );
		}
		if ( delete_option( Get_Options_Movie::get_data_tablename() ) === true ) {
			$this->logger->log?->error( '[uninstall][Options] Successfully deleted ' . Get_Options_Movie::get_data_tablename() );
		}
		if ( delete_option( Get_Options_Person::get_data_person_tablename() ) === true ) {
			$this->logger->log?->error( '[uninstall][Options] Successfully deleted ' . Get_Options_Person::get_data_person_tablename() );
		}
		if ( delete_option( Get_Options::get_cache_tablename() ) === true ) {
			$this->logger->log?->error( '[uninstall][Options] Successfully deleted ' . Get_Options::get_cache_tablename() );
		}
		$this->logger->log?->debug( '[uninstall][Delete options] Lumière options deletion processed.' );
	}
}

// Run uninstall.
( new Uninstall() )->run_uninstall();
