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

namespace Lumiere\Frontend\Layout;

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
class Output {

	/**
	 * Display misceallenous texts
	 *
	 * @param string $selector Select which column to return
	 * @param string $text_one Optional, an extra text to use
	 * @param string $text_two Optional, an extra text to use
	 * @param string $text_three Optional, an extra text to use
	 * @return string
	 */
	public function misc_layout( string $selector, string $text_one = '', string $text_two = '', string $text_three = '' ): string {
		$container = [
			/* translators: %1s is a movie field string, such as director, actor */
			'click_more_start'               => "\n\t\t\t<!-- start hidesection -->\n\t\t\t" . '<div class="activatehidesection lumiere_align_center"><strong>(' . wp_sprintf( __( 'click to show more %1s', 'lumiere-movies' ), $text_one ) . ')</strong></div>' . "\n\t\t\t<div class=\"hidesection\">",
			'click_more_end'                 => "\n\t\t\t</div>\n\t\t\t<!-- end hidesection -->",
			'see_all_start'                  => "\n\t\t\t<!-- start hidesection -->\n\t\t\t" . '&nbsp;<span class="activatehidesection"><font size="-1"><strong>(' . esc_html__( 'see all', 'lumiere-movies' ) . ")</strong></font></span>\n\t\t\t<span class=\"hidesection\">",
			'see_all_end'                    => "\n\t\t\t</span>\n\t\t\t<!-- end hidesection -->",
			'frontend_items_sub_cat_parent'  => "\n\t\t\t\t<br>\n\t\t\t\t<span class=\"lum_results_section_subtitle_parent\">\n\t\t\t\t\t<span class=\"lum_results_section_subtitle_subcat\">" . $text_one . '</span>: ',
			'frontend_items_sub_cat_content' => "\n\t\t\t\t\t<span class=\"lum_results_section_subtitle_subcat_content\">" . $text_one . "</span>\n\t\t\t\t</span>",
			'items_sub_cat_parent_close'     => "\n\t\t\t\t\t</span>\n\t\t\t\t</span>",
			'two_columns_first'              => "\n\t\t\t<div class=\"lumiere_align_center lumiere_container\">\n\t\t\t\t<div class=\"lumiere_align_left lumiere_flex_auto\">" . $text_one . "\n\t\t\t\t</div>",
			'two_columns_second'             => "\n\t\t\t\t<div class=\"lumiere_align_right lumiere_flex_auto\">" . $text_one . "\n\t\t\t\t</div>\n\t\t\t</div>",
			'frontend_subtitle_item'         => "\n\t\t\t<span class=\"lum_results_section_subtitle\">" . $text_one . ':</span>',
			'frontend_title'                 => "\n\t\t\t<span id=\"title_" . preg_replace( '/[^A-Za-z0-9\-]/', '', $text_one ) . '">' . $text_one . '</span>',
			'popup_subtitle_item'            => "\n\t\t\t\t<span class=\"lum_results_section_subtitle\">" . $text_one . '</span>',
			'numbered_list'                  => "\n\t\t\t<div>\n\t\t\t\t[#" . strval( $text_one ) . '] <i>' . $text_two . '</i>&nbsp;' . $text_three . "\n\t\t\t" . '</div>',
		];
		return $container[ $selector ];
	}

	/**
	 * Display misceallenous links
	 *
	 * @param string $selector Select which column to return
	 * @param string $text_one Optional, an extra text to use
	 * @param string $text_two Optional, an extra text to use
	 * @param string $text_three Optional, an extra text to use
	 * @param string $text_four Optional, an extra text to use
	 * @return string
	 */
	public function get_link( string $selector, string $text_one = '', string $text_two = '', string $text_three = '', string $text_four = '' ): string {
		$container = [
			'taxonomy'              => "\n\t\t\t" . '<a id="' . esc_attr( $text_one ) . '" class="lum_link_taxo_page" href="' . esc_url( $text_two ) . '" title="' . esc_attr( $text_three ) . '">' . esc_html( $text_four ) . '</a>',
			'internal_with_spinner' => '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" title="' . __( 'internal link', 'lumiere-movies' ) . ' ' . $text_two . '" href="' . esc_url( $text_one ) . '">' . esc_html( $text_two ) . '</a>',
		];
		return $container[ $selector ];
	}

