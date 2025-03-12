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

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Method to display Rating for movies
 * Uses Link_Maker class
 *
 * @since 4.5 new class
 */
class Movie_Rating extends \Lumiere\Frontend\Module\Parent_Module {

	/**
	 * Display the Rating
	 *
	 * @param \Imdb\Title $movie IMDbPHP title class
	 * @param 'rating' $item_name The name of the item, ie 'director', 'writer'
	 */
	public function get_module( \Imdb\Title $movie, string $item_name ): string {

		$votes_sanitized = intval( $movie->votes() );
		$rating_sanitized = intval( $movie->$item_name() );

		if ( $votes_sanitized === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( $votes_sanitized, $rating_sanitized );
		}

		return $this->link_maker->get_rating_picture( // From trait Main.
			$rating_sanitized,
			$votes_sanitized,
			__( 'vote average', 'lumiere-movies' ),
			__( 'out of 10', 'lumiere-movies' ),
			__( 'votes', 'lumiere-movies' )
		);
	}

	/**
	 * Display the Popup version of the module
	 * Array of results is sorted by column
	 *
	 * @param int $votes_sanitized Then number of votes
	 * @param int $rating_sanitized
	 */
	public function get_module_popup( int $votes_sanitized, int $rating_sanitized ): string {

		return $this->link_maker->get_rating_picture( // From trait Main.
			$rating_sanitized,
			$votes_sanitized,
			__( 'vote average', 'lumiere-movies' ),
			__( 'out of 10', 'lumiere-movies' ),
			__( 'votes', 'lumiere-movies' )
		);
	}
}
