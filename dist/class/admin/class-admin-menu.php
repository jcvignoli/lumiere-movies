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
use Lumiere\Admin\Submenu\General;
use Lumiere\Admin\Submenu\Data;
use Lumiere\Admin\Submenu\Cache;
use Lumiere\Admin\Submenu\Help;
use Lumiere\Admin\Cache_Tools;
use Lumiere\Admin\Admin_General;
use Exception;

/**
 * Display Admin Menus: Top, Left and default menus
 * Includes the notice messages definition called by child classes when form submission took place
 * Taxonomy theme pages copy class is called here
 *
 * @see \Lumiere\Admin\Copy_Template_Taxonomy to copy new page template
 * @see \Lumiere\Admin\Save_Options
 */
class Admin_Menu {

	/**
	 * Traits.
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
	 * Notification messages
	 * @var array<string, array<int, int|string>> $lumiere_notice_messages The messages with their color
	 * @phpstan-var array<string, array{0:string, 1:int}> $lumiere_notice_messages The messages with their color
	 */
	private array $lumiere_notice_messages = [
		'options_updated' => [ 'Options saved.', 1 ],
		'options_reset' => [ 'Options reset.', 1 ],
		'general_options_error_identical_value' => [ 'Wrong values. You can not select the same URL string for taxonomy pages and popups.', 3 ],
		'cache_delete_all_msg' => [ 'All cache files deleted.', 1 ],
		'cache_delete_ticked_msg' => [ 'Ticked file(s) deleted.', 1 ],
		'cache_delete_individual_msg' => [ 'The selected cache file was deleted.', 1 ],
		'cache_refresh_individual_msg' => [ 'The selected cache file was refreshed.', 1 ],
		'cache_query_deleted' => [ 'Query cache files deleted.', 1 ],
		'taxotemplatecopy_success' => [ 'Lumière template successfully copied in your theme folder.', 1 ],
		'taxotemplatecopy_failed' => [ 'Template copy failed! Check the permissions in you theme folder.', 3 ],
	];

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

		// Display notices based on transients.
		add_action( 'admin_notices', [ $this, 'lumiere_admin_display_messages' ] );

		/**
		 * Settings saved/reset, files deleted/refreshed
		 * Based on the $_GET and $_POSTS, the methods refreshing/saving/deleting will be processed
		 * @see Save_Options::process_headers()
		 * @since 4.0
		 */
		add_action( 'wp_loaded', fn() => Save_Options::lumiere_static_start() );

		// Copying taxonomy templates in Lumière! data taxonomy options
		if (
			isset( $_GET['taxotype'] )
			&& ( isset( $_GET['_wpnonce_linkcopytaxo'] ) && wp_verify_nonce( $_GET['_wpnonce_linkcopytaxo'], 'linkcopytaxo' ) !== false )
		) {
			add_action( 'admin_init', fn() => Copy_Template_Taxonomy::lumiere_start_copy_taxo( $this->page_data_taxo ) );
		}

		// @phpstan-ignore-next-line -- Parameter #2 $callback of function add_action expects callable(): mixed, array{$this(Lumiere\Admin), non-falsy-string} given
		add_action( 'admin_menu', [ &$this, $this->get_id() . '_add_left_menu' ] );

