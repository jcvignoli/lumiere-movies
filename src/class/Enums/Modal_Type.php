<?php
/**
 * Modal Type Enum
 *
 * @copyright (c) 2026, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
declare( strict_types = 1 );

namespace Lumiere\Enums;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
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
	 * @param string|null $value The modal type value string.
	 *
	 * @return self The corresponding Modal_Type instance.
	 *
	 * @see \Lumiere\Frontend\Link_Maker\Link_Factory::select_link_maker() For how modal types are converted into link maker classes.
	 */
	public static function from_string( ?string $value ): self {
		return match ( $value ) {
			'bootstrap' => self::BOOTSTRAP,
			'highslide' => self::HIGHSLIDE,
			'classic'   => self::CLASSIC,
			'nolinks'   => self::NO_LINKS,
			'amp'       => self::AMP,
			null        => self::BOOTSTRAP,
			default     => throw new \ValueError( 'Lumière Movies: Unknown modal type ' . esc_html( $value ) ),
		};
	}
}