	/**
	 * Function wrapping items with the theme selected in admin options
	 * Make sure the theme selected in admin options is used
	 * @see \Lumiere\Frontend\Movie\Movie_Factory::factory_items_methods()
	 *
	 * @param string $text The text to be embeded with the layout
	 * @param string $item The item to transform, such as director, title, etc
	 * @param non-empty-array<string, string> $imdb_admin_values
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @return string
	 */
	public function front_item_wrapper( string $text, string $item, array $imdb_admin_values ): string {

		$item = sanitize_text_field( $item );
		$item_caps = strtoupper( $item );

		$output = "\n\t\t\t\t\t\t\t" . '<!-- ' . $item . ' -->';
		$output .= "\n\t\t" . '<div class="';

		// title doesn't take item 'lumiere-lines-common' but a dedicated class instead.
		if ( $item !== 'title' ) {
			$output .= 'lumiere-lines-common ';
		}

		$output .= 'lumiere-lines-common_' . $imdb_admin_values['imdbintotheposttheme'] . ' imdbelement' . $item_caps . '_' . $imdb_admin_values['imdbintotheposttheme'];
		$output .= '">';
		$output .= $text;
		$output .= "\n\t\t" . '</div>';

		return $output;
	}

	/**
	 * Function wrapping with "<!-- Lumière! movies plugin -->" the final text
	 * Make sure the theme selected in admin options is used
	 * @see \Lumiere\Frontend\Movie\Movie_Display::lum_display_movies_box()
	 *
	 * @param non-empty-array<string, string> $imdb_admin_values
	 * @phpstan-param OPTIONS_ADMIN $imdb_admin_values
	 * @param string $text The text to be embeded with the layout
	 * @return string
	 */
	public function front_main_wrapper( array $imdb_admin_values, string $text ): string {
		return "\n\t\t\t\t\t\t\t\t\t<!-- Lumière! movies plugin -->\n\t<div class=\"lum_results_frame lum_results_frame_" . $imdb_admin_values['imdbintotheposttheme'] . '">' . $text . "\n\t</div>\n\t\t\t\t\t\t\t\t\t<!-- /Lumière! movies plugin -->";
	}

	/**
	 * Layout selection depends on $item_line_name value
	 * If data was passed, use the first layout, if null was passed, use the second layout
	 * First layout display two items per row
	 * Second layout display items comma-separated
	 * @see Movie_Factory
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
			$output .= $this->misc_layout(
				'two_columns_first',
				$this->get_link(
					'taxonomy',
					$link_id_final,
					$this->get_taxonomy_url_href( $taxo_options['taxonomy_term'], $taxo_options['custom_taxonomy_fullname'] ),
					__( 'Find similar taxonomy results', 'lumiere-movies' ),
					$taxo_options['taxonomy_term'],
				),
			);

			$output .= $this->misc_layout(
				'two_columns_second',
				preg_replace( '/\n/', '', $item_line_name ) ?? '' // remove breaking space.
			);
			return $output;
		}

		// layout two: display the layout for all details separated by commas, ie keywords
		$output .= $this->get_link(
			'taxonomy',
			$link_id_final,
			$this->get_taxonomy_url_href( $taxo_options['taxonomy_term'], $taxo_options['custom_taxonomy_fullname'] ),
			__( 'Find similar taxonomy results', 'lumiere-movies' ),
			$taxo_options['taxonomy_term'],
		);
		return $output;
	}

	/**
	 * Create an html href link for taxonomy using the name passed
	 *
	 * @param string $name_searched The name searched, such as 'Stanley Kubrick'
	 * @param string $taxo_category The taxonomy category used, such as 'lumiere-director'
	 * @return string The WordPress HTML href link for the name with that category
	 */
	private function get_taxonomy_url_href( string $name_searched, string $taxo_category ): string {
		$find_term = get_term_by( 'name', $name_searched, $taxo_category );
		$taxo_link = $find_term instanceof \WP_Term ? get_term_link( $find_term->term_id, $taxo_category ) : '';
		return $taxo_link instanceof \WP_Error ? '' : $taxo_link;
	}
}
