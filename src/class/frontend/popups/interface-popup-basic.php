<?php declare( strict_types = 1 );
/**
 * Interface for Popups
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Popups;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Interface for Popups
 */
interface Popup_Basic {

	/**
	 * Get the Title of the Page
	 *
	 * @param string|null $title Movie (ID or Name) or Person name
	 * @return string Title of the page
	 */
	public function get_title( ?string $title ): string;

	/**
	 * Edit the Title of the Page
	 * Used in add_filter( 'document_title_parts' )
	 *
	 * @param array<string, string> $title
	 * @phpstan-param array{title: string, page: string, tagline: string, site: string} $title
	 * @phpstan-return array{title: string, page: string, tagline: string, site: string}
	 */
	public function edit_title( array $title ): array;

	/**
	 * Display Page layout
	 * Used in add_filter( 'template_include' )
	 *
	 * @param string $template_path The path to the page of the theme currently in use - not utilised
	 * @return string
	 */
	public function get_layout( string $template_path ): string;
}
