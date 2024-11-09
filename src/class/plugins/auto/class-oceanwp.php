<?php declare( strict_types = 1 );
/**
 * Class for OceanWP
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version 1.0
 * @package lumiere-movies
 */

namespace Lumiere\Plugins\Auto;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Settings;

/**
 * Plugin to ensure Lumiere compatibility with OceanWP plugin
 * The styles/scripts are supposed to go in construct with add_action(), the methods can be called with Plugins_Start $this->plugins_classes_active
 *
 * @phpstan-import-type OPTIONS_ADMIN from Settings
 * @see \Lumiere\Plugins\Plugins_Start Class calling if the plugin is activated in \Lumiere\Plugins\Plugins_Detect
 */
class Oceanwp {

	/**
	 * List of plugins active (including current class)
	 * @var array<string> $active_plugins
	 * @phpstan-ignore-next-line -- Property Lumiere\Plugins\Amp::$active_plugins is never read, only written -- want to keep the possibility in the future
	 */
	private array $active_plugins;

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
	 * @param array<string> $active_plugins
	 */
	final public function __construct( array $active_plugins ) {

		// Get the list of active plugins.
		$this->active_plugins = $active_plugins;

		// Get the values from database.
		$this->imdb_admin_values = get_option( Settings::get_compat_admin_tablename() );

		// Build the css URL.
		$this->assets_css_url = plugin_dir_url( dirname( dirname( __DIR__ ) ) ) . 'assets/css';
		$this->assets_css_path = plugin_dir_path( dirname( dirname( __DIR__ ) ) ) . 'assets/css';

		// Remove conflicting assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'remove_oceanwp_assets' ] );

		// Add extra assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_oceanwp_assets' ], 9 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_oceanwp_assets' ], 9 );
	}

	/**
	 * Static start for extra functions not to be run in self::__construct. No $this available!
	 */
	public static function start_init_hook(): void {}

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
			str_contains( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), site_url( '', 'relative' ) . '/lumiere/' )
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

