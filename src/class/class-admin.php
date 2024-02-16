<?php declare( strict_types = 1 );
/**
 * Admin class for displaying all Admin sections.
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
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

class Admin {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Variable to allow automatic download of highslide when not found in package
	 * Unactivated on WP plugin team request
	 *
	 * @var bool $activate_highslide_download true if allowing download
	 */
	protected bool $activate_highslide_download = false;

	/**
	 * \Lumière\Utils class
	 */
	protected Utils $utils_class;

	/**
	 * \Lumiere\Plugins\Logger class
	 */
	protected Logger $logger;

	/**
	 * Store root directories of the plugin
	 * Path: absolute path
	 * URL: start with https
	 *
	 */
	protected string $root_path = '';
	protected string $root_url = '';
	protected string $page_cache_manage;
	protected string $page_cache_option;
	protected string $page_data;
	protected string $page_general_base;
	protected string $page_general_advanced;

	/**
	 * HTML allowed for use of wp_kses()
	 */
	const ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS = [
		'i' => true,
	];

	/**
	 * Notification messages
	 * @var array<string, string> $lumiere_notice_messages
	 */
	private array $lumiere_notice_messages = [
		'options_updated' => 'Options saved.',
		'options_reset' => 'Options reset.',
		'cache_options_update_msg' => 'Cache options saved.',
		'cache_options_refresh_msg' => 'Cache options reset.',
		'cache_delete_all_msg' => 'All cache files deleted.',
		'cache_delete_ticked_msg' => 'Ticked file(s) deleted.',
		'cache_delete_individual_msg' => 'Selected cache file deleted.',
		'cache_refresh_individual_msg' => 'Selected cache file refreshed.',
		'cache_query_deleted' => 'Query cache files deleted.',
		'taxotemplatecopy_success' => 'Template successfully copied.',
		'taxotemplatecopy_failed' => 'Template copy failed!',
		'highslide_success' => 'Highslide successfully installed!',
		'highslide_failure' => 'Highslide installation failed!',
		'highslide_down' => 'Website to download Highslide is currently down, please try again later.',
		'highslide_website_unkown' => 'Website variable is not set.',
	];

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

		// Start Utilities class
		$this->utils_class = new Utils();

		// Start Logger class
		$this->logger = new Logger( 'adminClass' );

		// Build constants
		$this->root_url = plugin_dir_url( __DIR__ );
		$this->root_path = plugin_dir_path( __DIR__ );
		$this->page_cache_manage = admin_url( 'admin.php?page=lumiere_options&subsection=cache&cacheoption=manage' );
		$this->page_cache_option = admin_url( 'admin.php?page=lumiere_options&subsection=cache&cacheoption=option' );
		$this->page_data = admin_url( 'admin.php?page=lumiere_options&subsection=dataoption' );
		$this->page_general_base = admin_url( 'admin.php?page=lumiere_options&generaloption=base' );
		$this->page_general_advanced = admin_url( 'admin.php?page=lumiere_options&generaloption=advanced' );

		// Start the debug
		// If runned earlier, such as 'admin_init', breaks block editor edition.
		add_action( 'wp', [ $this, 'lumiere_admin_maybe_start_debug' ], 0 );

		// Display notices.
		add_action( 'admin_notices', [ $this, 'lumiere_admin_display_messages' ] );

		// Install highslide if selected and if correct page.
		add_action(
			'admin_init',
			function(): void {
				if ( str_contains( $_SERVER['REQUEST_URI'] ?? '', 'admin/admin.php?page=lumiere_options&highslide=yes' ) && $this->activate_highslide_download === true ) {
					// This page is not a class, therefore must be included manually.
					require_once plugin_dir_path( __DIR__ ) . \Lumiere\Settings::HIGHSLIDE_DOWNLOAD_PAGE;
				}
			}
		);

	}

	/**
	 * Display admin notices
	 *
	 * @since 3.12 using transients for display cache notice messages
	 */
	public function lumiere_admin_display_messages(): void {

		// Exit if it is not a Lumière! admin page.
		if ( ! Utils::lumiere_array_contains_term(
			[
				'admin.php?page=lumiere_options',
				'options-general.php?page=lumiere_options',
			],
			$_SERVER['REQUEST_URI'] ?? ''
		) ) {
			return;
		}

		$new_taxo_template = $this->lumiere_new_taxo();
		if ( isset( $new_taxo_template ) ) {
			echo Utils::lumiere_notice(
				6,
				esc_html__( 'New taxonomy template file(s) found: ', 'lumiere-movies' )
				. implode( ' & ', $new_taxo_template )
				. '. ' . esc_html__( 'Please ', 'lumiere-movies' ) . '<a href="'
				. esc_url(
					admin_url() . 'admin.php?page=lumiere_options&subsection=dataoption&widgetoption=taxo#imdb_imdbtaxonomyactor_yes'
				)
				. '">' . esc_html__( 'update', 'lumiere-movies' ) . '</a>.'
			);
		}

		// Messages for child classes.
		$notif_msg = get_transient( 'notice_lumiere_msg' );
		if ( is_string( $notif_msg ) && array_key_exists( $notif_msg, $this->lumiere_notice_messages ) ) {
			echo Utils::lumiere_notice( 1, esc_html( $this->lumiere_notice_messages[ $notif_msg ] ) );
		}
	}

	/**
	 *  Wrapps the start of the debugging
	 */
	public function lumiere_admin_maybe_start_debug(): void {

		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( '1' === $this->imdb_admin_values['imdbdebug'] ) && ( $this->utils_class->debug_is_active === false ) ) {

			// Start debugging mode
			$this->utils_class->lumiere_activate_debug();

		}

	}

	/**
	 * Add the admin menu
	 * Called by class core
	 *
	 */
	public function lumiere_admin_menu(): void {

		// Store the logger class
		do_action( 'lumiere_logger' );

		add_action( 'admin_menu', [ &$this, 'lumiere_add_left_menu' ] );

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

		// Menu inside settings
		if ( function_exists( 'add_options_page' ) && ( ( isset( $this->imdb_admin_values['imdbwordpress_bigmenu'] ) ) && ( $this->imdb_admin_values['imdbwordpress_bigmenu'] === '0' ) ) ) {

			add_options_page(
				'Lumière Options',
				'<img src="' . $this->config_class->lumiere_pics_dir . 'lumiere-ico13x13.png" align="absmiddle"> Lumière',
				'administrator',
				'lumiere_options',
				[ $this, 'lumiere_admin_pages' ]
			);

			// Left menu
		} elseif ( function_exists( 'add_submenu_page' ) && ( ( isset( $this->imdb_admin_values['imdbwordpress_bigmenu'] ) ) && ( $this->imdb_admin_values['imdbwordpress_bigmenu'] === '1' ) ) ) {

			add_menu_page(
				'Lumière Options',
				'<i>Lumière</i>',
				'administrator',
				'lumiere_options',
				[ $this, 'lumiere_admin_pages' ],
				$this->config_class->lumiere_pics_dir . 'lumiere-ico13x13.png',
				65
			);
			add_submenu_page(
				'lumiere_options',
				esc_html__(
					'Lumière options page',
					'lumiere-movies'
				),
				esc_html__( 'General', 'lumiere-movies' ),
				'administrator',
				'lumiere_options',
				[ $this, 'lumiere_admin_pages' ]
			);
			add_submenu_page(
				'lumiere_options',
				esc_html__( 'Data management', 'lumiere-movies' ),
				esc_html__( 'Data', 'lumiere-movies' ),
				'administrator',
				'lumiere_options&subsection=dataoption',
				[ $this, 'lumiere_admin_pages' ]
			);
			add_submenu_page(
				'lumiere_options',
				esc_html__( 'Cache management options page', 'lumiere-movies' ),
				esc_html__( 'Cache', 'lumiere-movies' ),
				'administrator',
				'lumiere_options&subsection=cache',
				[ $this, 'lumiere_admin_pages' ]
			);
			add_submenu_page(
				'lumiere_options',
				esc_html__( 'Help page', 'lumiere-movies' ),
				esc_html__( 'Help', 'lumiere-movies' ),
				'administrator',
				'lumiere_options&subsection=help',
				[ $this, 'lumiere_admin_pages' ]
			);

		}

	}

	/**
	 * Add top admin menu
	 *
	 */
	public function admin_add_top_menu( \WP_Admin_Bar $admin_bar ): void {

		$admin_bar->add_menu(
			[
				'id' => 'lumiere_top_menu',
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "lumiere-ico13x13.png' width='16' height='16' />&nbsp;&nbsp;" . 'Lumière',
				'href' => 'admin.php?page=lumiere_options',
				'meta' =>
					[
						'title' => esc_html__( 'Lumière Menu', 'lumiere-movies' ),
					],
			]
		);

		$admin_bar->add_menu(
			[
				'parent' => 'lumiere_top_menu',
				'id' => 'lumiere_top_menu_general',
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "menu/admin-general.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'General', 'lumiere-movies' ),
				'href' => 'admin.php?page=lumiere_options',
				'meta' =>
					[
						'title' => esc_html__( 'Main and advanced options', 'lumiere-movies' ),
					],
			]
		);
		$admin_bar->add_menu(
			[
				'parent' => 'lumiere_top_menu',
				'id' => 'lumiere_top_menu_data',
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "menu/admin-widget-inside.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Data', 'lumiere-movies' ),
				'href' => 'admin.php?page=lumiere_options&subsection=dataoption',
				'meta' =>
					[
						'title' => esc_html__( 'Data option and taxonomy', 'lumiere-movies' ),
					],
			]
		);
		$admin_bar->add_menu(
			[
				'parent' => 'lumiere_top_menu',
				'id' => 'lumiere_top_menu_cache',
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "menu/admin-cache.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Cache', 'lumiere-movies' ),
				'href' => 'admin.php?page=lumiere_options&subsection=cache',
				'meta' =>
					[
						'title' => esc_html__( 'Cache options', 'lumiere-movies' ),
					],
			]
		);

		$admin_bar->add_menu(
			[
				'parent' => 'lumiere_top_menu',
				'id' => 'lumiere_top_menu_help',
				'title' => "<img src='" . $this->config_class->lumiere_pics_dir . "menu/admin-help.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Help', 'lumiere-movies' ),
				'href' => 'admin.php?page=lumiere_options&subsection=help',
				'meta' =>
					[
						'title' => esc_html__( 'Get support and support plugin development', 'lumiere-movies' ),

					],
			]
		);

	}

	/**
	 * Display admin pages
	 *
	 */
	public function lumiere_admin_pages(): void {

		// Start logging using hook defined in settings class.
		do_action( 'lumiere_logger' );

		$this->display_admin_menu();

		$this->display_admin_menu_subpages();

	}

	/**
	 * Display main menu
	 *
	 */
	private function display_admin_menu(): void {

		$imdb_admin_values = $this->imdb_admin_values;
		$imdb_widget_values = $this->imdb_widget_values;
		$imdb_cache_values = $this->imdb_cache_values;

		?>

	<div class=wrap>

		<h2 class="imdblt_padding_bottom_right_fifteen"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'lumiere-ico80x80.png' ); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;<i>Lumière!</i>&nbsp;<?php esc_html_e( 'admin options', 'lumiere-movies' ); ?></h2>

		<div class="subpage">
			<div align="left" class="imdblt_double_container">

				<div class="imdblt_padding_five imdblt_flex_auto">
					<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-general.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'General Options', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options' ); ?>"> <?php esc_html_e( 'General Options', 'lumiere-movies' ); ?></a>
				</div>

				<?php // Data subpage is relative to what is activated. ?>

				<div class="imdblt_padding_five imdblt_flex_auto">
					<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-widget-inside.png' ); ?>" align="absmiddle" width="16px" />&nbsp;


					<a title="<?php esc_html_e( 'Data Management', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=dataoption' ); ?>"><?php esc_html_e( 'Data Management', 'lumiere-movies' ); ?></a>

		<?php
		// Check if both widgets is are inactive (pre/post-5.8, aka block & legacy blocks)
		if ( Utils::lumiere_block_widget_isactive( Settings::BLOCK_WIDGET_NAME ) === false && is_active_widget( false, false, Settings::WIDGET_NAME, false ) === false ) {
			?>

					- <em><font size=-2><a href="<?php echo esc_url( admin_url() . 'widgets.php' ); ?>"><?php esc_html_e( 'Widget unactivated', 'lumiere-movies' ); ?></a></font></em>

			<?php
		}
		if ( ( $imdb_admin_values['imdbtaxonomy'] === '0' ) || ( ! isset( $this->imdb_admin_values['imdbtaxonomy'] ) ) ) {

			?> - <em><font size=-2><a href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&generaloption=advanced#imdb_imdbtaxonomy_yes' ); ?>"><?php esc_html_e( 'Taxonomy unactivated', 'lumiere-movies' ); ?></font></em>

	<?php } ?>

				</div>

				<div class="imdblt_padding_five imdblt_flex_auto">			
					<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-cache.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'Cache management', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=cache' ); ?>"><?php esc_html_e( 'Cache management', 'lumiere-movies' ); ?></a>
				</div>

				<div align="right" class="imdblt_padding_five imdblt_flex_auto" >
					<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-help.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'How to use Lumière!, check FAQs & changelog', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=help' ); ?>">
						<i>Lumière!</i> <?php esc_html_e( 'help', 'lumiere-movies' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 *  Display admin submenu
	 *
	 */
	private function display_admin_menu_subpages(): void {

		if ( ! isset( $_GET['subsection'] ) ) {

			// Make sure cache folder exists and is writable
			$this->config_class->lumiere_create_cache( true );

			$general_class = new General();
			$general_class->lumiere_general_layout();

		}

		if ( ( isset( $_GET['subsection'] ) ) && ( $_GET['subsection'] === 'dataoption' ) ) {

			$data_class = new Data();
			$data_class->lumiere_data_layout();

		} elseif ( ( isset( $_GET['subsection'] ) ) && ( $_GET['subsection'] === 'cache' ) ) {

			// Make sure cache folder exists and is writable
			$this->config_class->lumiere_create_cache( true );

			$cache_class = new Cache();
			$cache_class->lumiere_cache_layout();

		} elseif ( ( isset( $_GET['subsection'] ) ) && ( $_GET['subsection'] === 'help' ) ) {

			$help_class = new Help();
			$help_class->lumiere_admin_help_layout();
		}

		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $this->utils_class->lumiere_admin_signature();
		?>
	</div><!-- .wrap -->

		<?php
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
	 * Function checking if item/person template has been updated in them template
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
}
