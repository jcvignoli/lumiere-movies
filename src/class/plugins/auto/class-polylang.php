<?php declare( strict_types = 1 );
/**
 * Class Polylang plugin
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7.1
 * @package lumiere-movies
 */

namespace Lumiere\Plugins\Auto;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Plugins\Logger;
use Lumiere\Tools\Get_Options;

/**
 * Plugin for Polylang WordPress plugin
 * This class offers specific functions if Polylang is in use
 * The styles/scripts are supposed to go in construct with add_action(), the methods can be called with Plugins_Start $this->active_plugins
 * Executed in Frontend only (except for {@see Polylang::add_polylang_taxonomy()} that can be called from ie admin)
 *
 * @since 4.3 Using add_filter() and more OOP, removing custom Polylang translation for taxonomy terms (same term will have one entry only, no translation anymore)
 *
 * @phpstan-import-type AVAILABLE_PLUGIN_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type AVAILABLE_PLUGIN_CLASSES_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 * @link Polylang reference hooks https://polylang.pro/doc/filter-reference/
 */
class Polylang {

	/**
	 * Traits
	 */
	use Get_Options;

	/**
	 * Array of plugins currently in use
	 *
	 * @phpstan-var array<AVAILABLE_PLUGIN_CLASSES_KEYS, AVAILABLE_PLUGIN_CLASSES>
	 * @var array<string, string>
	 */
	private array $active_plugins;

	/**
	 * Logger class
	 */
	public ?Logger $logger = null;

	/**
	 * Constructor
	 *
	 * @phpstan-param array<AVAILABLE_PLUGIN_CLASSES_KEYS, AVAILABLE_PLUGIN_CLASSES> $active_plugins
	 * @param array<string, string> $active_plugins
	 */
	final public function __construct( array $active_plugins ) {

		// Get the list of active plugins.
		$this->active_plugins = $active_plugins;

		$this->logger = new Logger( 'Polylang' );

		// Return URLs with Polylang lang extension in domain name.
		add_filter( 'lum_polylang_rewrite_url_with_lang', [ $this, 'rewrite_url_with_lang' ], 10, 1 );

		// Return an array for a SQL Query for dropdown form in taxonomy people theme
		add_filter( 'lum_polylang_taxo_query', [ $this, 'get_polylang_query_form' ], 10, 2 );

		// Remove the custom taxonomy.
		add_filter( 'pll_get_taxonomies', [ $this, 'add_tax_to_pll' ], 10, 2 );

		// Return a form for selecting the lang in Taxonomy_People_Standard.
		add_filter( 'lum_polylang_form_taxonomy_people', [ $this, 'form_taxonomy_people_lang' ], 10, 1 );
	}

	/**
	 * Static start for extra functions not to be run in self::__construct. No $this available!
	 */
	public static function start_init_hook(): void {}

	/**
	 * Static start for Admin Polylang specific filters and actions
	 * 1. Only executed in Admin
	 * 2. Only executed if Polylang is active
	 *
	 * @see Lumiere\Admin Is called in Init 10
	 */
	public static function add_polylang_in_admin(): void {

		if ( function_exists( 'pll_current_language' ) === false ) {
			return;
		}

		$polylang_class = new self( [] );

		// It may run several times, limit it to once.
		if ( did_filter( 'pll_get_taxonomies' ) === 0 ) {
			add_filter( 'pll_get_taxonomies', [ $polylang_class, 'add_tax_to_pll' ], 10, 2 );
		}

		add_filter( 'lum_polylang_update_taxonomy_terms', [ $polylang_class, 'update_taxonomy_terms' ], 10, 5 );
	}

	/**
	 * Block the option to unselect Lumière custom taxonomy translation
	 * This is a secific Polylang filter to be called such as add_filter( 'pll_get_taxonomies', 'this_function' )
	 *
	 * @param array<string, string> $taxonomies
	 * @param bool $hide Option to hide (or not) Do not get exectly how it works
	 * @return array<string, string>
	 *
	 * @link WordPress admin /admin.php?page=mlang_settings
	 * @since 4.3 Deactivated this custom option that creates multiples entries for the same term.
	 */
	public static function add_tax_to_pll( array $taxonomies, bool $hide ) {
		$lum_activated_taxos = ( new class() { use Get_Options;
		} )->get_taxonomy_activated(); // Method in trait Get_Options.

		foreach ( $lum_activated_taxos as $taxo ) {
			$taxonomies[ $taxo ] = $taxo; // Activate custom polylang taxonomy.
			// unset( $taxonomies[ $taxo ] ); // Desactivate custom polylang taxonomy.
		}

		return $taxonomies;
	}

