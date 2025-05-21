<?php declare( strict_types = 1 );
/**
 * Class Polylang plugin
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Plugins\Auto;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Plugins\Logger;
use Lumiere\Config\Get_Options;
use Lumiere\Tools\Validate_Get;

/**
 * Plugin for Polylang WordPress plugin
 * This class offers specific functions if Polylang is in use
 * The styles/scripts are supposed to go in construct with add_action()
 * Can method get_active_plugins() to get an extra property $active_plugins, as available in {@link Plugins_Start::activate_plugins()}
 * Executed in Frontend only (except for {@see Polylang::add_polylang_taxonomy()} that can be called from ie admin)
 *
 * @since 3.7.1
 * @since 4.3 Using add_filter() and more OOP, removing custom Polylang translation for taxonomy terms (same term will have one entry only, no translation anymore)
 *
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 * @link Polylang reference hooks https://polylang.pro/doc/filter-reference/
 *
 * @phpstan-import-type PLUGINS_ALL_KEYS from \Lumiere\Plugins\Plugins_Detect
 * @phpstan-import-type PLUGINS_ALL_CLASSES from \Lumiere\Plugins\Plugins_Detect
 */
final class Polylang {

	/**
	 * Array of plugins currently in use
	 *
	 * @var array<string, class-string>
	 * @phpstan-var array{PLUGINS_ALL_KEYS?: class-string<PLUGINS_ALL_CLASSES>}
	 */
	private array $active_plugins;

	/**
	 * Constructor
	 */
	final public function __construct(
		private ?Logger $logger = new Logger( 'Polylang' ), // Can be null for certain functions that execute early.
	) {
		// Return URLs with Polylang lang extension in domain name.
		add_filter( 'lum_polylang_rewrite_url_with_lang', [ $this, 'rewrite_url_with_lang' ], 10, 1 );

		// Return an array for a SQL Query for dropdown form in taxonomy people theme
		add_filter( 'lum_polylang_taxo_query', [ $this, 'get_polylang_query_form' ], 10, 2 );

		// Remove the custom taxonomy.
		add_filter( 'pll_get_taxonomies', [ $this, 'add_tax_to_pll' ], 10, 2 );

		// Return a form for selecting the lang in Taxonomy_People_Standard.
		add_filter( 'lum_polylang_form_taxonomy_people', [ $this, 'form_taxonomy_people_lang' ] );
	}

	/**
	 * Get for extra params not to be run in self::__construct. Automatically executed from Plugins_Start
	 *
	 * @param array<string, class-string> $active_plugins
	 * @phpstan-param array{PLUGINS_ALL_KEYS: class-string<PLUGINS_ALL_CLASSES>} $active_plugins
	 */
	public function get_active_plugins( array $active_plugins ): void {
		// Get the list of active plugins.
		$this->active_plugins = $active_plugins;
	}

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

		$polylang_class = new self();

		// It may run several times, limit it to once.
		if ( did_filter( 'pll_get_taxonomies' ) === 0 ) {
			add_filter( 'pll_get_taxonomies', [ $polylang_class, 'add_tax_to_pll' ], 10, 2 );
		}

		add_filter( 'lum_polylang_update_taxonomy_terms', [ $polylang_class, 'update_taxonomy_terms' ], 10, 4 );
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
	public static function add_tax_to_pll( array $taxonomies, bool $hide ): array {
		$lum_activated_taxos = Get_Options::get_taxonomy_activated();

		foreach ( $lum_activated_taxos as $taxo ) {
			$taxonomies[ $taxo ] = $taxo; // Activate custom polylang taxonomy.
			// unset( $taxonomies[ $taxo ] ); // Desactivate custom polylang taxonomy.
		}

		return $taxonomies;
	}

