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

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Admin\Admin_Menu;
use Lumiere\Admin\Backoffice_Extra;
use Lumiere\Admin\Widget_Selection;
use Lumiere\Admin\Metabox_Selection;
use Lumiere\Tools\Utils;
use Lumiere\Tools\Settings_Global;

/**
 * All Admin-related functions
 * Build Admin menu, calls admin-related stylesheets/scripts
 * @see Admin\Admin_Menu
 */
class Admin {

	// Trait including the database settings.
	use Settings_Global;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get Global Settings class properties.
		$this->get_settings_class();
		$this->get_db_options();

		if ( is_admin() ) {

			// Add admin menu.
			add_action( 'init', fn() => Admin_Menu::lumiere_static_start() );

			// Add the metabox to editor.
			add_action( 'admin_init', fn() => Metabox_Selection::lumiere_static_start(), 0 );

			// Extra backoffice functions, such as privacy, plugins infos in plugins' page
			add_action( 'admin_init', fn() => Backoffice_Extra::lumiere_backoffice_start(), 0 );
		}

		// Widget.
		/** @psalm-suppress MissingClosureReturnType, UndefinedClass -- crazy psalm, it is defined! */
		add_action( 'widgets_init', fn() => Widget_Selection::lumiere_static_start(), 9 );

		// Register admin scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'lumiere_register_admin_assets' ], 0 );

		// Add admin header.
		add_action( 'admin_enqueue_scripts', [ $this, 'lumiere_execute_admin_assets' ], 0 );

		// Add admin tinymce button for wysiwig editor.
		add_action( 'admin_enqueue_scripts', [ $this, 'lumiere_execute_tinymce' ], 2 );
	}

	/**
	 * @see \Lumiere\Core
	 */
	public static function lumiere_static_start(): void {
		$start = new self();
	}

	/**
	 * Register admin scripts and styles
	 */
	public function lumiere_register_admin_assets(): void {

		// Register hide/show script
		wp_register_script(
			'lumiere_hide_show',
			$this->config_class->lumiere_js_dir . 'lumiere_hide_show.min.js',
			[ 'jquery' ],
			$this->config_class->lumiere_version,
			true
		);

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

	/**
	 *  Add assets of Lumière admin pages
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
}
