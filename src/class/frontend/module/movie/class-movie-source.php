<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Source.
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

use Lumiere\Config\Get_Options;

/**
 * Method to display Source for movies
 *
 * @since 4.5 new class
 */
class Movie_Source extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the main module version
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 * @param 'source' $item_name The name of the item
	 */
	public function get_module( \Imdb\Title $movie, string $item_name ): string {

		$get_mid = strlen( $movie->imdbid() ) > 0 ? strval( $movie->imdbid() ) : null;

		if ( $get_mid === null || $get_mid === '0' ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $get_mid );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			ucfirst( Get_Options::get_all_fields( /* no number because no plural here */ )[ $item_name ] )
		);

		$output .= $this->link_maker->get_source( $get_mid );

		return $output;
	}

	/**
	 * Display the Popup version of the module
	 *
	 * @param 'source' $item_name The name of the item
	 * @param string $get_mid
	 */
	public function get_module_popup( string $item_name, string $get_mid ): string {

		$output = $this->output_class->misc_layout(
			'popup_subtitle_item',
			ucfirst( Get_Options::get_all_fields( /* no number because no plural here */ )[ $item_name ] )
		);

		$output .= $this->link_maker->get_source( $get_mid );

		return $output;
	}
}
