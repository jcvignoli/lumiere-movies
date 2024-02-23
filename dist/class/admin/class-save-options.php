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
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Settings;
use Lumiere\Tools\Utils;
use Lumiere\Admin\Cache_Tools;
use Exception;

/**
 * Saving or reseting options when a form is submitted
 *
 * @since 3.12 Created by extracting all the methods from the main admin subclasses and put it here
 *
 * @info: the following OPTIONS_DATA_MINUS doesn't include 'imdbwidgetorder': array<string>, recreate it
 * @phpstan-type OPTIONS_DATA_MINUS array{'imdbwidgettitle': string, 'imdbwidgetpic': string,'imdbwidgetruntime': string, 'imdbwidgetdirector': string, 'imdbwidgetcountry': string, 'imdbwidgetactor':string, 'imdbwidgetactornumber':int|string, 'imdbwidgetcreator': string, 'imdbwidgetrating': string, 'imdbwidgetlanguage': string, 'imdbwidgetgenre': string, 'imdbwidgetwriter': string, 'imdbwidgetproducer': string, 'imdbwidgetproducernumber': bool|string, 'imdbwidgetkeyword': string, 'imdbwidgetprodcompany': string, 'imdbwidgetplot': string, 'imdbwidgetplotnumber': string, 'imdbwidgetgoof': string, 'imdbwidgetgoofnumber': string|bool, 'imdbwidgetcomment': string, 'imdbwidgetquote': string, 'imdbwidgetquotenumber': string|bool, 'imdbwidgettagline': string, 'imdbwidgettaglinenumber': string|bool, 'imdbwidgetcolor': string, 'imdbwidgetalsoknow': string, 'imdbwidgetalsoknownumber': string|bool, 'imdbwidgetcomposer': string, 'imdbwidgetsoundtrack': string, 'imdbwidgetsoundtracknumber': string|bool, 'imdbwidgetofficialsites': string, 'imdbwidgetsource': string, 'imdbwidgetyear': string, 'imdbwidgettrailer': string, 'imdbwidgettrailernumber': bool|string, 'imdbtaxonomycolor': string, 'imdbtaxonomycomposer': string, 'imdbtaxonomycountry': string, 'imdbtaxonomycreator': string, 'imdbtaxonomydirector': string, 'imdbtaxonomygenre': string, 'imdbtaxonomykeyword': string, 'imdbtaxonomylanguage': string, 'imdbtaxonomyproducer': string, 'imdbtaxonomyactor': string, 'imdbtaxonomywriter': string}
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Settings
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Settings
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Settings
 */
class Save_Options {

	/**
	 * Admin options
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 */
	private array $imdb_admin_values;

	/**
	 * Widget options
	 * @phpstan-var OPTIONS_DATA $imdb_widget_values
	 */
	private array $imdb_widget_values;

	/**
	 * Cache options
	 * @phpstan-var OPTIONS_CACHE $imdb_cache_values
	 */
	private array $imdb_cache_values;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get options from database.
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		$this->imdb_widget_values = get_option( Settings::LUMIERE_WIDGET_OPTIONS );
		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