		// Add Lumiere menu in toolbar menu (top WordPress menu)
		if ( $this->imdb_admin_values['imdbwordpress_tooladminmenu'] === '1' ) {
			// @phpstan-ignore-next-line -- Parameter #2 $callback of function add_action expects callable(): mixed, array{$this(Lumiere\Admin), non-falsy-string} given
			add_action( 'admin_bar_menu', [ $this, $this->get_id() . '_admin_add_top_menu' ], 70 );

		}
	}

	/**
	 * Add the admin menu
	 * @see \Lumiere\Admin
	 */
	public static function lumiere_static_start(): void {
		$admin_menu_class = new self();
	}

	/**
	 * Display admin notices
	 *
	 * @since 4.0 using transients for displaying cache notice messages
	 */
	public function lumiere_admin_display_messages(): void {

		// Display message for new taxonomy found.
		$new_taxo_template = $this->lumiere_new_taxo();
		if ( isset( $new_taxo_template ) ) {
			echo wp_kses(
				$this->lumiere_notice(
					6,
					esc_html__( 'New taxonomy template file(s) found: ', 'lumiere-movies' )
					. implode( ' & ', $new_taxo_template )
					/* translators: %1$s and %2$s are HTML 'a' tags links */
					. '. ' . wp_kses( sprintf( __( 'Please %1$supdate%2$s.', 'lumiere-movies' ), '<a href="' . $this->page_data_taxo . '#imdb_imdbtaxonomyactor_yes">', '</a>' ), [ 'a' => [ 'href' => [] ] ] )
				),
				[
					'div' => [ 'class' => [] ],
					'p' => [],
				]
			);
		}

		// Messages for notification using transiants.
		$notif_msg = get_transient( 'notice_lumiere_msg' );
		if ( is_string( $notif_msg ) && array_key_exists( $notif_msg, $this->lumiere_notice_messages ) ) {
			echo wp_kses(
				$this->lumiere_notice(
					$this->lumiere_notice_messages[ $notif_msg ][1],
					esc_html( $this->lumiere_notice_messages[ $notif_msg ][0] )
				),
				[
					'div' => [ 'class' => [] ],
					'p' => [],
				]
			);
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
				[ $this, 'load_view' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Data management', 'lumiere-movies' ),
				esc_html__( 'Data', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_data',
				[ $this, 'load_view' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Cache management options page', 'lumiere-movies' ),
				esc_html__( 'Cache', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_cache',
				[ $this, 'load_view' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Help page', 'lumiere-movies' ),
				esc_html__( 'Help', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_help',
				[ $this, 'load_view' ],
			);

			// Left menu
		} elseif ( isset( $this->imdb_admin_values['imdbwordpress_bigmenu'] ) && $this->imdb_admin_values['imdbwordpress_bigmenu'] === '1' ) {

			add_menu_page(
				esc_html__( 'Lumière options page', 'lumiere-movies' ),
				esc_html__( 'Lumière', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id,
				[ $this, 'load_view' ],
				$this->config_class->lumiere_pics_dir . 'lumiere-ico13x13.png',
				65
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Lumière general page', 'lumiere-movies' ),
				esc_html__( 'Main', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id,
				[ $this, 'load_view' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Data management', 'lumiere-movies' ),
				esc_html__( 'Data', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_data',
				[ $this, 'load_view' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Cache management options page', 'lumiere-movies' ),
				esc_html__( 'Cache', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_cache',
				[ $this, 'load_view' ],
			);
			add_submenu_page(
				$this->menu_id,
				esc_html__( 'Help page', 'lumiere-movies' ),
				esc_html__( 'Help', 'lumiere-movies' ),
				'manage_options',
				$this->menu_id . '_help',
				[ $this, 'load_view' ],
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
	 * Load views
	 * Called in {@see Admin::admin_add_top_menu()} and calls the methods dynamically generated according to the current view
	 *
	 * @TODO: The return $this->$method() is also null, investigate why
	 * @TODO: When all classes are using templates only, this method should call dynamically named classes instead of methods
	 *
	 * @return null|callable The private method to call
	 * @phpstan-return null|callable(): void
	 * @throws Exception if method is not found
	 */
	public function load_view(): ?callable {

		$method = $this->get_id() . '_admin_menu' . $this->get_current_page();

		if ( method_exists( $this, $method ) ) {

			return $this->$method();
		}
		throw new Exception( 'This method $this->' . $method . '() does not exist' );
	}

	/**
	 * Display admin General options
	 * This function is dynamically called in { @see Admin::load_view() }
	 */
	private function lumiere_admin_menu(): void {

		// The class.
		$general_class = new General();
		$general_class->display_general_options( new Cache_Tools() );

		$this->lumiere_add_signature_menus();
	}

	/**
	 * Display admin Data options
	 * This function is dynamically called in { @see Admin::load_view() }
	 */
	private function lumiere_admin_menu_data(): void {

		// The class.
		$data_class = new Data();
		$data_class->display_data_options();

		$this->lumiere_add_signature_menus();
	}

	/**
	 * Display admin Cache options
	 * This function is dynamically called in { @see Admin::load_view() }
	 */
	private function lumiere_admin_menu_cache(): void {

		// The class.
		$cache_class = new Cache();
		$cache_class->display_cache_options( new Cache_Tools() );

		$this->lumiere_add_signature_menus();
	}

	/**
	 * Display admin Help options
	 * This function is dynamically called in { @see Admin::load_view() }
	 */
	private function lumiere_admin_menu_help(): void {

		// The class.
		$help_class = new Help();
		$help_class->display_help_layout();

		$this->lumiere_add_signature_menus();
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

	/**
	 * Function checking if item/person template has been updated
	 * Uses lumiere_check_new_taxo() method to check into them folder
	 *
	 * @param null|string $only_one_item If only one taxonomy item has to be checked, pass it, use a loop otherwise
	 * @return array<int, null|string>|null Array of updated templates or null if none
	 */
	protected function lumiere_new_taxo( ?string $only_one_item = null ): ?array {

		$return = [];

		if ( isset( $only_one_item ) ) {
			$key = $this->lumiere_check_new_taxo( $only_one_item );
			if ( $key !== null ) {
				$return[] = $key;
			}
		} else {
			// Build array of people and items from config
			$array_all = array_merge( $this->config_class->array_people, $this->config_class->array_items );
			asort( $array_all );

			foreach ( $array_all as $item ) {
				$key = $this->lumiere_check_new_taxo( $item );
				if ( $key === null ) {
					continue;
				}
				$return[] = $key;
			}
		}
		return count( $return ) > 0 ? $return : null;
	}

	/**
	 * Function checking if item/person template has been updated in the template
	 *
	 * @param string $item String used to build the taxonomy filename that will be checked against the standard taxo
	 * @return null|string
	 */
	private function lumiere_check_new_taxo( string $item ): ?string {

		global $wp_filesystem;

		$return = '';

		// Initial vars
		$version_theme = 'no_theme';
		$version_origin = '';
		$pattern = '~Version: (.+)~i'; // pattern for regex

		// Files paths built based on $item value
		$lumiere_taxo_file_tocopy = in_array( $item, $this->config_class->array_people, true ) ? $this->config_class::TAXO_PEOPLE_THEME : $this->config_class::TAXO_ITEMS_THEME;
		$lumiere_taxo_file_copied = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $item . '.php';
		$lumiere_current_theme_path_file = get_stylesheet_directory() . '/' . $lumiere_taxo_file_copied;
		$lumiere_taxonomy_theme_file = $this->imdb_admin_values['imdbpluginpath'] . $lumiere_taxo_file_tocopy;

		// Make sure we have the credentials to read the files.
		Utils::lumiere_wp_filesystem_cred( $lumiere_current_theme_path_file );

		// Exit if no current file found.
		if ( is_file( $lumiere_current_theme_path_file ) === false ) {
			return null;
		}

		// Get the taxonomy file version in the theme.
		$content_intheme = $wp_filesystem !== null ? $wp_filesystem->get_contents( $lumiere_current_theme_path_file ) : null;
		if ( is_string( $content_intheme ) && preg_match( $pattern, $content_intheme, $match ) === 1 ) {
			$version_theme = $match[1];
		}

		// Get the taxonomy file version in the lumiere theme folder.
		$content_inplugin = $wp_filesystem !== null ? $wp_filesystem->get_contents( $lumiere_taxonomy_theme_file ) : null;
		if ( is_string( $content_inplugin ) && preg_match( $pattern, $content_inplugin, $match ) === 1 ) {
			$version_origin = $match[1];
		}

		// If version in theme file is older, build the filename and the return it.
		if ( $version_theme !== $version_origin ) {
			$return = $item;
		}
		return strlen( $return ) > 0 ? $return : null;
	}

}
