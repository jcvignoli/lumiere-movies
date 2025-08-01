<?php declare( strict_types = 1 );
/**
 * Admin class for displaying all Admin sections.
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Config\Open_Options;
use Lumiere\Plugins\Logger;
use Lumiere\Admin\Cache\Cache_Files_Management;
use Lumiere\Admin\Admin_General;
use Lumiere\Admin\Copy_Templates\Detect_New_Theme;
use Lumiere\Admin\Save\Save_Options;

/**
 * Display Admin Menus: Top, Left and default menus
 * Taxonomy theme pages copy class is called here
 * It is checked priorly if the user can manage options
 *
 * @see \Lumiere\Admin\Copy_Templates\Copy_Theme to copy new page template
 * @see \Lumiere\Admin\Save\Save_Options Check the $_GET to know if we need to save the submitted data
 * @see \Lumiere\Admin\Admin_Notifications
 */
class Admin_Menu {

	/**
	 * Traits
	 */
	use Open_Options, Admin_General;

	/**
	 * Store directories, pages
	 */
	protected string $page_cache_manage;
	protected string $page_cache_option;
	protected string $page_data_movie;
	protected string $page_data_movie_order;
	protected string $page_data_movie_taxo;
	protected string $page_data_person;
	protected string $page_data_person_order;
	protected string $page_main_base;
	protected string $page_main_advanced;
	protected string $page_help;
	protected string $page_help_support;
	protected string $page_help_filters;
	protected string $page_help_faqs;
	protected string $page_help_changelog;
	protected string $page_help_compatibility;

	/**
	 * Id utilised to build functions and menu titles
	 */
	protected string $menu_id;

	/**
	 * Used to define name of methods
	 */
	const LUMIERE_ADMIN_ID = 'lumiere';

	/**
	 * Transient name that will be used in templates to get the values passed
	 */
	const TRANSIENT_ADMIN = 'admin_template_pass_vars';

	/**
	 * Constructor
	 */
	public function __construct(
		protected Logger $logger = new Logger( 'adminClass' ),
	) {
		// Get global settings class properties.
		$this->get_db_options(); // In Open_Options trait.

		// Build vars.
		$this->menu_id = $this->get_id() . '_options';

		// Build pages vars.
		$this->page_main_base = admin_url( 'admin.php?page=' . $this->menu_id );
		$this->page_main_advanced = admin_url( 'admin.php?page=' . $this->menu_id . '&subsection=advanced' );

		$page_data_movie = 'admin.php?page=' . $this->menu_id . '_data_movie';
		$this->page_data_movie = admin_url( $page_data_movie );
		$this->page_data_movie_order = admin_url( $page_data_movie . '&subsection=order' );
		$this->page_data_movie_taxo = admin_url( $page_data_movie . '&subsection=taxo' );

		$page_data_person = 'admin.php?page=' . $this->menu_id . '_data_person';
		$this->page_data_person = admin_url( $page_data_person );
		$this->page_data_person_order = admin_url( $page_data_person . '&subsection=order' );

		$page_cache = 'admin.php?page=' . $this->menu_id . '_cache';
		$this->page_cache_option = admin_url( $page_cache );
		$this->page_cache_manage = admin_url( $page_cache . '&subsection=manage' );

		$page_help = 'admin.php?page=' . $this->menu_id . '_help';
		$this->page_help = admin_url( $page_help );
		$this->page_help_support = admin_url( $page_help . '&subsection=support' );
		$this->page_help_filters = admin_url( $page_help . '&subsection=filters' );
		$this->page_help_faqs = admin_url( $page_help . '&subsection=faqs' );
		$this->page_help_compatibility = admin_url( $page_help . '&subsection=compatibility' );
		$this->page_help_changelog = admin_url( $page_help . '&subsection=changelog' );
	}

