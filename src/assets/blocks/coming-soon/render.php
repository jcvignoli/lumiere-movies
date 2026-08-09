<?php
/**
 * Text that will be displayed on frontend only
 * @since 4.7 New file
 */
declare( strict_types = 1 );

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Coming_Soon;
use Lumiere\Frontend\Link_Maker\Link_Factory;
use Lumiere\Config\Settings_Service;

if ( isset( $attributes['region'], $attributes['type'], $attributes['startDateOverride'], $attributes['endDateOverride'] ) ) {
	$lumiere_date_format_override = isset( $attributes['dateFormatOverride'] ) && strlen( $attributes['dateFormatOverride'] ) > 0 && $attributes['dateFormatOverride'] !== 'WordPress format'
		? $attributes['dateFormatOverride']
		: null;
	try {
		$lumiere_settings_service = new Settings_Service();
		$lumiere_link_maker = ( new Link_Factory( $lumiere_settings_service ) )->select_link_maker();
		$lumiere_coming_soon = new Coming_Soon( link_maker: $lumiere_link_maker );
		$lumiere_coming_soon->init(
			strtoupper( $attributes['region'] ), // Countries are in lowercase in js
			strtoupper( $attributes['type'] ),
			intval( $attributes['startDateOverride'] ),
			intval( $attributes['endDateOverride'] ),
			$lumiere_date_format_override
		);
	} catch ( \Throwable $e ) {
		// Log the error to debug.log
		// error_log( 'Lumiere Movies Block Error: ' . $e->getMessage() );

		// Optionally display a friendly notice if the user is an admin editing the site
		if ( current_user_can( 'edit_posts' ) ) {
			echo '<div class="lumiere-error-notice" style="padding:10px; background:#fff3cd; color:#856404; border:1px solid #ffeeba;">' .
				 '<strong>Lumière Movies Notice:</strong> Unable to load upcoming movies from IMDb at this time.' .
				 '</div>';
		}
	}
}
