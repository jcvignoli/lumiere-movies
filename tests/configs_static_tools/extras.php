<?php
// PHPStan Extras functions

namespace {

	/**
	 * Returns true if Polylang manages languages and translations for this taxonomy.
	 *
	 * @api
	 * @since 1.0.1
	 *
	 * @param string $tax Taxonomy name.
	 * @return bool
	 */
	function pll_is_translated_taxonomy( $tax ) {
		return PLL()->model->is_translated_taxonomy( $tax );
	}


	/** ---------------------------------- Polylang */

	/**
	 * Sets the term language.
	 *
	 * @api
	 * @since 1.5
	 *
	 * @param int    $id   Term id.
	 * @param string $lang Language code.
	 * @return void
	 */
	function pll_set_term_language( $id, $lang ) {
		PLL()->model->term->set_language( $id, $lang );
	}

	/**
	 * Returns the current language on frontend.
	 * Returns the language set in admin language filter on backend ( false if set to all languages ).
	 *
	 * @api
	 * @since 0.8.1
	 *
	 * @param string $field Optional, the language field to return ( @see PLL_Language ), defaults to 'slug'. Pass OBJECT constant to get the language object.
	 * @return string|PLL_Language|false The requested field for the current language.
	 */
	function pll_current_language( $field = 'slug' ) {
		if ( OBJECT === $field ) {
			return PLL()->curlang;
		}
		return isset( PLL()->curlang->$field ) ? PLL()->curlang->$field : false;
	}

	/**
	 * Returns the term language.
	 *
	 * @api
	 * @since 1.5.4
	 *
	 * @param int    $term_id Term id.
	 * @param string $field   Optional, the language field to return ( @see PLL_Language ), defaults to 'slug'.
	 * @return string|false The requested field for the term language, false if no language is associated to that term.
	 */
	function pll_get_term_language( $term_id, $field = 'slug' ) {
		return ( $lang = PLL()->model->term->get_language( $term_id ) ) ? $lang->$field : false;
	}

	/**
	 * Returns the list of available languages.
	 *
	 * @api
	 * @since 1.5
	 *
	 * @param array $args {
	 *   Optional array of arguments.
	 *
	 *   @type bool   $hide_empty Hides languages with no posts if set to true ( defaults to false ).
	 *   @type string $fields     Return only that field if set ( @see PLL_Language for a list of fields ), defaults to 'slug'.
	 * }
	 * @return string[]
	 */
	function pll_languages_list( $args = array() ) {
		$args = wp_parse_args( $args, array( 'fields' => 'slug' ) );
		return PLL()->model->get_languages_list( $args );
	}
	
	/**
	 * Returns the default language.
	 *
	 * @api
	 * @since 1.0
	 * @since 3.4 Accepts composite values.
	 *
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
	function pll_default_language( $field = 'slug' ) {
		$lang = PLL()->model->get_default_language();

		if ( empty( $lang ) ) {
			return false;
		}

		if ( \OBJECT === $field ) {
			return $lang;
		}

		return $lang->get_prop( $field );
	}
	/**
	 * Returns the home url in a language.
	 *
	 * @api
	 * @since 0.8
	 *
	 * @param string $lang Optional language code, defaults to the current language.
	 * @return string
	 */
	function pll_home_url( $lang = '' ) {
		if ( empty( $lang ) ) {
			$lang = pll_current_language();
		}

		if ( empty( $lang ) || empty( PLL()->links ) ) {
			return home_url( '/' );
		}

		return PLL()->links->get_home_url( $lang );
	}
	/**
	 * Determine whether the current request is for an AMP page.
	 *
	 * This function cannot be called before the parse_query action because it needs to be able
	 * to determine the queried object is able to be served as AMP. If 'amp' theme support is not
	 * present, this function returns true just if the query var is present. If theme support is
	 * present, then it returns true in transitional mode if an AMP template is available and the query
	 * var is present, or else in standard mode if just the template is available.
	 *
	 * @since 2.0 Formerly known as is_amp_endpoint().
	 *
	 * @return bool Whether it is the AMP endpoint.
	 * @global WP_Query $wp_query
	 */
	function amp_is_request() {
		global $wp_query;

		$is_amp_url = (
			amp_is_canonical()
			||
			amp_has_paired_endpoint()
		);

		// If AMP is not available, then it's definitely not an AMP endpoint.
		if ( ! amp_is_available() ) {
			// But, if WP_Query was not available yet, then we will just assume the query is supported since at this point we do
			// know either that the site is in Standard mode or the URL was requested with the AMP query var. This can still
			// produce an undesired result when a Standard mode site has a post that opts out of AMP, but this issue will
			// have been flagged via _doing_it_wrong() in amp_is_available() above.
			if ( ! did_action( 'wp' ) || ! $wp_query instanceof WP_Query ) {
				return $is_amp_url && AMP_Options_Manager::get_option( Option::ALL_TEMPLATES_SUPPORTED );
			}

			return false;
		}

		return $is_amp_url;
	}

}