		$this->process_headers();
	}

	/**
	 * Build the current URL for referer
	 * Use all the values data in $_GET automatically, except those in $forbidden_url_strings
	 * @return false|string The URL string if it's ok, false if both the $_GET is non-existant and wp_get_referer() can't get anything
	 */
	private function get_referer(): bool|string {

		/** @psalm-suppress PossiblyNullArgument -- Argument 1 of esc_html cannot be null, possibly null value provided - I don't even understand*/
		$gets_array = array_map( 'esc_html', $_GET );
		// These forbiddent strings are generated in Cache class in $_GET
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
	 * Call from a WordPress hook
	 */
	public static function lumiere_static_start(): void {
		$start = new self();
	}

	/**
	 * Process headers to know what method to call based upon $_GETs and $_POSTs
	 * This is the main method executed to call the class methods
	 * @return void Settings saved/reset, files deleted/refreshed
	 */
	private function process_headers(): void {

		/** General options */
		if (    isset( $_POST['lumiere_update_general_settings'] )
			&& isset( $_POST['_nonce_general_settings'] )
			&& wp_verify_nonce( $_POST['_nonce_general_settings'], 'lumiere_nonce_general_settings' ) !== false
		) {
			$this->lumiere_general_options_save( $this->get_referer() );
		} elseif (
			isset( $_POST['lumiere_reset_general_settings'] )
			&& isset( $_POST['_nonce_general_settings'] )
			&& wp_verify_nonce( $_POST['_nonce_general_settings'], 'lumiere_nonce_general_settings' ) !== false
		) {
			$this->lumiere_general_options_reset( $this->get_referer() );
		}

		/** Cache options */
		if (
			isset( $_POST['lumiere_update_cache_settings'] )
			&& isset( $_POST['_nonce_cache_settings'] )
			&& wp_verify_nonce( $_POST['_nonce_cache_settings'], 'lumiere_nonce_cache_settings' ) !== false
		) {
			// save options
			$this->lumiere_cache_options_save( $this->get_referer() );
		} elseif (
			isset( $_POST['lumiere_reset_cache_settings'] )
			&& isset( $_POST['_nonce_cache_settings'] )
			&& wp_verify_nonce( $_POST['_nonce_cache_settings'], 'lumiere_nonce_cache_settings' ) !== false
		) {
			// reset options
			$this->lumiere_cache_options_reset( $this->get_referer() );
		} elseif (
			isset( $_POST['delete_all_cache'] )
			&& isset( $_POST['_nonce_cache_all_and_query_check'] )
			&& wp_verify_nonce( $_POST['_nonce_cache_all_and_query_check'], 'cache_all_and_query_check' ) !== false
		) {
			$this->lumiere_cache_delete_allfiles( $this->get_referer() );
		} elseif (
			isset( $_POST['delete_query_cache'] )
			&& isset( $_POST['_nonce_cache_all_and_query_check'] )
			&& wp_verify_nonce( $_POST['_nonce_cache_all_and_query_check'], 'cache_all_and_query_check' ) !== false
		) {
			// delete all query cache files.
			$this->lumiere_cache_delete_query( $this->get_referer(), new Cache_Tools() );
		} elseif (
			isset( $_POST['delete_ticked_cache'] )
			&& isset( $_POST['_nonce_cache_settings'] )
			&& wp_verify_nonce( $_POST['_nonce_cache_settings'], 'lumiere_nonce_cache_settings' ) !== false
		) {
			// delete several ticked files.
			$this->lumiere_cache_delete_ticked_files( $this->get_referer(), new Cache_Tools() );
		} elseif (
			isset( $_GET['dothis'] )
			&& $_GET['dothis'] === 'delete'
			&& isset( $_GET['type'] )
			&& isset( $_GET['_nonce_cache_deleteindividual'] )
			&& wp_verify_nonce( $_GET['_nonce_cache_deleteindividual'], 'deleteindividual' ) !== false
		) {
			// delete a specific file by clicking on it.
			$this->lumiere_cache_delete_linked_file( $this->get_referer(), new Cache_Tools() );
		} elseif (
			isset( $_GET['dothis'] )
			&& $_GET['dothis'] === 'refresh'
			&& isset( $_GET['type'] )
			&& isset( $_GET['_nonce_cache_refreshindividual'] )
			&& wp_verify_nonce( $_GET['_nonce_cache_refreshindividual'], 'refreshindividual' ) !== false
		) {
			// refresh a specific file by clicking on it.
			$this->lumiere_cache_refresh_linked_file( $this->get_referer(), new Cache_Tools() );
		}

		/** Data options */
		if (
			isset( $_POST['lumiere_update_data_settings'] )
			&& isset( $_POST['_nonce_data_settings'] )
			&& wp_verify_nonce( $_POST['_nonce_data_settings'], 'lumiere_nonce_data_settings' ) !== false
		) {
			$this->lumiere_data_options_save( $this->get_referer() );
		} elseif (
			isset( $_POST['lumiere_reset_data_settings'] )
			&& isset( $_POST['_nonce_data_settings'] )
			&& wp_verify_nonce( $_POST['_nonce_data_settings'], 'lumiere_nonce_data_settings' ) !== false
		) {
			$this->lumiere_data_options_reset( $this->get_referer() );
		}
	}

	/**
	 * Save General options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @throws Exception if nonces are incorrect
	 *
	 * @template T as OPTIONS_ADMIN
	 * @phan-suppress PhanTemplateTypeNotUsedInFunctionReturn
	 */
	// @phpstan-ignore-next-line method.templateTypeNotInParameter
	private function lumiere_general_options_save( string|bool $get_referer ): void {

		if ( ! isset( $_POST['_nonce_general_settings'] ) || wp_verify_nonce( $_POST['_nonce_general_settings'], 'lumiere_nonce_general_settings' ) === false ) {
			throw new Exception( __( 'Nounce error', 'lumiere-movies' ) );
		}

		// Check if $_POST['imdburlstringtaxo'] and $_POST['imdburlpopups'] are identical, because they can't be.
		$post_imdb_imdburlstringtaxo = isset( $_POST['imdb_imdburlstringtaxo'] ) ? esc_html( $_POST['imdb_imdburlstringtaxo'] ) : 'empty';
		$post_imdb_imdburlpopups = isset( $_POST['imdb_imdburlpopups'] ) ? esc_html( $_POST['imdb_imdburlpopups'] ) : 'empty';

		if (
			( $post_imdb_imdburlstringtaxo !== 'empty' ) &&
		( str_replace( '/', '', $post_imdb_imdburlstringtaxo ) === str_replace( '/', '', $post_imdb_imdburlpopups ) ) || isset( $this->imdb_admin_values['imdburlpopups'] ) && ( str_replace( '/', '', $post_imdb_imdburlstringtaxo ) === str_replace( '/', '', $this->imdb_admin_values['imdburlpopups'] ) )
									||
			( $post_imdb_imdburlpopups !== 'empty' ) &&
		( str_replace( '/', '', $post_imdb_imdburlpopups ) === str_replace( '/', '', $post_imdb_imdburlstringtaxo ) ) || isset( $this->imdb_admin_values['imdburlstringtaxo'] ) && ( str_replace( '/', '', $post_imdb_imdburlpopups ) === str_replace( '/', '', $this->imdb_admin_values['imdburlstringtaxo'] ) )
		) {

			set_transient( 'notice_lumiere_msg', 'general_options_error_identical_value', 1 );
			if ( $get_referer !== false && wp_safe_redirect( esc_url_raw( $get_referer ) ) ) {
				exit;
			}
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
			$post_sanitized = isset( $_POST[ $key_sanitized ] ) && is_string( $_POST[ $key_sanitized ] ) ? sanitize_text_field( $_POST[ $key_sanitized ] ) : null;
			if ( isset( $post_sanitized ) ) {
				/** @psalm-suppress InvalidArrayOffset, InvalidPropertyAssignmentValue */
				$this->imdb_admin_values[ $keynoimdb ] = $post_sanitized;
			}
		}

		// update options
		update_option( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, $this->imdb_admin_values );

		set_transient( 'notice_lumiere_msg', 'options_updated', 1 );
		if ( $get_referer !== false && wp_safe_redirect( esc_url_raw( $get_referer ) ) ) {
			exit;
		}
	}

	/**
	 * Reset General options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_general_options_reset( string|bool $get_referer ): void {

		delete_option( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS );

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'general_options_reset', 1 );
			exit;
		}
	}

	/**
	 * Save Cache options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 *
	 * @see {Lumiere\Admin\Cron::lumiere_add_remove_crons_cache()}
	 * @throws Exception if nonces are incorrect
	 *
	 * @template T as OPTIONS_CACHE
	 * @phan-suppress PhanTemplateTypeNotUsedInFunctionReturn
	 */
	// @phpstan-ignore-next-line method.templateTypeNotInParameter
	private function lumiere_cache_options_save( string|bool $get_referer ): void {

		if ( ! isset( $_POST['_nonce_cache_settings'] ) || wp_verify_nonce( $_POST['_nonce_cache_settings'], 'lumiere_nonce_cache_settings' ) === false ) {
			throw new Exception( __( 'Nounce error', 'lumiere-movies' ) );
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
			$post_sanitized = isset( $_POST[ $key_sanitized ] ) && is_string( $_POST[ $key_sanitized ] ) ? sanitize_text_field( $_POST[ $key_sanitized ] ) : null;
			if ( isset( $post_sanitized ) ) {
				/**
				 * @phpstan-var key-of<T> $keynoimdb
				 * @psalm-suppress InvalidArrayOffset, InvalidPropertyAssignmentValue
				 */
				$this->imdb_cache_values[ $keynoimdb ] = $post_sanitized;
			}
		}

		update_option( Settings::LUMIERE_CACHE_OPTIONS, $this->imdb_cache_values );

		set_transient( 'notice_lumiere_msg', 'options_updated', 1 );

		// If the option for cron imdbcachekeepsizeunder was modified.
		if ( isset( $_POST['imdb_imdbcachekeepsizeunder'] ) ) {
			set_transient( 'cron_settings_imdbcachekeepsizeunder_updated', 'imdbcachekeepsizeunder', 3 );
		}

		// If the option for cron imdbcachekeepsizeunder was modified.
		if ( isset( $_POST['imdb_imdbcacheautorefreshcron'] ) ) {
			set_transient( 'cron_settings_imdbcacheautorefreshcron_updated', 'imdbcacheautorefreshcron', 3 );
		}

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			exit;
		}
	}

	/**
	 * Reset Cache options
	 *
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_cache_options_reset( string|bool $get_referer ): void {
		delete_option( Settings::LUMIERE_CACHE_OPTIONS );

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_reset', 1 );
			exit;
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
			wp_die( Utils::lumiere_notice( 3, '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' ) );
		}

		// Delete all cache
		/** @psalm-suppress PossiblyNullArgument -- Argument 1 of lumiere_unlink_recursive cannot be null -- it can't, just checked! */
		Utils::lumiere_unlink_recursive( $this->imdb_cache_values['imdbcachedir'] );

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_all_msg', 1 );
			exit;
		}
	}

	/**
	 * Delete all Query files
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_cache_delete_query( string|bool $get_referer, Cache_Tools $cache_tools_class ): void {
		$cache_tools_class->cache_delete_query_cache_files();

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_query_deleted', 1 );
			exit;
		}
	}

	/**
	 * Delete ticked People/Movie files (based on inputs)
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param Cache_Tools $cache_tools_class object with the methods needed
	 * @throws Exception if nonces are incorrect
	 */
	private function lumiere_cache_delete_ticked_files( string|bool $get_referer, Cache_Tools $cache_tools_class ): void {

		if ( ! isset( $_POST['_nonce_cache_settings'] ) || wp_verify_nonce( $_POST['_nonce_cache_settings'], 'lumiere_nonce_cache_settings' ) === false ) {
			throw new Exception( __( 'Nounce error', 'lumiere-movies' ) );
		}

		if ( isset( $_POST['imdb_cachedeletefor_movies'] ) ) {
			$ids_to_delete = isset( $_POST['imdb_cachedeletefor_movies'] ) ? (array) $_POST['imdb_cachedeletefor_movies'] : [];
			$cache_tools_class->cache_delete_ticked_files( $ids_to_delete, 'movie' );
		} elseif ( isset( $_POST['imdb_cachedeletefor_people'] ) ) {
			$ids_to_delete = isset( $_POST['imdb_cachedeletefor_people'] ) ? (array) $_POST['imdb_cachedeletefor_people'] : [];
			$cache_tools_class->cache_delete_ticked_files( $ids_to_delete, 'people' );
		}

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_ticked_msg', 1 );
			exit;
		}
	}

	/**
	 * Delete specific People/Movie files (based on html links)
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param Cache_Tools $cache_tools_class object with the methods needed
	 */
	private function lumiere_cache_delete_linked_file( string|bool $get_referer, Cache_Tools $cache_tools_class ): void {

		$type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : null;
		$where = isset( $_GET['where'] ) ? sanitize_text_field( $_GET['where'] ) : null;
		$cache_tools_class->cache_delete_specific_file( $type, $where );

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_delete_individual_msg', 1 );
			exit;
		}
	}

	/**
	 * Refresh specific People/Movie files (based on html links)
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @param Cache_Tools $cache_tools_class object with the methods needed
	 */
	private function lumiere_cache_refresh_linked_file( string|bool $get_referer, Cache_Tools $cache_tools_class ): void {

		$type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : null;
		$where = isset( $_GET['where'] ) ? sanitize_text_field( $_GET['where'] ) : null;
		$cache_tools_class->cache_refresh_specific_file( $type, $where );

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'cache_refresh_individual_msg', 1 );
			exit;
		}
	}

	/**
	 * Save Data options
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 * @throws Exception if nonces are incorrect
	 */
	private function lumiere_data_options_save( string|bool $get_referer, ): void {

		if ( ! isset( $_POST['_nonce_data_settings'] ) || wp_verify_nonce( $_POST['_nonce_data_settings'], 'lumiere_nonce_data_settings' ) === false ) {
			throw new Exception( __( 'Nounce error', 'lumiere-movies' ) );
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

			$post_sanitized = is_string( $_POST[ $key_sanitized ] ) ? sanitize_text_field( $_POST[ $key_sanitized ] ) : null;
			// Copy $_POST to $this->imdb_widget_values var
			if ( isset( $post_sanitized ) ) {

				// remove "imdb_" from $key
				$keynoimdb = str_replace( 'imdb_', '', $key_sanitized );

				/**
				 * The following OPTIONS_DATA_MINUS doesn't include 'imdbwidgetorder': array<string> which is dealt with later
				 * @phpstan-var key-of<OPTIONS_DATA_MINUS> $keynoimdb  */
				$this->imdb_widget_values[ $keynoimdb ] = $post_sanitized;
			}
		}

		/**
		 * Special part related to details order
		 * Sanitize and reverse keys and values to insert $_POST['imdbwidgetorderContainer'] into imdb_widget_values['imdbwidgetorder']
		 */
		if ( isset( $_POST['imdbwidgetorderContainer'] ) ) {

			/** @psalm-suppress RedundantCondition -- int always contains numeric -- I know, but need to sanitize. */
			$data_keys_filtered = array_filter( array_keys( $_POST['imdbwidgetorderContainer'] ), 'is_numeric' );

			/** @psalm-suppress RedundantCondition -- Type string [...] is always string -- I know, but need to sanitize. */
			$data_values_filtered = array_filter( $_POST['imdbwidgetorderContainer'], 'is_string' );

			$imdbwidgetorder_sanitized = array_combine( $data_values_filtered, $data_keys_filtered );

			$this->imdb_widget_values['imdbwidgetorder'] = $imdbwidgetorder_sanitized;
		}

		// update options
		update_option( Settings::LUMIERE_WIDGET_OPTIONS, $this->imdb_widget_values );

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_updated', 1 );
			exit;
		}

	}

	/**
	 * Reset Data options
	 * @param false|string $get_referer The URL string from {@see Save_Options::get_referer()}
	 */
	private function lumiere_data_options_reset( string|bool $get_referer, ): void {

		// Delete the options to reset
		delete_option( Settings::LUMIERE_WIDGET_OPTIONS );

		if ( $get_referer !== false && wp_redirect( $get_referer ) ) {
			set_transient( 'notice_lumiere_msg', 'options_reset', 1 );
			exit;
		}
	}
}

