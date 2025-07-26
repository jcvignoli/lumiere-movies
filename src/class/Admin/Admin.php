<?php declare( strict_types = 1 );
/**
 * Admin class for displaying all Admin tools.
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! class_exists( 'Lumiere\Config\Settings' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Admin\Admin_Menu;
use Lumiere\Admin\Backoffice_Extra;
use Lumiere\Admin\Metabox_Selection;
use Lumiere\Admin\Search_Items;
use Lumiere\Tools\Data;
use Lumiere\Config\Get_Options;
use Lumiere\Config\Open_Options;

/**
 * All Admin-related functions
 * Build Admin menu, calls admin-related stylesheets/scripts
 * Search page redirect
 *
 * @see \Lumiere\Admin\Admin_Menu to display the menu
 */
final class Admin {

	/**
	 * Traits
	 */
	use Open_Options;

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
	public static function start(): void {

		$start = new self();

		/**
		 * (1) Don't bother doing stuff if the current user lacks permissions
		 */
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Display search page, must be executed before the admin control
		add_filter( 'template_redirect', [ $start, 'lum_search_movie_redirect' ] );

		// Gutenberg-style sidebar. Must be called before is_admin().
		add_action( 'init', fn() => Metabox_Selection::register_post_meta_sidebar() );

		/**
		 * (2) Only for admin pages, so after this, only init and below should work
		 */
		if ( ! is_admin() ) {
			return;
		}

		// Extra backoffice functions, such as privacy, plugins infos in plugins' page
		add_action( 'admin_init', fn() => Backoffice_Extra::lumiere_backoffice_start(), 0 );

		// Add the metabox to editor.
		add_action( 'admin_init', fn() => Metabox_Selection::init() );

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

		// Register hide/show script.
		wp_register_script(
			'lumiere_hide_show',
			Get_Options::LUM_JS_URL . 'lumiere_hide_show.min.js',
			[ 'jquery' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere_hide_show.min.js' ) ),
			true
		);

		// Register admin styles.
		wp_register_style(
			'lumiere_css_admin',
			Get_Options::LUM_CSS_URL . 'lumiere_admin.min.css',
			[],
			strval( filemtime( Get_Options::LUM_CSS_PATH . 'lumiere_admin.min.css' ) )
		);

		// Register admin scripts.
		wp_register_script(
			'lumiere_scripts_admin',
			Get_Options::LUM_JS_URL . 'lumiere_scripts_admin.min.js',
			[ 'jquery' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere_scripts_admin.min.js' ) ),
			false
		);

		// Register gutenberg admin scripts.
		wp_register_script(
			'lumiere_scripts_admin_gutenberg',
			Get_Options::LUM_JS_URL . 'lumiere_scripts_admin_gutenberg.min.js',
			[ 'jquery' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere_scripts_admin_gutenberg.min.js' ) ),
			false
		);

		// Register confirmation script upon deactivation.
		wp_register_script(
			'lumiere_deactivation_plugin_message',
			Get_Options::LUM_JS_URL . 'lumiere_admin_deactivation_msg.min.js',
			[ 'jquery' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere_admin_deactivation_msg.min.js' ) ),
			true
		);

		// Quicktag.
		wp_register_script(
			'lumiere_quicktag_addbutton',
			Get_Options::LUM_JS_URL . 'lumiere_admin_quicktags.min.js',
			[ 'quicktags' ],
			strval( filemtime( Get_Options::LUM_JS_PATH . 'lumiere_admin_quicktags.min.js' ) ),
			true
		);
	}

	/**
	 * Add assets of Lumière admin pages
	 * @param string $current_page
	 *
	 * @since 4.7 added site-editor
	 */
	public function lumiere_execute_admin_assets( string $current_page ): void {

		// Load assets only on Lumière admin pages.
		// + WordPress edition pages + Lumière own pages (ie gutenberg search).
		if (
			'toplevel_page_lumiere_options' === $current_page
			|| 'post.php' === $current_page
			|| 'post-new.php' === $current_page
			|| 'widgets.php' === $current_page
			|| 'site-editor.php' === $current_page
			// All Lumière pages.
			|| Data::array_contains_term(
				Get_Options::get_admin_lum_pages(),
				esc_url_raw( wp_unslash( strval( $_SERVER['REQUEST_URI'] ?? '' ) ) )
			)
			// Extra WP Admin pages.
			|| Data::array_contains_term(
				[
					'admin.php?page=lumiere_options',
					'options-general.php?page=lumiere_options',
				],
				esc_url_raw( wp_unslash( strval( $_SERVER['REQUEST_URI'] ?? '' ) ) )
			)
		) {
			// Load main css.
			wp_enqueue_style( 'lumiere_css_admin' );

			// Load main js.
			wp_enqueue_script( 'lumiere_scripts_admin' );

			// Add inline scripts.
			wp_add_inline_script( 'lumiere_scripts_admin', Get_Options::get_scripts_admin_vars(), 'before' );

			// Load hide/show js.
			wp_enqueue_script( 'lumiere_hide_show' );

			// Script for click on gutenberg block link to open a popup, script is loaded but it doesn't work!
			wp_enqueue_script( 'lumiere_scripts_admin_gutenberg' );
		}

		// On 'plugins.php' show a confirmation dialogue if 'imdbkeepsettings' is set on delete Lumière! options.
		if (
			( ! isset( $this->imdb_admin_values['imdbkeepsettings'] ) || $this->imdb_admin_values['imdbkeepsettings'] === '0' )
			&& $current_page === 'plugins.php'
		) {
			wp_enqueue_script( 'lumiere_deactivation_plugin_message' );
		}

		//  Add Quicktag.
		if ( ( 'post.php' === $current_page || 'post-new.php' === $current_page ) && wp_script_is( 'quicktags' ) ) {
			wp_enqueue_script( 'lumiere_quicktag_addbutton' );
		}
	}

	/**
	 * Register TinyMCE
	 * @param string $current_page
	 */
	public function lumiere_execute_tinymce( string $current_page ): void {

		// Add only in Rich Editor mode for post.php and post-new.php pages
		if (
			get_user_option( 'rich_editing' ) === 'true'
			&& ( 'post.php' === $current_page || 'post-new.php' === $current_page )
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

		$plugin_array['lumiere_tiny'] = Get_Options::LUM_JS_URL . 'lumiere_admin_tinymce_editor.min.js';
		return $plugin_array;
	}

	/**
	 * Display search popup/page for movies in admin, but since it's called in external pages, it can't be an admin page
	 *
	 * @param string $template_path The path to the page of the theme currently in use - not utilised
	 * @return Search_Items|string The Search class is displayed if successfull, template path otherwise
	 */
	public function lum_search_movie_redirect( string $template_path ): Search_Items|string {

		// Display only if URL is ok and is not admin (to save time.
		if (
			stripos( esc_url_raw( strval( wp_unslash( strval( $_SERVER['REQUEST_URI'] ?? '' ) ) ) ), site_url( '', 'relative' ) . Get_Options::LUM_SEARCH_ITEMS_URL_ADMIN ) !== 0
			|| is_admin()
		) {
			return $template_path;
		}

		return new Search_Items();
	}
}