	/**
	 * Add the admin menu
	 * @see \Lumiere\Admin
	 */
	public static function lumiere_static_start(): void {
		$that = new self();

		/**
		 * Display notices based on
		 * (1) only in Lumiere Admin options pages
		 * (2) template checking in Detect_New_Theme class
		 * (3) any 'notice_lumiere_msg' transient is found in Admin_Notifications class
		 */
		$is_lum_admin_menu = str_contains( $that->get_current_admin_url(), $that->page_main_base );
		if ( $is_lum_admin_menu === true ) {
			add_action( 'admin_notices', fn() => Detect_New_Theme::get_notif_templates( $that->page_data_movie_taxo ), 10, 1 );
			add_action( 'admin_notices', [ '\Lumiere\Admin\Admin_Notifications', 'start' ] );
		}

		/**
		 * Update the rewrite rules if needed (automatically done if checking permalinks option page, but that's an extra)
		 */
		if ( $is_lum_admin_menu === true ) {
			apply_filters( 'lum_add_rewrite_rules_if_admin', '' );
		}

		/**
		 * Settings saved/reset, files deleted/refreshed
		 * Based on the $_GET and $_POSTS, the methods refreshing/saving/deleting will be processed
		 * @see Save_Options::process_headers()
		 * @since 4.0
		 */
		add_action( 'wp_loaded', fn() => Save_Options::init( $that->page_data_movie_taxo ) );
		add_action( 'init', fn() => Save_Options::init_taxonomy( $that->page_main_advanced ), 11 );

		/**
		 * Copying taxonomy templates in Lumière! data taxonomy options
		 */
		if (
			isset( $_GET['taxotype'] )
			&& isset( $_GET['_wpnonce_linkcopytaxo'] )
			&& wp_verify_nonce( sanitize_key( $_GET['_wpnonce_linkcopytaxo'] ), 'linkcopytaxo' ) > 0
		) {
			add_action( 'admin_init', fn() => Copy_Templates\Copy_Theme::start_copy_theme( $that->page_data_movie_taxo ) );
		}

		/**
		 * Build the menus
		 * (a) on the left, can be the WP standard (inside settings) or a bigger one if that option was selected in the admin options
		 * (b) on the top, option selected by default but can be removed in admin options
		 */
		$menu_method = $that->get_id() . '_add_left_menu';
		// @phpstan-ignore-next-line (Parameter #2 $callback of function add_action expects callable(): mixed, array{$that(Lumiere\Admin), non-falsy-string} given)
		add_action( 'admin_menu', [ $that, $menu_method ] );

		// Add Lumiere menu in WordPress top menu
		if ( $that->imdb_admin_values['imdbwordpress_tooladminmenu'] === '1' ) {
			$bar_menu_method = $that->get_id() . '_admin_add_top_menu';
			// @phpstan-ignore-next-line (Parameter #2 $callback of function add_action expects callable(): mixed, array{$that(Lumiere\Admin), non-falsy-string} given)
			add_action( 'admin_bar_menu', [ $that, $bar_menu_method ], 70 );
		}
	}

	/**
	 * Get ID
	 */
	private function get_id(): string {
		return self::LUMIERE_ADMIN_ID;
	}

