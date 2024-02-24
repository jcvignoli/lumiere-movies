<?php declare( strict_types = 1 );
/**
 * Core Class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       3.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Admin\Cache_Tools;
use Lumiere\PluginsDetect;
use Lumiere\Plugins\Amp;
use Lumiere\Plugins\Logger;
use Lumiere\Plugins\Polylang;
use Lumiere\Updates;

/**
 * Main WordPress actions happen here
 * Calling all actions and filters
 */
class Core {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Lumiere\Plugins\Logger class
	 *
	 */
	private Logger $logger;

	/**
	 * Constructor
	 */
	public function __construct () {

		// Construct Global Settings trait.
		$this->settings_open();

		// Start Logger class.
		$this->logger = new Logger( 'coreClass' );

		// redirect popups URLs.
		add_action( 'init', [ 'Lumiere\Alteration\Rewrite_Rules', 'lumiere_static_start' ], 0 );
		add_action( 'init', [ 'Lumiere\Alteration\Redirect_Virtual_Page', 'lumiere_static_start' ], 1 );

		/**
		 * Admin actions.
		 * Must be called before init, as an init, 0 is called.
		 */
		add_action( 'set_current_user', [ 'Lumiere\Admin', 'lumiere_static_start' ], 0 );

		// Add taxonomy to Lumière!
		add_action( 'registered_taxonomy', [ 'Lumiere\Alteration\Taxonomy', 'lumiere_static_start' ], 0 );

		/**
		 * Frontpage.
		 */

		// Registers javascripts and styles.
		add_action( 'init', [ $this, 'lumiere_register_assets' ], 0 );

		// Execute javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_frontpage_execute_assets' ], 0 );

		// Register Gutenberg blocks.
		add_action( 'init', [ $this, 'lumiere_register_gutenberg_blocks' ] );

		// Frontpage classes if it is not an admin page
		if ( ! is_admin() ) {
			add_action( 'init', [ 'Lumiere\Frontend\Movie', 'lumiere_static_start' ], 0 );
			add_action( 'init', [ 'Lumiere\Frontend\Widget_Frontpage', 'lumiere_widget_frontend_start' ], 0 );
			add_action( 'init', [ 'Lumiere\Tools\Ban_Bots', 'lumiere_static_start' ], 0 );
		}

		// AMP remove headers if AMP is active.
		add_action(
			'wp',
			function(): void {
				$pluginsdetect_class = new PluginsDetect();
				if (
					count( $pluginsdetect_class->plugins_class ) > 0
					&& in_array( 'AMP', $pluginsdetect_class->plugins_class, true )
				) {
					$amp_class = new Amp();
					$amp_class->lumiere_amp_remove_header();
				}
			}
		);

		/**
		 * Updates & Crons. Must be free of any conditions.
		 */

		// On updating lumiere plugin.
		add_action( 'automatic_updates_complete', [ $this, 'lumiere_on_lumiere_upgrade_autoupdate' ], 10, 1 );
		add_action( 'upgrader_process_complete', [ $this, 'lumiere_on_lumiere_upgrade_manual' ], 10, 2 );

		// Crons schedules.
		add_action( 'init', [ 'Lumiere\Admin\Cron', 'lumiere_cron_start' ], 0 );

	}

