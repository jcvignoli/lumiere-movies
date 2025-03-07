<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Runtime.
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
use Lumiere\Config\Get_Options;

/**
 * Method to display Runtime for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Runtime {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Constructor
	 */
	public function __construct(
		protected Output $output_class = new Output(),
	) {
		// Construct Frontend Main trait with options and links.
		$this->start_main_trait();
	}

	/**
	 * Display the Runtime
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'runtime' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$item_results = isset( $movie->$item_name()[0]['time'] ) ? esc_html( strval( $movie->$item_name()[0]['time'] ) ) : '';

		if ( strlen( $item_results ) === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $item_name, $item_results );
		}

		return $this->output_class->misc_layout(
			'frontend_subtitle_item',
			esc_html( ucfirst( Get_Options::get_all_fields( /* no number because no plural here */ )[ $item_name ] ) )
		)
			. $item_results . ' ' . esc_html__( 'minutes', 'lumiere-movies' );
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
			esc_html( ucfirst( Get_Options::get_all_fields( /* no number because no plural here */ )[ $item_name ] ) )
		)
			. $item_results . ' ' . esc_html__( 'minutes', 'lumiere-movies' );
	}
}
