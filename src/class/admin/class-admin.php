<?php declare( strict_types = 1 );
/**
 * Admin class for displaying all Admin tools.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Settings;
use Lumiere\Admin\Admin_Menu;
use Lumiere\Admin\Backoffice_Extra;
use Lumiere\Admin\Metabox_Selection;
use Lumiere\Admin\Search;
use Lumiere\Tools\Data;
use Lumiere\Tools\Settings_Global;

/**
 * All Admin-related functions
 * Build Admin menu, calls admin-related stylesheets/scripts
 * Search page redirect
 *
 * @see \Lumiere\Admin\Admin_Menu to display the menu
 */
class Admin {

	/**
	 * Traits
	 */
	use Settings_Global;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get Global Settings class properties.
		$this->get_db_options();
	}

	/**
	 * Static start
	 * @see \Lumiere\Core
	 */
	public static function lumiere_static_start(): void {

		$start = new self();

		/**
		 * (1) Don't bother doing stuff if the current user lacks permissions
		 */
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Display search page, must be executed before the admin control
		add_filter( 'template_redirect', [ $start, 'lum_search_redirect' ] );

		/**
		 * (2) Only for admin pages, so after this, only init and below should work
		 */
		if ( ! is_admin() ) {
			return;
		}

		// Extra backoffice functions, such as privacy, plugins infos in plugins' page
		add_action( 'admin_init', fn() => Backoffice_Extra::lumiere_backoffice_start(), 0 );

		// Add the metabox to editor.
		add_action( 'admin_init', fn() => Metabox_Selection::lumiere_static_start() );

		// Register admin scripts.
		add_action( 'admin_enqueue_scripts', [ $start, 'lumiere_register_admin_assets' ] );

		// Add admin header.
		add_action( 'admin_enqueue_scripts', [ $start, 'lumiere_execute_admin_assets' ] );

		// Add admin tinymce button for wysiwig editor.
		add_action( 'admin_enqueue_scripts', [ $start, 'lumiere_execute_tinymce' ], 2 );

		/**
		 * (3) Admin menu is only for those who can manage options
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Add Polylang specific methods to admin. Static methods that executes ony if Polylang is active.
		add_action( 'init', [ 'Lumiere\Plugins\Auto\Polylang', 'add_polylang_in_admin' ] );

		// Add admin menu.
		add_action( 'init', fn() => Admin_Menu::lumiere_static_start() );
	}

	/**
	 * Register admin scripts and styles
	 */
	public function lumiere_register_admin_assets(): void {

		// Register hide/show script
		wp_register_script(
			'lumiere_hide_show',
			Settings::LUM_JS_URL . 'lumiere_hide_show.min.js',
			[ 'jquery' ],
			strval( filemtime( Settings::LUM_JS_PATH . 'lumiere_hide_show.min.js' ) ),
			true
		);

		// Register admin styles
		wp_register_style(
			'lumiere_css_admin',
			Settings::LUM_CSS_URL . 'lumiere_admin.min.css',
			[],
			strval( filemtime( Settings::LUM_CSS_PATH . 'lumiere_admin.min.css' ) )
		);

		// Register admin scripts
		wp_register_script(
			'lumiere_scripts_admin',
			Settings::LUM_JS_URL . 'lumiere_scripts_admin.min.js',
			[ 'jquery' ],
			strval( filemtime( Settings::LUM_JS_PATH . 'lumiere_scripts_admin.min.js' ) ),
			false
		);

		// Register gutenberg admin scripts
		wp_register_script(
			'lumiere_scripts_admin_gutenberg',
			Settings::LUM_JS_URL . 'lumiere_scripts_admin_gutenberg.min.js',
			[ 'jquery' ],
			strval( filemtime( Settings::LUM_JS_PATH . 'lumiere_scripts_admin_gutenberg.min.js' ) ),
			false
		);

		// Register confirmation script upon deactivation
		wp_register_script(
			'lumiere_deactivation_plugin_message',
			Settings::LUM_JS_URL . 'lumiere_admin_deactivation_msg.min.js',
			[ 'jquery' ],
			strval( filemtime( Settings::LUM_JS_PATH . 'lumiere_admin_deactivation_msg.min.js' ) ),
			true
		);

		// Quicktag
		wp_register_script(
			'lumiere_quicktag_addbutton',
			Settings::LUM_JS_URL . 'lumiere_admin_quicktags.min.js',
			[ 'quicktags' ],
			strval( filemtime( Settings::LUM_JS_PATH . 'lumiere_admin_quicktags.min.js' ) ),
			true
		);
	}

	/**
	 * Add assets of Lumière admin pages
	 * @param string $page_caller
	 */
	public function lumiere_execute_admin_assets ( string $page_caller ): void {

		// Load assets only on Lumière admin pages.
		// + WordPress edition pages + Lumière own pages (ie gutenberg search).
		if (
			'toplevel_page_lumiere_options' === $page_caller
			|| 'post.php' === $page_caller
			|| 'post-new.php' === $page_caller
			|| 'widgets.php' === $page_caller
			// All Lumière pages.
			|| Data::lumiere_array_contains_term( Settings::get_all_lumiere_pages(), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) )
			// Extra WP Admin pages.
			|| Data::lumiere_array_contains_term(
				[
					'admin.php?page=lumiere_options',
					'options-general.php?page=lumiere_options',
				],
				esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) )
			) // Trait data
		) {

			// Load main css.
			wp_enqueue_style( 'lumiere_css_admin' );

			// Load main js.
			wp_enqueue_script( 'lumiere_scripts_admin' );

			// Register paths, fake script to get a hook for add inline scripts
			wp_add_inline_script(
				'lumiere_scripts_admin',
				Settings::get_scripts_admin_vars(),
				'before'
			);

			// Load hide/show js.
			wp_enqueue_script( 'lumiere_hide_show' );

			// Script for click on gutenberg block link to open a popup, script is loaded but it doesn't work!
			wp_enqueue_script( 'lumiere_scripts_admin_gutenberg' );

		}

		// On 'plugins.php' show a confirmation dialogue if 'imdbkeepsettings' is set on delete Lumière! options.
		if (
			( ! isset( $this->imdb_admin_values['imdbkeepsettings'] ) || $this->imdb_admin_values['imdbkeepsettings'] === '0' )
			&& $page_caller === 'plugins.php'
		) {
			wp_enqueue_script( 'lumiere_deactivation_plugin_message' );
		}

		//  Add Quicktag.
		if ( ( 'post.php' === $page_caller || 'post-new.php' === $page_caller ) && wp_script_is( 'quicktags' ) ) {
			wp_enqueue_script( 'lumiere_quicktag_addbutton' );
		}
	}

	/**
	 *  Register TinyMCE
	 * @param string $page_caller
	 */
	public function lumiere_execute_tinymce( string $page_caller ): void {

		// Add only in Rich Editor mode for post.php and post-new.php pages
		if (
			get_user_option( 'rich_editing' ) === 'true'
			&& ( 'post.php' === $page_caller || 'post-new.php' === $page_caller )
		) {

			add_filter( 'mce_external_plugins', [ $this, 'lumiere_tinymce_addbutton' ] );
			add_filter( 'mce_buttons', [ $this, 'lumiere_tinymce_button_position' ] );
		}
	}

	/**
	 * Change TinyMCE buttons position
	 * @param array<mixed> $buttons
	 * @return array<mixed>
	 */
	public function lumiere_tinymce_button_position( array $buttons ): array {

		array_push( $buttons, 'separator', 'lumiere_tiny' );
		return $buttons;
	}

	/**
	 * Add TinyMCE buttons
	 * @param array<mixed> $plugin_array
	 * @return array<mixed>
	 */
	public function lumiere_tinymce_addbutton( array $plugin_array ): array {

		$plugin_array['lumiere_tiny'] = Settings::LUM_JS_URL . 'lumiere_admin_tinymce_editor.min.js';
		return $plugin_array;
	}

	/**
	 * Display search popup/page in admin, but since it's called in external pages, it can't be an admin page
	 *
	 * @param string $template_path The path to the page of the theme currently in use - not utilised
	 * @return Search|string The Search class is displayed if successfull, template path otherwise
	 */
	public function lum_search_redirect( string $template_path ): Search|string {

		// Display only if URL is ok and is not admin (to save time.
		if (
			stripos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), site_url( '', 'relative' ) . Settings::GUTENBERG_SEARCH_URL ) !== 0
			|| is_admin()
		) {
			return $template_path;
		}

		return new Search();
	}
}
