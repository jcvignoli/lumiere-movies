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

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Tools\Utils;
use Lumiere\Plugins\Logger;
use Lumiere\Admin\General;
use Lumiere\Admin\Data;
use Lumiere\Admin\Cache;
use Lumiere\Admin\Help;
use Lumiere\Admin\Cache_Tools;

class Admin {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	protected Utils $utils_class;
	protected Logger $logger;

	/**
	 * Store root directories of the plugin
	 * Path: absolute path
	 * URL: start with https
	 */
	protected string $root_path = '';
	protected string $root_url = '';
	protected string $page_cache_manage;
	protected string $page_cache_option;
	protected string $page_data;
	protected string $page_data_taxo;
	protected string $page_general_base;
	protected string $page_general_advanced;
	protected string $page_general_help;

	/**
	 * HTML allowed for use of wp_kses()
	 */
	const ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS = [
		'i' => true,
	];

	/**
	 * Used to define name of functions
	 */
	const LUMIERE_ADMIN_ID = 'lumiere';

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
		'cache_delete_individual_msg' => [ 'Selected cache file deleted.', 1 ],
		'cache_refresh_individual_msg' => [ 'Selected cache file refreshed.', 1 ],
		'cache_query_deleted' => [ 'Query cache files deleted.', 1 ],
		'taxotemplatecopy_success' => [ 'Template successfully copied.', 1 ],
		'taxotemplatecopy_failed' => [ 'Template copy failed!', 3 ],
	];

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

		// Start Utilities class.
		$this->utils_class = new Utils();

		// Start Logger class.
		$this->logger = new Logger( 'adminClass' );

		// Build constants
		$this->root_url = plugin_dir_url( __DIR__ );
		$this->root_path = plugin_dir_path( __DIR__ );
		$this->page_cache_manage = admin_url( 'admin.php?page=' . $this->get_id() . '_options_cache&cacheoption=manage' );
		$this->page_cache_option = admin_url( 'admin.php?page=' . $this->get_id() . '_options_cache' );
		$this->page_data = admin_url( 'admin.php?page=' . $this->get_id() . '_options_data' );
		$this->page_data_taxo = admin_url( 'admin.php?page=' . $this->get_id() . '_options_data&widgetoption=taxo' );
		$this->page_general_base = admin_url( 'admin.php?page=' . $this->get_id() . '_options' );
		$this->page_general_advanced = admin_url( 'admin.php?page=' . $this->get_id() . '_options&generaloption=advanced' );
		$this->page_general_help = admin_url( 'admin.php?page=' . $this->get_id() . '_options_help' );

		// Start the debug
		// If runned earlier, such as 'admin_init', breaks block editor edition.
		add_action( 'wp', [ $this, 'lumiere_admin_maybe_start_debug' ], 0 );

		// Display notices based on transients.
		add_action( 'admin_notices', [ $this, 'lumiere_admin_display_messages' ] );

		/**
		 * Settings saved/reset, files deleted/refreshed
		 * Based on the $_GET and $_POSTS, the methods refreshing/saving/deleting will be processed
		 * @see Save_Options::process_headers()
		 * @since 3.12
		 */
		add_action( 'wp_loaded', [ 'Lumiere\Admin\Save_Options', 'lumiere_static_start' ] );
	}

	/**
	 * Display admin notices
	 *
	 * @since 3.12 using transients for display cache notice messages
	 */
	public function lumiere_admin_display_messages(): void {

		// Display message for new taxonomy found.
		$new_taxo_template = $this->lumiere_new_taxo();
		if ( isset( $new_taxo_template ) ) {
			echo Utils::lumiere_notice(
				6,
				esc_html__( 'New taxonomy template file(s) found: ', 'lumiere-movies' )
				. implode( ' & ', $new_taxo_template )
				. '. ' . esc_html__( 'Please ', 'lumiere-movies' ) . '<a href="'
				. $this->page_data . '&widgetoption=taxo#imdb_imdbtaxonomyactor_yes'
				. '">' . esc_html__( 'update', 'lumiere-movies' ) . '</a>.'
			);
		}

		// Messages for notification using transiants.
		$notif_msg = get_transient( 'notice_lumiere_msg' );
		if ( is_string( $notif_msg ) && array_key_exists( $notif_msg, $this->lumiere_notice_messages ) ) {
			echo Utils::lumiere_notice(
				$this->lumiere_notice_messages[ $notif_msg ][1],
				esc_html( $this->lumiere_notice_messages[ $notif_msg ][0] )
			);
		}
	}

	/**
	 * Wrapps the start of the debugging
	 */
	public function lumiere_admin_maybe_start_debug(): void {

		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( '1' === $this->imdb_admin_values['imdbdebug'] ) && ( $this->utils_class->debug_is_active === false ) ) {
			// Start debugging mode
			$this->utils_class->lumiere_activate_debug();
		}
	}

	/**
	 * Get id
	 */
	private function get_id(): string {
		return self::LUMIERE_ADMIN_ID;
	}

	/**
	 * Get id
	 * @phpstan-return non-falsy-string
	 */
	private function get_method( string $template_name ): string {

		$method = $this->get_id() . '_admin_menu_' . $template_name;
		if ( method_exists( $this, $method ) && is_callable( [ $this, $method ] ) ) {
			return $method;
		}
		throw new \Exception( 'This method ' . $method . ' does not exist' );
	}

	/**
	 * Add the admin menu
	 * Called by class core
	 *
	 */
	public function lumiere_admin_menu(): void {

		// Store the logger class
		do_action( 'lumiere_logger' );

		// @phpstan-ignore-next-line -- Parameter #2 $callback of function add_action expects callable(): mixed, array{$this(Lumiere\Admin), non-falsy-string} given
		add_action( 'admin_menu', [ &$this, $this->get_id() . '_add_left_menu' ] );

		// add imdblt menu in toolbar menu (top WordPress menu)
		if ( $this->imdb_admin_values['imdbwordpress_tooladminmenu'] === '1' ) {
			add_action( 'admin_bar_menu', [ &$this, 'admin_add_top_menu' ], 70 );

		}
	}

	/**
	 * Add left admin menu
	 *
	 */
	public function lumiere_add_left_menu(): void {

		$menu_id = $this->get_id() . '_options';

		// Menu inside settings
		if ( isset( $this->imdb_admin_values['imdbwordpress_bigmenu'] ) && $this->imdb_admin_values['imdbwordpress_bigmenu'] === '0' ) {

			add_options_page(
				'Lumière Options',
				'<img src="' . $this->config_class->lumiere_pics_dir . 'lumiere-ico13x13.png" align="absmiddle"> Lumière',
				'manage_options',
				$menu_id,
				// @phpstan-ignore-next-line -- Parameter #6 $callback of function add_submenu_page expects callable(): mixed
				[ $this, $this->get_method( 'general' ) ]
			);

			add_submenu_page(
				$menu_id,
				esc_html__( 'Data management', 'lumiere-movies' ),
				esc_html__( 'Data', 'lumiere-movies' ),
				'manage_options',
				'lumiere_options_data',
				// @phpstan-ignore-next-line -- Parameter #6 $callback of function add_submenu_page expects callable(): mixed
				[ $this, $this->get_method( 'data' ) ]
			);
			add_submenu_page(
				$menu_id,
				esc_html__( 'Cache management options page', 'lumiere-movies' ),
				esc_html__( 'Cache', 'lumiere-movies' ),
				'manage_options',
				'lumiere_options_cache',
				// @phpstan-ignore-next-line -- Parameter #6 $callback of function add_submenu_page expects callable(): mixed
				[ $this, $this->get_method( 'cache' ) ]
			);
			add_submenu_page(
				$menu_id,
				esc_html__( 'Help page', 'lumiere-movies' ),
				esc_html__( 'Help', 'lumiere-movies' ),
				'manage_options',
				'lumiere_options_help',
				// @phpstan-ignore-next-line -- Parameter #6 $callback of function add_submenu_page expects callable(): mixed
				[ $this, $this->get_method( 'help' ) ]
			);

			// Left menu
		} elseif ( isset( $this->imdb_admin_values['imdbwordpress_bigmenu'] ) && $this->imdb_admin_values['imdbwordpress_bigmenu'] === '1' ) {

			add_menu_page(
				esc_html__( 'Lumière options page', 'lumiere-movies' ),
				esc_html__( 'Lumière', 'lumiere-movies' ),
				'manage_options',
				$menu_id,
				// @phpstan-ignore-next-line -- Parameter #6 $callback of function add_submenu_page expects callable(): mixed
				[ $this, $this->get_method( 'general' ) ],
				$this->config_class->lumiere_pics_dir . 'lumiere-ico13x13.png',
				65
			);

			add_submenu_page(
				$menu_id,
				esc_html__( 'Data management', 'lumiere-movies' ),
				esc_html__( 'Data', 'lumiere-movies' ),
				'manage_options',
				'lumiere_options_data',
				// @phpstan-ignore-next-line -- Parameter #6 $callback of function add_submenu_page expects callable(): mixed
				[ $this, $this->get_method( 'data' ) ]
			);
			add_submenu_page(
				$menu_id,
				esc_html__( 'Cache management options page', 'lumiere-movies' ),
				esc_html__( 'Cache', 'lumiere-movies' ),
				'manage_options',
				'lumiere_options_cache',
				// @phpstan-ignore-next-line -- Parameter #6 $callback of function add_submenu_page expects callable(): mixed
				[ $this, $this->get_method( 'cache' ) ]
			);
			add_submenu_page(
				$menu_id,
				esc_html__( 'Help page', 'lumiere-movies' ),
				esc_html__( 'Help', 'lumiere-movies' ),
				'manage_options',
				'lumiere_options_help',
				// @phpstan-ignore-next-line -- Parameter #6 $callback of function add_submenu_page expects callable(): mixed
				[ $this, $this->get_method( 'help' ) ]
			);
		}
	}

	/**
	 * Add top Admin menu
	 */
	public function admin_add_top_menu( \WP_Admin_Bar $admin_bar ): void {

		$id = $this->get_id() . '_top_menu';

		$admin_bar->add_menu(
			[
				'id' => $id,
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "lumiere-ico13x13.png' width='16' height='16' />&nbsp;&nbsp;" . 'Lumière',
				'parent' => null,
				'href' => $this->page_general_base,
				'meta' =>
					[
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
				'meta' =>
					[
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
				'meta' =>
					[
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
				'meta' =>
					[
						'title' => esc_html__( 'Cache options', 'lumiere-movies' ),
					],
			]
		);

		$admin_bar->add_menu(
			[
				'parent' => $id,
				'id' => $this->get_id() . '_top_menu_help',
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "menu/admin-help.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Help', 'lumiere-movies' ),
				'href' => $this->page_general_help,
				'meta' =>
					[
						'title' => esc_html__( 'Get support and support plugin development', 'lumiere-movies' ),

					],
			]
		);

	}

	/**
	 * Display admin General options
	 */
	public function lumiere_admin_menu_general(): void {

		// Make sure cache folder exists and is writable
		$cache_tools_class = new Cache_Tools();
		$cache_tools_class->lumiere_create_cache( true );

		$this->display_admin_menu_first_part();

		$general_class = new General();
		$general_class->lumiere_general_display_submenu();
		$general_class->lumiere_general_display_body();

		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $this->utils_class->lumiere_admin_signature();
	}

	/**
	 * Display admin Data options
	 */
	public function lumiere_admin_menu_data(): void {

		$this->display_admin_menu_first_part();

		$data_class = new Data();
		$data_class->lumiere_data_display_submenu();
		$data_class->lumiere_data_display_body();

		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $this->utils_class->lumiere_admin_signature();
	}

	/**
	 * Display admin Cache options
	 */
	public function lumiere_admin_menu_cache(): void {

		// Make sure cache folder exists and is writable
		$cache_tools_class = new Cache_Tools();
		$cache_tools_class->lumiere_create_cache( true );

		$this->display_admin_menu_first_part();

		$cache_class = new Cache( $cache_tools_class );
		$cache_class->lumiere_cache_display_submenu();
		$cache_class->lumiere_cache_display_body();

		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $this->utils_class->lumiere_admin_signature();
	}

	/**
	 * Display admin Help options
	 */
	public function lumiere_admin_menu_help(): void {

		$this->display_admin_menu_first_part();

		$help_class = new Help();
		$help_class->lumiere_admin_help_layout();

		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $this->utils_class->lumiere_admin_signature();
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
		$lumiere_taxo_file_tocopy = in_array( $item, $this->config_class->array_people, true ) ? Settings::TAXO_PEOPLE_THEME : Settings::TAXO_ITEMS_THEME;
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

	/**
	 * Display the first piece of the main menu
	 * Using a template
	 */
	private function display_admin_menu_first_part(): void {
		// Pass the self class as variable for later use.
		set_transient( 'admin_template_this', $this, 2 );
		// The template will retrieve the transient with get_transient().
		require_once plugin_dir_path( __FILE__ ) . 'admin/templates/admin-menu-first-part.php';
	}
}
