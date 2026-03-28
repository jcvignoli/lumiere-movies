<?php declare( strict_types = 1 );
/**
 * Saving admin options.
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */

namespace Lumiere\Admin\Save;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Cache\Cache_Files_Management;
use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Config\Get_Options_Person;
use Lumiere\Config\Settings_Service;
use Lumiere\Admin\Save\Save_Helper;
use Exception;

/**
 * Saving or resetting options when an admin form is submitted
 *
 * @since 4.0 Created by extracting all the methods from the main admin menu and its subclasses and factorized them here, added check nonces for refresh/delete individual movies, added transiants to trigger notices in {@see \Lumiere\Admin\Admin_Menu::lumiere_admin_display_messages() } and crons in {@see \Lumiere\Admin\Cron\Cron::lumiere_add_remove_crons_cache() }
 * @since 4.6 refactorized, use a parent class
 */
final class Save_Options extends Save_Helper {

	/**
	 * Constructor
	 * @param string|null $page_data_taxo Full URL to data page taxonomy subpage
	 * @param Settings_Service $settings
	 */
	public function __construct(
		private ?string $page_data_taxo = null,
		protected Settings_Service $settings = new Settings_Service()
	) {}

	/**
	 * Register hooks
	 *
	 * @since 4.1 added param, I need it to restrain rewrite rules flush to data taxo pages
	 * @see self::save_movie_options() use $this->page_data_taxo
	 */
	public function register_hooks(): void {
		add_action( 'admin_init', [ $this, 'process_headers' ] );
	}

	/**
	 * Update taxonomy and terms in every post
	 *
	 * @see \Lumiere\Admin\Admin_Menu Call this method
	 * @see Taxonomy Process
	 * @since 4.3 Method added
	 */
	public function init_taxonomy(): void {

		if ( $this->is_valid_nonce( '_nonce_main_settings', 'lumiere_nonce_main_settings' ) === false ) { // in Admin_General trait.
			return;
		}

		$imdburlstringtaxo = isset( $_POST['imdb_imdburlstringtaxo'] ) ? sanitize_key( $_POST['imdb_imdburlstringtaxo'] ) : '';

		if (
			sanitize_key( $this->settings->get_admin_option( 'imdburlstringtaxo' ) ) !== $imdburlstringtaxo // DB value is equal to posted value
		) {

			add_action(
				'init',
				function() use ( $imdburlstringtaxo ) {
					\Lumiere\Alteration\Taxonomy::start(
						$this->settings->get_admin_option( 'imdburlstringtaxo' ),
						$imdburlstringtaxo,
						'update_old_taxo'
					);
				},
				12
			);
		}

	}

	/**
	 * Process headers to know what method to call based upon $_GETs and $_POSTs
	 * This is the main method executed to call the class methods
	 * @return void Settings saved/reset, files deleted/refreshed
	 */
	public function process_headers(): void {

		// Determine which nonce to verify based on the attempted action.
		if ( isset( $_POST['lumiere_update_main_settings'] ) || isset( $_POST['lumiere_reset_main_settings'] ) ) {
			$this->is_valid_nonce_die( '_nonce_main_settings', 'lumiere_nonce_main_settings' );
		} elseif (
			isset( $_POST['lumiere_update_cache_settings'] )
			|| isset( $_POST['lumiere_reset_cache_settings'] )
			|| isset( $_POST['delete_all_cache'] )
			|| isset( $_POST['delete_query_cache'] )
			|| isset( $_POST['refresh_ticked_cache'] )
			|| isset( $_POST['delete_ticked_cache'] )
		) {
			$this->is_valid_nonce_die( '_nonce_cache_settings', 'lumiere_nonce_cache_settings' );
		} elseif ( isset( $_POST['lumiere_update_data_movie_settings'] ) || isset( $_POST['lumiere_reset_data_movie_settings'] ) ) {
			$this->is_valid_nonce_die( '_nonce_data_settings', 'lumiere_nonce_data_settings' );
		} elseif ( isset( $_POST['lumiere_update_data_person_settings'] ) || isset( $_POST['lumiere_reset_data_person_settings'] ) ) {
			$this->is_valid_nonce_die( '_nonce_data_person_settings', 'lumiere_nonce_data_person_settings' );
		}

		$referer = parent::get_referer();

		$this->handle_main_options( $referer );
		$this->handle_cache_options( $referer );
		$this->handle_individual_cache_files( $referer );
		$this->handle_data_options( $referer );
	}

