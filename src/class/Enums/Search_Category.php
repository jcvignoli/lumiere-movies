<?php declare( strict_types = 1 );
/**
 * Search Category Enum
 *
 * @copyright (c) 2026, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
namespace Lumiere\Enums;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * Categories for IMDb search.
 */
enum Search_Category: string {
	case MOVIES        = 'MOVIE';
	case MOVIES_SERIES = 'MOVIE,TV';
	case SERIES        = 'TV';
	case VIDEOGAMES    = 'VIDEO_GAME';
	case PODCASTS      = 'PODCAST_EPISODE';

	/**
	 * Get the enum case from the internal Lumière key.
	 *
	 * @param string $key
	 * @return self
	 */
	public static function from_key( string $key ): self {
		return match ( $key ) {
			'movies'        => self::MOVIES,
			'movies+series' => self::MOVIES_SERIES,
			'series'        => self::SERIES,
			'videogames'    => self::VIDEOGAMES,
			'podcasts'      => self::PODCASTS,
			default         => self::MOVIES_SERIES,
		};
	}
}
