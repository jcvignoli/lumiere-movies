<?php declare( strict_types = 1 );
/**
 * Select the Popup to display
 *
 * @author      Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright   2025, Lost Highway
 *
 * @version     1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Popups;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Exception;

/**
 * Popups redirection, return a new text replacing the normal expected text
 * Use template_redirect hook to call it
 * 1. A var is defined in {@see \Lumiere\Config\Settings} constant URL_BIT_POPUPS_*
 * 2. That var is used to created the class name using get_query_var()
 * 3. If the class exists, it returns the relevant Popup class with method get_layout() (which echoes instead of returning, needs therefore an ending return)
 *
 * @since 4.4 Is a class
 * @phpstan-type POPUPS_CLASSES '\Lumiere\Frontend\Popups\Popup_Movie'|'\Lumiere\Frontend\Popups\Popup_Movie_Search'|'\Lumiere\Frontend\Popups\Popup_Person'
 */
class Popup_Select {

	/**
	 * The query var to look for in the URL
	 */
	private const QUERY_VAR = 'popup';

	/**
	 * Find if a template exists according to the query var
	 * @see \Lumiere\Frontend\Frontend that include this method into an add_filter()
	 *
	 * @param string $template_path The path to the page of the theme currently in use
	 * @return string The template path if no popup was found, the popup otherwise
	 */
	public function maybe_find_template( string $template_path ): string {

		$query_popup = get_query_var( self::QUERY_VAR );

		// The query var doesn't exist, exit.
		if ( ! isset( $query_popup ) || strlen( $query_popup ) === 0 ) {
			return $template_path;
		}

		/** @phpstan-var POPUPS_CLASSES $class_name */
		$class_name = $this->build_class_name( $query_popup );
		if ( class_exists( $class_name ) ) {
			( new $class_name() )->get_layout();
			// Fake return string since it is inside an add_filter()
			return '';
		}

		// No valid popup was found, return normal template_path.
		return $template_path;
	}

	/**
	 * Create the name of the class
	 * Built from Settings::URL_BIT_POPUPS_* in {@see \Lumiere\Config\Settings}
	 *
	 * @param string $query_popup, ie 'film', 'person', 'movie_search'
	 * @return string
	 * @throws Exception if wrong URL bit is provided
	 */
	private function build_class_name( string $query_popup ): string {
		$settings_const_name = 'URL_BIT_POPUPS_' . strtoupper( $query_popup );
		$full_const_path = "\Lumiere\Config\Get_Options::$settings_const_name";

		// Wrong URL.
		if ( ! defined( $full_const_path ) ) {
			throw new Exception( 'Lumiere: trying a wrong URL' );
		}

		$const_value = ucfirst( constant( $full_const_path ) );

		// If the constant value contains an underscore, capitalize every word to build the Popup class, ie 'movie_search' => 'Movie_Search'.
		if ( str_contains( $const_value, '_' ) ) {
			$words_array = explode( '_', $const_value );
			$words_caps = array_map( 'ucfirst', $words_array );
			$const_value = join( '_', $words_caps );
		}

		/** @phpstan-return POPUPS_CLASSES  */
		return __NAMESPACE__ . '\\Popup_' . $const_value;
	}
}
