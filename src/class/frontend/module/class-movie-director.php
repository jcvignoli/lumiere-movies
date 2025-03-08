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
 * Method to display actor for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Director {

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
	 * @param 'director' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nbtotalitems = count( $item_results );

		// if no result, exit.
		if ( $nbtotalitems === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nbtotalitems );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nbtotalitems )[ $item_name ] )
		);

		for ( $i = 0; $i < $nbtotalitems; $i++ ) {

			/**
			 * Use links builder classes.
			 * Each one has its own class passed in $link_maker,
			 * according to which option the lumiere_select_link_maker() found in Frontend.
			 */
			$output .= $this->link_maker->lumiere_link_popup_people( $item_results, $i );

			if ( $i < $nbtotalitems - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 * @see Movie_Director::get_module() Calling this
	 *
	 * @param 'director' $item_name The name of the item
	 * @param array<int<0, max>, array<string, string>> $item_results
	 * @param int<1, max> $nbtotalitems
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nbtotalitems ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nbtotalitems )[ $item_name ] )
		);

		for ( $i = 0; $i < $nbtotalitems; $i++ ) {
			$output .= "\n\t\t\t" . '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="' . esc_url( wp_nonce_url( Get_Options::get_popup_url( 'person', site_url() ) . '?mid=' . $item_results[ $i ]['imdb'] ) ) . '" title="' . __( 'internal link', 'lumiere-movies' ) . '">' . $item_results[ $i ]['name'] . '</a>';

			if ( $i < $nbtotalitems - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module, displaying all results on two columns
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'director' $item_name The name of the item
	 */
	public function get_module_popup_two_columns( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nbtotalitems = count( $item_results );

		// if no result, exit.
		if ( $nbtotalitems === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nbtotalitems )[ $item_name ] )
		);

		for ( $i = 0; $i < $nbtotalitems; $i++ ) {

			$output .= $this->output_class->misc_layout(
				'two_columns_first',
				"\n\t\t\t" . '<a rel="nofollow" class="lum_popup_internal_link lum_add_spinner" href="' . esc_url( wp_nonce_url( Get_Options::get_popup_url( 'person', site_url() ) . '?mid=' . $item_results[ $i ]['imdb'] ) ) . '" title="' . __( 'internal link', 'lumiere-movies' ) . '">' . $item_results[ $i ]['name'] . '</a>'
			);

			$output .= $this->output_class->misc_layout(
				'two_columns_second',
				''
			);

		}
		return $output;
	}

	/**
	 * Display the Taxonomy module version
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'director' $item_name The name of the item
	 */
	public function get_module_taxo( Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nbtotalitems = count( $item_results );

		// if no result, exit.
		if ( $nbtotalitems === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options::get_all_fields( $nbtotalitems )[ $item_name ] )
		);

		for ( $i = 0; $i < $nbtotalitems; $i++ ) {

			$get_taxo_options = $this->movie_taxo->create_taxonomy_options( $item_name, $item_results[ $i ]['name'], $this->imdb_admin_values );
			$output .= $this->output_class->get_layout_items( $movie->title(), $get_taxo_options );

			if ( $i < $nbtotalitems - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}
}
