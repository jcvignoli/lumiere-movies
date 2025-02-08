<?php declare( strict_types = 1 );
/**
 * Saving admin options.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Cache\Cache_Files_Management;
use Lumiere\Admin\Admin_General;
use Lumiere\Tools\Settings_Global;
use Lumiere\Tools\Get_Options;
use Exception;

/**
 * Saving or resetting options when an admin form is submitted
 *
 * @since 4.0 Created by extracting all the methods from the main admin menu and its subclasses and factorized them here, added check nonces for refresh/delete individual movies, added transiants to trigger notices in {@see \Lumiere\Admin\Admin_Menu::lumiere_admin_display_messages() } and crons in {@see \Lumiere\Admin\Cron::lumiere_add_remove_crons_cache() }
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Settings
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Settings
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Settings
 */
class Save_Options {

	/**
	 * Traits
	 */
	use Settings_Global, Admin_General;

	/**
	 * Allows to limit the calls to rewrite rules refresh
	 * @var string|null $page_data_taxo Full URL to data page taxonomy subpage
	 * @see Save_Options::save_data_options()
	 * @since 4.1
	 */
	private null|string $page_data_taxo;

	/**
	 * Constructor
	 * @param string|null $page_data_taxo Full URL to data page taxonomy subpage
	 */
	public function __construct( ?string $page_data_taxo = null ) {

		// Store page
		$this->page_data_taxo = $page_data_taxo;

		// Get options from database.
		$this->get_db_options(); // In Settings_Global trait.

		add_action( 'admin_init', [ $this, 'process_headers' ] );
	}

	/**
	 * Called in hook
	 * @param string|null $page_data_taxo Full URL to data page taxonomy subpage
	 *
	 * @since 4.1 added param, I need it to restrain rewrite rules flush to data taxo pages
	 * @see self::save_data_options() use $this->page_data_taxo
	 */
	public static function lumiere_static_start( ?string $page_data_taxo = null ): void {
		$class_save = new self( $page_data_taxo );
	}

	/**
	 * Update taxonomy and terms in every post
	 * @param string|null $get_referer Full URL to Lumière admin advanced options
	 *
	 * @see Admin_Menu Call the method
	 * @see Taxonomy Process
	 * @since 4.3 Method added
	 */
	public static function lumiere_static_start_taxonomy( ?string $get_referer = null ): void {

		$class_save = new self();

		if ( isset( $_POST['_nonce_main_settings'] ) && ( wp_verify_nonce( sanitize_key( $_POST['_nonce_main_settings'] ), 'lumiere_nonce_main_settings' ) > 0 ) ) { // Nonce

			$imdburlstringtaxo = isset( $_POST['imdb_imdburlstringtaxo'] ) ? sanitize_key( $_POST['imdb_imdburlstringtaxo'] ) : '';
			if (
				strlen( $imdburlstringtaxo ) > 0
				&& isset( $_POST['imdb_imdburlstringtaxo_terms'] ) && sanitize_key( $_POST['imdb_imdburlstringtaxo_terms'] ) === '1' // Checkbox update terms
				&& sanitize_key( $class_save->imdb_admin_values['imdburlstringtaxo'] ) !== $imdburlstringtaxo // DB value is equal to posted value
			) {

				add_action(
					'init',
					function() use ( $class_save, $imdburlstringtaxo ) {
						\Lumiere\Alteration\Taxonomy::lumiere_static_start(
							$class_save->imdb_admin_values['imdburlstringtaxo'],
							$imdburlstringtaxo,
							'update_old_taxo'
						);
						/*if ( isset( $get_referer ) && $get_referer !== false && wp_safe_redirect( esc_url_raw( $get_referer ) ) ) {
							exit;
						}*/
					},
					12
				);
			}
		}
	}