	/**
	 * Get the current $GET['page'] of the current page
	 * @return string
	 */
	private function get_current_page(): string {

		$get_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_URL );
		$current_step = $get_page !== false ? $get_page : '';
		// @phpstan-ignore argument.type (Parameter #3 $subject of function str_replace expects array<string>|string, string|null given => it can't be null, not using FILTER_NULL_ON_FAILURE flag)
		return str_replace( $this->get_id() . '_options', '', $current_step );
	}

	/**
	 * Add left admin menu
	 */
	public function lumiere_add_left_menu(): void {

		// Menu inside settings
		if ( isset( $this->imdb_admin_values['imdbwordpress_bigmenu'] ) && $this->imdb_admin_values['imdbwordpress_bigmenu'] === '0' ) {

			add_options_page(
				'Lumière Options',
				'<img src="' . Get_Options::LUM_PICS_URL . 'lumiere-ico13x13.png" align="absmiddle"> Lumière',
				'manage_options',
				$this->menu_id,
				[ $this, 'call_admin_subclass' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Data management for movies', 'lumiere-movies' ),
				esc_html__( 'Data movies', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_data_movie',
				[ $this, 'call_admin_subclass' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Data management for persons', 'lumiere-movies' ),
				esc_html__( 'Data persons', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_data_person',
				[ $this, 'call_admin_subclass' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Cache management options page', 'lumiere-movies' ),
				esc_html__( 'Cache', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_cache',
				[ $this, 'call_admin_subclass' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Help page', 'lumiere-movies' ),
				esc_html__( 'Help', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_help',
				[ $this, 'call_admin_subclass' ],
			);

			// Left menu
		} elseif ( isset( $this->imdb_admin_values['imdbwordpress_bigmenu'] ) && $this->imdb_admin_values['imdbwordpress_bigmenu'] === '1' ) {

			add_menu_page(
				esc_html__( 'Lumière options page', 'lumiere-movies' ),
				esc_html__( 'Lumière', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id,
				[ $this, 'call_admin_subclass' ],
				Get_Options::LUM_PICS_URL . 'lumiere-ico13x13.png',
				65
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Lumière main page', 'lumiere-movies' ),
				esc_html__( 'Main', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id,
				[ $this, 'call_admin_subclass' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Data management for movies', 'lumiere-movies' ),
				esc_html__( 'Data movies', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_data_movie',
				[ $this, 'call_admin_subclass' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Data management for persons', 'lumiere-movies' ),
				esc_html__( 'Data persons', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_data_person',
				[ $this, 'call_admin_subclass' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Cache management options page', 'lumiere-movies' ),
				esc_html__( 'Cache', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_cache',
				[ $this, 'call_admin_subclass' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Help page', 'lumiere-movies' ),
				esc_html__( 'Help', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_help',
				[ $this, 'call_admin_subclass' ],
			);
		}
	}

	/**
	 * Add top Admin menu
	 */
	public function lumiere_admin_add_top_menu( \WP_Admin_Bar $admin_bar ): void {

		$id = $this->get_id() . '_top_menu';

		$admin_bar->add_node(
			[
				'parent' => '',
				'id' => $id,
				'title' => "<img src='" . Get_Options::LUM_PICS_URL . "lumiere-ico13x13.png' width='16' height='16' />&nbsp;&nbsp;" . 'Lumière',
				'href' => $this->page_main_base,
				'meta' => [
					'title' => esc_html__( 'Lumière Menu', 'lumiere-movies' ),
				],
			]
		);

		$admin_bar->add_node(
			[
				'parent' => $id,
				'id' => $id . '_main',
				'title' => "<img src='" . Get_Options::LUM_PICS_URL . "menu/admin-main.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Main', 'lumiere-movies' ),
				'href' => $this->page_main_base,
				'meta' => [
					'title' => esc_html__( 'Main and advanced options', 'lumiere-movies' ),
				],
			]
		);
		$admin_bar->add_node(
			[
				'parent' => $id,
				'id' => $id . '_data_movie',
				'title' => "<img src='" . Get_Options::LUM_PICS_URL . "menu/admin-widget-inside-movie-items.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Data movie', 'lumiere-movies' ),
				'href' => $this->page_data_movie,
				'meta' => [
					'title' => esc_html__( 'Data option and taxonomy for movies', 'lumiere-movies' ),
				],
			]
		);
		$admin_bar->add_node(
			[
				'parent' => $id,
				'id' => $id . '_data_person',
				'title' => "<img src='" . Get_Options::LUM_PICS_URL . "menu/admin-widget-inside-person-items.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Data person', 'lumiere-movies' ),
				'href' => $this->page_data_person,
				'meta' => [
					'title' => esc_html__( 'Data option and taxonomy for persons', 'lumiere-movies' ),
				],
			]
		);
		$admin_bar->add_node(
			[
				'parent' => $id,
				'id' => $id . '_cache',
				'title' => "<img src='" . Get_Options::LUM_PICS_URL . "menu/admin-cache.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Cache', 'lumiere-movies' ),
				'href' => $this->page_cache_option,
				'meta' => [
					'title' => esc_html__( 'Cache options', 'lumiere-movies' ),
				],
			]
		);

		$admin_bar->add_node(
			[
				'parent' => $id,
				'id' => $id . '_help',
				'title' => "<img src='" . Get_Options::LUM_PICS_URL . "menu/admin-help.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Help', 'lumiere-movies' ),
				'href' => $this->page_help,
				'meta' => [
					'title' => esc_html__( 'Get support and support plugin development', 'lumiere-movies' ),

				],
			]
		);
	}

	/**
	 * Get admin subclass according to the current view
	 * Classes listed in $class_need_cache need to start a cache class
	 *
	 * @return void The private method to call
	 */
	public function call_admin_subclass(): void {

		$class_name_from_page = strlen( $this->get_current_page() ) > 0 ? $this->get_current_page() : '_main';
		$class_name_cleaned = ucfirst( str_replace( '_', '', $class_name_from_page ) );
		$full_class_name = '\Lumiere\Admin\Submenu\\' . $class_name_cleaned;

		if ( class_exists( $full_class_name ) ) {

			$instance = new $full_class_name();

			if ( method_exists( $instance, 'lum_submenu_start' ) ) {
				$instance->lum_submenu_start( new Cache_Files_Management(), wp_create_nonce( 'check_display_page' ) );
			}

			$this->lumiere_add_signature_menus();
		}
	}

	/**
	 * Display the end of page signature for Lumiere
	 * All pages have it
	 */
	private function lumiere_add_signature_menus(): void {
		// Signature.
		$this->include_with_vars(
			'admin-menu-signature',
			[ $this->page_help_support ], /** Add in an array all vars to send in the template */
			self::TRANSIENT_ADMIN,
		);
	}
}
