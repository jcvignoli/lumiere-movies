<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Producer.
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
 * Method to display Producer for movies
 *
 * @since 4.5 new class
 */
class Movie_Producer extends \Lumiere\Frontend\Module\Parent_Module {

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
	 * @param \Imdb\Title $movie IMDbPHP title class
	 * @param 'producer' $item_name The name of the item
	 */
	public function get_module( \Imdb\Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
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

			$count_jobs = isset( $item_results[ $i ]['jobs'] ) && count( $item_results[ $i ]['jobs'] ) > 0 ? count( $item_results[ $i ]['jobs'] ) : 0;
			$second_column = '';
			if ( $count_jobs > 0 ) {
				for ( $j = 0; $j < $count_jobs; $j++ ) {
					$second_column .= $item_results[ $i ]['jobs'][ $j ];
					if ( $j < ( $count_jobs - 1 ) ) {
						$second_column .= ', ';
					}
				}
			} elseif ( $count_jobs === 0 ) {
				$second_column .= '&nbsp;';
			}

			$output .= $this->output_class->misc_layout(
				'two_columns_second',
				$second_column
			);

		}

		return $output;
	}

	/**
	 * Display the Popup version of the module, all results are displayed in one line comma-separated
	 * Array of results is sorted by column
	 *
	 * @param 'producer' $item_name The name of the item
	 * @param array<int<0, max>, array<string, string>> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		// Sort by column name the array of results.
		$column_item_results = array_column( $item_results, 'name' );
		array_multisort( $column_item_results, SORT_ASC, $item_results );

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= parent::get_person_url( $item_results[ $i ]['imdb'], $item_results[ $i ]['name'] );

			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, displaying all results on two columns
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 * @param 'producer' $item_name The name of the item
	 */
	public function get_module_popup_two_columns( \Imdb\Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout( 'popup_subtitle_item', ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] ) );

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$output .= $this->output_class->misc_layout( 'two_columns_first', parent::get_person_url( $item_results[ $i ]['imdb'], $item_results[ $i ]['name'] ) );

			$output .= $this->output_class->misc_layout( 'two_columns_second', $item_results[ $i ]['jobs'][0] ?? '' );

		}
		return $output;
	}

	/**
	 * Display the Taxonomy module version
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 * @param 'producer' $item_name The name of the item
	 */
	public function get_module_taxo( \Imdb\Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$admin_total_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;
		$nb_total_items = count( $item_results );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		$total_displayed = $admin_total_items > $nb_total_items ? $nb_total_items : $admin_total_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $total_displayed )[ $item_name ] )
		);

		for ( $i = 0; ( $i < $nb_total_items ) && ( $i < $admin_total_items ); $i++ ) {

			$count_jobs = isset( $item_results[ $i ]['jobs'] ) && count( $item_results[ $i ]['jobs'] ) > 0 ? count( $item_results[ $i ]['jobs'] ) : 0;

			$jobs = '';
			for ( $j = 0; $j < $count_jobs; $j++ ) {
				$jobs .= $item_results[ $i ]['jobs'][ $j ] ?? '';
				if ( $j < ( $count_jobs - 1 ) ) {
					$jobs .= ', ';
				}
			}

			$taxo_options = $this->add_taxo_class->create_taxonomy_options(
				$item_name,
				$item_results[ $i ]['name'] ?? '',
				$this->imdb_admin_values
			);
			$output .= $this->output_class->get_taxo_layout_items(
				$movie->title(),
				$taxo_options,
				$this->add_taxo_class->get_taxonomy_url_href( $taxo_options['taxonomy_term'], $taxo_options['custom_taxonomy_fullname'] ),
				$jobs,
			);
		}
		return $output;
	}
}
