<?php declare( strict_types = 1 );
/**
 * Modal Type Enum
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
 * Types of modal windows available for popups.
 */
enum Modal_Type: string {
	case BOOTSTRAP = 'bootstrap';
	case HIGHSLIDE = 'highslide';
	case CLASSIC   = 'classic';
	case NO_LINKS  = 'nolinks';
	case AMP       = 'amp';

	/**
	 * Get the enum case from a string.
	 *
	 * @param string $value
	 * @return self
	 */
	public static function from_string( string $value ): self {
		return match ( $value ) {
			'bootstrap' => self::BOOTSTRAP,
			'highslide' => self::HIGHSLIDE,
			'classic'   => self::CLASSIC,
			default     => throw new \ValueError( 'Lumière Movies: Unknown modal type ' . $value ),
		};
	}
}

