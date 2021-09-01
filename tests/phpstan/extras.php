<?php
// PHPStan Extras functions

namespace {

	// Override wp_kses, param $allowed_html is wrong, trying to override
	// Doesn't work, can't find a working solution
	if ( !function_exists('wp_kses')) {
		/**
		 * wp_kses
		 *
		 * Filters text content and strips out disallowed HTML.
		 *
		 * This function makes sure that only the allowed HTML element names, attribute
		 * names, attribute values, and HTML entities will occur in the given text string.
		 *
		 * This function expects unslashed data.
		 *
		 * @see wp_kses_post() for specifically filtering post content and fields.
		 * @see wp_allowed_protocols() for the default allowed protocols in link URLs.
		 *
		 * @since 1.0.0
		 *
		 * @param string         $string            Text content to filter.
		 * @param array<string, array<string, bool>|true> $allowed_html      An array of allowed HTML elements and attributes,
		 *                                          or a context name such as 'post'. See wp_kses_allowed_html()
		 *                                          for the list of accepted context names.
		 * @param string[]       $allowed_protocols Array of allowed URL protocols.
		 * @return string Filtered content containing only the allowed HTML.
		*/
	    function wp_kses($string, $allowed_html, $allowed_protocols = array()) {}
	}

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

}


