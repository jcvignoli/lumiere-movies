<?php
// Phan Needs this previous definition, dunno know why

namespace {
	
	if ( ! function_exists( 'get_terms' ) ) {
		/**
		 *
		 * @param array|string $args       Optional. Array or string of arguments. See WP_Term_Query::__construct()
		 *                                 for information on accepted arguments. Default empty array.
		 * @param array|string $deprecated Optional. Argument array, when using the legacy function parameter format.
		 *                                 If present, this parameter will be interpreted as `$args`, and the first
		 *                                 function parameter will be parsed as a taxonomy or array of taxonomies.
		 *                                 Default empty.
		 * @return WP_Term[]|int[]|string[]|string|WP_Error Array of terms, a count thereof as a numeric string,
		 *                                                  or WP_Error if any of the taxonomies do not exist.
		 *                                                  See the function description for more information.
		 */
		function get_terms( $args = array(), $deprecated = '' ) {}
	}

}
