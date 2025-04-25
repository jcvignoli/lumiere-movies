<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Genre.
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
 * Method to display genre for movies
 *
 * @since 4.5 new class
 */
class Movie_Genre extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Constructor
	 */
	public function __construct(
		protected Add_Taxonomy $add_taxo_class = new Add_Taxonomy()
	) {
		parent::__construct();
	}

	/**
	 * Display the Genre
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'genre' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

		$item_results = $movie->$item_name();
		$nb_total_items = count( $item_results );

		if ( $nb_total_items === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results, $nb_total_items );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= $item_results[ $i ]['mainGenre'] ?? '';
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'genre' $item_name The name of the item
	 * @param array<array-key, array<string, string>> $item_results
	 * @param int<1, max> $nb_total_items
	 */
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nb_total_items )[ $item_name ] )
		);

		for ( $i = 0; $i < $nb_total_items; $i++ ) {
			$output .= $item_results[ $i ]['mainGenre'] ?? '';
			if ( $i < $nb_total_items - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}

	/**
	 * Display the Genre for taxonomy
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'genre' $item_name The name of the item, ie 'director', 'writer'
	 */
	public function get_module_taxo( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

		$genre = $movie->$item_name();
		$nbtotalgenre = count( $genre ) > 0 ? count( $genre ) : 0;

		if ( $nbtotalgenre === 0 ) {
			return '';
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( $nbtotalgenre )[ $item_name ] )
		);

		for ( $i = 0; $i < $nbtotalgenre; $i++ ) {

			if ( ! isset( $genre[ $i ]['mainGenre'] ) ) {
				continue;
			}

			$taxo_options = $this->add_taxo_class->create_taxonomy_options( $item_name, $genre[ $i ]['mainGenre'], $this->imdb_admin_values );
			$output .= $this->output_class->get_taxo_layout_items(
				$movie->title(),
				$taxo_options,
				$this->add_taxo_class->get_taxonomy_url_href( $taxo_options['taxonomy_term'], $taxo_options['custom_taxonomy_fullname'] )
			);

			if ( $i < $nbtotalgenre - 1 ) {
				$output .= ', ';
			}
		}
		return $output;
	}
}
