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

}
