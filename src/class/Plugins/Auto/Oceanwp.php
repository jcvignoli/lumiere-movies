<?php declare( strict_types = 1 );
/**
 * Class for OceanWP
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Plugins\Auto;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;

/**
 * Plugin to ensure Lumiere compatibility with OceanWP plugin
 * The styles/scripts are supposed to go in construct with add_action()
 * Can method get_active_plugins() to get an extra property $active_plugins, as available in {@link Plugins_Start::activate_plugins()}
 * Executed in Frontend only
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 */
class Oceanwp {

	/**
	 * URL to css assets
	 */
	private string $assets_css_url;

	/**
	 * URL to css assets
	 */
	private string $assets_css_path;

	/**
	 * Lumière Admin options.
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 * @var array<string, string>
	 */
	private array $imdb_admin_values;

	/**
	 * Constructor
	 */
	final public function __construct() {

		// Get the values from database.
		$this->imdb_admin_values = get_option( Get_Options::get_admin_tablename() );

		// Build the css URL.
		$this->assets_css_url = LUM_WP_URL . 'assets/css';
		$this->assets_css_path = LUM_WP_PATH . 'assets/css';

		// Remove conflicting assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'remove_oceanwp_assets' ] );

		// Add extra assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_oceanwp_assets' ], 9 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_oceanwp_assets' ], 9 );
	}

	/**
	 * Remove unwanted OceanWP assets
	 *
	 * @return void Scripts and Styles are deregistered
	 */
	public function remove_oceanwp_assets(): void {

		$styles_deregister = [];
		$scripts_deregister = [];

		// If Highslide modal window is active, remove competing scripts and stylesheets.
		if ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'highslide' ) {

			$styles_deregister = [
				'magnific-popup',
			];

			$scripts_deregister = [
				'magnific-popup',
				'oceanwp-lightbox',
				'ow-magnific-popup',
			];
		}

		foreach ( $scripts_deregister as $script ) {
			if ( wp_script_is( $script, 'registered' ) === true ) {
				wp_deregister_script( $script );
			}
		}

		foreach ( $styles_deregister as $style ) {
			if ( wp_style_is( $style, 'registered' ) === true ) {
				wp_deregister_style( $style );
			}
		}

	}

	/**
	 * Register special assets for OceanWP
	 */
	public function register_oceanwp_assets(): void {

		// Register OceanWP theme fixes for popups only.
		wp_register_style(
			'lumiere_style_oceanwpfixes_popups',
			$this->assets_css_url . '/lumiere-subpages-oceanwpfixes.min.css',
			[],
			strval( filemtime( $this->assets_css_path . '/lumiere-subpages-oceanwpfixes.min.css' ) )
		);

		// Register OceanWP theme fixes for all pages but popups.
		wp_register_style(
			'lumiere_style_oceanwpfixes_general',
			$this->assets_css_url . '/lumiere-extrapages-oceanwpfixes.min.css',
			[],
			strval( filemtime( $this->assets_css_path . '/lumiere-extrapages-oceanwpfixes.min.css' ) )
		);
	}

	/**
	 * Enqueue special assets for OceanWP
	 */
	public function enqueue_oceanwp_assets(): void {

		// OceanWP template css fix for popups only.
		if (
			stripos( get_template_directory_uri(), esc_url( site_url() . '/wp-content/themes/oceanwp' ) ) === 0
			&&
			str_contains( esc_url_raw( wp_unslash( strval( $_SERVER['REQUEST_URI'] ?? '' ) ) ), site_url( '', 'relative' ) . '/lumiere/' )
		) {

			wp_enqueue_style( 'lumiere_style_oceanwpfixes_popups' );
		}

		// All other cases.
		wp_enqueue_style( 'lumiere_style_oceanwpfixes_general' );
	}

	/**
	 * Remove Popup assets
	 * Kept for the records, there is no documentation about it
	 * @deprecated 4.0 The popup construction is now done with 'the_posts' instead of the 'content', not calling theme specifics anymore
	 */
	public function remove_popup_assets(): void {

		remove_action( 'after_setup_theme', [ '\OCEANWP_Theme_Class', 'classes' ], 4 );
		remove_action( 'after_setup_theme', [ '\OCEANWP_Theme_Class', 'theme_setup' ], 10 );
		remove_action( 'widgets_init', [ '\OCEANWP_Theme_Class', 'register_sidebars' ] );
		remove_action( 'wp_enqueue_scripts', [ '\OCEANWP_Theme_Class', 'theme_css' ] );
	}
}

