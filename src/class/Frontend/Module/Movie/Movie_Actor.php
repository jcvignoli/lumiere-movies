<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Actor.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options_Movie;
use Lumiere\Frontend\Taxonomy\Add_Taxonomy;

/**
 * Method to display actor for movies
 *
 * @since 4.5 new class
 */
final class Movie_Actor extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Constructor
	 */
	public function __construct(
		protected Add_Taxonomy $add_taxo_class = new Add_Taxonomy()
	) {
		parent::__construct();
	}

	/**
	 * Display the main module version
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'actor' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

		$item_results = $movie->cast();
		$admin_total_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;
		$nb_total_items = count( $item_results );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$total_displayed = $admin_total_items > $nb_total_items ? $nb_total_items : $admin_total_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $total_displayed )[ $item_name ] )
		);

		for ( $i = 0; $i < $admin_total_items && ( $i < $nb_total_items ); $i++ ) {

			$output .= $this->output_class->misc_layout(
				'two_columns_first',
				parent::get_popup_person( $item_results[ $i ]['imdb'], $item_results[ $i ]['name'] )
			);

			$output .= $this->output_class->misc_layout(
				'two_columns_second',
				isset( $item_results[ $i ]['character'][0] ) && strlen( $item_results[ $i ]['character'][0] ) > 0 ? $item_results[ $i ]['character'][0] : '<i>' . __( 'role unknown', 'lumiere-movies' ) . '</i>'
			);

		}

		return $output;
	}

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 * @since 4.6.2 Click more added, since a lot of actors are displayed in popup main display
	 *
	 * @param 'actor' $item_name The name of the item
	 * @param array<array-key, array<string, string>> $item_results
	 * @param int<0, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout( 'popup_subtitle_item', ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] ) );

		$nb_rows_click_more = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : $nb_total_items; /** max number of movies before breaking with "see all" */

		// Sort by column name the array of results.
		// $column_item_results = array_column( $item_results, 'name' );
		// array_multisort( $column_item_results, SORT_ASC, $item_results ); // Keep sorting by actor popularity

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			// Display a "show more" after XX results
			if ( $i === $nb_rows_click_more ) {
				$isset_next = isset( $item_results[ $i + 1 ] ) ? true : false;
				$output .= $isset_next === true ? $this->output_class->misc_layout( 'see_all_start' ) : '';
			}
			if ( $i > 0 && $i < $nb_total_items - 1 && $i !== $nb_rows_click_more ) {
				$output .= ', ';
			}
			$output .= parent::get_person_url( $item_results[ $i ]['imdb'], $item_results[ $i ]['name'] );

			if ( $i > $nb_rows_click_more && $i === ( $nb_total_items - 1 ) ) {
				$output .= $this->output_class->misc_layout( 'see_all_end' );
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, displaying all results on two columns
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'actor' $item_name The name of the item
	 */
	public function get_module_popup_two_columns( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

		$item_results = $movie->cast();
		$nb_total_items = count( $item_results );

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout( 'popup_subtitle_item', ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] ) );

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$output .= $this->output_class->misc_layout( 'two_columns_first', $item_results[ $i ]['character'][0] ?? '<i>' . __( 'role unknown', 'lumiere-movies' ) . '</i>' );

			$output .= $this->output_class->misc_layout( 'two_columns_second', parent::get_person_url( $item_results[ $i ]['imdb'], $item_results[ $i ]['name'] ) );

		}
		return $output;
	}

	/**
	 * Display the Taxonomy module version
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'actor' $item_name The name of the item
	 */
	public function get_module_taxo( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

		$item_results = $movie->cast();
		$admin_total_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;
		$nb_total_items = count( $item_results );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		$total_displayed = $admin_total_items > $nb_total_items ? $nb_total_items : $admin_total_items;
		$output = $this->output_class->misc_layout( 'frontend_subtitle_item', ucfirst( Get_Options_Movie::get_all_fields( $total_displayed )[ $item_name ] ) );

		for ( $i = 0; ( $i < $nb_total_items ) && ( $i < $admin_total_items ); $i++ ) {

			// If either name or character are not available, jump.
			if ( ! isset( $item_results[ $i ]['character'][0] ) || ! isset( $item_results[ $i ]['name'] ) ) {
				continue;
			}

			$taxo_options = $this->add_taxo_class->create_taxonomy_options(
				$item_name,
				$item_results[ $i ]['name'],
				$this->imdb_admin_values
			);
			$output .= $this->output_class->get_taxo_layout_items(
				$movie->title(),
				$taxo_options,
				$this->add_taxo_class->get_taxonomy_url_href( $taxo_options['taxonomy_term'], $taxo_options['custom_taxonomy_fullname'] ),
				esc_attr( $item_results[ $i ]['character'][0] )
			);

		}
		return $output;
	}
}
