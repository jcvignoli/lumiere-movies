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
 * Method to display Source for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Source {

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
	 * @param 'source' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$get_mid = strlen( $movie->imdbid() ) > 0 ? strval( $movie->imdbid() ) : null;

		if ( $get_mid === null ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $get_mid );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( /* no number because no plural here */ )[ $item_name ] ) )
		);

		$output .= $this->link_maker->lumiere_movies_source_details( $get_mid );

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
			esc_html( ucfirst( Get_Options::get_all_fields( /* no number because no plural here */ )[ $item_name ] ) )
		);

		if ( strlen( $get_mid ) === 0 ) {
			esc_html_e( 'No source found.', 'lumiere-movies' );
		}

		$output = $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( /* no number because no plural here */ )[ $item_name ] ) )
		);

		$output .= $this->link_maker->lumiere_movies_source_details( $get_mid );

		return $output;
	}
}
