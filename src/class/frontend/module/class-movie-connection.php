<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Connection.
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
 * Method to display connection for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Connection {

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
	 * @param 'connection' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$admin_total_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;
		$nb_total_items = count( $item_results );

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $movie, $item_name, $item_results, $nb_total_items );
		}

		// count the actual results in values associative arrays
		$item_results_sub = array_filter( $item_results, fn( array $item_results ) => ( count( array_values( $item_results ) ) > 0 ) );
		$nbtotal_item_results_sub = count( $item_results_sub );

		if ( $nb_total_items === 0 || $nbtotal_item_results_sub === 0 ) {
			esc_html_e( 'No connected movies found.', 'lumiere-movies' );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		foreach ( Get_Options::define_list_connect_cat() as $category => $data_explain ) {

			// Total items for this category.
			$nb_items_cat = count( $item_results[ $category ] );

			for ( $i = 0; $i < $admin_total_items; $i++ ) {
				if ( isset( $item_results[ $category ][ $i ]['titleId'] ) && $item_results[ $category ][ $i ]['titleName'] ) {

					if ( $i === 0 ) {
						$output .= $this->output_class->misc_layout( 'frontend_items_sub_cat', $data_explain );
					}

					$output .= '<span class="lum_results_section_subtitle_subcat_content">';

					/**
					 * Use links builder classes.
					 * Each one has its own class passed in $link_maker,
					 * according to which option the lumiere_select_link_maker() found in Frontend.
					 */
					$output .= $this->link_maker->popup_film_link_inbox( // In trait Main.
						$item_results[ $category ][ $i ]['titleName'],
						$item_results[ $category ][ $i ]['titleId']
					);

					$output .= isset( $item_results[ $category ][ $i ]['description'] ) ? ' (' . esc_html( $item_results[ $category ][ $i ]['description'] ) . ')' : '';
					if ( $i < ( $admin_total_items - 1 ) && $i < $nb_total_items && $i < ( $nb_items_cat - 1 ) ) {
						$output .= ', '; // add comma to every connected movie but the last.
					}
					$output .= '</span></span>';
				}
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 * Array of results is sorted by column
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'connection' $item_name The name of the item
	 * @param array<string, array<array-key, array<string, string>>> $item_results
	 * @param int<0, max> $nb_total_items
	 */
	public function get_module_popup( Title $movie, string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		// count the actual results in values associative arrays
		$item_results_sub = array_filter( $item_results, fn( array $item_results ) => ( count( array_values( $item_results ) ) > 0 ) );
		$nbtotal_item_results_sub = count( $item_results_sub );

		if ( $nb_total_items === 0 || $nbtotal_item_results_sub === 0 ) {
			esc_html_e( 'No connected movies found.', 'lumiere-movies' );
		}

		foreach ( Get_Options::get_list_connect_cat() as $category => $data_explain ) {

			// Total items for this category.
			$nb_items_cat = count( $item_results[ $category ] );

			for ( $i = 0; $i < $nb_total_items; $i++ ) {
				if ( isset( $item_results[ $category ][ $i ]['titleId'] ) && isset( $item_results[ $category ][ $i ]['titleName'] ) ) {

					if ( $i === 0 ) {
						$output .= $this->output_class->misc_layout( 'frontend_items_sub_cat', $data_explain );
					}

					$output .= "\n\t\t\t\t" . '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="' . esc_url( wp_nonce_url( Get_Options::get_popup_url( 'film', site_url() ) . '?mid=' . $item_results[ $category ][ $i ]['titleId'] ) ) . '" title="' . esc_html( $item_results[ $category ][ $i ]['titleName'] ) . '">' . "\n\t\t\t\t" . esc_html( $item_results[ $category ][ $i ]['titleName'] ) . '</a>';

					$output .= isset( $item_results[ $category ][ $i ]['description'] ) ? ' (' . esc_html( $item_results[ $category ][ $i ]['year'] ) . ') (<i>' . esc_html( $item_results[ $category ][ $i ]['description'] ) . '</i>)' : '';

					if ( $i < ( $nb_total_items - 1 ) && $i < ( $nb_items_cat - 1 ) ) {
						$output .= ', '; // add comma to every connected movie but the last.
					}
					if ( $i === ( $nb_total_items - 1 ) ) {
						$output .= '<br>';
					}
				}
			}
		}
		return $output;
	}
}