	/**
	 * Polylang form: Display a form to change the language if Polylang plugin is active
	 * Compatible with AMP plugin. If AMP Plugin is detected, the AMP form will be displayed
	 *
	 * @param string $taxonomy The current taxonomy to check and build the form according to it
	 * @return string The form is returned
	 *
	 * @since 4.1 The AMP form works!
	 * @since 4.1.2 replaced strval( $lang_object->term_id ) by str_replace( 'pll_', '', strval( $lang_object->slug ) )
	 * @since 4.3 Rewritten using proper loops with languages. What the hell was this... thing?
	 */
	public function form_taxonomy_people_lang( string $taxonomy ): string {

		// Language selected: $_GET['tag_lang'] Retrieve it if nonce is valid. Null otherwise.
		$selected_lang =
			isset( $_GET['tag_lang'] ) && is_string( $_GET['tag_lang'] )
			&& isset( $_GET['_wpnonce_lum_taxo_polylangform'] )
			&& ( wp_verify_nonce( sanitize_key( $_GET['_wpnonce_lum_taxo_polylangform'] ), 'lum_taxo_polylangform' ) > 0 )
			? sanitize_key( wp_unslash( $_GET['tag_lang'] ) )
			: null;

		// Combine in a single array two different Polylang fields, ie [ 'en' => 'English' ].
		$all_lang_array = array_combine( pll_languages_list( [ 'fields' => 'slug' ] ), pll_languages_list( [ 'fields' => 'name' ] ) );

		/**
		 * Use AMP form if AMP plugin is active
		 */
		if ( in_array( 'amp', array_keys( $this->active_plugins ), true ) === true ) {
			return $this->amp_form_polylang_selection( $all_lang_array, $selected_lang );
		}

		$output = "\n\t\t\t" . '<div align="center">';
		$output .= "\n\t\t\t\t" . '<form method="get" id="lang_form" name="lang_form" action="#lang_form">';
		$output .= "\n\t\t\t\t\t" . '<select name="tag_lang" id="tag_lang">';
		$output .= "\n\t\t\t\t\t\t" . '<option value="">' . esc_html__( 'All', 'lumiere-movies' ) . '</option>';

		// Build an option html tag for every language.
		foreach ( $all_lang_array as $slug => $lang ) {
			$output .= "\n\t\t\t\t\t\t" . '<option value="' . esc_attr( $slug ) . '"';
			if ( $selected_lang === $slug ) {
				$output .= ' selected="selected"';
			}
			$output .= '>' . ucfirst( esc_html( $lang ) ) . '</option>';
		}
		$output .= "\n\t\t\t\t\t" . '</select>&nbsp;&nbsp;&nbsp;';
		$output .= "\n\t\t\t\t\t";
		$output .= wp_nonce_field( 'lum_taxo_polylangform', '_wpnonce_lum_taxo_polylangform', true, false );
		$output .= "\n\t\t\t\t\t";
		$output .= '<input type="submit" name="submit_lang" id="submit_lang" class="button button-primary" value="' . __( 'Filter language', 'lumiere-movies' ) . '"  />';
		$output .= "\n\t\t\t\t" . '</form>';
		$output .= "\n\t\t\t" . '</div>';

		return $output;
	}

	/**
	 * Special form for compatiblity with AMP
	 * @param array<string, string> $all_lang_array List of Polylang languages in use
	 * @param string|null $selected_lang Optional: Slug of the selected language
	 * @return string The AMP form is returned
	 *
	 * @see Lumiere\Taxonomy_People_Standard::amp_form_submit()
	 * @since 4.1.2 replaced strval( $lang_object->term_id ) by str_replace( 'pll_', '', strval( $lang_object->slug ) )
	 * @since 4.3 Rewritten using proper loops with languages. What the hell was this... thing?
	 */
	private function amp_form_polylang_selection( array $all_lang_array, ?string $selected_lang = null ): string {

		$output = "\n\t\t\t" . '<div align="center">';
		$output .= "\n\t\t\t\t" . '<form method="get" id="lang_form" name="lang_form" action="?amp" target="_top">';
		$output .= "\n\t\t\t\t\t" . '<select name="tag_lang" id="tag_lang">';
		$output .= "\n\t\t\t\t\t\t" . '<option value="">' . esc_html__( 'All', 'lumiere-movies' ) . '</option>';

		// Build an option html tag for every language.
		foreach ( $all_lang_array as $slug => $lang ) {
			$output .= "\n\t\t\t\t\t\t" . '<option value="' . esc_attr( $slug ) . '"';
			if ( $selected_lang === $slug ) {
				$output .= ' selected="selected"';
			}
			$output .= '>' . ucfirst( esc_html( $lang ) ) . '</option>';
		}

		$output .= "\n\t\t\t\t\t" . '</select>';
		$output .= "\n\t\t\t\t\t" . wp_nonce_field( 'lum_taxo_polylangform', '_wpnonce_lum_taxo_polylangform', true, false );
		$output .= "\n\t\t\t\t\t" . '<button type="submit" name="submit_lang" id="submit_lang" class="button-primary" aria-live="assertive" value="' . esc_html__( 'Filter language', 'lumiere-movies' ) . '">&nbsp;&nbsp;&nbsp;' . __( 'Filter language', 'lumiere-movies' ) . '</button>';
		$output .= "\n\t\t\t\t" . '</form>';
		$output .= "\n\t\t\t" . '</div>';
		return $output;
	}

