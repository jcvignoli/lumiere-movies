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
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use \Lumiere\Settings;
use \Lumiere\Utils;
use \Lumiere\Logger;
use \Lumiere\Admin\General;
use \Lumiere\Admin\Data;
use \Lumiere\Admin\Cache;
use \Lumiere\Admin\Help;

class Admin {

	/**
	 * Admin options
	 * @var array{ 'imdbplugindirectory': string, 'imdbplugindirectory_partial': string, 'imdbpluginpath': string,'imdburlpopups': string,'imdbkeepsettings': string,'imdburlstringtaxo': string,'imdbcoversize': string,'imdbcoversizewidth': string, 'imdbmaxresults': int, 'imdbpopuptheme': string, 'imdbpopuplarg': string,'imdbpopuplong': string, 'imdbintotheposttheme': string, 'imdblinkingkill': string, 'imdbautopostwidget': string, 'imdblanguage': string, 'imdbdebug': string, 'imdbdebuglog': string, 'imdbdebuglogpath': string, 'imdbdebuglevel': string, 'imdbdebugscreen': string, 'imdbwordpress_bigmenu': string, 'imdbwordpress_tooladminmenu': string, 'imdbpopup_highslide': string, 'imdbtaxonomy': string, 'imdbHowManyUpdates': int, 'imdbseriemovies': string } $imdb_admin_values
	 */
	protected array $imdb_admin_values;

	/**
	 * Widget options
	 * @var array{'imdbwidgettitle': string, 'imdbwidgetpic': string,'imdbwidgetruntime': string, 'imdbwidgetdirector': string, 'imdbwidgetcountry': string, 'imdbwidgetactor':string, 'imdbwidgetactornumber':int, 'imdbwidgetcreator': string, 'imdbwidgetrating': string, 'imdbwidgetlanguage': string, 'imdbwidgetgenre': string, 'imdbwidgetwriter': string, 'imdbwidgetproducer': string, 'imdbwidgetproducernumber': bool|string, 'imdbwidgetkeyword': string, 'imdbwidgetprodcompany': string, 'imdbwidgetplot': string, 'imdbwidgetplotnumber': string, 'imdbwidgetgoof': string, 'imdbwidgetgoofnumber': string|bool, 'imdbwidgetcomment': string, 'imdbwidgetquote': string, 'imdbwidgetquotenumber': string|bool, 'imdbwidgettagline': string, 'imdbwidgettaglinenumber': string|bool, 'imdbwidgetcolor': string, 'imdbwidgetalsoknow': string, 'imdbwidgetalsoknownumber': string|bool, 'imdbwidgetcomposer': string, 'imdbwidgetsoundtrack': string, 'imdbwidgetsoundtracknumber': string|bool, 'imdbwidgetofficialsites': string, 'imdbwidgetsource': string, 'imdbwidgetyear': string, 'imdbwidgettrailer': string, 'imdbwidgettrailernumber': bool|string, 'imdbwidgetorder': array<string>, 'imdbtaxonomycolor': string, 'imdbtaxonomycomposer': string, 'imdbtaxonomycountry': string, 'imdbtaxonomycreator': string, 'imdbtaxonomydirector': string, 'imdbtaxonomygenre': string, 'imdbtaxonomykeyword': string, 'imdbtaxonomylanguage': string, 'imdbtaxonomyproducer': string, 'imdbtaxonomyactor': string, 'imdbtaxonomywriter': string} $imdb_widget_values
	 */
	protected array $imdb_widget_values;

	/**
	 * Cache options
	 * @var array{'imdbcachedir_partial': string, 'imdbstorecache': bool, 'imdbusecache': string, 'imdbconverttozip': bool, 'imdbusezip': bool, 'imdbcacheexpire': string, 'imdbcachedetailsshort': string,'imdbcachedir': string,'imdbphotoroot': string, 'imdbphotodir': string} $imdb_cache_values
	 */
	protected array $imdb_cache_values;

	/**
	 * \Lumiere\Settings class
	 */
	protected Settings $configClass;

	/**
	 * \Lumière\Utils class
	 */
	protected Utils $utilsClass;

	/**
	 * \Lumiere\Logger class
	 */
	protected Logger $logger;

	/**
	 * Store root directories of the plugin
	 * Path: absolute path
	 * URL: start with https
	 *
	 */
	protected string $rootPath = '';
	protected string $rootURL = '';

	/**
	 * HTML allowed for use of wp_kses()
	 */
	const ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS = [
		'i' => true,
	];

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Load all classes in class/Admin folder, will be loaded when needed
		spl_autoload_register( [ 'Lumiere\Admin', 'lumiere_admin_loader' ] );

		// Get database options.
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		$this->imdb_widget_values = get_option( Settings::LUMIERE_WIDGET_OPTIONS );
		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

		// Start Settings class.
		$this->configClass = new Settings();

		// Start Utilities class
		$this->utilsClass = new Utils();

		// Start Logger class
		$this->logger = new Logger( 'adminClass' );

		// Build constants
		$this->rootURL = plugin_dir_url( __DIR__ );
		$this->rootPath = plugin_dir_path( __DIR__ );

		// Start the debug
		// If runned earlier, such as 'admin_init', breaks block editor edition.
		add_action( 'wp', [ $this, 'lumiere_admin_maybe_start_debug' ], 0 );