	/**
	 * Handle main options save/reset
	 * @param false|string $referer Current referer URL
	 */
	private function handle_main_options( bool|string $referer ): void {
		if ( isset( $_POST['lumiere_update_main_settings'], $_POST['imdb_imdburlstringtaxo'], $_POST['imdb_imdburlpopups'] ) ) {
			$this->lumiere_main_options_save(
				$referer,
				sanitize_text_field( wp_unslash( (string) $_POST['imdb_imdburlstringtaxo'] ) ),
				sanitize_text_field( wp_unslash( (string) $_POST['imdb_imdburlpopups'] ) )
			);
		} elseif ( isset( $_POST['lumiere_reset_main_settings'] ) ) {
			$this->lumiere_main_options_reset( $referer );
		}
	}

	/**
	 * Handle cache options save/reset/delete/refresh
	 * @param false|string $referer Current referer URL
	 */
	private function handle_cache_options( bool|string $referer ): void {
		if ( isset( $_POST['lumiere_update_cache_settings'] ) ) {
			$this->cache_options_save( $referer );
		} elseif ( isset( $_POST['lumiere_reset_cache_settings'] ) ) {
			$this->cache_options_reset( $referer );
		} elseif ( isset( $_POST['delete_all_cache'] ) ) {
			$this->cache_delete_allfiles( $referer );
		} elseif ( isset( $_POST['delete_query_cache'] ) ) {
			$this->cache_delete_query( $referer, new Cache_Files_Management() );
		} elseif ( isset( $_POST['refresh_ticked_cache'] ) ) {
			$cachedeletefor_movies = isset( $_POST['imdb_cachedeletefor_movies'] ) ? map_deep( $_POST['imdb_cachedeletefor_movies'], 'sanitize_key' ) : null;
			$cachedeletefor_people = isset( $_POST['imdb_cachedeletefor_people'] ) ? map_deep( $_POST['imdb_cachedeletefor_people'], 'sanitize_key' ) : null;
			$this->cache_refresh_ticked_files( $referer, new Cache_Files_Management(), $cachedeletefor_movies, $cachedeletefor_people );
		} elseif ( isset( $_POST['delete_ticked_cache'] ) ) {
			$cachedeletefor_movies = isset( $_POST['imdb_cachedeletefor_movies'] ) ? map_deep( $_POST['imdb_cachedeletefor_movies'], 'sanitize_key' ) : null;
			$cachedeletefor_people = isset( $_POST['imdb_cachedeletefor_people'] ) ? map_deep( $_POST['imdb_cachedeletefor_people'], 'sanitize_key' ) : null;
			$this->cache_delete_ticked_files( $referer, new Cache_Files_Management(), $cachedeletefor_movies, $cachedeletefor_people );
		}
	}

	/**
	 * Handle individual cache file delete/refresh
	 * @param false|string $referer Current referer URL
	 */
	private function handle_individual_cache_files( bool|string $referer ): void {
		if ( isset( $_GET['dothis'] ) ) {
			$cache_mngmt_class = new Cache_Files_Management();
			$type = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['type'] ) ) : '';
			$where = isset( $_GET['where'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['where'] ) ) : '';

