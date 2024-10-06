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
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Plugins\Logger;

/**
 * Plugin for Polylang WordPress plugin
 * This class offers specific functions if Polylang is in use
 * The styles/scripts are supposed to go in construct with add_action(), the methods can be called with Plugins_Start $this->plugins_classes_active
 *
 * @phpstan-import-type AVAILABLE_PLUGIN_CLASSES from \Lumiere\Plugins\Plugins_Detect
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 */
class Polylang {

	/**
	 * Array of plugins currently in use
	 *
	 * @phpstan-var array<int, AVAILABLE_PLUGIN_CLASSES>
	 * @var array<int, string>
	 */
	private array $active_plugins;

	/**
	 * Logger class
	 */
	public Logger $logger;

	/**
	 * Constructor
	 *
	 * @phpstan-param array<int, AVAILABLE_PLUGIN_CLASSES> $active_plugins
	 * @param array<int, string> $active_plugins
	 */
	final public function __construct( array $active_plugins ) {

		// Get the list of active plugins.
		$this->active_plugins = $active_plugins;

		$this->logger = new Logger( 'Polylang' );

	}

	/**
	 * Static start for extra functions not to be run in self::__construct. No $this available!
	 */
	public static function start_init_hook(): void {}

	/**
	 * Add the language in use to taxonomy terms ------- In class movie
	 *
	 * @deprecated 4.0 not utilised anymore, WordPress functions do all what we need
	 *
	 * @param array<string|int, string|int> $term
	 */
	public function lumiere_polylang_add_lang_to_taxo( array $term ): void {

		// Get the language of the term already registred.
		$term_registred_lang = pll_get_term_language( intval( $term['term_id'] ), 'slug' );
		// Get the language of the page.
		$get_lang = filter_var( pll_current_language( 'slug' ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$lang = is_string( $get_lang ) ? $get_lang : '';

		// If the language for this term is not already registered, register it.
		// Check current page language, compare against already registred term.
		if ( $term_registred_lang !== $lang ) {

			//      if ( pll_default_language() == $lang )
			//          pll_save_term_translations( array ( $lang, $term_id) );

			pll_set_term_language( intval( $term['term_id'] ), $lang );
			pll_save_term_translations( [ $lang => intval( $term['term_id'] ) ] );
			$this->logger->log()->debug( '[Lumiere][Polylang] Taxonomy id ' . $term['term_id'] . ' added' );
		}
	}

	/**
	 * Polylang form: Display a form to change the language if Polylang plugin is active
	 * Compatible with AMP plugin. If AMP Plugin is detected, the AMP form will be displayed
	 *
	 * @param string $taxonomy The current taxonomy to check and build the form according to it
	 * @param string $person_name name of the current person in taxonomy
	 * @return string The form is returned
	 *
	 * @since 4.1 The AMP form works!
	 * @since 4.1.2 replaced strval( $lang_object->term_id ) by str_replace( 'pll_', '', strval( $lang_object->slug ) )
	 */
	public function lumiere_get_form_polylang_selection( string $taxonomy, string $person_name ): string {

		/**
		 * Is the current taxonomy, such as "lumiere_actor", registered and activated for translation?
		 * Must be activated in wp-admin/admin.php?page=mlang_settings - Custom post types and Taxonomies - Custom taxonomies
		 */
		if ( ! pll_is_translated_taxonomy( $taxonomy ) ) {
			$this->logger->log()->debug( "[Lumiere][Polylang][taxonomy_$taxonomy] No activated taxonomy found for $person_name with $taxonomy." );
			return '';
		}

		// @phan-suppress-next-line PhanAccessMethodInternal -- Cannot access internal method \get_terms() of namespace \ defined at vendor/php-stubs/wordpress-stubs/wordpress-stubs.php:133181 from namespace \Lumiere\Plugins -> PHAN gets crazy with get_terms()!
		$pll_lang_init = get_terms(
			[
				'taxonomy' => 'term_language',
				'hide_empty' => false,
			]
		);
		$pll_lang = is_array( $pll_lang_init ) ? $pll_lang_init : null;

		if ( ! isset( $pll_lang ) ) {
			$this->logger->log()->debug( "[Lumiere][Polylang][taxonomy_$taxonomy] No Polylang language set." );
			return '';
		}

		/**
		 * Use AMP form if AMP plugin is active
		 * Return the AMP form and exit
		 */
		if ( in_array( 'amp', $this->active_plugins, true ) === true ) {
			return $this->amp_form_polylang_selection( $pll_lang );
		}

		$output = "\n\t\t\t" . '<div align="center">';
		$output .= "\n\t\t\t\t" . '<form method="get" id="lang_form" name="lang_form" action="#lang_form">';
		$output .= "\n\t\t\t\t\t" . '<select name="tag_lang" id="tag_lang">';
		$output .= "\n\t\t\t\t\t\t" . '<option value="">' . esc_html__( 'All', 'lumiere-movies' ) . '</option>';

		// Build an option html tag for every language.
		/** @psalm-var \WP_Term $lang_object */
		foreach ( $pll_lang as $lang_object ) {

			/** @psalm-suppress PossiblyInvalidPropertyFetch -- Cannot fetch property on possible non-object => Always object! */
			$output .= "\n\t\t\t\t\t\t" . '<option value="' . str_replace( 'pll_', '', strval( $lang_object->slug ) ) . '"';

			if (
				// @phpcs:ignore WordPress.Security.NonceVerification -- it is processed in the second line, right below
				isset( $_GET['tag_lang'] ) && $lang_object->term_id === (int) $_GET['tag_lang']
				&& isset( $_GET['_wpnonce_lum_taxo_polylangform'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce_lum_taxo_polylangform'] ), 'lum_taxo_polylangform' ) !== false
			) {
				$output .= ' selected="selected"';
			}

			/** @psalm-suppress PossiblyInvalidPropertyFetch -- Cannot fetch property on possible non-object => Always object! */
			$output .= '>' . ucfirst( $lang_object->name ) . '</option>';

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
	 * @param array<int, \WP_Term|int|string> $pll_lang List of Polylang languages in use
	 * @return string The AMP form is returned
	 *
	 * @see Lumiere\Taxonomy_People_Standard::__construct
	 * @see Lumiere\Taxonomy_People_Standard::amp_form_submit()
	 * @since 4.1.2 replaced strval( $lang_object->term_id ) by str_replace( 'pll_', '', strval( $lang_object->slug ) )
	 */
	private function amp_form_polylang_selection( array $pll_lang ): string {

		$output = "\n\t\t\t" . '<div align="center">';
		$output .= "\n\t\t\t\t" . '<form method="get" id="lang_form" name="lang_form" action="?amp" target="_top">';
		$output .= "\n\t\t\t\t\t" . '<select name="tag_lang" id="tag_lang">';
		$output .= "\n\t\t\t\t\t\t" . '<option value="">' . esc_html__( 'All', 'lumiere-movies' ) . '</option>';

		// Build an option html tag for every language.
		foreach ( $pll_lang as $lang_object ) {

			if ( ! $lang_object instanceof \WP_Term ) { // Only psalm needs that...
				continue;
			}

			$output .= "\n\t\t\t\t\t\t" . '<option value="' . str_replace( 'pll_', '', strval( $lang_object->slug ) ) . '">' . ucfirst( $lang_object->name ) . '</option>';
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
	 * Meant to allow a $_GET insted of a $_POST form submission, thus using ajax, not in use
	 * Not in use
	 *
	 * @see self::amp_form_polylang_selection() Use $_POST
	 * @see \Lumiere\Taxonomy_People_Standard which is supposed to use it
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
			} else {
				$success = false;
				$message = __( 'Could not change the language.', 'lumiere-movies' );
				wp_send_json(
					[
						'msg' => __( 'No data passed', 'lumiere-movies' ),
						'response' => sanitize_text_field( wp_unslash( $_GET['tag_lang'] ) ),
						'back_link' => true,
					]
				);
			}

			/** wp_send_json() already sent a wp_die(), this is not executed
			header( 'AMP-Redirect-To: ' . wp_sanitize_redirect( $_GET['_wp_http_referer'] ?? '' ) );

			wp_die(
				esc_html( $message ),
				'',
				[ 'response' => $success ? 200 : 400 ]
			);
			*/

		}
	}

	/**
	 * Append to home url the polylang url
	 * Allows to rewrite for example the popups to make them compatible with polylang system
	 *
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
	 * Build the list of arguments for a wp_query
	 *
	 * @since 4.1.2
	 * @param string $polylang_lang Lang in two characters in the $_GET which is used in lang
	 * @param string $imdburlstringtaxo
	 * @param string $person_name Name searched
	 * @param string $role
	 * @return array<string, array<int|string, array<string, string>|string>|bool|int|string>
	 */
	public function get_polylang_taxo_query( string $polylang_lang, string $imdburlstringtaxo, string $person_name, string $role ): array {

		return [
			'post_type' => [ 'post', 'page' ],
			'post_status' => 'publish',
			'numberposts' => -1,
			'nopaging' => true,
			'lang' => sanitize_key( $polylang_lang ),
			'tax_query' => [
				// @phan-suppress-next-line PhanPluginMixedKeyNoKey Should not mix array entries of the form [key => value,] with entries of the form [value,]. -- Since WordPress accepts it, it's ok!
				[
					'taxonomy' => sanitize_text_field( $imdburlstringtaxo . $role ),
					'field' => 'name',
					'terms' => sanitize_text_field( $person_name ),
				],
			],
		];
	}
}
