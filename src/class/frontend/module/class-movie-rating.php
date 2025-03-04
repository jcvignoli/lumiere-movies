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
 * Method to display Rating for movies
 * Uses Link_Maker class
 *
 * @since 4.4.3 new class
 */
class Movie_Rating {

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
	 * Display the Rating
	 *
	 * @param Title $movie IMDbPHP title class
	 * @param 'rating' $item_name The name of the item, ie 'director', 'writer'
	 */
	public function get_module( Title $movie, string $item_name ): string {

		$votes_sanitized = intval( $movie->votes() );
		$rating_sanitized = intval( $movie->$item_name() );

		if ( $votes_sanitized === 0 ) {
			return '';
		}

		/**
		 * Use links builder classes.
		 * Each one has its own class passed in $link_maker,
		 * according to which option the lumiere_select_link_maker() found in Frontend.
		 */
		return $this->link_maker->lumiere_movies_rating_picture( // From trait Main.
			$rating_sanitized,
			$votes_sanitized,
			esc_html__( 'vote average', 'lumiere-movies' ),
			esc_html__( 'out of 10', 'lumiere-movies' ),
			esc_html__( 'votes', 'lumiere-movies' )
		);
	}
}