	/**
	 * Polylang form: Display a form to change the language if Polylang plugin is active
	 * Compatible with AMP plugin. If AMP Plugin is detected, the relevant AMP form will be displayed instead of the classic one
	 *
	 * @return string The form is returned
	 *
	 * @since 4.1 The AMP form works!
	 * @since 4.1.2 replaced strval( $lang_object->term_id ) by str_replace( 'pll_', '', strval( $lang_object->slug ) )
	 * @since 4.3 Rewritten using proper loops with languages. What the hell was this... thing?
	 */
	public function form_taxonomy_people_lang(): string {

		// Language selected: $_GET['tag_lang'] Retrieve it if nonce is valid. Null otherwise.
		$selected_lang =
			Validate_Get::sanitize_url( 'tag_lang' ) !== null
			&& isset( $_GET['_wpnonce_lum_taxo_polylangform'] )
			&& ( wp_verify_nonce( sanitize_key( $_GET['_wpnonce_lum_taxo_polylangform'] ), 'lum_taxo_polylangform' ) > 0 )
			? Validate_Get::sanitize_url( 'tag_lang' )
			: null;

		// Combine in a single array two different Polylang fields, ie [ 'en' => 'English' ].
		$all_lang_array = array_combine( pll_languages_list( [ 'fields' => 'slug' ] ), pll_languages_list( [ 'fields' => 'name' ] ) );

		/**
		 * Use AMP form if AMP plugin is active
		 */
		if ( in_array( 'amp', $this->active_plugins, true ) === true ) {
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
			Validate_Get::sanitize_url( 'submit_lang' ) !== null && Validate_Get::sanitize_url( 'tag_lang' ) !== null
			&& isset( $_POST['_wpnonce_lum_taxo_polylangform'] )
			&& wp_verify_nonce( sanitize_key( $_POST['_wpnonce_lum_taxo_polylangform'] ), 'lum_taxo_polylangform' ) > 0
		) {

			/** @psalm-suppress PossiblyNullArgument (It can't! checked above!) */
			if ( strlen( Validate_Get::sanitize_url( 'tag_lang' ) ) > 0 ) { /** @phan-suppress-current-line PhanTypeMismatchArgumentNullableInternal (already checked if null above!) */
				$success = true;
				$message = __( 'Language successfully changed.', 'lumiere-movies' );
				wp_send_json(
					[
						'success' => $success,
						'message' => $message,
					]
				);
			}

			wp_send_json(
				[
					'message' => __( 'No data passed', 'lumiere-movies' ),
					'response' => Validate_Get::sanitize_url( 'tag_lang' ),
					'back_link' => true,
				]
			);

			/** wp_send_json() sent a wp_die(), this is not executed
			header( 'AMP-Redirect-To: ' . wp_sanitize_redirect( $_GET['_wp_http_referer'] ?? '' ) );
			$success = false;
			$message = __( 'Could not change the language.', 'lumiere-movies' );

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
	 * Return a WP Query that includes Polylang language and 'terms' fields for the current lang
	 * The lang is built according to the $_GET in the form
	 * If no lang is used, use all langs active on the website
	 *
	 * @param array<string, array<int|string, array<string, string>|string>|bool|int|string> $query Original Query
	 * @param array{person_name: string, taxonomy: string} $args Taxonomy ie 'lumiere-director'
	 * @return array<string, array<int|string, array<string, array<int|string, string>|string>|string>|int|string|true>|array<string, array<int|string, array<string, string>|string>|bool|int|string>
	 *
	 * @since 4.3
	 * @since 4.4.1 Fixed the nonce check, using the term_id to find all terms related to current langage, use $this->get_terms_translated()
	 * @see \Lumiere\Taxonomy_People_Standard Uses the query to display a dropdown field
	 */
	public function get_polylang_query_form( array $query, array $args ): array {

		// 1. $_GET['tag_lang'] Retrieve it if nonce is valid. Null otherwise.
		$tag_lang = Validate_Get::sanitize_url( 'tag_lang' ) ?? null;

		if (
			( isset( $tag_lang ) && ! isset( $_GET['_wpnonce_lum_taxo_polylangform'] ) )
			|| ( isset( $tag_lang ) && ! ( wp_verify_nonce( sanitize_key( $_GET['_wpnonce_lum_taxo_polylangform'] ), 'lum_taxo_polylangform' ) > 0 ) )
		) {
			return $query;
		}

		// 2. Lang: if there is a lang and it is neither '' nor 'all', keep it, otherwise make a string of all languages available on the site.
		$lang = isset( $tag_lang ) && strlen( $tag_lang ) > 0 && $tag_lang !== 'all' ? $tag_lang : join( ',', pll_languages_list() );

		// 3. Final query using term_ids, lang and taxonomy.
		return [
			'post_type' => [ 'post', 'page' ],
			'numberposts' => -1,
			'nopaging' => true,
			'lang' => $lang,
			'fields' => 'ids',
			'tax_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => esc_html( $args['taxonomy'] ),
					'field' => 'term_id',
					'terms' => $this->get_terms_translated( $args ), // 4. Find all term ids related to this term and create an array
				],
			],
		];
	}

