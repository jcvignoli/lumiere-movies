<?php declare( strict_types = 1 );
/**
 * Item Type Enum
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
 * Types of items (Movie or Person).
 */
enum Item_Type: string {
	case MOVIE  = 'movie';
	case PERSON = 'person';

	/**
	 * Get the enum case from a string.
	 *
	 * @param string|null $value
	 * @return self
	 */
	public static function from_string( ?string $value ): self {
		return match ( $value ) {
			'person', 'people' => self::PERSON,
			default            => self::MOVIE,
		};
	}
}

