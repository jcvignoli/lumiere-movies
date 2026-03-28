<?php declare( strict_types = 1 );
/**
 * Select the Popup to display
 *
 * @copyright     2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Popups;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Config\Settings_Service;
use Lumiere\Enums\Popup_Type;
use Lumiere\Frontend\Link_Maker\Link_Factory;
use Lumiere\Frontend\Link_Maker\Interface_Linkmaker;
use Exception;

/**
 * Popups redirection, return a new text replacing the normal expected text
 * Use template_redirect hook to call it
 * 1. A var is defined in {@see \Lumiere\Config\Settings} URL_BIT_POPUPS constant
 * 2. That var is used to check if the url includes it, return the template otherwise
 * 3. If it the URL contains the get_query_var(), build a class name that includes it
 *
 * @since 4.4 Is a class
 * @phpstan-type POPUPS_CLASSES '\Lumiere\Frontend\Popups\Popup_Film'|'\Lumiere\Frontend\Popups\Popup_Movie_Search'|'\Lumiere\Frontend\Popups\Popup_Person'
 */
final class Popup_Factory {

	/**
	 * Constructor
	 */
	public function __construct(
		protected Settings_Service $settings,
		protected Interface_Linkmaker $link_maker
	) {
		$this->link_maker = ( new Link_Factory( $this->settings ) )->select_link_maker();
	}

	/**
	 * Find if a template exists according to the query var
	 * @see \Lumiere\Frontend\Frontend that include this method into an add_filter() hook 'template_include'
	 *
	 * @param string $template_path The path to the page of the theme currently in use
	 * @return string $template_path if no popup was found, the popup otherwise
	 */
	public function maybe_find_template( string $template_path ): string {

		$query_popup = get_query_var( Get_Options::LUM_POPUP_STRING );

		// The query var doesn't exist, return the template untouched.
		if ( ! isset( $query_popup ) || strlen( $query_popup ) === 0 ) {
			return $template_path;
		}

		/** @phpstan-var POPUPS_CLASSES $class_name */
		$class_name = $this->build_class_name( $query_popup );
		if ( class_exists( $class_name ) ) {
			( new $class_name( settings: $this->settings, link_maker: $this->link_maker ) )->display_layout();
			// Fake return string since it is inside an add_filter()
			return '';
		}

		// No valid popup class was found, return normal template_path.
		return $template_path;
	}

	/**
	 * Create the name of the class
	 * Check if the query is included in enum Popup_Type in {@see \Lumiere\Config\Settings}
	 *
	 * @param string $query_popup
	 * @return string
	 * @throws Exception if wrong URL bit is provided
	 */
	private function build_class_name( string $query_popup ): string {

		$const_key_val = Popup_Type::from_key( $query_popup )->value;

		/**
		 * Wrong URL string passed. Don't know why static tools believe it's always set.
		 * @psalm-suppress DocblockTypeContradiction
		 * @phpstan-ignore isset.variable (Variable $const_key_val in isset() always exists and is not nullable)
		 */
		if ( ! isset( $const_key_val )  ) {
			throw new Exception( 'Lumiere: *' . esc_html( $query_popup ) . '* is a wrong URL path' );
		}

		// If the constant value contains an underscore, capitalize every word to build the Popup class, ie 'movie_search' => 'Movie_Search'.
		if ( str_contains( $const_key_val, '_' ) ) {
			$words_array = explode( '_', $const_key_val );
			$words_caps = array_map( 'ucfirst', $words_array );
			$const_key_val = join( '_', $words_caps );
		}

		// Map 'Film' to 'Film' and 'Movie_Search' to 'Movie_Search'. 'Film' popup class is Popup_Film.
		$class_name_part = ucwords( $const_key_val );

		/** @phpstan-return POPUPS_CLASSES  */
		return __NAMESPACE__ . '\\Popup_' . $class_name_part;
	}
}
