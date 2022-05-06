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

namespace Lumiere\Plugins;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Settings;
use \Lumiere\PluginsDetect;

class Highslide {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * \Lumière\Plugins class
	 * Array of plugins in use
	 *
	 * @since 3.7
	 * @var array<int, string>
	 */
	public array $plugins_in_use = [];

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

		// Registers javascripts and styles.
		add_action( 'init', [ $this, 'lumiere_highslide_register_assets' ], 0 );

		// Execute javascripts and styles only if AMP WP plugin is not active.
		add_action(
			'wp_enqueue_scripts',
			function (): void {
				if ( in_array( 'AMP', $this->plugins_in_use, true ) === false ) {
					$this->lumiere_highslide_execute_assets();
				}
			},
			0
		);

		/* ## Highslide download library, function deactivated upon WordPress plugin team request
		add_filter( 'init', function( $template ) {
			if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . '/wp-admin/admin.php?page=lumiere_options&highslide=yes' ) )
				require_once ( plugin_dir_path( __DIR__ ) . \Lumiere\Settings::HIGHSLIDE_DOWNLOAD_PAGE );

		} );*/

	}

	/**
	 *  Register frontpage scripts and styles
	 *
	 */
	public function lumiere_highslide_register_assets(): void {

		// Register highslide scripts and styles
		wp_register_script(
			'lumiere_highslide',
			$this->config_class->lumiere_js_dir . 'highslide/highslide-with-html.min.js',
			[],
			$this->config_class->lumiere_version,
			true
		);
		wp_register_script(
			'lumiere_highslide_options',
			$this->config_class->lumiere_js_dir . 'highslide-options.min.js',
			[ 'lumiere_highslide' ],
			$this->config_class->lumiere_version,
			true
		);
		wp_enqueue_style(
			'lumiere_highslide',
			$this->config_class->lumiere_css_dir . 'highslide.min.css',
			[],
			$this->config_class->lumiere_version
		);

	}

	/**
	 * Add the stylesheet & javascript to frontpage.
	 *
	 */
	public function lumiere_highslide_execute_assets (): void {

		// Only display assets if highslide is active
		if ( $this->lumiere_is_highslide_active() === true ) {

			wp_enqueue_style( 'lumiere_highslide' );

			wp_enqueue_script( 'lumiere_highslide' );

			// Pass variables to javascript highslide-options.js.
			wp_add_inline_script(
				'lumiere_highslide_options',
				$this->config_class->lumiere_scripts_highslide_vars,
				'before',
			);

			wp_enqueue_script( 'lumiere_highslide_options' );

		}

	}

	/**
	 * Detect if the current plugin is active
	 * @return bool true if active
	 */
	public function lumiere_is_highslide_active (): bool {

		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {

			return true;

		}

		return false;

	}
}