	/**
	 * Build the current URL for referer
	 * Use all the values data in $_GET automatically, except those in $forbidden_url_strings
	 * @return false|string The URL string if it's ok, false if both the $_GET is non-existant and wp_get_referer() can't get anything
	 */
	private function get_referer(): bool|string {

		/** @psalm-suppress PossiblyNullArgument -- Argument 1 of esc_html cannot be null, possibly null value provided - I don't even understand*/
		$gets_array = array_map( 'esc_html', $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- no escape ok!
		// These forbidden strings are generated in Cache class in $_GET
		$forbidden_url_strings = [ 'dothis', 'where', 'type', '_nonce_cache_deleteindividual', '_nonce_cache_refreshindividual' ];
		$first_url_string = '';
		$next_url_strings = '';
		foreach ( $gets_array as $var => $value ) {

			// Don't add to the URL those forbidden strings
			if ( in_array( $var, $forbidden_url_strings, true ) ) {
				continue;
			}
			// Build the beginning of the URL on the first occurence
			if ( $var === array_key_first( $gets_array ) ) {
				$first_url_string = 'admin.php?' . $var . '=' . $value;
				continue;
			}

			// Add the strings on the next lines after the first one
			$next_url_strings .= '&' . $var . '=' . $value;
		}
		return count( $gets_array ) > 0 ? admin_url( $first_url_string . $next_url_strings ) : wp_get_referer();
	}

	/**
	 * Process headers to know what method to call based upon $_GETs and $_POSTs
	 * This is the main method executed to call the class methods
	 * @return void Settings saved/reset, files deleted/refreshed
	 */
	public function process_headers(): void {

		/** Main options */
		if (
			isset( $_POST['lumiere_update_main_settings'], $_POST['_nonce_main_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_main_settings'] ), 'lumiere_nonce_main_settings' ) > 0
		) {
			$this->lumiere_main_options_save(
				$this->get_referer(),
				// @phpstan-var array{'imdb_imdburlstringtaxo': string|null} $_POST
				sanitize_text_field( wp_unslash( $_POST['imdb_imdburlstringtaxo'] ?? '' ) ),
				sanitize_text_field( wp_unslash( $_POST['imdb_imdburlpopups'] ?? '' ) )
			);
		} elseif (
			isset( $_POST['lumiere_reset_main_settings'], $_POST['_nonce_main_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_main_settings'] ), 'lumiere_nonce_main_settings' ) > 0
		) {
			$this->lumiere_main_options_reset( $this->get_referer() );
		}

