<?php declare( strict_types = 1 );
/**
 * Text that will be displayed on frontend only
 * @since 4.7 New file
 */
namespace Lumiere;

use Lumiere\Frontend\Calendar\Coming_Soon;

if ( isset( $attributes['region'], $attributes['type'], $attributes['startDateOverride'], $attributes['endDateOverride'] ) ) {
	Coming_Soon::init(
		strtoupper( $attributes['region'] ),
		strtoupper( $attributes['type'] ),
		intval( $attributes['startDateOverride'] ),
		intval( $attributes['endDateOverride'] )
	);
}
