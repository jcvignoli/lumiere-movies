<?php declare( strict_types = 1 );
/**
 * Popup Type Enum
 *
 * @copyright (c) 2026, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Enums;

/**
 * Types of popups available.
 */
enum Popup_Type: string {
	case FILM         = 'film';
	case PERSON       = 'person';
	case MOVIE_SEARCH = 'movie_search';

	/**
	 * Get the enum case from the internal Lumière key.
	 *
	 * @param string $key
	 * @return self
	 */
	public static function from_key( string $key ): self {
		return match ( $key ) {
			'film'         => self::FILM,
			'person'       => self::PERSON,
			'movie_search' => self::MOVIE_SEARCH,
			default        => self::FILM,
		};
	}
}
