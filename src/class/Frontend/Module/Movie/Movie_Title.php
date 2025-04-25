<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Title.
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

/**
 * Method to display title for movies
 *
 * @since 4.5 new class
 */
class Movie_Title extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the title and possibly the year
	 *
	 * @param \Lumiere\Vendor\Imdb\Title $movie IMDbPHP title class
	 * @param 'title' $item_name The name of the item
	 */
	public function get_module( \Lumiere\Vendor\Imdb\Title $movie, string $item_name ): string {

		$year = $movie->year();
		$title = $movie->$item_name();

		$year_text = '';
		if ( strlen( strval( $year ) ) > 0 && isset( $this->imdb_data_values['imdbwidgetyear'] ) && $this->imdb_data_values['imdbwidgetyear'] === '1' ) {
			$year_text = ' (' . strval( $year ) . ')';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $title, $year_text );
		}

		return $this->output_class->misc_layout(
			'frontend_title',
			$title . $year_text
		);
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param string $title The title
	 * @param string $year_text The year
	 */
	public function get_module_popup( string $title, string $year_text ): string {

		return $this->output_class->misc_layout(
			'popup_title',
			$title . $year_text
		);
	}
}
