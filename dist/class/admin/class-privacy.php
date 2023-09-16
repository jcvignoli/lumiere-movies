<?php declare( strict_types = 1 );
/**
 * Policy: Display the policy related to this plugin
 *
 * @author      Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright   2023, Lost Highway
 *
 * @version     1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

/**
 * A policy option is added to wp-admin/options-privacy.php?tab=policyguide
 * The text is saved in WP database, and automatically displayed as updated should it change
 */
class Privacy {

	/**
	 * Return the default suggested privacy policy content.
	 *
	 * @return string The default policy content.
	 */
	private static function lumiere_get_default_privacy_content(): string {
		return '<h2>' . __( 'What personal data does Lumière Movies plugin collect?', 'lumiere-movies' ) . '</h2>' .
		/* translators: %s: IMDb website */
		'<p>' . __( 'Although we are bound ourselves to the <a href="%s">IMDb privacy policy</a> when retrieving information from IMDb, Lumière Movies WordPress plugin does not collect data by itself. You remain anonymous to IMDb by visiting our website, your data is not sent to any third-party.', 'lumiere-movies' ) . '</p>';
	}

	/**
	 * Return the default suggested privacy policy content.
	 *
	 * @return void The default policy content has been added to WP policy page
	 */
	public static function lumiere_privacy_declarations(): void {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {

				$content = sprintf(
					self::lumiere_get_default_privacy_content(),
					'https://www.imdb.com/privacy'
				);

			wp_add_privacy_policy_content(
				__( 'Lumiere! Movies', 'lumiere-movies' ),
				wp_kses_post( wpautop( $content ) )
			);
		}
	}
}
