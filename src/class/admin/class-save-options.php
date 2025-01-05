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

use Lumiere\Settings;
use Lumiere\Admin\Cache_Tools;
use Lumiere\Admin\Admin_General;
use Lumiere\Tools\Settings_Global;
use Exception;

/**
 * Saving or reseting options when a form is submitted
 *
 * @since 4.0 Created by extracting all the methods from the main admin menu and its subclasses and factorized them here, added check nonces for refresh/delete individual movies, added transiants to trigger notices in {@see \Lumiere\Admin\Admin_Menu::lumiere_admin_display_messages() } and crons in {@see \Lumiere\Admin\Cron::lumiere_add_remove_crons_cache() }
 *
 * @info: the following OPTIONS_DATA_MINUS doesn't include 'imdbwidgetorder': array<string>, recreate it for phpstan
 * @phpstan-type OPTIONS_DATA_MINUS array{'imdbwidgettitle': string, 'imdbwidgetpic': string,'imdbwidgetruntime': string, 'imdbwidgetdirector': string, 'imdbwidgetcountry': string, 'imdbwidgetactor':string, 'imdbwidgetactornumber':int|string, 'imdbwidgetcreator': string, 'imdbwidgetrating': string, 'imdbwidgetlanguage': string, 'imdbwidgetgenre': string, 'imdbwidgetwriter': string, 'imdbwidgetproducer': string, 'imdbwidgetproducernumber': bool|string, 'imdbwidgetkeyword': string, 'imdbwidgetprodcompany': string, 'imdbwidgetplot': string, 'imdbwidgetplotnumber': string, 'imdbwidgetgoof': string, 'imdbwidgetgoofnumber': string|bool, 'imdbwidgetcomment': string, 'imdbwidgetquote': string, 'imdbwidgetquotenumber': string|bool, 'imdbwidgettagline': string, 'imdbwidgettaglinenumber': string|bool, 'imdbwidgetcolor': string, 'imdbwidgetalsoknow': string, 'imdbwidgetalsoknownumber': string|bool, 'imdbwidgetcomposer': string, 'imdbwidgetsoundtrack': string, 'imdbwidgetsoundtracknumber': string|bool, 'imdbwidgetofficialsites': string, 'imdbwidgetsource': string, 'imdbwidgetyear': string, 'imdbwidgettrailer': string, 'imdbwidgettrailernumber': bool|string, 'imdbtaxonomycolor': string, 'imdbtaxonomycomposer': string, 'imdbtaxonomycountry': string, 'imdbtaxonomycreator': string, 'imdbtaxonomydirector': string, 'imdbtaxonomygenre': string, 'imdbtaxonomykeyword': string, 'imdbtaxonomylanguage': string, 'imdbtaxonomyproducer': string, 'imdbtaxonomyactor': string, 'imdbtaxonomywriter': string}
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Tools\Settings_Global
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Tools\Settings_Global
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Tools\Settings_Global
 */
class Save_Options {

	/**
	 * Traits
	 */
	use Settings_Global, Admin_General;

	/**
	 * Allows to limit the calls to rewrite rules refresh
	 * @var string|null $page_data_taxo Full URL to data page taxonomy subpage
	 * @see Save_Options::lumiere_data_options_save()
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
	}

	/**
	 * Call from a WordPress hook
	 * @param string|null $page_data_taxo Full URL to data page taxonomy subpage
	 *
	 * @since 4.1 added param, I need it to restrain rewrite rules flush to data taxo pages
	 * @see self::lumiere_data_options_save() use $this->page_data_taxo
	 */
	public static function lumiere_static_start( ?string $page_data_taxo = null ): void {

		$class_save = new self( $page_data_taxo );

		add_action( 'admin_init', [ $class_save, 'process_headers' ] );
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

		if ( isset( $_POST['_nonce_general_settings'] ) && ( wp_verify_nonce( sanitize_key( $_POST['_nonce_general_settings'] ), 'lumiere_nonce_general_settings' ) > 0 ) ) { // Nonce

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
							echo 'test';
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

		/** General options */
		if (
			isset( $_POST['lumiere_update_general_settings'] )
			&& isset( $_POST['_nonce_general_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_general_settings'] ), 'lumiere_nonce_general_settings' ) > 0
		) {
			$this->lumiere_general_options_save(
				$this->get_referer(),
				// @phpstan-var array{'imdb_imdburlstringtaxo': string|null} $_POST
				sanitize_text_field( wp_unslash( $_POST['imdb_imdburlstringtaxo'] ?? '' ) ),
				sanitize_text_field( wp_unslash( $_POST['imdb_imdburlpopups'] ?? '' ) )
			);
		} elseif (
			isset( $_POST['lumiere_reset_general_settings'] )
			&& isset( $_POST['_nonce_general_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_general_settings'] ), 'lumiere_nonce_general_settings' ) > 0
		) {
			$this->lumiere_general_options_reset( $this->get_referer() );
		}

