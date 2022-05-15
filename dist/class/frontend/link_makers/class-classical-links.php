<?php declare( strict_types = 1 );
/**
 * Class to use highslide plugin
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Settings;

class Classical_Links {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();


		// Registers javascripts and styles.
		add_action( 'init', [ $this, 'lumiere_classic_register_assets' ], 0 );

		// Execute javascripts and styles only if the vars in lumiere_highslide_options were not already enqueued
		// (prevents a bug if the vars are displayed twice, the popup doesn't open).
		add_action(
			'wp_enqueue_scripts',
			function (): void {
				if ( !wp_script_is( 'lumiere_highslide_options', 'enqueued' ) ) {
					$this->lumiere_classic_execute_assets();
				}
			},
			0
		);

	}

	/**
	 *  Register frontpage scripts and styles
	 *
	 */
	public function lumiere_classic_register_assets(): void {

		wp_register_script(
			'lumiere_highslide_options',
			$this->config_class->lumiere_js_dir . 'highslide-options.min.js',
			[ 'lumiere_highslide' ],
			$this->config_class->lumiere_version,
			true
		);

	}
	/**
	 * Add javascript values to the frontpage
	 *
	 */
	public function lumiere_classic_execute_assets (): void {

		// Pass variables to javascript highslide-options.js.
		wp_add_inline_script(
			'lumiere_highslide_options',
			$this->config_class->lumiere_scripts_highslide_vars,
			'before',
		);

	}

	/**
	 * Build link to popup for IMDb people
	 * @param array<int, array<string, string>> $imdb_data_people
	 * @param int $number
	 * @return string
	 */
	public function lumiere_link_popup_people ( array $imdb_data_people, int $number ): string {

		return "\n\t\t\t" . '<a class="linkincmovie link-imdblt-classicpeople" data-highslidepeople="' . sanitize_text_field( $imdb_data_people[ $number ]['imdb'] ) . '" title="' . esc_html__( 'Link to local IMDb', 'lumiere-movies' ) . '">' . sanitize_text_field( $imdb_data_people[ $number ]['name'] ) . '</a>';

	}

}
