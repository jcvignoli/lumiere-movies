<?php declare( strict_types = 1 );
/**
 * Text that will be displayed on frontend only
 * @since 4.7 New file
 */
namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Coming_Soon;

if ( isset( $attributes['region'], $attributes['type'], $attributes['startDateOverride'], $attributes['endDateOverride'] ) ) {
	$lumiere_date_format_override = isset( $attributes['dateFormatOverride'] ) && strlen( $attributes['dateFormatOverride'] ) > 0 && $attributes['dateFormatOverride'] !== 'WordPress format'
		? $attributes['dateFormatOverride']
		: null;
	Coming_Soon::init(
		strtoupper( $attributes['region'] ), // Countries are in lowercase in js
		strtoupper( $attributes['type'] ),
		intval( $attributes['startDateOverride'] ),
		intval( $attributes['endDateOverride'] ),
		$lumiere_date_format_override
	);
}
