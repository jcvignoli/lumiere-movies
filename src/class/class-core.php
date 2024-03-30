<?php declare( strict_types = 1 );
/**
 * Core Class
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       4.0
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
	 */
	private Logger $logger;

	/**
	 * Constructor
	 */
	public function __construct () {

		// Get Global Settings class properties.
		$this->get_db_options();

		// Start Logger class.
		$this->logger = new Logger( 'coreClass' );

		/**
		 * Widgets fire at init priority 0, so must either be called here with widgets_init or with init priority 0
		 * https://developer.wordpress.org/reference/hooks/widgets_init/#comment-2643
		 * They're not only for admin area, since they're executed in the frontpage as well
		 */
		add_action( 'widgets_init', fn() => Admin\Widget_Selection::lumiere_static_start() );

		/**
		 * Taxonomy, must be executed on the whole website
		 */
		add_action( 'registered_taxonomy', fn() => Alteration\Taxonomy::lumiere_static_start() );

		/**
		 * Rewrite rules, must be executed on the whole website
		 */
		add_action( 'init', fn() => Alteration\Rewrite_Rules::lumiere_static_start() );

		/**
		 * Gutenberg blocks, must be executed on the whole website
		 */
		add_action( 'enqueue_block_editor_assets', [ $this, 'lumiere_register_gutenberg_blocks' ] );

		/**
		 * Admin.
		 */
		add_action( 'init', fn() => Admin\Admin::lumiere_static_start(), 9 );

		/**
		 * Frontpage.
		 */
		add_action( 'init', fn() => Frontend\Frontend::lumiere_static_start() ); // Priority must be below 10.

		/**
		 * Updates & Crons. Must be free of any conditions.
		 */
		// On updating the plugin.
		add_action( 'automatic_updates_complete', [ $this, 'lumiere_on_lumiere_upgrade_autoupdate' ], 10, 1 );
		add_action( 'upgrader_process_complete', [ $this, 'lumiere_on_lumiere_upgrade_manual' ], 10, 2 );

		// Crons schedules.
		add_action( 'init', fn() => Admin\Cron::lumiere_cron_start() );

		// Call the translation.
		load_plugin_textdomain( 'lumiere-movies', false, plugin_dir_path( __DIR__ ) . 'languages/' );
	}

	/**
	 * Register gutenberg blocks, must be executed on the whole website
	 *
	 * @since 4.1 Using block.json, added script translation, added lumiere_scripts_admin_gutenberg script
	 * @see \Lumiere\Admin\Widget_Selection::lumiere_register_widget_block() which registers gutenberg widget blocks
	 */
	public function lumiere_register_gutenberg_blocks(): void {
		$blocks = [ 'movie', 'addlink', 'opensearch' ];
		$block_dir = plugin_dir_path( __DIR__ ) . 'assets/blocks';

		foreach ( $blocks as $block ) {
			register_block_type_from_metadata( $block_dir . '/' . $block );
			wp_set_script_translations( 'lumiere-' . $block . '-editor-script', 'lumiere-movies', plugin_dir_path( __DIR__ ) . 'languages/' );
		}

		// Script for Gutenberg blocks only.
		wp_register_script(
			'lumiere_scripts_admin_gutenberg',
			plugin_dir_url( __DIR__ ) . 'assets/js/lumiere_scripts_admin_gutenberg.min.js',
			[],
			strval( filemtime( plugin_dir_path( __DIR__ ) . 'assets/js/lumiere_scripts_admin_gutenberg.min.js' ) )
		);
		wp_enqueue_script( 'lumiere_scripts_admin_gutenberg' );
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
			$this->logger->log()->info( '[Lumiere][coreClass][activation] Lumière cache has not been created (maybe was already created?)' );
		}

		/* Set up WP Cron exec once if it doesn't exist */
		if ( wp_next_scheduled( 'lumiere_cron_exec_once' ) === false ) {
			// Cron to run once, in 2 minutes.
			wp_schedule_single_event( time() + 120, 'lumiere_cron_exec_once' );
			$this->logger->log()->debug( '[Lumiere][coreClass][activation] Lumière cron lumiere_cron_exec_once successfully set up.' );
		} else {
			$this->logger->log()->error( '[Lumiere][coreClass][activation] Cron lumiere_cron_exec_once was not set up (maybe an issue during activation?)' );
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