	/**
	 * Get Polylang translation for a given term and taxonomy
	 *
	 * @since 4.4.1
	 *
	 * @param array{person_name: string, taxonomy: string} $args Taxonomy ie 'lumiere-director'
	 * @return string[]
	 */
	private function get_terms_translated( array $args ): array {
		$search_term_id = get_term_by( 'name', $args['person_name'], $args['taxonomy'] ); // find the term object related to current person.
		$term_id = $search_term_id instanceof \WP_Term ? $search_term_id->term_id : 0; // Make sure it exists.
		$all_terms_id = pll_get_term_translations( $term_id ); // Find all translated term_id
		$terms_lang = [];
		foreach ( pll_languages_list() as $pll_lang ) {
			if ( isset( $all_terms_id[ $pll_lang ] ) ) {
				$terms_lang[] = esc_html( strval( $all_terms_id[ $pll_lang ] ) ); // Create an array of term_id such as [ '1', '2', 'etc' ]
			}
		}
		return $terms_lang;
	}

	/**
	 * Rewrite the provided link in Polylang format
	 *
	 * @param string $url The URL to edit
	 * @return string The URL with Polylang lang
	 * @since 3.11
	 * @since 4.3 Moved from Trait frontend Main to this Polylang class
	 * @since 4.6.2 new condition to avoid putting twice the "/lang"
	 * @see Popups (and @link \Lumiere\Frontend\Module\Parent_Module) classes, that are not taken into charge by Polylang
	 */
	public function rewrite_url_with_lang( string $url ): string {
		// Do not replaced twice (do not add the "/lang" twice if multiple calls are made)
		/** @psalm-suppress PossiblyInvalidArgument (Argument 1 of pll_home_url expects string, but possibly different type PLL_Language|false|string provided) */
		if ( pll_current_language() !== pll_default_language() && is_string( pll_current_language() ) && ! str_contains( $url, pll_home_url( pll_current_language() ) ) ) {
			/** @psalm-suppress PossiblyInvalidArgument (Argument 1 of pll_home_url expects string, but possibly different type PLL_Language|false|string provided) */
			$replace_url = str_replace( home_url(), trim( pll_home_url( pll_current_language() ), '/' ), $url );
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
	 * @param int $page_id Post Id
	 * @param string $full_new_taxonomy the new taxonomy
	 * @param string $full_old_taxonomy the taxonomy to be replaced
	 * @param string $title Post title
	 * @return bool True if terms were updated
	 */
	public function update_taxonomy_terms( int $page_id, string $full_new_taxonomy, string $full_old_taxonomy, string $title ): bool {

		// Method executed in init so logging prevents throws a "headers already sent" -> trick to prevent logger to be run
		if ( did_action( 'wp_loaded' ) !== 1 ) {
			$this->logger = null;
		}

		$this->logger?->log?->info( '[Taxonomy][Update terms][Polylang] Polylang taxonomy version started' );
		$this->logger?->log?->debug( '[Taxonomy][Update terms][Polylang][Post] Title "' . esc_html( $title ) . '" being processed' );

		$get_lang = pll_get_post_language( $page_id );
		$lang = $get_lang !== false ? $get_lang : '';
		$terms_post = get_the_terms( $page_id, $full_old_taxonomy );

		if ( $terms_post === false || $terms_post instanceof \WP_Error ) {
			$this->logger?->log?->error( '[Taxonomy][Update terms][Polylang][Post] No taxonomy terms found, although there should be there due to the SQL Query.' );
			return false;
		}

		$this->logger?->log?->debug( '[Taxonomy][Update terms][Polylang][Post] Title "' . esc_html( $title ) . '" in lang ' . esc_html( $lang ) . ' being processed' );

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

				$this->logger?->log?->notice( '[Taxonomy][Update terms][Polylang][Missing term] Term *' . esc_html( $term_slug ) . '* was missing, so created in taxonomy ' . esc_html( $full_new_taxonomy ) );

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
				$this->logger?->log?->info( '[Taxonomy][Update terms][Polylang][Added] Term *' . esc_html( $term_slug ) . '* to post *' . esc_html( $title ) . '* in lang ' . esc_html( $lang ) );
			}
			$this->logger?->log?->debug( '[Taxonomy][Update terms][Polylang][Processed] Term *' . esc_html( $term_slug ) . '* processed' );
		}
		$this->logger?->log?->debug( '[Taxonomy][Update terms][Polylang][Post] Title *' . esc_html( $title ) . '* processed' );
		return true;
	}
}
