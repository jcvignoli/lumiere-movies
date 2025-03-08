<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Goof.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Module;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;
use Lumiere\Frontend\Main;
use Lumiere\Frontend\Layout\Output;
use Lumiere\Config\Get_Options;

/**
 * Method to display goof for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Goof {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output $output_class = new Output(),
	) {
		// Construct Frontend Main trait with options and links.
		$this->start_main_trait();
	}

	/**
	 * Display the main module version
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'goof' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$filter_nbtotal_items = array_filter( $item_results, fn( array $item_results ) => ( count( array_values( $item_results ) ) > 0 ) ); // counts the actual goofs, not their categories
		$nb_total_items = count( $filter_nbtotal_items );
		$admin_total_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$total_displayed = $admin_total_items > $nb_total_items ? $nb_total_items : $admin_total_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
		);

		foreach ( Get_Options::get_list_goof_cat() as $category => $data_explain ) {
			// Loop conditions: less than the total number of goofs available AND less than the goof limit setting, using a loop counter.
			for ( $i = 0; $i < $total_displayed; $i++ ) {
				if ( isset( $item_results[ $category ][ $i ]['content'] ) ) {
					$output .= $this->output_class->misc_layout( 'frontend_items_sub_cat', $data_explain );
					if ( isset( $item_results[ $category ][ $i ]['content'] ) && strlen( $item_results[ $category ][ $i ]['content'] ) > 0 ) {
						$output .= "\n\t\t\t\t" . '<span class="lum_results_section_subtitle_subcat_content">' . esc_html( $item_results[ $category ][ $i ]['content'] ) . '</span>&nbsp;';
					}
					$output .= '</span>';
				}
			}
		}

		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'goof' $item_name The name of the item
	 * @param array<string, array<array-key, array<string, string>>> $item_results
	 * @param int<0, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$translated_item = Get_Options::get_all_fields( $nb_total_items )[ $item_name ];
		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			esc_html( ucfirst( $translated_item ) )
		);

		$overall_loop = 1;

		foreach ( Get_Options::get_list_goof_cat() as $category => $data_explain ) {

			// Loop conditions: less than the total number of goofs available AND less than the goof limit setting, using a loop counter.
			for ( $i = 0; $i < $nb_total_items; $i++ ) {

				if ( isset( $item_results[ $category ][ $i ]['content'] ) && strlen( $item_results[ $category ][ $i ]['content'] ) > 0 ) {
					$output .= "\n\t\t\t<div>\n\t\t\t\t[#" . esc_html( strval( $overall_loop ) ) . '] <i>' . esc_html( $data_explain ) . '</i>&nbsp;';
					$output .= $this->link_maker->lumiere_imdburl_to_internalurl( $item_results[ $category ][ $i ]['content'] );
					$output .= "\n\t\t\t" . '</div>';
				}

				if ( $overall_loop === 5 ) {
					$isset_next = isset( $item_results[ $category ][ $i + 1 ] ) ? true : false;
					$output .= $isset_next === true ? $this->output_class->misc_layout( 'click_more_start', $translated_item ) : '';
				}
				$overall_loop ++;
			}

			if ( $category === array_key_last( Get_Options::get_list_goof_cat() ) ) {
				$output .= $this->output_class->misc_layout( 'click_more_end' );
			}

		}
		return $output;
	}
}
