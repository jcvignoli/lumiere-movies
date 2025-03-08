<?php declare( strict_types = 1 );
/**
 * Class for displaying movies module Title.
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

/**
 * Method to display title for movies
 *
 * @since 4.4.3 new class
 */
class Movie_Title {

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
	 * Display the title and possibly the year
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'title' $item_name The name of the item
	 */
	public function get_module( Title $movie, string $item_name ): string {

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
			'frontend_title',
			$title . $year_text
		);
	}
}