	/**
	 * Use specific headers if it is an AMP submission
	 * Meant to allow a $_GET insted of a $_POST form submission, thus using Ajax, not in use
	 * Not in use ( amp_form should be on post)
	 *
	 * @see self::amp_form_polylang_selection() Switch 'form method="get"' to 'post' to get it active
	 * @see \Lumiere\Taxonomy_People_Standard Implements it
	 */
	public function amp_form_submit(): void {

		if (
			isset( $_GET['submit_lang'], $_GET['tag_lang'] )
			&& isset( $_POST['_wpnonce_lum_taxo_polylangform'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_wpnonce_lum_taxo_polylangform'] ), 'lum_taxo_polylangform' ) > 0
		) {

			if ( strlen( sanitize_key( $_GET['tag_lang'] ) ) > 0 ) {
				$success = true;
				$message = __( 'Language successfully changed.', 'lumiere-movies' );
				wp_send_json( [ 'success' => true ] );
			}

			$success = false;
			$message = __( 'Could not change the language.', 'lumiere-movies' );
			wp_send_json(
				[
					'msg' => __( 'No data passed', 'lumiere-movies' ),
					'response' => esc_url_raw( wp_unslash( $_GET['tag_lang'] ) ),
					'back_link' => true,
				]
			);

			/** wp_send_json() sent a wp_die(), this is not executed
			header( 'AMP-Redirect-To: ' . wp_sanitize_redirect( $_GET['_wp_http_referer'] ?? '' ) );

			wp_die(
				esc_html( $message ),
				'',
				[ 'response' => $success ? 200 : 400 ]
			);*/
		}
	}

	/**
	 * Append to home url the polylang url
	 * Allows to rewrite for example the popups to make them compatible with polylang system
	 * @since 3.11
	 * @param string $content The URL that contains home_url() in it
	 * @param null|string $extra_url An extra portion of url if needed
	 * @return string
	 * @deprecated 4.1 no use of this method
	 */
	public function rewrite_string_with_polylang_url( string $content, ?string $extra_url = null ): string {
		$home_slashed = str_replace( '/', '\/', home_url() );
		$pll_home_slashed = str_replace( '/', '\/', trim( pll_home_url(), '/' ) );
		$extra_url_piece = isset( $extra_url ) ? str_replace( '/', '\/', $extra_url ) : '';
		$final_url = str_replace( $home_slashed . $extra_url_piece, $pll_home_slashed . $extra_url_piece, $content );

		return strlen( $final_url ) > 0 ? $final_url : $content;
	}

	/**
	 * Return a WP Query that includes Polylang language and 'terms' fields
	 * The term ('name') and the taxonomy are passed in $args - the orginal query too
	 * The lang is built according to the $_GET in the form
	 *
	 * @param array<string, array<int|string, array<string, string>|string>|bool|int|string> $query Original Query
	 * @param array{person_name: string, taxonomy: string} $args Taxonomy ie 'lumiere-director'
	 * @return array<string, array<int|string, array<string, array<int|string, string>|string>|string>|int|string|true>|array<string, array<int|string, array<string, string>|string>|bool|int|string>
	 *
	 * @since 4.3
	 * @see \Lumiere\Taxonomy_People_Standard Uses the query to display a dropdown field
	 */
	public function get_polylang_query_form( array $query, array $args ): array {

		if ( ! isset( $_GET['_wpnonce_lum_taxo_polylangform'] ) || ! ( wp_verify_nonce( sanitize_key( $_GET['_wpnonce_lum_taxo_polylangform'] ), 'lum_taxo_polylangform' ) > 0 ) ) {
			return $query;
		}

		// 1. $_GET['tag_lang'] Retrieve it if nonce is valid. Null otherwise.
		$tag_lang = isset( $_GET['tag_lang'] ) && is_string( $_GET['tag_lang'] ) ? sanitize_key( wp_unslash( $_GET['tag_lang'] ) ) : null;
		// 2. Lang: if there is a lang and it is neither '' nor 'all', keep it, otherwise make a string of all languages available on the site.
		$lang = isset( $tag_lang ) && strlen( $tag_lang ) > 0 && $tag_lang !== 'all' ? $tag_lang : join( ',', pll_languages_list() );

		return [
			'post_type' => [ 'post', 'page' ],
			'numberposts' => -1,
			'nopaging' => true,
			'lang' => $lang,
			'fields' => 'ids',
			'tax_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => esc_html( $args['taxonomy'] ),
					'field' => 'name',
					'terms' => esc_html( $args['person_name'] ),
				],
			],
		];
	}

