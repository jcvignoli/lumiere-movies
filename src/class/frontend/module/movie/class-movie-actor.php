<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Actor.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;
use Lumiere\Config\Get_Options;
use Lumiere\Frontend\Movie\Movie_Taxonomy;

/**
 * Method to display actor for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Actor extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Constructor
	 */
	public function __construct(
		protected Movie_Taxonomy $movie_taxo = new Movie_Taxonomy()
	) {
		parent::__construct();
	}

	/**
	 * Display the main module version
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'actor' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

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
			ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] )
		);

		for ( $i = 0; $i < $admin_total_items && ( $i < $nb_total_items ); $i++ ) {

			$output .= $this->output_class->misc_layout(
				'two_columns_first',
				$this->link_maker->lumiere_link_popup_people( $item_results, $i ) // From trait Main.
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
	 * Array of results is sorted by column
	 *
	 * @param 'actor' $item_name The name of the item
	 * @param array<array-key, array<string, string>> $item_results
	 * @param int<0, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		// Sort by column name the array of results.
		$column_item_results = array_column( $item_results, 'name' );
		array_multisort( $column_item_results, SORT_ASC, $item_results );

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= "\n\t\t\t\t\t" . $this->output_class->get_link(
				'internal_with_spinner',
				parent::get_person_url( $item_results[ $i ]['imdb'] ),
				$item_results[ $i ]['name'],
			);
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, displaying all results on two columns
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'actor' $item_name The name of the item
	 */
	public function get_module_popup_two_columns( Title $movie, string $item_name ): string {

		$item_results = $movie->cast();
		$nb_total_items = count( $item_results );

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$output .= $this->output_class->misc_layout(
				'two_columns_first',
				$item_results[ $i ]['character'][0] ?? '<i>' . __( 'role unknown', 'lumiere-movies' ) . '</i>'
			);

			$output .= $this->output_class->misc_layout(
				'two_columns_second',
				"\n\t\t\t\t\t" . $this->output_class->get_link(
					'internal_with_spinner',
					parent::get_person_url( $item_results[ $i ]['imdb'] ),
					$item_results[ $i ]['name'],
				),
			);

		}
		return $output;
	}

	/**
	 * Display the Taxonomy module version
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'actor' $item_name The name of the item
	 */
	public function get_module_taxo( Title $movie, string $item_name ): string {

		$item_results = $movie->cast();
		$admin_total_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;
		$nb_total_items = count( $item_results );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		$total_displayed = $admin_total_items > $nb_total_items ? $nb_total_items : $admin_total_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] )
		);

		for ( $i = 0; ( $i < $nb_total_items ) && ( $i < $admin_total_items ); $i++ ) {

			// If either name or character are not available, jump.
			if ( ! isset( $item_results[ $i ]['character'][0] ) || ! isset( $item_results[ $i ]['name'] ) ) {
				continue;
			}

			$get_taxo_options = $this->movie_taxo->create_taxonomy_options(
				$item_name,
				$item_results[ $i ]['name'],
				$this->imdb_admin_values
			);
			$output .= $this->output_class->get_layout_items( $movie->title(), $get_taxo_options, esc_attr( $item_results[ $i ]['character'][0] ) );

		}
		return $output;
	}
}
