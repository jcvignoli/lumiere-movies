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

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Plugins\Logger;

/**
 * Plugin for Polylang WordPress plugin
 * This class offers specific functions if Polylang is in use
 */
class Polylang {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Constant Polylang slug
	 *
	 */
	const POLYLANGSLUG = 'polylang/polylang.php';

	/**
	 * Logger class
	 */
	public Logger $logger;

	/**
	 * Constructor
	 *
	 * @TODO: pass logger class in construct params when updating to PHP8.1
	 */
	public function __construct() {

		$this->logger = new Logger( 'Polylang' );

		// Construct Global Settings trait.
		$this->settings_open();

	}

	/**
	 * Determine whether Polylang is activated
	 *
	 * @return bool true if Polylang plugin is active
	 */
	public function polylang_is_active(): bool {

		if ( function_exists( 'pll_count_posts' ) && is_plugin_active( self::POLYLANGSLUG ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Add the language in use to taxonomy terms ------- In class movie
	 *
	 * @obsolete since 3.12, not utilised anymore, WordPress functions do all what we need
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
			$this->logger->log()->debug( '[Lumiere][polylangClass] Taxonomy id ' . $term['term_id'] . ' added' );
		}
	}

	/**
	 *  Polylang form: Display a form to change the language if Polylang plugin is active ---- FROM class-taxonomy-people-standard
	 *
	 * @param string $taxonomy The current taxonomy to check and build the form according to it
	 * @param string $person_name name of the current person in taxonomy
	 */
	// @TODO: make it AMP compatible.
	public function lumiere_get_form_polylang_selection( string $taxonomy, string $person_name ): void {

		// Is the current taxonomy, such as "lumiere_actor", registered and activated for translation?
		// Must be activated in wp-admin/admin.php?page=mlang_settings - Custom post types and Taxonomies - Custom taxonomies
		if ( ! pll_is_translated_taxonomy( $taxonomy ) ) {
			$this->logger->log()->debug( "[Lumiere][taxonomy_$taxonomy][polylang plugin] No activated taxonomy found for $person_name with $taxonomy." );
			return;
		}

		// @phan-suppress-next-line PhanAccessMethodInternal -- Cannot access internal method \get_terms() of namespace \ defined at vendor/php-stubs/wordpress-stubs/wordpress-stubs.php:133181 from namespace \Lumiere\Plugins -> PHAN got creazy with get_terms()!
		$pll_lang_init = get_terms(
			[
				'taxonomy' => 'term_language',
				'hide_empty' => false,
			]
		);
		$pll_lang = is_array( $pll_lang_init ) ? $pll_lang_init : null;

		if ( ! isset( $pll_lang ) ) {
			$this->logger->log()->debug( "[Lumiere][taxonomy_$taxonomy] No Polylang language is set." );
			return;
		}

		// Build the form.
		echo "\n\t\t\t" . '<div align="center">';
		// @since 3.9: added URI to form.
		$parts_url = wp_parse_url( home_url() );
		$current_uri = $parts_url !== false && isset( $parts_url['scheme'] ) && isset( $parts_url['host'] )
			? $parts_url['scheme'] . '://' . $parts_url['host'] . add_query_arg( null, null )
			: '';
		echo "\n\t\t\t\t" . '<form method="post" id="lang_form" name="lang_form" action="' . esc_url( $current_uri ) . '#lang_form">';
		echo "\n\t\t\t\t\t" . '<select name="tag_lang" id="tag_lang">';
		echo "\n\t\t\t\t\t\t" . '<option value="">' . esc_html__( 'All', 'lumiere-movies' ) . '</option>';

		// Build an option html tag for every language.
		foreach ( $pll_lang as $lang_object ) {

			/** @psalm-suppress PossiblyInvalidPropertyFetch -- Cannot fetch property on possible non-object => Always object! */
			echo "\n\t\t\t\t\t\t" . '<option value="' . esc_attr( strval( $lang_object->term_id ) ) . '"';

			if (
				// @phpcs:ignore WordPress.Security.NonceVerification -- it is process on the second line!
				isset( $_POST['tag_lang'] ) && intval( $lang_object->term_id ) === intval( $_POST['tag_lang'] )
				&& isset( $_POST['_wpnonce_polylangform'] ) && wp_verify_nonce( $_POST['_wpnonce_polylangform'], 'polylangform' ) !== false
			) {
				echo 'selected="selected"';
			}

			/** @psalm-suppress PossiblyInvalidPropertyFetch -- Cannot fetch property on possible non-object => Always object! */
			echo '>' . esc_html( ucfirst( $lang_object->name ) ) . '</option>';

		}
		echo "\n\t\t\t\t\t" . '</select>&nbsp;&nbsp;&nbsp;';
		echo "\n\t\t\t\t\t";
		// @phpcs:ignore WordPress.Security.EscapeOutput
		wp_nonce_field( 'polylangform', '_wpnonce_polylangform' );
		// WP submit_button() is not compatible with AMP plugin and not available for AMP pages.
		if ( function_exists( 'submit_button' ) ) {
			echo "\n\t\t\t\t\t";
			submit_button( esc_html__( 'Filter language', 'lumiere-movies' ), 'primary', 'submit_lang', false );
		} else { // Below code is used by AMP
			echo "\n\t\t\t\t\t" . '<button type="submit" name="submit_lang" id="submit_lang" class="button-primary" aria-live="assertive" value="' . esc_html__( 'Filter language', 'lumiere-movies' ) . '">' . esc_html__( 'Filter language', 'lumiere-movies' ) . '</button>';
		}
		echo "\n\t\t\t\t" . '</form>';
		echo "\n\t\t\t" . '</div>';

	}

	/**
	 * Polylang add the language currently active in the plugin and add it to the rewrite rules
	 * IE "/en/lumiere/person/?mid=0319843" becomes available
	 * @since 3.11
	 * @return void The rewrite rules have been added
	 */
	public function polylang_add_url_rewrite_rules(): void {

		if ( $this->polylang_is_active() === false ) {
			return;
		}

		$list_lang_rewrite = $this->get_lang_list_rewrite();

		// Add rewrite rules for /lumiere/search|person|movie/ url string.
		// Created only if the rule doesn't exists, so we avoid using flush_rewrite_rules() unecessarily
		$wordpress_rewrite_rules = get_option( 'rewrite_rules' );
		$lumiere_popups_rewrite_rule = '(' . $list_lang_rewrite . ')/?lumiere/([^/]+)/?';

		if ( ! isset( $wordpress_rewrite_rules [ $lumiere_popups_rewrite_rule ] ) ) {
			add_rewrite_rule(
				$lumiere_popups_rewrite_rule,
				'index.php?lang=$matches[1]&popup=$matches[2]',
				'top'
			);
			// @done should not use this function, but didn't find any other solution
			// It is once in class core
			//flush_rewrite_rules();
		}

	}

	/**
	 * Get the list of langs in a format for rewrite rules (separated by a "|" )
	 * @since 3.11
	 */
	private function get_lang_list_rewrite(): string {

		if ( $this->polylang_is_active() === false ) {
			return '';
		}

		$string_rewrite = '';
		$list_lang = pll_languages_list(
			[
				'hide_empty' => 1,
				'fields' => 'slug',
			]
		);
		$total = count( $list_lang );
		for ( $i = 0; $i < $total; $i++ ) {
			// No extra "|" for the first result
			if ( $i === 0 ) {
				$string_rewrite .= $list_lang[ $i ];
				continue;
			}
			$string_rewrite .= '|' . $list_lang[ $i ];
		}
		return $string_rewrite;
	}

	/**
	 * Append to home url the polylang url
	 * Allows to rewrite for example the popups to make them compatible with polylang system
	 *
	 * @since 3.11
	 * @param string $content The URL that contains home_url() in it
	 * @param null|string $extra_url An extra portion of url if needed
	 * @return string
	 */
	public function rewrite_string_with_polylang_url( string $content, string $extra_url = null ): string {

		$home_slashed = str_replace( '/', '\/', home_url() );
		$pll_home_slashed = str_replace( '/', '\/', trim( pll_home_url(), '/' ) );
		$extra_url_piece = isset( $extra_url ) ? str_replace( '/', '\/', $extra_url ) : '';
		$final_url = str_replace( $home_slashed . $extra_url_piece, $pll_home_slashed . $extra_url_piece, $content );

		return strlen( $final_url ) > 0 ? $final_url : $content;
	}

	/**
	 * Copy metas from one post in original language to another post in other language
	 * Polylang version
	 * @TODO: not yet implemented, not sure if needed, maybe not, need further tests
	 * to call it: add_filter('pll_copy_post_metas', 'lumiere_copy_post_metas_polylang', 10, 2)
	 */
	/*
	public function lumiere_copy_post_metas_polylang( $metas, $sync) {

		if(!is_admin()) return false;
		if($sync) return $metas;
		global $current_screen;

		if($current_screen-post_type == 'wine'){ // substitue 'wine' with post type
			$keys = array_key(get_fields($_GET['imdbltid']));
			return array_merge($metas, $keys);
		}

		return $metas;

	}
	*/

}
