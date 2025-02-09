<?php declare( strict_types = 1 );
/**
 * Class for displaying movies' layout.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Layouts
 *
 * @since 4.4 Class created, using methods that were in Movie_Display
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 */
class Movie_Layout {

	/**
	 * Subtitles to the items
	 * Title specifically
	 * @see Movie_Data::get_item_title()
	 */
	public function subtitle_item_title( string $title_sanitized, string $year ): string {
		return "\n\t\t\t<span id=\"title_$title_sanitized\">" . $title_sanitized . $year . '</span>';
	}

	/**
	 * Subtitles to the items
	 * @see Movie_Data
	 *
	 * @param string $text The text to be embeded with the layout
	 */
	public function subtitle_item( string $text ): string {
		return "\n\t\t\t" . '<span class="lum_results_section_subtitle">' . $text . ':</span>';
	}

	/**
	 * Function wrapping with <div> the final text with the theme selected in admin options
	 * @see Movie_Display::factory_items_methods()
	 *
	 * @param string $text The text to be embeded with the layout
	 * @param string $item The item to transform, such as director, title, etc
	 * @param non-empty-array<string, string> $imdb_admin_values
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @return string
	 */
	public function final_div_wrapper( string $text, string $item, array $imdb_admin_values ): string {

		$item = sanitize_text_field( $item );
		$item_caps = strtoupper( $item );

		$output = "\n\t\t\t\t\t\t\t" . '<!-- ' . $item . ' -->';

		// title doesn't take item 'lumiere-lines-common' as a class
		if ( $item !== 'title' ) {
			$output .= "\n\t\t" . '<div class="lumiere-lines-common';
		} else {
			$output .= "\n\t\t" . '<div class="imdbelement' . $item_caps;
		}

		$output .= ' lumiere-lines-common_' . $imdb_admin_values['imdbintotheposttheme'] . ' imdbelement' . $item_caps . '_' . $imdb_admin_values['imdbintotheposttheme'];
		$output .= '">';
		$output .= $text;
		$output .= "\n\t\t" . '</div>';

		return $output;
	}

	/**
	 * Layout selection depends on $item_line_name value
	 * If data was passed, use the first layout, if null was passed, use the second layout
	 * First layout display two items per row
	 * Second layout display items comma-separated
	 * @see Movie_Data
	 *
	 * @param string $movie_title
	 * @param array<string, string> $taxo_options
	 * @phstan-param array{'custom_taxonomy_fullname': string, 'taxonomy_term': string} $taxo_options
	 * @param string|null $item_line_name Null if the layout two should be utilised
	 * @return string
	 */
	public function get_layout_items( string $movie_title, array $taxo_options, ?string $item_line_name = null ): string {

		$lang = strtok( get_bloginfo( 'language' ), '-' );
		$lang_term = $lang !== false ? $lang : '';
		$output = '';

		// Build the id for the link <a id="$link_id">
		$link_id = esc_html( $movie_title ) . '_' . esc_html( $lang_term ) . '_' . esc_html( $taxo_options['custom_taxonomy_fullname'] ) . '_' . esc_html( $taxo_options['taxonomy_term'] );
		$link_id_cleaned = preg_replace( "/^'|[^A-Za-z0-9\'-]|'|\-$/", '_', $link_id ) ?? '';
		$link_id_final = 'link_taxo_' . strtolower( str_replace( '-', '_', $link_id_cleaned ) );

		// layout one: display the layout for two items per row, ie actors, writers, producers
		if ( is_string( $item_line_name ) === true ) {
			$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
			$output .= "\n\t\t\t\t\t<a id=\"" . $link_id_final . '" class="lum_link_taxo_page" href="'
					. esc_url( $this->create_taxonomy_weblink( $taxo_options['taxonomy_term'], $taxo_options['custom_taxonomy_fullname'] ) )
					. '" title="' . esc_html__( 'Find similar taxonomy results', 'lumiere-movies' )
					. '">';
			$output .= "\n\t\t\t\t\t" . $taxo_options['taxonomy_term'];
			$output .= "\n\t\t\t\t\t" . '</a>';
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
			$output .= preg_replace( '/\n/', '', $item_line_name ); // remove breaking space.
			$output .= "\n\t\t\t\t" . '</div>';
			$output .= "\n\t\t\t" . '</div>';
			return $output;
		}

		// layout two: display the layout for all details separated by commas, ie keywords
		$output .= '<a id="' . $link_id_final . '" class="lum_link_taxo_page" '
				. 'href="' . esc_url( $this->create_taxonomy_weblink( $taxo_options['taxonomy_term'], $taxo_options['custom_taxonomy_fullname'] ) )
				. '" '
				. 'title="' . esc_html__( 'Find similar taxonomy results', 'lumiere-movies' ) . '">';
		$output .= $taxo_options['taxonomy_term'];
		$output .= '</a>';
		return $output;
	}

	/**
	 * Create an html link for taxonomy using the name passed
	 *
	 * @param string $name_searched The name searched, such as 'Stanley Kubrick'
	 * @param string $taxo_category The taxonomy category used, such as 'lumiere-director'
	 * @return string The WordPress full HTML link for the name with that category
	 */
	private function create_taxonomy_weblink( string $name_searched, string $taxo_category ): string {
		$find_term = get_term_by( 'name', $name_searched, $taxo_category );
		$taxo_link = $find_term instanceof \WP_Term ? get_term_link( $find_term->term_id, $taxo_category ) : '';
		return $taxo_link instanceof \WP_Error ? '' : $taxo_link;
	}
}
