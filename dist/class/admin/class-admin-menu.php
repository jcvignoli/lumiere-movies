<?php declare( strict_types = 1 );
/**
 * Admin class for displaying all Admin sections.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Tools\Settings_Global;
use Lumiere\Tools\Utils;
use Lumiere\Tools\Files;
use Lumiere\Plugins\Logger;
use Lumiere\Admin\Cache_Tools;
use Lumiere\Admin\Admin_General;

/**
 * Display Admin Menus: Top, Left and default menus
 * Taxonomy theme pages copy class is called here
 *
 * @see \Lumiere\Admin\Copy_Template_Taxonomy to copy new page template
 * @see \Lumiere\Admin\Save_Options Check the $_GET to know if we need to save the submitted data
 * @see \Lumiere\Admin\Admin_Notifications
 */
class Admin_Menu {

	/**
	 * Traits
	 */
	use Settings_Global, Files, Admin_General;

	/**
	 * Classes
	 */
	protected Utils $utils_class;
	protected Logger $logger;

	/**
	 * Store directories, pages
	 */
	protected string $page_cache_manage;
	protected string $page_cache_option;
	protected string $page_data;
	protected string $page_data_order;
	protected string $page_data_taxo;
	protected string $page_general_base;
	protected string $page_general_advanced;
	protected string $page_help;
	protected string $page_help_support;
	protected string $page_help_faqs;
	protected string $page_help_changelog;

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
	public function __construct() {

		// Get Global Settings class properties.
		$this->get_settings_class();
		$this->get_db_options();

		// Start Utilities class.
		$this->utils_class = new Utils();

		// Start Logger class.
		$this->logger = new Logger( 'adminClass' );

		// Build vars.
		$this->menu_id = $this->get_id() . '_options';

		// Build pages vars.
		$this->page_general_base = admin_url( 'admin.php?page=' . $this->menu_id );
		$this->page_general_advanced = admin_url( 'admin.php?page=' . $this->menu_id . '&subsection=advanced' );

		$page_data = 'admin.php?page=' . $this->menu_id . '_data';
		$this->page_data = admin_url( $page_data );
		$this->page_data_order = admin_url( $page_data . '&subsection=order' );
		$this->page_data_taxo = admin_url( $page_data . '&subsection=taxo' );

		$page_cache = 'admin.php?page=' . $this->menu_id . '_cache';
		$this->page_cache_option = admin_url( $page_cache );
		$this->page_cache_manage = admin_url( $page_cache . '&subsection=manage' );

		$page_help = 'admin.php?page=' . $this->menu_id . '_help';
		$this->page_help = admin_url( $page_help );
		$this->page_help_support = admin_url( $page_help . '&subsection=support' );
		$this->page_help_faqs = admin_url( $page_help . '&subsection=faqs' );
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
		 * (2) template checking in Detect_New_Template_Taxo class
		 * (3) any 'notice_lumiere_msg' transient is found in Admin_Notifications class
		 */
		$current_url = $that->lumiere_get_current_admin_url();
		if ( str_contains( $current_url, $that->page_general_base ) === true ) {
			add_action( 'admin_notices', fn() => Detect_New_Template_Taxo::lumiere_static_start( $that->page_data_taxo ), 10, 1 );
			add_action( 'admin_notices', [ '\Lumiere\Admin\Admin_Notifications', 'lumiere_static_start' ] );
		}

		/**
		 * Settings saved/reset, files deleted/refreshed
		 * Based on the $_GET and $_POSTS, the methods refreshing/saving/deleting will be processed
		 * @see Save_Options::process_headers()
		 * @since 4.0
		 */
		add_action( 'wp_loaded', fn() => Save_Options::lumiere_static_start( $that->page_data_taxo ) );

		/**
		 * Copying taxonomy templates in Lumière! data taxonomy options
		 */
		if (
			isset( $_GET['taxotype'] )
			&& ( isset( $_GET['_wpnonce_linkcopytaxo'] ) && wp_verify_nonce( $_GET['_wpnonce_linkcopytaxo'], 'linkcopytaxo' ) !== false )
		) {
			add_action( 'admin_init', fn() => Copy_Template_Taxonomy::lumiere_start_copy_taxo( $that->page_data_taxo ) );
		}

		/**
		 * Build the menus
		 * (a) on the left, can be the WP standard (inside settings) or a bigger one if that option was selected in the admin options
		 * (b) on the top, option selected by default but can be removed in admin options
		 */
		// @phpstan-ignore-next-line -- Parameter #2 $callback of function add_action expects callable(): mixed, array{$that(Lumiere\Admin), non-falsy-string} given
		add_action( 'admin_menu', [ &$that, $that->get_id() . '_add_left_menu' ] );

		// Add Lumiere menu in WordPress top menu
		if ( $that->imdb_admin_values['imdbwordpress_tooladminmenu'] === '1' ) {
			// @phpstan-ignore-next-line -- Parameter #2 $callback of function add_action expects callable(): mixed, array{$that(Lumiere\Admin), non-falsy-string} given
			add_action( 'admin_bar_menu', [ $that, $that->get_id() . '_admin_add_top_menu' ], 70 );
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
		/** @psalm-suppress RedundantCondition, TypeDoesNotContainNull -- Type string for $get_page is never null => according to PHPStan, it can! */
		$current_step = $get_page !== false && $get_page !== null ? $get_page : '';

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
				'<img src="' . $this->config_class->lumiere_pics_dir . 'lumiere-ico13x13.png" align="absmiddle"> Lumière',
				'manage_options',
				$this->menu_id,
				[ $this, 'call_admin_subclass' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Data management', 'lumiere-movies' ),
				esc_html__( 'Data', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_data',
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
				$this->config_class->lumiere_pics_dir . 'lumiere-ico13x13.png',
				65
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Lumière general page', 'lumiere-movies' ),
				esc_html__( 'Main', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id,
				[ $this, 'call_admin_subclass' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Data management', 'lumiere-movies' ),
				esc_html__( 'Data', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_data',
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

		$admin_bar->add_menu(
			[
				'id' => $id,
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "lumiere-ico13x13.png' width='16' height='16' />&nbsp;&nbsp;" . 'Lumière',
				'parent' => null,
				'href' => $this->page_general_base,
				'meta' => [
					'title' => esc_html__( 'Lumière Menu', 'lumiere-movies' ),
				],
			]
		);

		$admin_bar->add_menu(
			[
				'parent' => $id,
				'id' => $this->get_id() . '_top_menu_general',
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "menu/admin-general.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'General', 'lumiere-movies' ),
				'href' => $this->page_general_base,
				'meta' => [
					'title' => esc_html__( 'Main and advanced options', 'lumiere-movies' ),
				],
			]
		);
		$admin_bar->add_menu(
			[
				'parent' => $id,
				'id' => $this->get_id() . '_top_menu_data',
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "menu/admin-widget-inside.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Data', 'lumiere-movies' ),
				'href' => $this->page_data,
				'meta' => [
					'title' => esc_html__( 'Data option and taxonomy', 'lumiere-movies' ),
				],
			]
		);
		$admin_bar->add_menu(
			[
				'parent' => $id,
				'id' => $this->get_id() . '_top_menu_cache',
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "menu/admin-cache.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Cache', 'lumiere-movies' ),
				'href' => $this->page_cache_option,
				'meta' => [
					'title' => esc_html__( 'Cache options', 'lumiere-movies' ),
				],
			]
		);

		$admin_bar->add_menu(
			[
				'parent' => $id,
				'id' => $this->get_id() . '_top_menu_help',
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "menu/admin-help.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Help', 'lumiere-movies' ),
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

		$class_name_from_page = strlen( $this->get_current_page() ) > 0 ? $this->get_current_page() : '_general';
		$class_name_cleaned = ucfirst( str_replace( '_', '', $class_name_from_page ) );
		$full_class_name = '\Lumiere\Admin\Submenu\\' . $class_name_cleaned;

		if ( class_exists( $full_class_name ) ) {

			$instance = new $full_class_name();

			if ( method_exists( $instance, 'lum_submenu_start' ) ) {
				$instance->lum_submenu_start( new Cache_Tools() );
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
			[ $this->page_help_support, $this->config_class->lumiere_version ], /** Add in an array all vars to send in the template */
			self::TRANSIENT_ADMIN,
		);
	}
}