	/**
	 *  Register frontpage scripts and styles
	 */
	public function lumiere_register_assets(): void {

		// Register hide/show script
		wp_register_script(
			'lumiere_hide_show',
			$this->config_class->lumiere_js_dir . 'lumiere_hide_show.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			true
		);

		// Register frontpage script
		wp_register_script(
			'lumiere_scripts',
			$this->config_class->lumiere_js_dir . 'lumiere_scripts.min.js',
			[],
			$this->config_class->lumiere_version,
			true
		);

		// Register customised main style, located in active theme directory
		if ( file_exists( get_stylesheet_directory_uri() . '/lumiere.css' ) ) {
			wp_register_style(
				'lumiere_style_custom',
				get_stylesheet_directory_uri() . '/lumiere.css',
				[],
				$this->config_class->lumiere_version
			);
		}

		// Register main style
		wp_register_style(
			'lumiere_style_main',
			$this->config_class->lumiere_css_dir . 'lumiere.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Register OceanWP theme fixes for popups only
		wp_register_style(
			'lumiere_style_oceanwpfixes_popups',
			$this->config_class->lumiere_css_dir . 'lumiere-subpages-oceanwpfixes.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Register OceanWP theme fixes for all pages but popups
		wp_register_style(
			'lumiere_style_oceanwpfixes_general',
			$this->config_class->lumiere_css_dir . 'lumiere-extrapages-oceanwpfixes.min.css',
			[],
			$this->config_class->lumiere_version
		);

	}

	/**
	 * Register gutenberg blocks
	 *
	 * @TODO update the registration using the new WP way https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/
	 * @see \Lumiere\Admin\Widget_Selection::lumiere_register_widget_block() which registers gutenberg widget blocks
	 */
	public function lumiere_register_gutenberg_blocks(): void {

		wp_register_script(
			'lumiere_gutenberg_main',
			$this->config_class->lumiere_blocks_dir . 'editor/index.min.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ],
			$this->config_class->lumiere_version,
			false
		);

		wp_register_script(
			'lumiere_gutenberg_buttons',
			$this->config_class->lumiere_blocks_dir . 'editor/buttons.min.js',
			[ 'wp-element', 'wp-compose', 'wp-components', 'wp-i18n', 'wp-data' ],
			$this->config_class->lumiere_version,
			false
		);

		wp_register_style(
			'lumiere_gutenberg_main',
			$this->config_class->lumiere_blocks_dir . 'editor/index.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Register block script and style.
		register_block_type(
			'lumiere/main',
			[
				'editor_style_handles' => [ 'lumiere_gutenberg_main' ],
				'editor_script_handles' => [ 'lumiere_gutenberg_main' ], // Loads only on editor.
			]
		);

		register_block_type(
			'lumiere/buttons',
			[
				'editor_script_handles' => [ 'lumiere_gutenberg_buttons' ], // Loads only on editor.
			]
		);

	}

	/**
	 * Add the stylesheet & javascript to frontpage.
	 */
	public function lumiere_frontpage_execute_assets(): void {

		// Use local template lumiere.css if there is one in current theme folder.
		if ( file_exists( get_template_directory() . '/lumiere.css' ) ) { // a lumiere.css exists inside theme folder, use it!
			wp_enqueue_style( 'lumiere_style_custom' );

		} else {
			wp_enqueue_style( 'lumiere_style_main' );
		}

		// OceanWP template css fix.
		// Enqueues lumiere.css only if using oceanwp template.
		// Popups.
		if (
			( 0 === stripos( get_template_directory_uri(), esc_url( site_url() . '/wp-content/themes/oceanwp' ) ) )
			&&
			( str_contains( $_SERVER['REQUEST_URI'] ?? '', site_url( '', 'relative' ) . $this->config_class->lumiere_urlstring ) )
		) {

			wp_enqueue_style( 'lumiere_style_oceanwpfixes_popups' );

			// All other cases.
		} elseif ( 0 === stripos( get_template_directory_uri(), esc_url( site_url() . '/wp-content/themes/oceanwp' ) ) ) {

			wp_enqueue_style( 'lumiere_style_oceanwpfixes_general' );

		}

		wp_enqueue_script( 'lumiere_hide_show' );

		if ( wp_script_is( 'lumiere_scripts', 'enqueued' ) ) {
			return;
		}

		wp_enqueue_script( 'lumiere_scripts' );

		/**
		 * Pass variables to javascript lumiere_scripts.js.
		 * These variables contains popup sizes, color, paths, etc.
		 */
		wp_add_inline_script(
			'lumiere_scripts',
			$this->wrap_lumiere_script(),
			'before'
		);
	}

	/**
	 * Run on lumiere WordPress manual upgrade
	 *
	 * @param \WP_Upgrader $upgrader_object Upgrader class
	 * @param mixed[] $options Type of update process, such as 'plugin', 'theme', 'translation' or 'core'
	 */
	public function lumiere_on_lumiere_upgrade_manual( \WP_Upgrader $upgrader_object, array $options ): void {

		// Start the logger.
		do_action( 'lumiere_logger' );

		// If an update has taken place and the updated type is plugins and the plugins element exists.
		if ( $options['type'] === 'plugin' && $options['action'] === 'update' && isset( $options['plugins'] ) ) {

			// Iterate through the plugins being updated and check if ours is there.
			foreach ( $options['plugins'] as $plugin ) {

				// It is Lumière!, so run the functions.
				if ( $plugin === 'lumiere-movies/lumiere-movies.php' ) {

					$start_update_options = new Updates();
					$start_update_options->run_update_options();

					$this->logger->log()->debug( '[Lumiere][coreClass][manualupdate] Lumière manual update successfully run.' );

				}
			}
		}
	}

	/**
	 * Run on Lumiere! WordPress auto upgrade
	 *
	 * @param array<string, array<int, object>> $results Array of plugins updated
	 * @return void Plugin updated, log about success or not
	 */
	public function lumiere_on_lumiere_upgrade_autoupdate( array $results ): void {

		// Start the logger.
		do_action( 'lumiere_logger' );

		// Exit if not exist.
		if ( ! isset( $results['plugin'] ) ) {
			return;
		}

		// Iterate through the plugins being updated and check if ours is there.
		foreach ( $results['plugin'] as $plugin ) {

			if (
				// @phpstan-ignore-next-line Access to an undefined property object::$item
				isset( $plugin->item->slug )
				&& strlen( $plugin->item->slug ) > 0
				&& $plugin->item->slug === 'lumiere-movies'
			) {

				// It is Lumière!, so run the functions.
				$this->logger->log()->debug( '[Lumiere][coreClass][autoupdate] Starting Lumière autoupdate...' );
				$start_update_options = new Updates();
				$start_update_options->run_update_options();
				$this->logger->log()->debug( '[Lumiere][coreClass][autoupdate] Lumière autoupdate successfully run.' );
			}

		}
	}

	/**
	 * Run on plugin activation
	 * @return void all activation functions have been executed
	 */
	public function lumiere_on_activation(): void {

		// Start the logger.
		$this->logger->lumiere_start_logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		$current_admin = get_option( Settings::LUMIERE_CACHE_OPTIONS );

		/* Create the value of number of updates on first install */
		// Start Settings class.
		if ( ! isset( $current_admin['imdbHowManyUpdates'] ) ) {

			$settings_class = new Settings();
			$this->logger->log()->info( "[Lumiere][coreClass][activation] Lumière option 'imdbHowManyUpdates' successfully created." );
		} else {
			$this->logger->log()->info( "[Lumiere][coreClass][activation] Lumière option 'imdbHowManyUpdates' already exists." );
		}

		/* Create the cache folders */

		// Make sure cache folder exists and is writable
		$cache_tools_class = new Cache_Tools();

		if ( $cache_tools_class->lumiere_create_cache() === true ) {
			$this->logger->log()->info( '[Lumiere][coreClass][activation] Lumière cache successfully created.' );
		} else {
			$this->logger->log()->info( '[Lumiere][coreClass][activation] Lumière cache has not been created (maybe already existed?)' );
		}

		/* Set up WP Cron exec once if it doesn't exist */
		if ( wp_next_scheduled( 'lumiere_cron_exec_once' ) === false ) {
			// Cron to run once, in 2 minutes.
			wp_schedule_single_event( time() + 120, 'lumiere_cron_exec_once' );
			$this->logger->log()->debug( '[Lumiere][coreClass][activation] Lumière cron lumiere_cron_exec_once successfully set up.' );
		} else {
			$this->logger->log()->error( '[Lumiere][coreClass][activation] Cron lumiere_cron_exec_once was not set up (maybe was not active?)' );
		}

		$this->logger->log()->debug( '[Lumiere][coreClass][activation] Lumière plugin activated.' );
	}

	/**
	 * Run on plugin deactivation
	 * @return void all deactivation functions have been executed
	 */
	public function lumiere_on_deactivation(): void {

		// Start the logger.
		$this->logger->lumiere_start_logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		// Remove WP lumiere crons should they exist.
		$list_crons_available = [ 'lumiere_cron_exec_once', 'lumiere_cron_deletecacheoversized', 'lumiere_cron_autofreshcache' ];
		foreach ( $list_crons_available as $cron_installed ) {
			wp_clear_scheduled_hook( $cron_installed );
			$this->logger->log()->info( '[Lumiere][coreClass][deactivation] Cron ' . $cron_installed . ' removed' );
		}

		// Reset options related to crons, since we removed them.
		$current_admin = get_option( Settings::LUMIERE_CACHE_OPTIONS );
		$current_admin['imdbcacheautorefreshcron'] = '0';
		$current_admin['imdbcachekeepsizeunder'] = '0';
		update_option( Settings::LUMIERE_CACHE_OPTIONS, $current_admin );

		$this->logger->log()->info( '[Lumiere][coreClass][deactivation] Lumière deactivated' );
	}

	/**
	 * Wrap the lumiere script
	 * Currenty replaces the home_url() in popups with pll_home_url for use with Polylang
	 * The $lumiere_scripts_vars is a var in class Settings that can't be changed (executed too early)
	 */
	private function wrap_lumiere_script(): string {

		$polylang_class = new Polylang();
		$final_lumiere_script =
			$polylang_class->polylang_is_active() === true
			? $polylang_class->rewrite_string_with_polylang_url(
				$this->config_class->lumiere_scripts_vars,
				$this->imdb_admin_values['imdburlpopups']
			)
			: $this->config_class->lumiere_scripts_vars;
		return $final_lumiere_script;
	}
}

