<?php

namespace {

	/**
	 * @param string $tax Taxonomy name.
	 * @return bool
	 */
	function pll_is_translated_taxonomy( $tax ) {}

	/** ---------------------------------- Polylang */

	/**
	 * @param int    $id   Term id.
	 * @param string $lang Language code.
	 * @return void
	 */
	function pll_set_term_language( $id, $lang ) {
		PLL()->model->term->set_language( $id, $lang );
	}

	/**
	 * @param string $field Optional, the language field to return ( @see PLL_Language ), defaults to 'slug'. Pass OBJECT constant to get the language object.
	 * @return string|PLL_Language|false The requested field for the current language.
	 */
	function pll_current_language( $field = 'slug' ) {}

	/**
	 * @param int    $term_id Term id.
	 * @param string $field   Optional, the language field to return ( @see PLL_Language ), defaults to 'slug'.
	 * @return string|false The requested field for the term language, false if no language is associated to that term.
	 */
	function pll_get_term_language( $term_id, $field = 'slug' ) {}

	/**
	 * @param array $args {
	 *   Optional array of arguments.
	 *
	 *   @type bool   $hide_empty Hides languages with no posts if set to true ( defaults to false ).
	 *   @type string $fields     Return only that field if set ( @see PLL_Language for a list of fields ), defaults to 'slug'.
	 * }
	 * @return string[]
	 */
	function pll_languages_list( $args = [] ) {}

	/**
	 * @param string $field Optional, the language field to return (@see PLL_Language), defaults to `'slug'`.
	 *                      Pass `\OBJECT` constant to get the language object. A composite value can be used for language
	 *                      term property values, in the form of `{language_taxonomy_name}:{property_name}` (see
	 *                      {@see PLL_Language::get_tax_prop()} for the possible values). Ex: `term_language:term_taxonomy_id`.
	 * @return string|int|bool|string[]|PLL_Language The requested field or object for the default language, `false` if the field isn't set or if default language doesn't exist yet.
	 *
	 * @phpstan-return (
	 *     $field is \OBJECT ? PLL_Language : (
	 *         $field is 'slug' ? non-empty-string : string|int|bool|list<non-empty-string>
	 *     )
	 * )|false
	 */
	function pll_default_language( $field = 'slug' ) {}

	/**
	 * @param string $lang Optional language code, defaults to the current language.
	 * @return string
	 */
	function pll_home_url( $lang = '' ) {}

	/**
	 * @param int[] $arr An associative array of translations with language code as key and term ID as value.
	 * @return int[] An associative array with language codes as key and term IDs as values.
	 *
	 * @phpstan-return array<non-empty-string, positive-int>
	 */
	function pll_save_term_translations( $arr ) {}

	/**
	 * @param int                 $term_id Term ID.
	 * @param PLL_Language|string $lang    Optional language (object or slug), defaults to the current language.
	 * @return int The translation term ID if exists. 0 if not translated, the term has no language or if the language doesn't exist.
	 *
	 * @phpstan-return int<0, max>
	 */
	function pll_get_term( $term_id, $lang = '' ) {}

	/**
	 * @param int    $post_id Post ID.
	 * @param string $field Optional, the language field to return (@see PLL_Language), defaults to `'slug'`.
	 *                      Pass `\OBJECT` constant to get the language object. A composite value can be used for language
	 *                      term property values, in the form of `{language_taxonomy_name}:{property_name}` (see
	 *                      {@see PLL_Language::get_tax_prop()} for the possible values). Ex: `term_language:term_taxonomy_id`.
	 * @return string|int|bool|string[]|PLL_Language The requested field or object for the post language, `false` if no language is associated to that post.
	 *
	 * @phpstan-return (
	 *     $field is \OBJECT ? PLL_Language : (
	 *         $field is 'slug' ? non-empty-string : string|int|bool|list<non-empty-string>
	 *     )
	 * )|false
	 */
	function pll_get_post_language( $post_id, $field = 'slug' ) {}

	/**
	 * @param int $term_id Term ID.
	 * @return int[] An associative array of translations with language code as key and translation term ID as value.
	 * @phpstan-return array<non-empty-string, positive-int>
	 */
	function pll_get_term_translations( $term_id ) {}

	/**
	 * @return bool Whether it is the AMP endpoint.
	 */
	function amp_is_request() {}

	/**
	 * Intelly related posts
	 */
	function irp_head() {}

	function irp_footer() {}

	function irp_shortcode( $atts, $content = '' ) {}

	function irp_ui_get_box( $ids, $options = null ) {}

	function irp_the_content( $content ) {}

	function irp_ui_first_time() {}

	function irp_get_list_posts() {}
}
