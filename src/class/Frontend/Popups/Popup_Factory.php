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
use Lumiere\Frontend\Link_Maker\Interface_Linkmaker;

/**
 * Popups redirection, return a new text replacing the normal expected text
 * Use template_redirect hook to call it
 * 1. A var is defined in {@see \Lumiere\Config\Settings} URL_BIT_POPUPS constant
 * 2. That var is used to check if the url includes it, return the template otherwise
 * 3. If it the URL contains the get_query_var(), build a class name that includes it
 *
 * @since 4.4 Is a class
 * @since 4.8 Simplified, using Enum
 */
final class Popup_Factory {

	/**
	 * Constructor
	 */
	public function __construct(
		protected readonly Settings_Service $settings,
		protected readonly Interface_Linkmaker $link_maker
	) {}

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

		$class_name = $this->resolve_popup_class( $query_popup );

		if ( isset( $class_name ) && class_exists( $class_name ) ) {
			/** @var \Lumiere\Frontend\Popups\Popup_Interface $popup_instance */
			$popup_instance = new $class_name( $this->settings, $this->link_maker );
			$popup_instance->display_layout();

			// Fake return string since it is inside an add_filter()
			return '';
		}

		// No valid popup class was found, return normal template_path.
		return $template_path;
	}

	/**
	 * Resolve the class name using PHP 8.1 match expressions.
	 * This is type-safe and avoids fragile string manipulation.
	 * @since 4.8
	 *
	 * @param string $query_val
	 * @return null|string
	 */
	private function resolve_popup_class( string $query_val ): ?string {

		$type = Popup_Type::tryFrom( $query_val );

		if ( $type === null ) {
			return null;
		}

		return match ( $type ) {
			Popup_Type::FILM         => Popup_Film::class,
			Popup_Type::MOVIE_SEARCH => Popup_Movie_Search::class,
			Popup_Type::PERSON       => Popup_Person::class,
		};
	}
}
