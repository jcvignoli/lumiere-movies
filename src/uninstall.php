<?php
/**
 * Uninstall Class : Lumière get blind forever
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

class LumiereUninstall {

	/**
	 * Options vars
	 * 
	 */
	private $imdb_admin_values;
	private $imdb_widget_values;
	private $imdb_cache_values;

	/**
	 * \Lumiere\Settings class
	 * 
	 */
	private $configClass;

	/**
	 * \Lumière\Utils class
	 * 
	 */
	private $utilsClass;


	/**
	 * constructor
	 *
	 */
	public function __construct() {

		include_once ( plugin_dir_path( __FILE__ ) . 'bootstrap.php' );

		// Start Settings class.
		$this->configClass = new Settings();
		$this->imdb_admin_values = $this->configClass->get_imdb_admin_option();
		$this->imdb_widget_values = $this->configClass->get_imdb_widget_option();

		// Start Utils class.
		$this->utilsClass = new Utils();

		// Start WP_Filesystem class.
		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( $url, '', true, false, null );
			return;
		}

		$this->uninstall();

	}

	/**
	 * Remove ALL plugin's options
	 */
	private function uninstall() {

		global $wp_filesystem;

		// Start the logger.
		$this->configClass->lumiere_start_logger('uninstall', false );

		$this->configClass->loggerclass->debug('[Lumiere][uninstall] Lumière uninstall process debug message.');

		/****** Below actions are executed for everybody */

		// Remove WP Cron shoud it exists.
		$timestamp = wp_next_scheduled( 'lumiere_cron_hook' );
		if ($timestamp){

			wp_unschedule_event( $timestamp, 'lumiere_cron_hook' );
			$this->configClass->loggerclass->debug('[Lumiere][uninstall] Cron deleted.');

		}

		// Keep the settings if selected so.
		if ( (isset($this->imdb_admin_values['imdbkeepsettings'])) && ( $this->imdb_admin_values['imdbkeepsettings'] === true ) ) {

			$this->configClass->loggerclass->info('[Lumiere][uninstall] Lumière uninstall: keep settings selected, process finished.');

			return;
		}


		/*** Following actions are not executed if the user selected to keep their settings ***/

		// Remove cache.
		$lumiere_cache_path = ABSPATH . 'wp-content/cache/lumiere/';
		if  ($wp_filesystem->is_dir($lumiere_cache_path) )  {

			$wp_filesystem->delete($lumiere_cache_path, true);

			$this->configClass->loggerclass->debug('[Lumiere][uninstall] Cache files and folder deleted.');

		} else {

			$this->configClass->loggerclass->warning('[Lumiere][uninstall] Standard cache folder was not found. Could not delete $lumiere_cache_path.');

		}


		# Delete Taxonomy
		// Search for all imdbtaxonomy* in config array, 
		// If a taxonomy is found, let's get related terms and delete them
		foreach ( $this->utilsClass->lumiere_array_key_exists_wildcard( $this->imdb_widget_values, 'imdbtaxonomy*', 'key-value') as $key => $value ) {

			$filter_taxonomy = str_replace('imdbtaxonomy', '', $this->imdb_admin_values['imdburlstringtaxo']  . $key );

			$this->configClass->loggerclass->debug('[Lumiere][uninstall] Process of deleting taxonomy $filter_taxonomy started');

			# Register taxonomy: must be registered in order to delete its terms
			register_taxonomy( $filter_taxonomy, null, array( 'label' => false, 'public' => false, 'query_var' => false, 'rewrite' => false ) );

			# Get all terms, even if empty
			$terms = get_terms( array(
				'taxonomy' => $filter_taxonomy,
				'hide_empty' => false
			) );

			# Delete taxonomy terms and unregister taxonomy
			foreach ( $terms as $term ) {

				// Sanitize terms
				$term_id = (int) $term->term_id;
				$term_name = (string) sanitize_text_field($term->name);
				$term_taxonomy = (string) sanitize_text_field($term->taxonomy);

				if ( ! empty( $term_id ) ) {

					wp_delete_term( $term_id, $filter_taxonomy );
					$this->configClass->loggerclass->debug('[Lumiere][uninstall] Taxonomy: term ' . $term_name . " in " . $term_taxonomy . " deleted.");

				}

			}

			unregister_taxonomy( $filter_taxonomy );
			$this->configClass->loggerclass->debug("[Lumiere][uninstall] Taxonomy $filter_taxonomy deleted.");

		}

		# Delete Lumière options
		delete_option( 'imdbAdminOptions' ); 
		delete_option( 'imdbWidgetOptions' );
		delete_option( 'imdbCacheOptions' );
		$this->configClass->loggerclass->debug('[Lumiere][uninstall] Lumière options deleted.');

		# Delete transients
		delete_transient( 'lumiere_tmp' );
		$this->configClass->loggerclass->debug('[Lumiere][uninstall] Lumière transients deleted.');

	}


}

new LumiereUninstall();