	/**
	 * Rewrite the provided link in Polylang format
	 *
	 * @param string $url The URL to edit
	 * @return string The URL with Polylang lang
	 * @since 3.11
	 * @since 4.3 Moved from Trait frontend Main to this Polylang class
	 * @see Popups classes, that are not taken into charge by Polylang
	 */
	public function rewrite_url_with_lang( string $url ): string {

		// Do not replaced twice (do not add the "/lang" twice if multiple calls are made)
		if ( ! str_contains( $url, trim( pll_home_url(), '/' ) )  ) {
			$replace_url = str_replace( home_url(), trim( pll_home_url(), '/' ), $url );
			return trim( $replace_url, '/' );
		}
		return $url;
	}

	/**
	 * Update all taxonomy terms, inserting them with the approriate language.
	 * Do a loop of all terms found related to the current post but former taxonomy, then insert the terms in the new taxonomy
	 * Using 'slug' instead of 'name' as the former is different according to the language. If using 'name', all terms get merged.
	 * Using wp_set_object_terms() which is less ressource demanding than wp_set_post_terms()
	 *
	 * @since 4.3
	 * @info Using "instanceof \WP_Error" instead of "is_wp_error()" because PHPStan doesn't understand the latter
	 *
	 * @param null|Logger $logger Logger
	 * @param int $page_id Post Id
	 * @param string $full_new_taxonomy the new taxonomy
	 * @param string $full_old_taxonomy the taxonomy to be replaced
	 * @param string $title Post title
	 * @return bool True if terms were updated
	 */
	public function update_taxonomy_terms( ?Logger $logger, int $page_id, string $full_new_taxonomy, string $full_old_taxonomy, string $title ): bool {

		$logger?->log()->info( '[Lumiere][Taxonomy][Update terms][Polylang] Polylang taxonomy version started' );
		$logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Polylang][Post] Title "' . esc_html( $title ) . '" being processed' );

		$get_lang = pll_get_post_language( $page_id );
		$lang = $get_lang !== false ? $get_lang : '';
		$terms_post = get_the_terms( $page_id, $full_old_taxonomy );

		if ( $terms_post === false || $terms_post instanceof \WP_Error ) {
			$logger?->log()->error( '[Lumiere][Taxonomy][Update terms][Polylang][Post] No taxonomy terms found, although there should be there due to the SQL Query.' );
			return false;
		}

		$logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Polylang][Post] Title "' . esc_html( $title ) . '" in lang ' . esc_html( $lang ) . ' being processed' );

		foreach ( $terms_post as $key => $term_post ) {

			// Add an extension if the current term is not the term by default
			$ext = pll_default_language() === $lang ? '' : '-' . $lang;

			// Instert the term, if it doesn't exist, using the slug. Add the extension to the slug if relevant.
			$term_inserted = wp_insert_term( $term_post->name, $full_new_taxonomy, [ 'slug' => $term_post->name . $ext ] );

			if ( ! $term_inserted instanceof \WP_Error ) {

				// Set the term's language.
				pll_set_term_language( $term_inserted['term_id'], $lang );
				// Since it's a new term, the term inserted overrides the loop's slug
				$term_post = get_term( $term_inserted['term_id'] );
				$term_slug = isset( $term_post ) && ! $term_post instanceof \WP_Error ? $term_post->slug : '';

				$logger?->log()->notice( '[Lumiere][Taxonomy][Update terms][Polylang][Missing term] Term *' . esc_html( $term_slug ) . '* was missing, so created in taxonomy ' . esc_html( $full_new_taxonomy ) );

			} else {
				// Set the term's language.
				pll_set_term_language( $term_post->term_id, $lang );
				// User loop's slug.
				$term_slug = $term_post->slug;
			}

			$adding_terms = strlen( $term_slug ) > 0 ? wp_set_object_terms(
				$page_id,
				$term_slug,
				$full_new_taxonomy,
				true /* True: Append the term, False: Replace all previous terms by current one */
			) : null;

			// Insert sucess.
			if ( isset( $adding_terms ) && ! $adding_terms instanceof \WP_Error && count( $adding_terms ) > 0 ) {
				$logger?->log()->info( '[Lumiere][Taxonomy][Update terms][Polylang][Added] Term *' . esc_html( $term_slug ) . '* to post *' . esc_html( $title ) . '* in lang ' . esc_html( $lang ) );
			}
			$logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Polylang][Processed] Term *' . esc_html( $term_slug ) . '* processed' );
		}
		$logger?->log()->debug( '[Lumiere][Taxonomy][Update terms][Polylang][Post] Title *' . esc_html( $title ) . '* processed' );
		return true;
	}
}
