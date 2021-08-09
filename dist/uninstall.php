<?php
/**
 * Uninstall Class : Lumière get blind forever
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 */


// If uninstall is not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

class LumiereUninstall {

	/* Options vars
	 * 
	 */
	private $imdb_admin_values, $imdb_widget_values,$imdb_cache_values;

	/* \Lumiere\Settings class
	 * 
	 */
	private $configClass;

	/* \Lumière\Utils class
	 * 
	 * 
	 */
	private $utilsClass;


	/*
	 * constructor
	 *
	 */
	function __construct() {

		// Start Settings class
		$this->configClass = new \Lumiere\Settings();
		$this->imdb_admin_values = $this->configClass->get_imdb_admin_option();

		// Start Utils class
		$this->utilsClass = new \Lumiere\Utils();

	}

	/*
	 * Remove ALL plugin options
	 */
	function uninstall() {

		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;
		$imdb_cache_values = $this->imdb_cache_values;

		// Activate debug
		$this->utilsClass->lumiere_activate_debug();
		// Start the logger
		$this->configClass->lumiere_start_logger('coreLumiere');
		// Store the classes so we can use it later

		$this->configClass->lumiere_maybe_log('debug', "[Lumiere][coreClass][uninstall] Lumière uninstall process debug message.");

		/****** Below actions are executed for everybody */

		// Remove WP Cron shoud it exists
/*		$timestamp = wp_next_scheduled( 'lumiere_cron_hook' );
		wp_unschedule_event( $timestamp, 'lumiere_cron_hook' );

		// Keep the settings if selected so
		if ( (isset($this->imdb_admin_values['imdbkeepsettings'])) && ( $this->imdb_admin_values['imdbkeepsettings'] == true ) ) {

			$this->configClass->lumiere_maybe_log('info', "[Lumiere][coreClass][uninstall] Lumière uninstall: keep settings selected, process finished.");

			return;
		}

		// Below actions are not executed if the user selected to keep their settings 

		// search for all imdbtaxonomy* in config array, 
		// if a taxonomy is found, let's get related terms and delete them
		foreach ( $this->utilsClass->lumiere_array_key_exists_wildcard($this->imdb_widget_values,'imdbtaxonomy*','key-value') as $key=>$value ) {
			$filter_taxonomy = str_replace('imdbtaxonomy', '', $this->imdb_admin_values['imdburlstringtaxo']  . $key );

			# get all terms, even if empty
			$terms = get_terms( array(
				'taxonomy' => $filter_taxonomy,
				'hide_empty' => false
			) );

			# Delete taxonomy terms and unregister taxonomy
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, $filter_taxonomy ); 

				$this->configClass->lumiere_maybe_log('info', "[Lumiere][coreClass][uninstall] Taxonomy: term $term in $filter_taxonomy deleted.");

				unregister_taxonomy( $filter_taxonomy );

				$this->configClass->lumiere_maybe_log('info', "[Lumiere][coreClass][uninstall] Taxonomy: taxonomy $filter_taxonomy deleted.");

			}
		}

		# Delete the options after needing them
		delete_option( 'imdbAdminOptions' ); 
		delete_option( 'imdbWidgetOptions' );
		delete_option( 'imdbCacheOptions' );

		$this->configClass->lumiere_maybe_log('info', "[Lumiere][coreClass][uninstall] Lumière options deleted.");

		# Remove cache
		if ( (isset($this->imdb_cache_values['imdbcachedir'])) && (is_dir($this->imdb_cache_values['imdbcachedir'])) ) {

			$this->utilsClass->lumiere_unlinkRecursive($this->imdb_cache_values['imdbcachedir']);

			$this->configClass->lumiere_maybe_log('info', "[Lumiere][coreClass][uninstall] Cache files and folder deleted.");

		} else {

			$this->configClass->lumiere_maybe_log('warning', "[Lumiere][coreClass][uninstall] Cache was not removed.");

		}
*/

	}
}

new LumiereUninstall();
