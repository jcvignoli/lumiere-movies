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
}