			if ( $_GET['dothis'] === 'delete' && $this->is_valid_nonce( '_nonce_cache_deleteindividual', 'deleteindividual' ) ) { // in Admin_General trait.
				$this->do_delete_cache_linked_file( $referer, $cache_mngmt_class, $type, $where );
			} elseif ( $_GET['dothis'] === 'refresh' && $this->is_valid_nonce( '_nonce_cache_refreshindividual', 'refreshindividual' ) ) { // in Admin_General trait.
				$this->do_refresh_cache_linked_file( $referer, $cache_mngmt_class, $type, $where );
			}
		}
	}

	/**
	 * Handle data options save/reset
	 * @param false|string $referer Current referer URL
	 */
	private function handle_data_options( bool|string $referer ): void {
		if ( isset( $_POST['lumiere_update_data_movie_settings'] ) ) {
			$this->save_movie_options( $referer );
		} elseif ( isset( $_POST['lumiere_reset_data_movie_settings'] ) ) {
			$this->lumiere_data_options_reset( $referer );
		}

		if ( isset( $_POST['lumiere_update_data_person_settings'] ) ) {
			$this->save_data_person_options( $referer );
		} elseif ( isset( $_POST['lumiere_reset_data_person_settings'] ) ) {
			$this->lumiere_data_person_options_reset( $referer );
		}
	}

	/**
	 * Save Main options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 * @param null|string $imdburlstringtaxo $_POST['imdb_imdburlstringtaxo']
	 * @param null|string $imdburlpopups $_POST['imdbpopup_modal_window']
	 */
	private function lumiere_main_options_save( bool|string $get_referer, ?string $imdburlstringtaxo, ?string $imdburlpopups ): void {

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

		/**
		 * If $_POST['imdb_imdburlstringtaxo'] was submitted, need a rewrite rules flush to update new URLs according to new taxo terms.
		 * It is executed after self::lumiere_static_start_taxonomy()
		 */
		if (
			isset( $_POST['imdb_imdburlstringtaxo'] ) && strlen( sanitize_key( $_POST['imdb_imdburlstringtaxo'] ) ) > 0
			&& sanitize_key( $this->settings->get_admin_option( 'imdburlstringtaxo' ) ) !== sanitize_key( $_POST['imdb_imdburlstringtaxo'] )
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
		$imdb_admin_values = $this->settings->get_admin_options();

		foreach ( $_POST as $key => $postvalue ) {

			if ( in_array( $key, $forbidden_terms, true ) ) {
				continue;
			}

			if ( is_string( $postvalue ) ) {
				// Sanitize key.
				$key_san = sanitize_key( $key );
				// remove "imdb_" from $key.
				$key_final = str_replace( 'imdb_', '', $key_san );
				$val_final = sanitize_text_field( $postvalue );

				// Context-aware sanitization for values.
				if ( in_array( $key_final, [ 'imdburlpopups', 'imdbplugindirectory', 'imdbplugindirectory_partial' ], true ) ) {
					$val_final = esc_url_raw( $postvalue );
				} elseif (
					str_ends_with( $key_final, 'larg' )
					|| str_ends_with( $key_final, 'long' )
					|| str_ends_with( $key_final, 'width' )
					|| str_ends_with( $key_final, 'results' )
					|| str_ends_with( $key_final, 'request' )
					|| str_ends_with( $key_final, 'Updates' )
				) {
					$val_final = strval( absint( $postvalue ) );
				} else {
					$val_final = sanitize_text_field( $postvalue );
				}

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
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 */
	private function lumiere_main_options_reset( bool|string $get_referer ): void {

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
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 *
	 * @see Lumiere\Admin\Cron\Cron::lumiere_add_remove_crons_cache()
	 * @throws Exception if nonces are incorrect
	 */
	private function cache_options_save( bool|string $get_referer ): void {

		// These $_POST values shouldn't be processed
		$forbidden_terms = [ 'lumiere_update_cache_settings', '_wp_http_referer', '_nonce_cache_settings' ];

		$imdb_cache_values = $this->settings->get_cache_options();

		foreach ( $_POST as $key => $postvalue ) {

			if ( in_array( $key, $forbidden_terms, true ) ) {
				continue;
			}

			if ( is_string( $postvalue ) ) {
				// Sanitize key.
				$key_san = sanitize_key( $key );
				// remove "imdb_" from $key.
				$key_final = str_replace( 'imdb_', '', $key_san );
				$val_final = sanitize_text_field( $postvalue );

				// Context-aware sanitization for values.
				if ( str_ends_with( $key_final, 'dir' ) || str_ends_with( $key_final, 'dir_partial' ) || str_ends_with( $key_final, 'root' ) ) {
					$val_final = sanitize_text_field( $postvalue ); // Paths are not always valid URLs.
				} elseif ( str_ends_with( $key_final, 'expire' ) || str_ends_with( $key_final, 'limit' ) ) {
					$val_final = strval( absint( $postvalue ) );
				} else {
					$val_final = sanitize_text_field( $postvalue );
				}

				// Dirty code that should be in Settings: Relative cache paths to be updated if 'imdbcachedir_partial' is updated.
				if ( $key_final === 'imdbcachedir_partial' ) {
					$imdb_cache_values['imdbcachedir']  = WP_CONTENT_DIR . $val_final;
					$imdb_cache_values['imdbphotoroot'] = WP_CONTENT_DIR . $val_final . 'images/';
					$imdb_cache_values['imdbphotodir']  = content_url() . $val_final . 'images/';
				}

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
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 */
	private function cache_options_reset( bool|string $get_referer ): void {
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
	private function cache_delete_allfiles( bool|string $get_referer ): void {

		// prevent drama
		if ( $this->settings->get_cache_option( 'imdbcachedir' ) === null || strlen( $this->settings->get_cache_option( 'imdbcachedir' ) ) < 1 ) {
			wp_die( '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' );
		}

		// Delete all cache
		$this->dir_unlink_recursive( $this->settings->get_cache_option( 'imdbcachedir' ) ); // in trait Files which is in trait Admin_General.

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_all_msg', 30 );
			exit( 0 );
		}
	}

	/**
	 * Delete all Query files
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function cache_delete_query( bool|string $get_referer, Cache_Files_Management $cache_mngmt_class ): void {
		$cache_mngmt_class->delete_query_cache_files();

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_query_deleted', 30 );
			exit( 0 );
		}
	}

	/**
	 * Refresh ticked People/Movie files (based on inputs)
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
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
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 * @param Cache_Files_Management $cache_mngmt_class object with the methods needed
	 * @param null|array<string> $delete_movies $_POST['imdb_cachedeletefor_movies']
	 * @param null|array<string> $delete_people $_POST['imdb_cachedeletefor_people']
	 */
	private function cache_delete_ticked_files(
		bool|string $get_referer,
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
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 * @param Cache_Files_Management $cache_mngmt_class object with the methods needed
	 * @param 'movie'|'people'|string $type result of $_GET['type'] to define either people or movie
	 * @param string $where result of $_GET['where'] the people or movie IMDb ID
	 */
	private function do_delete_cache_linked_file( bool|string $get_referer, Cache_Files_Management $cache_mngmt_class, string $type, string $where ): void {

		$cache_mngmt_class->delete_file( $type, $where );

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_individual_msg', 30 );
			exit( 0 );
		}
	}

	/**
	 * Refresh specific People/Movie files (based on html links)
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 * @param Cache_Files_Management $cache_mngmt_class object with the methods needed
	 * @param 'movie'|'people'|string $type result of $_GET['type'] to define either people or movie
	 * @param string $where result of $_GET['where'] the people or movie IMDb ID
	 */
	private function do_refresh_cache_linked_file( bool|string $get_referer, Cache_Files_Management $cache_mngmt_class, string $type, string $where ): void {

		$cache_mngmt_class->refresh_file( $type, $where );

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_refresh_individual_msg', 30 );
			exit( 0 );
		}
	}

	/**
	 * Save Data movies options
	 * Do the distinction between value in associative array as array ('imdbwidgetorder') and string (the others)
	 * Remove string 'imdb_' sent in $_POST
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 *
	 * @throws Exception if nonces are incorrect
	 * @since 4.1 added flush_rewrite_rules()
	 * @since 4.4 refactorized
	 */
	private function save_movie_options( bool|string $get_referer, ): void {

		// These $_POST values shouldn't be processed
		$forbidden_terms = [
			'imdb_imdbwidgetorder', // empty value, using 'imdbwidgetorderContainer' instead.
			// Nonce and others
			'lumiere_nonce_data_settings',
			'lumiere_update_data_settings',
			'_wp_http_referer',
			'_nonce_data_settings',
		];

		$imdb_data_values = $this->settings->get_movie_options();
		foreach ( $_POST as $key => $postvalue ) {

			if ( in_array( $key, $forbidden_terms, true ) ) {
				continue;
			}

			if ( $key === 'imdbwidgetorderContainer' && is_array( $postvalue ) ) { // build 'imdbwidgetorder' row.
				$post_value_san = map_deep( $postvalue, 'sanitize_key' );
				$key_final = 'imdbwidgetorder';
				$val_final = [];
				foreach ( $post_value_san as $val_array_key => $val_array_value ) {
					// use the row number as value; add one since it's supposed to start at 1 in Settings.
					$val_final[ $val_array_value ] = strval( $val_array_key + 1 );
				}
			} elseif ( is_string( $postvalue ) ) {
				// Sanitize.
				$key_san = sanitize_text_field( $key ); /** @info don't use sanitize_key() since it lower all letters: data_movies have caps+small letters mixed */
				// remove "imdb_" from $key.
				$key_final = str_replace( 'imdb_', '', $key_san );

				if ( str_ends_with( $key_final, 'number' ) ) {
					$val_final = strval( absint( $postvalue ) );
				} else {
					$val_final = sanitize_text_field( $postvalue );
				}
			}

			if ( isset( $key_final ) && isset( $val_final ) ) {
				$imdb_data_values[ $key_final ] = $val_final;
			}
		}

		update_option( Get_Options_Movie::get_data_tablename(), $imdb_data_values );

		/**
		 * New custom pages need a flush rewrite rules to make sure taxonomy pages are available.
		 * Execute only if referer page is options data taxo page
		 * Won't execute when copying taxonomy template
		 */
		$last_url_piece = $get_referer !== false ? strrchr( $get_referer, '?' ) : false;
		if ( $last_url_piece !== false && isset( $this->page_data_taxo ) && admin_url( 'admin.php' ) . $last_url_piece === $this->page_data_taxo ) {
			flush_rewrite_rules();
		}

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_updated', 30 );
			exit( 0 );
		}
	}

	/**
	 * Reset Data options
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 */
	private function lumiere_data_options_reset( bool|string $get_referer, ): void {

		delete_option( Get_Options_Movie::get_data_tablename() );
		Get_Options::create_database_options();

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_reset', 30 );
			exit( 0 );
		}
	}

	/**
	 * Save Data person options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 *
	 * @throws Exception if nonces are incorrect
	 * @since 4.6 new
	 */
	private function save_data_person_options( bool|string $get_referer, ): void {

		$imdb_data_person_values = $this->settings->get_person_options();
		foreach ( $_POST as $key => $post_value ) {
			if ( str_contains( $key, '_active' ) ) {
				$key_san = sanitize_key( $key );
				$val_san = is_string( $post_value ) ? strval( absint( $post_value ) ) : '';
				$imdb_data_person_values['activated'][ $key_san ] = $val_san;
			} elseif ( str_contains( $key, '_number' ) ) {
				$key_san = sanitize_key( $key );
				$val_san = is_string( $post_value ) ? sanitize_text_field( $post_value ) : '';
				$imdb_data_person_values['number'][ $key_san ] = $val_san;
			} elseif ( $key === 'person_order' && is_array( $post_value ) ) {
				$post_value_san = map_deep( $post_value, 'sanitize_key' );
				$key_final = 'order';
				$val_final = [];
				foreach ( $post_value_san as $val_array_key => $val_array_value ) {
					// use the row number as value; add one since it's supposed to start at 1 in Settings.
					$val_final[ $val_array_value ] = strval( $val_array_key + 1 );
				}
				$imdb_data_person_values[ $key_final ] = $val_final;
			}
		}

		update_option( Get_Options_Person::get_data_person_tablename(), $imdb_data_person_values );

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_updated', 30 );
			exit( 0 );
		}
	}

	/**
	 * Reset Data person options
	 * @param false|string $get_referer The URL string from {@see Save_Helper::get_referer()}
	 */
	private function lumiere_data_person_options_reset( bool|string $get_referer, ): void {

		delete_option( Get_Options_Person::get_data_person_tablename() );
		Get_Options::create_database_options();

		if ( $get_referer !== false && wp_safe_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_reset', 30 );
			exit( 0 );
		}
	}
}