		/** Cache options */
		if (
			isset( $_POST['lumiere_update_cache_settings'], $_POST['_nonce_cache_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_settings'] ), 'lumiere_nonce_cache_settings' ) > 0
		) {
			// save options
			$this->lumiere_cache_options_save( $this->get_referer() );
		} elseif (
			isset( $_POST['lumiere_reset_cache_settings'], $_POST['_nonce_cache_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_settings'] ), 'lumiere_nonce_cache_settings' ) !== false
		) {
			// reset options
			$this->lumiere_cache_options_reset( $this->get_referer() );
		} elseif (
			isset( $_POST['delete_all_cache'], $_POST['_nonce_cache_all_and_query_check'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_all_and_query_check'] ), 'cache_all_and_query_check' ) > 0
		) {
			$this->lumiere_cache_delete_allfiles( $this->get_referer() );
		} elseif (
			isset( $_POST['delete_query_cache'], $_POST['_nonce_cache_all_and_query_check'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_all_and_query_check'] ), 'cache_all_and_query_check' ) > 0
		) {
			// delete all query cache files.
			$this->lumiere_cache_delete_query( $this->get_referer(), new Cache_Files_Management() );
		} elseif (
			isset( $_POST['refresh_ticked_cache'], $_POST['_nonce_cache_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_settings'] ), 'lumiere_nonce_cache_settings' ) > 0
		) {
			// Refresh several ticked files, they can be either movies or people, using same method. Using checkboxes of delete.
			$refresh_movies = isset( $_POST['imdb_cachedeletefor_movies'] ) ? array_map( 'sanitize_key', $_POST['imdb_cachedeletefor_movies'] ) : null;
			$refresh_people = isset( $_POST['imdb_cachedeletefor_people'] ) ? array_map( 'sanitize_key', $_POST['imdb_cachedeletefor_people'] ) : null;
			$this->cache_refresh_ticked_files( $this->get_referer(), new Cache_Files_Management(), $refresh_movies, $refresh_people );
		} elseif (
			isset( $_POST['delete_ticked_cache'], $_POST['_nonce_cache_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_settings'] ), 'lumiere_nonce_cache_settings' ) > 0
		) {
			// delete several ticked files, they can be either movies or people, using same method.
			$delete_movies = isset( $_POST['imdb_cachedeletefor_movies'] ) ? array_map( 'sanitize_key', $_POST['imdb_cachedeletefor_movies'] ) : null;
			$delete_people = isset( $_POST['imdb_cachedeletefor_people'] ) ? array_map( 'sanitize_key', $_POST['imdb_cachedeletefor_people'] ) : null;
			$this->cache_delete_ticked_files( $this->get_referer(), new Cache_Files_Management(), $delete_movies, $delete_people );
		} elseif (
			isset( $_GET['dothis'] ) && $_GET['dothis'] === 'delete'
			&& isset( $_GET['type'], $_GET['where'], $_GET['_nonce_cache_deleteindividual'] )
			&& wp_verify_nonce( sanitize_key( $_GET['_nonce_cache_deleteindividual'] ), 'deleteindividual' ) > 0
		) {
			// delete a specific file by clicking on it.
			$this->do_delete_cache_linked_file( $this->get_referer(), new Cache_Files_Management(), sanitize_text_field( wp_unslash( $_GET['type'] ) ), sanitize_text_field( wp_unslash( $_GET['where'] ) ) );
		} elseif (
			isset( $_GET['dothis'] ) && $_GET['dothis'] === 'refresh'
			&& isset( $_GET['type'], $_GET['where'], $_GET['_nonce_cache_refreshindividual'] )
			&& wp_verify_nonce( sanitize_key( $_GET['_nonce_cache_refreshindividual'] ), 'refreshindividual' ) > 0
		) {
			// refresh a specific file by clicking on it.
			$this->do_refresh_cache_linked_file( $this->get_referer(), new Cache_Files_Management(), sanitize_text_field( wp_unslash( $_GET['type'] ) ), sanitize_text_field( wp_unslash( $_GET['where'] ) ) );
		}

		/** Data options */
		if (
			isset( $_POST['lumiere_update_data_settings'], $_POST['_nonce_data_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_data_settings'] ), 'lumiere_nonce_data_settings' ) > 0
		) {
			$this->save_data_options( $this->get_referer() );
		} elseif (
			isset( $_POST['lumiere_reset_data_settings'], $_POST['_nonce_data_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_data_settings'] ), 'lumiere_nonce_data_settings' ) > 0
		) {
			$this->lumiere_data_options_reset( $this->get_referer() );
		}
	}

	/**
	 * Save Main options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param null|string $imdburlstringtaxo $_POST['imdb_imdburlstringtaxo']
	 * @param null|string $imdburlpopups $_POST['imdbpopup_modal_window']
	 */
	private function lumiere_main_options_save( string|bool $get_referer, ?string $imdburlstringtaxo, ?string $imdburlpopups ): void {

		// Check if $_POST['imdb_imdburlstringtaxo'] and $_POST['imdb_imdburlpopups'] are identical, because they can't be, so exit if they are.
		if (
			isset( $imdburlstringtaxo )
			&& isset( $imdburlpopups )
			&& '/' . $imdburlstringtaxo === $imdburlpopups
		) {
			set_transient( 'notice_lumiere_msg', 'main_options_error_identical_value', 30 );
			if ( $get_referer !== false && wp_safe_redirect( esc_url_raw( $get_referer ) ) ) {
				exit( 0 );
			}
		}

		// Check if $_POST['imdb_imdburlpopups'] is an acceptable path.
		if (
			! isset( $_POST['imdbpopup_modal_window'] ) // Make sure this is processed only in advanced options.
			&& isset( $imdburlpopups ) // always set, not usefull.
			&& ( $imdburlpopups === '/' || strlen( $imdburlpopups ) === 0 ) // forbid '/' or nothing.
		) {
			set_transient( 'notice_lumiere_msg', 'main_options_error_imdburlpopups_invalid', 30 );
			if ( $get_referer !== false && wp_safe_redirect( esc_url_raw( $get_referer ) ) ) {
				exit( 0 );
			}
		}

		// Check if nonce is a valid value.
		if ( ! isset( $_POST['_nonce_main_settings'] ) || ! ( wp_verify_nonce( sanitize_key( $_POST['_nonce_main_settings'] ), 'lumiere_nonce_main_settings' ) > 0 ) ) {
			set_transient( 'notice_lumiere_msg', 'invalid_nonce', 30 );
			if ( $get_referer !== false && wp_safe_redirect( esc_url_raw( $get_referer ) ) ) {
				exit( 0 );
			}
		}

		/**
		 * If $_POST['imdb_imdburlstringtaxo'] was submitted, need a rewrite rules flush to update new URLs according to new taxo terms.
		 * It is executed after self::lumiere_static_start_taxonomy()
		 */
		if (
			isset( $_POST['imdb_imdburlstringtaxo'] ) && strlen( sanitize_key( $_POST['imdb_imdburlstringtaxo'] ) ) > 0
			&& sanitize_key( $this->imdb_admin_values['imdburlstringtaxo'] ) !== sanitize_key( $_POST['imdb_imdburlstringtaxo'] )
		) {
			flush_rewrite_rules();
		}

		/**
		 * If $_POST['imdb_imdbtaxonomy'] was submitted, need a rewrite rules flush
		 */
		if (
			isset( $_POST['imdb_imdbtaxonomy'] ) && strlen( sanitize_key( $_POST['imdb_imdbtaxonomy'] ) ) > 0
		) {
			flush_rewrite_rules();
		}

		$forbidden_terms = [ 'lumiere_update_main_settings', '_wp_http_referer', '_nonce_main_settings' ];
		$imdb_admin_values = get_option( Get_Options::get_admin_tablename(), [] );

		foreach ( $_POST as $key => $postvalue ) {

			if ( in_array( $key, $forbidden_terms, true ) ) {
				continue;
			} elseif ( isset( $_POST[ $key ] ) && is_string( $postvalue ) ) {
				// Sanitize
				$key_san = sanitize_text_field( $key );
				// remove "imdb_" from $key
				$key_final = str_replace( 'imdb_', '', $key_san );
				$val_final = sanitize_text_field( $postvalue );
			}

			if ( isset( $key_final ) && isset( $val_final ) ) {
				$imdb_admin_values[ $key_final ] = $val_final;
			}
		}

		update_option( Get_Options::get_admin_tablename(), $imdb_admin_values );

		set_transient( 'notice_lumiere_msg', 'options_updated', 30 );
		if ( $get_referer !== false && wp_safe_redirect( esc_url_raw( $get_referer ) ) ) {
			exit( 0 );
		}
	}

	/**
	 * Reset Main options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_main_options_reset( string|bool $get_referer ): void {

		delete_option( Get_Options::get_admin_tablename() );
		Get_Options::create_database_options();

		set_transient( 'notice_lumiere_msg', 'options_reset', 30 );
		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			exit( 0 );
		}
	}

	/**
	 * Save Cache options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 *
	 * @see Lumiere\Admin\Cron::lumiere_add_remove_crons_cache()
	 * @throws Exception if nonces are incorrect
	 */
	private function lumiere_cache_options_save( string|bool $get_referer ): void {

		if ( ! isset( $_POST['_nonce_cache_settings'] ) || wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_settings'] ), 'lumiere_nonce_cache_settings' ) === false ) {
			throw new Exception( esc_html__( 'Nounce error', 'lumiere-movies' ) );
		}

		// These $_POST values shouldn't be processed
		$forbidden_terms = [ 'lumiere_update_cache_settings', '_wp_http_referer', '_nonce_cache_settings' ];

		$imdb_cache_values = get_option( Get_Options::get_cache_tablename(), [] );

		foreach ( $_POST as $key => $postvalue ) {

			if ( in_array( $key, $forbidden_terms, true ) ) {
				continue;
			} elseif ( isset( $_POST[ $key ] ) && is_string( $postvalue ) ) {
				// Sanitize
				$key_san = sanitize_text_field( $key );
				// remove "imdb_" from $key
				$key_final = str_replace( 'imdb_', '', $key_san );
				$val_final = sanitize_text_field( $postvalue );
				// Dirty code that should be in Settings: Relative cache paths to be updated if 'imdbcachedir_partial' is updated.
				if ( $key_final === 'imdbcachedir_partial' ) {
					$imdb_cache_values['imdbcachedir'] = WP_CONTENT_DIR . $val_final;
				}
				if ( $key_final === 'imdbcachedir_partial' && isset( $imdb_cache_values['imdbcachedir'] ) ) {
					$imdb_cache_values['imdbphotoroot'] = $imdb_cache_values['imdbcachedir'] . 'images/';
				}
			}

			if ( isset( $key_final ) && isset( $val_final ) ) {
				$imdb_cache_values[ $key_final ] = $val_final;
			}
		}

		update_option( Get_Options::get_cache_tablename(), $imdb_cache_values );

		set_transient( 'notice_lumiere_msg', 'options_updated', 30 );

		// If the option for cron imdbcachekeepsizeunder was modified.
		if ( isset( $_POST['imdb_imdbcachekeepsizeunder'] ) ) {
			set_transient( 'cron_settings_imdbcachekeepsizeunder_updated', 'imdbcachekeepsizeunder', 30 );
		}

		// If the option for cron imdbcachekeepsizeunder was modified.
		if ( isset( $_POST['imdb_imdbcacheautorefreshcron'] ) ) {
			set_transient( 'cron_settings_imdbcacheautorefreshcron_updated', 'imdbcacheautorefreshcron', 30 );
		}

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			exit( 0 );
		}
	}

	/**
	 * Reset Cache options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_cache_options_reset( string|bool $get_referer ): void {
		delete_option( Get_Options::get_cache_tablename() );
		Get_Options::create_database_options();

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_reset', 30 );
			exit( 0 );
		}
	}

	/**
	 * Delete all Cache files
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_cache_delete_allfiles( string|bool $get_referer ): void {

		// prevent drama
		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) {
			wp_die( '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' );
		}

		// Delete all cache
		$this->dir_unlink_recursive( $this->imdb_cache_values['imdbcachedir'] ); // in trait Files which is in trait Admin_General.

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_all_msg', 30 );
			exit( 0 );
		}
	}

	/**
	 * Delete all Query files
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_cache_delete_query( string|bool $get_referer, Cache_Files_Management $cache_mngmt_class ): void {
		$cache_mngmt_class->delete_query_cache_files();

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_query_deleted', 30 );
			exit( 0 );
		}
	}

	/**
	 * Refresh ticked People/Movie files (based on inputs)
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param Cache_Files_Management $cache_mngmt_class object with the methods needed
	 * @param null|array<string> $refresh_movies $_POST['imdb_cachedeletefor_movies']
	 * @param null|array<string> $refresh_people $_POST['imdb_cachedeletefor_people']
	 */
	private function cache_refresh_ticked_files(
		string|bool $get_referer,
		Cache_Files_Management $cache_mngmt_class,
		?array $refresh_movies,
		?array $refresh_people
	): void {

		if ( isset( $refresh_movies ) ) {
			$cache_mngmt_class->refresh_multiple_files( $refresh_movies, 'movie' );
		} elseif ( isset( $refresh_people ) ) {
			$cache_mngmt_class->refresh_multiple_files( $refresh_people, 'people' );
		}

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_refresh_ticked_msg', 30 );
			exit( 0 );
		}
	}

	/**
	 * Delete ticked People/Movie files (based on inputs)
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param Cache_Files_Management $cache_mngmt_class object with the methods needed
	 * @param null|array<string> $delete_movies $_POST['imdb_cachedeletefor_movies']
	 * @param null|array<string> $delete_people $_POST['imdb_cachedeletefor_people']
	 */
	private function cache_delete_ticked_files(
		string|bool $get_referer,
		Cache_Files_Management $cache_mngmt_class,
		?array $delete_movies,
		?array $delete_people
	): void {

		if ( isset( $delete_movies ) ) {
			$cache_mngmt_class->delete_multiple_files( $delete_movies, 'movie' );
		} elseif ( isset( $delete_people ) ) {
			$cache_mngmt_class->delete_multiple_files( $delete_people, 'people' );
		}

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_ticked_msg', 30 );
			exit( 0 );
		}
	}

	/**
	 * Delete specific People/Movie files (based on html links)
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param Cache_Files_Management $cache_mngmt_class object with the methods needed
	 * @param 'movie'|'people'|string $type result of $_GET['type'] to define either people or movie
	 * @param string $where result of $_GET['where'] the people or movie IMDb ID
	 */
	private function do_delete_cache_linked_file( string|bool $get_referer, Cache_Files_Management $cache_mngmt_class, string $type, string $where ): void {

		$cache_mngmt_class->delete_file( $type, $where );

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_individual_msg', 30 );
			exit( 0 );
		}
	}

	/**
	 * Refresh specific People/Movie files (based on html links)
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param Cache_Files_Management $cache_mngmt_class object with the methods needed
	 * @param 'movie'|'people'|string $type result of $_GET['type'] to define either people or movie
	 * @param string $where result of $_GET['where'] the people or movie IMDb ID
	 */
	private function do_refresh_cache_linked_file( string|bool $get_referer, Cache_Files_Management $cache_mngmt_class, string $type, string $where ): void {

		$cache_mngmt_class->refresh_file( $type, $where );

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_refresh_individual_msg', 30 );
			exit( 0 );
		}
	}

	/**
	 * Save Data options
	 * Do the distinction between value in associative array as array ('imdbwidgetorder') and string (the others)
	 * Remove string 'imdb_' sent in $_POST
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 *
	 * @throws Exception if nonces are incorrect
	 * @since 4.1 added flush_rewrite_rules()
	 * @since 4.4 refactorized
	 */
	private function save_data_options( string|bool $get_referer, ): void {

		if ( ! isset( $_POST['_nonce_data_settings'] ) || wp_verify_nonce( sanitize_key( $_POST['_nonce_data_settings'] ), 'lumiere_nonce_data_settings' ) === false ) {
			throw new Exception( 'Wrong nounce error' );
		}

		// These $_POST values shouldn't be processed
		$forbidden_terms = [
			'imdb_imdbwidgetorder', // empty value, using 'imdbwidgetorderContainer' instead.
			// Nonce and others
			'lumiere_nonce_data_settings',
			'lumiere_update_data_settings',
			'_wp_http_referer',
			'_nonce_data_settings',
		];

		$imdb_data_values = get_option( Get_Options::get_data_tablename(), [] );
		foreach ( $_POST as $key => $postvalue ) {

			if ( in_array( $key, $forbidden_terms, true ) ) {
				continue;
			} elseif ( $key === 'imdbwidgetorderContainer' && is_array( $postvalue ) ) { // build 'imdbwidgetorder' row.
				$post_value_san = map_deep( $postvalue, 'sanitize_text_field' );
				$key_final = 'imdbwidgetorder';
				$val_final = [];
				foreach ( $post_value_san as $val_array_key => $val_array_value ) {
					// use the row number as value; add one since it's supposed to start at 1 in Settings.
					$val_final[ $val_array_value ] = strval( $val_array_key + 1 );
				}
			} elseif ( isset( $_POST[ $key ] ) && is_string( $postvalue ) ) {
				// Sanitize
				$key_san = sanitize_key( $key );
				// remove "imdb_" from $key
				$key_final = str_replace( 'imdb_', '', $key_san );
				$val_final = sanitize_key( $postvalue );
			}

			if ( isset( $key_final ) && isset( $val_final ) ) {
				$imdb_data_values[ $key_final ] = $val_final;
			}
		}

		update_option( Get_Options::get_data_tablename(), $imdb_data_values );

		/**
		 * New custom pages need a flush rewrite rules to make sure taxonomy pages are available.
		 * Execute only if referer page is options data taxo page
		 * Won't execute when copying taxonomy template
		 */
		if ( $get_referer !== false && isset( $this->page_data_taxo ) && admin_url( 'admin.php' ) . strrchr( $get_referer, '?' ) === $this->page_data_taxo ) {
			flush_rewrite_rules();
		}

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_updated', 30 );
			exit( 0 );
		}

	}

	/**
	 * Reset Data options
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_data_options_reset( string|bool $get_referer, ): void {

		delete_option( Get_Options::get_data_tablename() );
		Get_Options::create_database_options();

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_reset', 30 );
			exit( 0 );
		}
	}
}