		// Display notices.
		add_action( 'admin_notices', [ $this, 'lumiere_admin_display_messages' ] );

	}

	/**
	 * Load all files included in class/admin
	 * Loaded in spl_autoload_register() in __construct()
	 *
	 * @param string $class_name Class name automagically retrieved from spl_autoload_register()
	 */
	public function lumiere_admin_loader( string $class_name ): void {

		$parts = explode( '\\', $class_name );
		$class = 'class-' . strtolower( array_pop( $parts ) );
		$folder = strtolower( implode( DIRECTORY_SEPARATOR, $parts ) );
		$folder_cleaned = str_replace( 'lumiere/', '', $folder );

		// Final path for inclusion
		$classpath = plugin_dir_path( __DIR__ ) . 'class' . DIRECTORY_SEPARATOR . $folder_cleaned . DIRECTORY_SEPARATOR . $class . '.php';

		if ( file_exists( $classpath ) ) {

			require_once $classpath;

		}

	}

	/**
	 *  Display admin notices
	 *
	 */
	public function lumiere_admin_display_messages(): ?string {
		return null;
	}

	/**
	 *  Wrapps the start of the debugging
	 */
	public function lumiere_admin_maybe_start_debug(): void {

		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( '1' === $this->imdb_admin_values['imdbdebug'] ) && ( $this->utilsClass->debug_is_active === false ) ) {

			// Start debugging mode
			$this->utilsClass->lumiere_activate_debug();

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
				'<img src="' . $this->configClass->lumiere_pics_dir . 'lumiere-ico13x13.png" align="absmiddle"> Lumière',
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
				$this->configClass->lumiere_pics_dir . 'lumiere-ico13x13.png',
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
				'title' => "<img src='" . $this->configClass->lumiere_pics_dir . "lumiere-ico13x13.png' width='16' height='16' />&nbsp;&nbsp;" . 'Lumière',
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
				'title' => "<img src='" . $this->configClass->lumiere_pics_dir . "menu/admin-general.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'General', 'lumiere-movies' ),
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
				'title' => "<img src='" . $this->configClass->lumiere_pics_dir . "menu/admin-widget-inside.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Data', 'lumiere-movies' ),
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
				'title' => "<img src='" . $this->configClass->lumiere_pics_dir . "menu/admin-cache.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Cache', 'lumiere-movies' ),
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
				'title' => "<img src='" . $this->configClass->lumiere_pics_dir . "menu/admin-help.png' width='16px' />&nbsp;&nbsp;" . esc_html__( 'Help', 'lumiere-movies' ),
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

		<h2 class="imdblt_padding_bottom_right_fifteen"><img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'lumiere-ico80x80.png' ); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;<i>Lumière!</i>&nbsp;<?php esc_html_e( 'admin options', 'lumiere-movies' ); ?></h2>

		<div class="subpage">
			<div align="left" class="imdblt_double_container">

				<div class="imdblt_padding_five imdblt_flex_auto">
					<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-general.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'General Options', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options' ); ?>"> <?php esc_html_e( 'General Options', 'lumiere-movies' ); ?></a>
				</div>

				<?php ### Data subpage is relative to what is activated ?>

				<div class="imdblt_padding_five imdblt_flex_auto">
					<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-widget-inside.png' ); ?>" align="absmiddle" width="16px" />&nbsp;


					<a title="<?php esc_html_e( 'Data Management', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=dataoption' ); ?>"><?php esc_html_e( 'Data Management', 'lumiere-movies' ); ?></a>

		<?php
				// Check if any widget is active:
				// is_active_widget() (pre 5.8 WordPress) or lumiere_block_widget_isactive() (post 5.8)
		if ( ( is_active_widget( false, false, \Lumiere\Widget::WIDGET_NAME, false ) === false ) && ( Utils::lumiere_block_widget_isactive() === false ) ) {
			?>

					- <em><font size=-2><a href="<?php echo esc_url( admin_url() . 'widgets.php' ); ?>"><?php esc_html_e( 'Widget unactivated', 'lumiere-movies' ); ?></a></font></em>

			<?php
		}
		if ( ( $imdb_admin_values['imdbtaxonomy'] === '0' ) || ( ! isset( $this->imdb_admin_values['imdbtaxonomy'] ) ) ) {
			?>

					- <em><font size=-2><a href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&generaloption=advanced#imdb_imdbtaxonomy_yes' ); ?>"><?php esc_html_e( 'Taxonomy unactivated', 'lumiere-movies' ); ?></font></em>

	<?php } ?>

				</div>

				<div class="imdblt_padding_five imdblt_flex_auto">			
					<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-cache.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
					<a title="<?php esc_html_e( 'Cache management', 'lumiere-movies' ); ?>" href="<?php echo admin_url(); ?>admin.php?page=lumiere_options&subsection=cache"><?php esc_html_e( 'Cache management', 'lumiere-movies' ); ?></a>
				</div>

				<div align="right" class="imdblt_padding_five imdblt_flex_auto" >
					<img src="<?php echo esc_url( $this->configClass->lumiere_pics_dir . 'menu/admin-help.png' ); ?>" align="absmiddle" width="16px" />&nbsp;
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

		// select the sub-page.

		if ( ! isset( $_GET['subsection'] ) ) {

			// Make sure cache folder exists and is writable
			$this->configClass->lumiere_create_cache( true );

			$adminGeneral = new General();

		}

		if ( ( isset( $_GET['subsection'] ) ) && ( $_GET['subsection'] === 'dataoption' ) ) {

			$adminData = new Data();

		} elseif ( ( isset( $_GET['subsection'] ) ) && ( $_GET['subsection'] === 'cache' ) ) {

			// Make sure cache folder exists and is writable
			$this->configClass->lumiere_create_cache( true );

			$adminCache = new Cache();

		} elseif ( ( isset( $_GET['subsection'] ) ) && ( $_GET['subsection'] === 'help' ) ) {

			$adminHelp = new Help();

		}
		// end subselection

		echo $this->utilsClass->lumiere_admin_signature();

		?>
	</div><!-- .wrap -->

		<?php
	}

}

