<?php declare( strict_types = 1 );
/**
 * Core Class : Main WordPress actions happen here
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

use Lumiere\Admin\Metabox_Selection;
use Lumiere\PluginsDetect;
use Lumiere\Plugins\Amp;
use Lumiere\Plugins\Logger;
use Lumiere\Plugins\Polylang;
use Lumiere\Updates;
use Lumiere\Tools\Utils;

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
	 *
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
		 * Admin interface.
		 */

		if ( is_admin() ) {

			// Add admin menu.
			$lumiere_admin_class = new Admin();
			add_action( 'init', [ $lumiere_admin_class, 'lumiere_admin_menu' ] );

			// Add the metabox to editor.
			$metabox_selection_class = new Metabox_Selection();

			// Widget
			add_action( 'init', [ 'Lumiere\Admin\Widget_Selection', 'lumiere_widget_start' ], 0 );

			// Extra backoffice functions, such as privacy, plugins infos in plugins' page
			add_action( 'init', [ 'Lumiere\Admin\Backoffice_Extra', 'lumiere_backoffice_start' ], 0 );
		}

		// Add taxonomy to Lumière!
		add_action( 'registered_taxonomy', [ 'Lumiere\Alteration\Taxonomy', 'lumiere_static_start' ], 0 );

		// Register admin scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'lumiere_register_admin_assets' ], 0 );

		// Add admin header.
		add_action( 'admin_enqueue_scripts', [ $this, 'lumiere_execute_admin_assets' ] );

		// Add admin tinymce button for wysiwig editor.
		add_action( 'admin_enqueue_scripts', [ $this, 'lumiere_execute_tinymce' ], 2 );

		/**
		 * Frontpage.
		 */

		// Registers javascripts and styles.
		add_action( 'init', [ $this, 'lumiere_register_assets' ], 0 );

		// Execute javascripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'lumiere_execute_assets' ], 0 );

		// Register Gutenberg blocks.
		add_action( 'init', [ $this, 'lumiere_register_gutenberg_blocks' ] );

		// Frontpage classes if it is not an admin page
		if ( ! is_admin() ) {
			add_action( 'init', [ 'Lumiere\Tools\Ban_Bots', 'lumiere_static_start' ], 0 );
			add_action( 'init', [ 'Lumiere\Frontend\Movie', 'lumiere_static_start' ], 0 );
			add_action( 'init', [ 'Lumiere\Frontend\Widget_Frontpage', 'lumiere_widget_frontend_start' ], 0 );
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
	 *
	 */
	public function lumiere_register_assets(): void {

		// Common assets to admin and frontpage
		$this->lumiere_register_both_assets();

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

	/*  Register admin scripts and styles
	 *
	 */
	public function lumiere_register_admin_assets(): void {

		// Common assets to admin and frontpage
		$this->lumiere_register_both_assets();

		// Register paths, fake script to get a hook for add inline scripts
		wp_register_script(
			'lumiere_scripts_admin_vars',
			'',
			[],
			$this->config_class->lumiere_version,
			true
		);

		// Register admin styles
		wp_register_style(
			'lumiere_css_admin',
			$this->config_class->lumiere_css_dir . 'lumiere-admin.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Register admin scripts
		wp_register_script(
			'lumiere_scripts_admin',
			$this->config_class->lumiere_js_dir . 'lumiere_scripts_admin.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			false
		);

		// Register gutenberg admin scripts
		wp_register_script(
			'lumiere_scripts_admin_gutenberg',
			$this->config_class->lumiere_js_dir . 'lumiere_scripts_admin_gutenberg.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			false
		);

		// Register confirmation script upon deactivation
		wp_register_script(
			'lumiere_deactivation_plugin_message',
			$this->config_class->lumiere_js_dir . 'lumiere_admin_deactivation_msg.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			true
		);

		// Quicktag
		wp_register_script(
			'lumiere_quicktag_addbutton',
			$this->config_class->lumiere_js_dir . 'lumiere_admin_quicktags.min.js',
			[ 'quicktags' ],
			$this->config_class->lumiere_version,
			true
		);

	}

	/*  Common assets registration
	 *  For both admin and frontpage utilisation scripts and styles
	 *
	 */
	public function lumiere_register_both_assets(): void {

		// Register hide/show script
		wp_register_script(
			'lumiere_hide_show',
			$this->config_class->lumiere_js_dir . 'lumiere_hide_show.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			true
		);

	}

	/**
	 *  Register TinyMCE
	 * @param string $hook
	 */
	public function lumiere_execute_tinymce( string $hook ): void {

		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Add only in Rich Editor mode for post.php and post-new.php pages
		if (
			( get_user_option( 'rich_editing' ) === 'true' )
			&& ( ( 'post.php' === $hook ) || ( 'post-new.php' === $hook ) )
		) {

			add_filter( 'mce_external_plugins', [ $this, 'lumiere_tinymce_addbutton' ] );
			add_filter( 'mce_buttons', [ $this, 'lumiere_tinymce_button_position' ] );

		}
	}

	/**
	 * Change TinyMCE buttons position
	 * @param mixed[] $buttons
	 * @return mixed[]
	 */
	public function lumiere_tinymce_button_position( array $buttons ): array {

		array_push( $buttons, 'separator', 'lumiere_tiny' );

		return $buttons;

	}

	/**
	 * Add TinyMCE buttons
	 * @param mixed[] $plugin_array
	 * @return mixed[]
	 */
	public function lumiere_tinymce_addbutton( array $plugin_array ): array {

		$plugin_array['lumiere_tiny'] = $this->config_class->lumiere_js_dir . 'lumiere_admin_tinymce_editor.min.js';

		return $plugin_array;

	}

	/**
	 *  Register gutenberg blocks
	 *
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

		wp_register_style( 'lumiere_gutenberg_main', $this->config_class->lumiere_blocks_dir . 'editor/index.min.css', [], $this->config_class->lumiere_version );

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
	 *
	 */
	public function lumiere_execute_assets (): void {

		// Use local template lumiere.css if there is one in current theme folder.
		if ( file_exists( get_template_directory() . '/lumiere.css' ) ) { // a lumiere.css exists inside theme folder, use it!
			wp_enqueue_style( 'lumiere_style_custom' );

		} else {

			wp_enqueue_style( 'lumiere_style_main' );
		}

		// OceanWP template css fix.
		// enqueue lumiere.css only if using oceanwp template.
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
	 *  Add assets of Lumière admin pages
	 *
	 */
	public function lumiere_execute_admin_assets ( string $hook ): void {

		// Load assets only on Lumière admin pages.
		// + WordPress edition pages + Lumière own pages (ie gutenberg search).
		if (
			( 'toplevel_page_lumiere_options' === $hook )
			|| ( 'post.php' === $hook )
			|| ( 'post-new.php' === $hook )
			|| ( 'widgets.php' === $hook )
			// All Lumière pages.
			|| ( Utils::lumiere_array_contains_term( $this->config_class->lumiere_list_all_pages, $_SERVER['REQUEST_URI'] ?? '' ) )
			// Extra WP Admin pages.
			|| ( Utils::lumiere_array_contains_term(
				[
					'admin.php?page=lumiere_options',
					'options-general.php?page=lumiere_options',
				],
				$_SERVER['REQUEST_URI'] ?? ''
			)
				)
		) {

			// Load main css.
			wp_enqueue_style( 'lumiere_css_admin' );

			// Load main js.
			wp_enqueue_script( 'lumiere_scripts_admin' );

			// Pass path variables to javascripts.
			wp_add_inline_script(
				'lumiere_scripts_admin',
				$this->config_class->lumiere_scripts_admin_vars,
				'before'
			);

			// Load hide/show js.
			wp_enqueue_script( 'lumiere_hide_show' );

			// Script for click on gutenberg block link to open a popup, script is loaded but it doesn't work!
			wp_enqueue_script( 'lumiere_scripts_admin_gutenberg' );

		}

		// On 'plugins.php' show a confirmation dialogue if.
		// 'imdbkeepsettings' is set on delete Lumière! options.
		if ( ( ( ! isset( $this->imdb_admin_values['imdbkeepsettings'] ) ) || ( $this->imdb_admin_values['imdbkeepsettings'] === '0' ) ) && ( 'plugins.php' === $hook )  ) {

			wp_enqueue_script( 'lumiere_deactivation_plugin_message' );

		}

		//  Add Quicktag.
		if ( ( ( 'post.php' === $hook ) || ( 'post-new.php' === $hook ) ) && ( wp_script_is( 'quicktags' ) ) ) {

			wp_enqueue_script( 'lumiere_quicktag_addbutton' );

		}

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
	 */
	public function lumiere_on_activation(): void {

		/* remove activation issue
		ob_start(); */

		// Start the logger.
		$this->logger->lumiere_start_logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		/* Create the value of number of updates on first install */
		// Start Settings class.
		if ( ! isset( $this->imdb_admin_values['imdbHowManyUpdates'] ) ) {

			$settings_class = new Settings();
			$this->logger->log()->info( "[Lumiere][coreClass][activation] Lumière option 'imdbHowManyUpdates' successfully created." );

		} else {

			$this->logger->log()->info( "[Lumiere][coreClass][activation] Lumière option 'imdbHowManyUpdates' already exists." );

		}

		/* Create the cache folders */
		if ( $this->config_class->lumiere_create_cache() === true ) {

			$this->logger->log()->info( '[Lumiere][coreClass][activation] Lumière cache successfully created.' );

		} else {

			$this->logger->log()->info( '[Lumiere][coreClass][activation] Lumière cache has not been created.' );

		}

		/* Set up WP Cron if it doesn't exist */
		if ( wp_next_scheduled( 'lumiere_cron_hook' ) === false ) {

			// Cron to run once, in 30 minutes.
			wp_schedule_single_event( time() + 1800, 'lumiere_cron_hook' );

			$this->logger->log()->debug( '[Lumiere][coreClass][activation] Lumière cron successfully set up.' );

		} else {

			$this->logger->log()->error( '[Lumiere][coreClass][activation] Crons were not set up.' );

		}

		$this->logger->log()->debug( '[Lumiere][coreClass][activation] Lumière plugin activated.' );

		/* remove activation issue
		trigger_error(ob_get_contents(),E_USER_ERROR);*/
	}

	/**
	 * Run on plugin deactivation
	 */
	public function lumiere_on_deactivation(): void {

		// Start the logger.
		$this->logger->lumiere_start_logger( 'coreClass', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );

		// Remove WP Cron lumiere_cron_exec_once should it exists.
		$wp_cron_list = count( _get_cron_array() ) > 0 ? _get_cron_array() : [];
		foreach ( $wp_cron_list as $time => $hook ) {
			if ( isset( $hook['lumiere_cron_exec_once'] ) ) {
				$timestamp = wp_next_scheduled( 'lumiere_cron_hook' );
				if ( $timestamp !== false ) {
					wp_unschedule_event( $timestamp, 'lumiere_cron_hook' );
					$this->logger->log()->info( '[Lumiere][coreClass][deactivation] Cron removed' );
				}
			}
		}

		$this->logger->log()->info( '[Lumiere][coreClass][deactivation] Lumière deactivated' );

	}

	/**
	 * Wrap the lumiere script
	 * Currenty replaces the home_url() in popupus with pll_home_url for use with Polylang
	 * The $lumiere_scripts_vars is a var in class Settings that can't be changed (executed too early)
	 */
	private function wrap_lumiere_script(): string {

		$polylang_class = new Polylang();
		$final_lumiere_script = $polylang_class->polylang_is_active() === true
		? $polylang_class->rewrite_string_with_polylang_url(
			$this->config_class->lumiere_scripts_vars,
			$this->imdb_admin_values['imdburlpopups']
		)
		: $this->config_class->lumiere_scripts_vars;
		return $final_lumiere_script;
	}
}

