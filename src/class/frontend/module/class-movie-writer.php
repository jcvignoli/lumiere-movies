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

namespace Lumiere\Frontend\Module;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Title;
use Lumiere\Frontend\Main;
use Lumiere\Frontend\Layout\Output;
use Lumiere\Frontend\Movie\Movie_Taxonomy;
use Lumiere\Config\Get_Options;

/**
 * Method to display writer for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Writer {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output $output_class = new Output(),
		protected Movie_Taxonomy $movie_taxo = new Movie_Taxonomy()
	) {
		// Construct Frontend Main trait with options and links.
		$this->start_main_trait();
	}

	/**
	 * Display the main module version
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'writer' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$admin_max_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;
		$nb_total_items = count( $item_results );

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $movie, $item_name, $item_results, $nb_total_items );
		}

		$total_displayed = $admin_max_items > $nb_total_items ? $nb_total_items : $admin_max_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nb_total_items && $i < $admin_max_items; $i++ ) {

			$output .= $this->output_class->misc_layout(
				'two_columns_first',
				$this->link_maker->lumiere_link_popup_people( $item_results, $i )
			);

			$count_jobs = isset( $item_results[ $i ]['jobs'] ) && count( $item_results[ $i ]['jobs'] ) > 0 ? count( $item_results[ $i ]['jobs'] ) : 0;

			$second_column = '';
			for ( $j = 0; $j < $count_jobs; $j++ ) {
				$second_column .= sanitize_text_field( $item_results[ $i ]['jobs'][ $j ] );
				if ( $j < ( $count_jobs - 1 ) ) {
					$second_column .= ', ';
				}
			}

			// Add number of episode and year they worked in.
			// @phan-suppress-next-line PhanTypeInvalidDimOffset */
			if ( $item_results[ $i ]['episode'] !== null && count( $item_results[ $i ]['episode'] ) > 0 && isset( $item_results[ $i ]['episode']['total'] ) && $item_results[ $i ]['episode']['total'] !== 0 ) {
				$total = isset( $item_results[ $i ]['episode']['total'] ) ? esc_html( $item_results[ $i ]['episode']['total'] ) . ' ' . esc_html( _n( 'episode', 'episodes', $item_results[ $i ]['episode']['total'], 'lumiere-movies' ) ) : '';
				/* translators: "In" like in "in 2025" */
				$year_from_or_in = isset( $item_results[ $i ]['episode']['endYear'] ) ? __( 'from', 'lumiere-movies' ) : __( 'in', 'lumiere-movies' );
				$year = isset( $item_results[ $i ]['episode']['year'] ) ? ' ' . esc_html( $year_from_or_in ) . ' ' . esc_html( $item_results[ $i ]['episode']['year'] ) : '';
				/* translators: "To" like in "to 2025" */
				$end_year = isset( $item_results[ $i ]['episode']['endYear'] ) ? ' ' . esc_html__( 'to', 'lumiere-movies' ) . ' ' . esc_html( $item_results[ $i ]['episode']['endYear'] ) : '';
				$second_column .= ' (<i>' . $total . $year . $end_year . '</i>)';
			}

			$output .= $this->output_class->misc_layout( 'two_columns_second', $second_column );

		}

		return $output;
	}

	/**
	 * Display the Popup version of the module
	 * @see Movie_Writer::get_module() Calling this
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'writer' $item_name The name of the item
	 * @param array<int<0, max>, array<string, string>> $item_results
	 * @param int<0, max> $nb_total_items
	 */
	public function get_module_popup( Title $movie, string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$output .= '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="' . esc_url( wp_nonce_url( Get_Options::get_popup_url( 'person', site_url() ) . '?mid=' . $item_results[ $i ]['imdb'] ) ) . '" title="' . esc_html__( 'internal link', 'lumiere-movies' ) . '">';
			$output .= "\n\t\t\t" . esc_html( $item_results[ $i ]['name'] ) . '</a>';

			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
			$output .= "\n</div>";
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, displaying all results on two columns
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'writer' $item_name The name of the item
	 */
	public function get_module_popup_two_columns( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $nb_total_items )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {

			$output .= $this->output_class->misc_layout(
				'two_columns_first',
				'<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="' . esc_url( wp_nonce_url( Get_Options::get_popup_url( 'person', site_url() ) . $item_results[ $i ]['imdb'] . '/?mid=' . $item_results[ $i ]['imdb'] ) ) . '" title="' . esc_html__( 'internal link', 'lumiere-movies' ) . ' ' . esc_html( $item_results[ $i ]['name'] ) . '">' . "\n\t\t\t\t" . esc_html( $item_results[ $i ]['name'] ) . '</a>'
			);

			$output .= $this->output_class->misc_layout(
				'two_columns_second',
				isset( $item_results[ $i ]['jobs'][0] ) ? esc_html( $item_results[ $i ]['jobs'][0] ) : ''
			);

		}
		return $output;
	}

	/**
	 * Display the Taxonomy module version
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'writer' $item_name The name of the item
	 */
	public function get_module_taxo( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );
		$admin_max_items = isset( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) ? intval( $this->imdb_data_values[ 'imdbwidget' . $item_name . 'number' ] ) : 0;

		// if no result, exit.
		if ( $nb_total_items === 0 ) {
			return '';
		}

		$total_displayed = $admin_max_items > $nb_total_items ? $nb_total_items : $admin_max_items;
		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( $total_displayed )[ $item_name ] ) )
		);

		for ( $i = 0; $i < $nb_total_items && $i < $admin_max_items; $i++ ) {

			$count_jobs = isset( $item_results[ $i ]['jobs'] ) && count( $item_results[ $i ]['jobs'] ) > 0 ? count( $item_results[ $i ]['jobs'] ) : 0;
			$jobs = '';

			for ( $j = 0; $j < $count_jobs; $j++ ) {

				// Add number of episode and year they worked in.
				$dates_episodes = '';
				// @phan-suppress-next-line PhanTypeInvalidDimOffset */
				if ( $item_results[ $i ]['episode'] !== null && count( $item_results[ $i ]['episode'] ) > 0 && isset( $item_results[ $i ]['episode']['total'] ) && $item_results[ $i ]['episode']['total'] !== 0 ) {
					$total = $item_results[ $i ]['episode']['total'] > 0 ? esc_html( $item_results[ $i ]['episode']['total'] ) . ' ' . esc_html( _n( 'episode', 'episodes', $item_results[ $i ]['episode']['total'], 'lumiere-movies' ) ) : '';
					/* translators: "From" like in "from 2025" */
					$year_from_or_in = isset( $item_results[ $i ]['episode']['endYear'] ) ? __( 'from', 'lumiere-movies' ) : __( 'in', 'lumiere-movies' );
					/* translators: "To" like in "to 2025" */
					$year_to_or_in = isset( $item_results[ $i ]['episode']['year'] ) ? __( 'to', 'lumiere-movies' ) : __( 'in', 'lumiere-movies' );
					$year = isset( $item_results[ $i ]['episode']['year'] ) ? ' ' . esc_html( $year_from_or_in ) . ' ' . esc_html( $item_results[ $i ]['episode']['year'] ) : '';
					$end_year = isset( $item_results[ $i ]['episode']['endYear'] ) ? ' ' . esc_html( $year_to_or_in ) . ' ' . esc_html( $item_results[ $i ]['episode']['endYear'] ) : '';
					$dates_episodes = strlen( $total . $year . $end_year ) > 0 ? ' (<i>' . $total . $year . $end_year . '</i>)' : '';
				}
				$jobs .= isset( $item_results[ $i ]['jobs'][ $j ] ) && strlen( $item_results[ $i ]['jobs'][ $j ] ) > 0 ? $item_results[ $i ]['jobs'][ $j ] . $dates_episodes : '';
				if ( $j < ( $count_jobs - 1 ) ) {
					$jobs .= ', ';
				}

			}

			$get_taxo_options = $this->movie_taxo->create_taxonomy_options(
				$item_name,
				// @phan-suppress-next-line PhanTypeInvalidDimOffset,PhanTypeMismatchArgument (Invalid offset "name" of $producer[$i] of array type array{jobs:\Countable|non-empty-array<mixed,mixed>} -> would require to define $producer array, which would be a nightmare */
				isset( $item_results[ $i ]['name'] ) ? esc_html( $item_results[ $i ]['name'] ) : '',
				$this->imdb_admin_values
			);
			$output .= $this->output_class->get_layout_items( esc_html( $movie->title() ), $get_taxo_options, $jobs );

		}

		return $output;
	}
}