		/** Cache options */
		if (
			isset( $_POST['lumiere_update_cache_settings'] )
			&& isset( $_POST['_nonce_cache_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_settings'] ), 'lumiere_nonce_cache_settings' ) > 0
		) {
			// save options
			$this->lumiere_cache_options_save( $this->get_referer() );
		} elseif (
			isset( $_POST['lumiere_reset_cache_settings'] )
			&& isset( $_POST['_nonce_cache_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_settings'] ), 'lumiere_nonce_cache_settings' ) !== false
		) {
			// reset options
			$this->lumiere_cache_options_reset( $this->get_referer() );
		} elseif (
			isset( $_POST['delete_all_cache'] )
			&& isset( $_POST['_nonce_cache_all_and_query_check'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_all_and_query_check'] ), 'cache_all_and_query_check' ) > 0
		) {
			$this->lumiere_cache_delete_allfiles( $this->get_referer() );
		} elseif (
			isset( $_POST['delete_query_cache'] )
			&& isset( $_POST['_nonce_cache_all_and_query_check'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_all_and_query_check'] ), 'cache_all_and_query_check' ) > 0
		) {
			// delete all query cache files.
			$this->lumiere_cache_delete_query( $this->get_referer(), new Cache_Tools() );
		} elseif (
			isset( $_POST['delete_ticked_cache'] )
			&& isset( $_POST['_nonce_cache_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_settings'] ), 'lumiere_nonce_cache_settings' ) > 0
		) {
			// delete several ticked files, they can be either movies or people, using same method.
			$delete_movies = isset( $_POST['imdb_cachedeletefor_movies'] ) ? array_map( 'sanitize_key', $_POST['imdb_cachedeletefor_movies'] ) : null;
			$delete_people = isset( $_POST['imdb_cachedeletefor_people'] ) ? array_map( 'sanitize_key', $_POST['imdb_cachedeletefor_people'] ) : null;
			$this->lumiere_cache_delete_ticked_files( $this->get_referer(), new Cache_Tools(), $delete_movies, $delete_people );
		} elseif (
			isset( $_GET['dothis'] )
			&& $_GET['dothis'] === 'delete'
			&& isset( $_GET['type'] )
			&& isset( $_GET['where'] )
			&& isset( $_GET['_nonce_cache_deleteindividual'] )
			&& wp_verify_nonce( sanitize_key( $_GET['_nonce_cache_deleteindividual'] ), 'deleteindividual' ) > 0
		) {
			// delete a specific file by clicking on it.
			$this->lumiere_cache_delete_linked_file( $this->get_referer(), new Cache_Tools(), sanitize_text_field( wp_unslash( $_GET['type'] ) ), sanitize_text_field( wp_unslash( $_GET['where'] ) ) );
		} elseif (
			isset( $_GET['dothis'] )
			&& $_GET['dothis'] === 'refresh'
			&& isset( $_GET['type'] )
			&& isset( $_GET['where'] )
			&& isset( $_GET['_nonce_cache_refreshindividual'] )
			&& wp_verify_nonce( sanitize_key( $_GET['_nonce_cache_refreshindividual'] ), 'refreshindividual' ) > 0
		) {
			// refresh a specific file by clicking on it.
			$this->lumiere_cache_refresh_linked_file( $this->get_referer(), new Cache_Tools(), sanitize_text_field( wp_unslash( $_GET['type'] ) ), sanitize_text_field( wp_unslash( $_GET['where'] ) ) );
		}

		/** Data options */
		if (
			isset( $_POST['lumiere_update_data_settings'] )
			&& isset( $_POST['_nonce_data_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_data_settings'] ), 'lumiere_nonce_data_settings' ) > 0
		) {
			$this->lumiere_data_options_save( $this->get_referer() );
		} elseif (
			isset( $_POST['lumiere_reset_data_settings'] )
			&& isset( $_POST['_nonce_data_settings'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_nonce_data_settings'] ), 'lumiere_nonce_data_settings' ) > 0
		) {
			$this->lumiere_data_options_reset( $this->get_referer() );
		}
	}

	/**
	 * Save General options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param null|string $imdburlstringtaxo $_POST['imdb_imdburlstringtaxo']
	 * @param null|string $imdburlpopups $_POST['imdbpopup_modal_window']
	 *
	 * @template T as OPTIONS_ADMIN
	 * @phan-suppress PhanTemplateTypeNotUsedInFunctionReturn
	 */
	// @phpstan-ignore-next-line method.templateTypeNotInParameter
	private function lumiere_general_options_save( string|bool $get_referer, ?string $imdburlstringtaxo, ?string $imdburlpopups ): void {

		// Check if $_POST['imdb_imdburlstringtaxo'] and $_POST['imdb_imdburlpopups'] are identical, because they can't be, so echo 'test' if they are.
		if (
			isset( $imdburlstringtaxo )
			&& isset( $imdburlpopups )
			&& '/' . $imdburlstringtaxo === $imdburlpopups
		) {
			set_transient( 'notice_lumiere_msg', 'general_options_error_identical_value', 30 );
			if ( $get_referer !== false && wp_safe_redirect( esc_url_raw( $get_referer ) ) ) {
				echo 'test';
			}
		}

		// Check if $_POST['imdb_imdburlpopups'] is an acceptable path.
		if (
			! isset( $_POST['imdbpopup_modal_window'] ) // Make sure this is processed only in advanced options.
			&& isset( $imdburlpopups ) // always set, not usefull.
			&& ( $imdburlpopups === '/' || strlen( $imdburlpopups ) === 0 ) // forbid '/' or nothing.
		) {
			set_transient( 'notice_lumiere_msg', 'general_options_error_imdburlpopups_invalid', 30 );
			if ( $get_referer !== false && wp_safe_redirect( esc_url_raw( $get_referer ) ) ) {
				echo 'test';
			}
		}

		// Check if nonce is a valid value.
		if ( ! isset( $_POST['_nonce_general_settings'] ) || ! ( wp_verify_nonce( sanitize_key( $_POST['_nonce_general_settings'] ), 'lumiere_nonce_general_settings' ) > 0 ) ) {
			echo 'test';
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

		foreach ( $_POST as $key => $postvalue ) {

			// Sanitize keys
			$key_sanitized = sanitize_text_field( $key );
			/** @phpstan-var key-of<T> $keynoimdb */
			$keynoimdb = str_replace( 'imdb_', '', $key_sanitized );

			// These $_POST values shouldn't be processed
			$forbidden_terms = [ 'lumiere_update_general_settings', '_wp_http_referer', '_nonce_general_settings' ];
			if ( in_array( $key_sanitized, $forbidden_terms, true ) ) {
				continue;
			}

			/** @phpstan-var value-of<T>|null $post_sanitized */
			$post_sanitized = isset( $_POST[ $key_sanitized ] ) && is_string( $_POST[ $key_sanitized ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key_sanitized ] ) ) : null;
			if ( isset( $post_sanitized ) ) {
				/** @psalm-suppress InvalidArrayOffset, InvalidPropertyAssignmentValue */
				$this->imdb_admin_values[ $keynoimdb ] = $post_sanitized;
			}
		}

		// update options, keep the originals if there is a problem.
		update_option( \Lumiere\Settings::get_admin_tablename(), $this->imdb_admin_values );

		set_transient( 'notice_lumiere_msg', 'options_updated', 30 );
		if ( $get_referer !== false && wp_safe_redirect( esc_url_raw( $get_referer ) ) ) {
			echo 'test';
		}
	}

	/**
	 * Reset General options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_general_options_reset( string|bool $get_referer ): void {

		delete_option( \Lumiere\Settings::get_admin_tablename() );
		Settings::build_options();

		set_transient( 'notice_lumiere_msg', 'options_reset', 30 );
		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			echo 'test';
		}
	}

	/**
	 * Save Cache options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 *
	 * @see Lumiere\Admin\Cron::lumiere_add_remove_crons_cache()
	 * @throws Exception if nonces are incorrect
	 *
	 * @template T as OPTIONS_CACHE
	 * @phan-suppress PhanTemplateTypeNotUsedInFunctionReturn
	 */
	// @phpstan-ignore-next-line method.templateTypeNotInParameter
	private function lumiere_cache_options_save( string|bool $get_referer ): void {

		if ( ! isset( $_POST['_nonce_cache_settings'] ) || wp_verify_nonce( sanitize_key( $_POST['_nonce_cache_settings'] ), 'lumiere_nonce_cache_settings' ) === false ) {
			throw new Exception( esc_html__( 'Nounce error', 'lumiere-movies' ) );
		}

		foreach ( $_POST as $key => $postvalue ) {

			// Sanitize
			$key_sanitized = sanitize_text_field( $key );

			// These $_POST values shouldn't be processed
			$forbidden_terms = [ 'lumiere_update_cache_settings', '_wp_http_referer', '_nonce_cache_settings' ];
			if ( in_array( $key_sanitized, $forbidden_terms, true ) ) {
				continue;
			}

			$keynoimdb = str_replace( 'imdb_', '', $key_sanitized );
			$post_sanitized = isset( $_POST[ $key_sanitized ] ) && is_string( $_POST[ $key_sanitized ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key_sanitized ] ) ) : null;
			if ( isset( $post_sanitized ) ) {
				/**
				 * @phpstan-var key-of<T> $keynoimdb
				 * @psalm-suppress InvalidArrayOffset, InvalidPropertyAssignmentValue
				 */
				$this->imdb_cache_values[ $keynoimdb ] = $post_sanitized;
			}
		}

		update_option( Settings::get_cache_tablename(), $this->imdb_cache_values );

		set_transient( 'notice_lumiere_msg', 'options_updated', 30 );

		// If the option for cron imdbcachekeepsizeunder was modified.
		if ( isset( $_POST['imdb_imdbcachekeepsizeunder'] ) ) {
			set_transient( 'cron_settings_imdbcachekeepsizeunder_updated', 'imdbcachekeepsizeunder', 30 );
		}

		// If the option for cron imdbcachekeepsizeunder was modified.
		if ( isset( $_POST['imdb_imdbcacheautorefreshcron'] ) ) {
			set_transient( 'cron_settings_imdbcacheautorefreshcron_updated', 'imdbcacheautorefreshcron', 30 );
		}

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			echo 'test';
		}
	}

	/**
	 * Reset Cache options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_cache_options_reset( string|bool $get_referer ): void {
		delete_option( Settings::get_cache_tablename() );
		Settings::build_options();

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_reset', 30 );
			echo 'test';
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
		/** @psalm-suppress PossiblyNullArgument -- Argument 1 of lumiere_unlink_recursive cannot be null -- it can't, just checked! */
		$this->lumiere_unlink_recursive( $this->imdb_cache_values['imdbcachedir'] ); // in trait Files.

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_all_msg', 30 );
			echo 'test';
		}
	}

	/**
	 * Delete all Query files
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_cache_delete_query( string|bool $get_referer, Cache_Tools $cache_tools_class ): void {
		$cache_tools_class->cache_delete_query_cache_files();

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_query_deleted', 30 );
			echo 'test';
		}
	}

	/**
	 * Delete ticked People/Movie files (based on inputs)
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param Cache_Tools $cache_tools_class object with the methods needed
	 * @param null|array<string> $delete_movies $_POST['imdb_cachedeletefor_movies']
	 * @param null|array<string> $delete_people $_POST['imdb_cachedeletefor_people']
	 */
	private function lumiere_cache_delete_ticked_files(
		string|bool $get_referer,
		Cache_Tools $cache_tools_class,
		?array $delete_movies,
		?array $delete_people
	): void {

		if ( isset( $delete_movies ) ) {
			$cache_tools_class->cache_delete_ticked_files( $delete_movies, 'movie' );
		} elseif ( isset( $delete_people ) ) {
			$cache_tools_class->cache_delete_ticked_files( $delete_people, 'people' );
		}

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_ticked_msg', 30 );
			echo 'test';
		}
	}

	/**
	 * Delete specific People/Movie files (based on html links)
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param Cache_Tools $cache_tools_class object with the methods needed
	 * @param string $type result of $_GET['type'] to define either people or movie
	 * @param string $where result of $_GET['where'] the people or movie IMDb ID
	 */
	private function lumiere_cache_delete_linked_file( string|bool $get_referer, Cache_Tools $cache_tools_class, string $type, string $where ): void {

		$cache_tools_class->cache_delete_specific_file( $type, $where );

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_individual_msg', 30 );
			echo 'test';
		}
	}

	/**
	 * Refresh specific People/Movie files (based on html links)
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param Cache_Tools $cache_tools_class object with the methods needed
	 * @param string $type result of $_GET['type'] to define either people or movie
	 * @param string $where result of $_GET['where'] the people or movie IMDb ID
	 */
	private function lumiere_cache_refresh_linked_file( string|bool $get_referer, Cache_Tools $cache_tools_class, string $type, string $where ): void {

		$cache_tools_class->cache_refresh_specific_file( $type, $where );

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_refresh_individual_msg', 30 );
			echo 'test';
		}
	}

	/**
	 * Save Data options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 *
	 * @throws Exception if nonces are incorrect
	 * @since 4.1 added flush_rewrite_rules()
	 * @TODO refactorize, function is too much complex
	 */
	private function lumiere_data_options_save( string|bool $get_referer, ): void {

		if ( ! isset( $_POST['_nonce_data_settings'] ) || wp_verify_nonce( sanitize_key( $_POST['_nonce_data_settings'] ), 'lumiere_nonce_data_settings' ) === false ) {
			throw new Exception( esc_html__( 'Nounce error', 'lumiere-movies' ) );
		}

		foreach ( $_POST as $key => $postvalue ) {

			// Sanitize
			$key_sanitized = sanitize_text_field( $key );

			// These $_POST values shouldn't be processed
			$forbidden_terms = [
				// Keep $_POST['imdbwidgetorderContainer'] and $_POST['imdbwidgetorder'] untouched
				'imdbwidgetordercontainer',
				'imdb_imdbwidgetorder',
				// Nonce and others
				'lumiere_nonce_data_settings',
				'lumiere_update_data_settings',
				'_wp_http_referer',
				'_nonce_data_settings',
			];
			if ( in_array( $key_sanitized, $forbidden_terms, true ) ) {
				continue;
			}

			$post_sanitized = isset( $_POST[ $key_sanitized ] ) && is_string( $_POST[ $key_sanitized ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key_sanitized ] ) ) : null;
			// Copy $_POST to $this->imdb_data_values var
			if ( isset( $post_sanitized ) ) {

				// remove "imdb_" from $key
				$keynoimdb = str_replace( 'imdb_', '', $key_sanitized );

				/**
				 * The following OPTIONS_DATA_MINUS doesn't include 'imdbwidgetorder': array<string> which is dealt with later
				 * @phpstan-var key-of<OPTIONS_DATA_MINUS> $keynoimdb  */
				$this->imdb_data_values[ $keynoimdb ] = $post_sanitized;
			}
		}

		/**
		 * Special part related to details order
		 * Sanitize and reverse keys and values to insert $_POST['imdbwidgetorderContainer'] into $imdb_data_values['imdbwidgetorder']
		 */
		if ( isset( $_POST['imdbwidgetorderContainer'] ) ) {

			/**
			 * @psalm-suppress InvalidArgument
			 */
			$datafiltered = map_deep( wp_unslash( $_POST['imdbwidgetorderContainer'] ), 'sanitize_text_field' );
			;
			$data_keys_filtered = array_keys( $datafiltered );

			$data_values_filtered = $datafiltered;

			$imdbwidgetorder_sanitized = array_combine( $data_values_filtered, $data_keys_filtered );

			$this->imdb_data_values['imdbwidgetorder'] = $imdbwidgetorder_sanitized;
		}

		// update options
		update_option( Settings::get_data_tablename(), $this->imdb_data_values );

		/**
		 * New custom pages need a flush rewrite rules to make sure taxonomy pages are available.
		 * Execute only if referer page is options data taxo page
		 * Won't execute when copying taxonomy template
		 */
		if ( $get_referer !== false && isset( $this->page_data_taxo ) && admin_url( 'admin.php' ) . strrchr( $get_referer, '?' ) === $this->page_data_taxo ) {
			flush_rewrite_rules();
		}

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_updated', 30 );
			echo 'test';
		}

	}

	/**
	 * Reset Data options
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_data_options_reset( string|bool $get_referer, ): void {

		delete_option( Settings::get_data_tablename() );
		Settings::build_options();

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_reset', 30 );
			echo 'test';
		}
	}
}

