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
use Lumiere\Tools\Settings_Global;
use Lumiere\Plugins\Logger;
use Lumiere\Updates;

/**
 * Main WordPress actions happen here
 * Calling all actions and filters
 */
class Core {

	// Trait including the database settings.
	use Settings_Global;

	/**
	 * Lumiere\Plugins\Logger class
	 *
	 */
	private Logger $logger;

	/**
	 * Constructor
	 */
	public function __construct () {

		// Get Global Settings class properties.
		$this->get_settings_class();
		$this->get_db_options();

		// Start Logger class.
		$this->logger = new Logger( 'coreClass' );

		// redirect popups URLs.
		add_action( 'init', fn() => Alteration\Rewrite_Rules::lumiere_static_start(), 0 );
		add_action( 'init', fn() => Alteration\Redirect_Virtual_Page::lumiere_static_start(), 1 );

		/**
		 * Admin actions.
		 * Must be called before with the highest priority.
		 */
		add_action( 'init', fn() => Admin::lumiere_static_start(), 0 );

		// Add taxonomy to Lumière!
		add_action( 'registered_taxonomy', fn() => Alteration\Taxonomy::lumiere_static_start(), 0 );

		/**
		 * Frontpage.
		 */

		// Registers javascripts and styles.
		add_action( 'init', [ $this, 'lumiere_register_assets' ], 0 );

		// Execute javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_frontpage_execute_assets' ], 9 );
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_frontpage_execute_assets_priority' ], 0 );

		// Register Gutenberg blocks.
		add_action( 'init', [ $this, 'lumiere_register_gutenberg_blocks' ] );

		// Frontpage classes if it is not an admin page
		if ( ! is_admin() ) {
			add_action( 'init', fn() => Frontend\Movie::lumiere_static_start(), 0 );
			add_action( 'init', fn() => Frontend\Widget_Frontpage::lumiere_widget_frontend_start(), 0 );
			add_action( 'init', fn() => Tools\Ban_Bots::lumiere_static_start(), 0 );
		}

		/**
		 * Updates & Crons. Must be free of any conditions.
		 */

		// On updating lumiere plugin.
		add_action( 'automatic_updates_complete', [ $this, 'lumiere_on_lumiere_upgrade_autoupdate' ], 10, 1 );
		add_action( 'upgrader_process_complete', [ $this, 'lumiere_on_lumiere_upgrade_manual' ], 10, 2 );

		// Crons schedules.
		add_action( 'init', fn() => Admin\Cron::lumiere_cron_start(), 0 );

		// Call the plugin translation
		load_plugin_textdomain( 'lumiere-movies', false, plugin_dir_path( __DIR__ ) . 'languages/' );
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

	}

	/**
	 * Register gutenberg blocks
	 *
	 * @since 4.0.3 Using block.json, added script translation
	 * @see \Lumiere\Admin\Widget_Selection::lumiere_register_widget_block() which registers gutenberg widget blocks
	 */
	public function lumiere_register_gutenberg_blocks(): void {
		$blocks = [ 'movie', 'addlink', 'opensearch' ];
		foreach ( $blocks as $block ) {
			register_block_type_from_metadata( dirname( __DIR__ ) . '/assets/blocks/' . $block );
			wp_set_script_translations( 'lumiere-' . $block . '-editor-script-js', 'lumiere-movies', plugin_dir_path( __DIR__ ) . 'languages/' );
		}
	}

	/**
	 * Execute Frontpage stylesheets & javascripts.
	 */
	public function lumiere_frontpage_execute_assets(): void {

		// Use local template lumiere.css if there is one in current theme folder.
		if ( file_exists( get_template_directory() . '/lumiere.css' ) ) { // a lumiere.css exists inside theme folder, use it!
			wp_enqueue_style( 'lumiere_style_custom' );

		} else {
			wp_enqueue_style( 'lumiere_style_main' );
		}

		wp_enqueue_script( 'lumiere_hide_show' );

		/**
		 * Pass variables to javascript lumiere_scripts.js.
		 * These variables contains popup sizes, color, paths, etc.
		 */
		wp_add_inline_script(
			'lumiere_scripts',
			$this->config_class->lumiere_scripts_vars,
		);
	}

	/**
	 * Execute lumiere_scripts Frontpage javascript.
	 * This must be run in 0 priority, otherwise wp_add_inline_script() in lumiere_frontpage_execute_assets() doesn't get the vars
	 * @since 4.0.3
	 */
	public function lumiere_frontpage_execute_assets_priority(): void {

		// Do not enqueue it more than once.
		if ( wp_script_is( 'lumiere_scripts', 'enqueued' ) ) {
			return;
		}

		wp_enqueue_script( 'lumiere_scripts' );

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
}

