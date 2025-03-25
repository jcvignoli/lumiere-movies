<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Runtime.
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

/**
 * Method to display Runtime for movies
 *
 * @since 4.5 new class
 */
class Movie_Runtime extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the Runtime
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 * @param 'runtime' $item_name The name of the item
	 */
	public function get_module( \Imdb\Title $movie, string $item_name ): string {

		$item_results = isset( $movie->$item_name()[0]['time'] ) ? strval( $movie->$item_name()[0]['time'] ) : '';

		if ( strlen( $item_results ) === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results );
		}

		return $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( /* no number because no plural here */ )[ $item_name ] )
		)
			. $item_results . ' ' . __( 'minutes', 'lumiere-movies' );
	}

	/**
	 * Display the Popup version of the module
	 * Array of results is sorted by column
	 *
	 * @param 'runtime' $item_name The name of the item
	 * @param string $item_results
	 */
	public function get_module_popup( string $item_name, string $item_results ): string {

		return $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options_Movie::get_all_fields( /* no number because no plural here */ )[ $item_name ] )
		)
			. $item_results . ' ' . __( 'minutes', 'lumiere-movies' );
	}
}
