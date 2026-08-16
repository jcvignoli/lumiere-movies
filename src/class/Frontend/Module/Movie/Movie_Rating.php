<?php
/**
 * Class for displaying movies module Title.
 *
 * @copyright (c) 2025, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Frontend\Module\Movie;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Module\Parent_Module;

/**
 * Method to display Rating for movies
 * @since 4.8.2 Using interface
 *
 * @extends Parent_Module<'rating', array<string, int>, \Lumiere\Vendor\Imdb\Title>
 */
final class Movie_Rating extends Parent_Module {

	/**
	 * Display the Rating
	 * @inherit
	 */
	#[\Override]
	public function get_module( object $imdb_class, string $item_name ): string {
		$array = [
			'votes_sanitized' => intval( $imdb_class->votes() ),
			'rating_sanitized' => intval( $imdb_class->$item_name() ),
		];

		if ( $array['votes_sanitized'] === 0 ) {
			return '';
		}

		if ( $this->is_popup_page() === true ) { // Method in trait Main.
			return $this->get_module_popup( 'rating', $array, 0 );
		}

		return $this->link_maker->get_rating_picture(
			$array['rating_sanitized'],
			$array['votes_sanitized'],
			__( 'vote average', 'lumiere-movies' ),
			__( 'out of 10', 'lumiere-movies' ),
			__( 'votes', 'lumiere-movies' )
		);
	}

	/**
	 * Display the Popup version of the module
	 * @inherit
	 */
	#[\Override]
	public function get_module_popup( string $item_name, array $item_results, int $nb_total_items ): string {
		return $this->link_maker->get_rating_picture(
			$item_results['rating_sanitized'],
			$item_results['votes_sanitized'],
			__( 'vote average', 'lumiere-movies' ),
			__( 'out of 10', 'lumiere-movies' ),
			__( 'votes', 'lumiere-movies' )
		);
	}
}
