<?php declare( strict_types = 1 );
/**
 * Class of configuration
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */
namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	die( 'You can not call directly this page' );
}

use FilesystemIterator;
use Exception;

/**
 * Configuration class
 * Some settings are created from the outset, others are processed in __construct()
 * On calling __construct(), options are created in database
 *
 * @TODO options should be created only when installing/activating the plugin
 * @since 4.0 moved cache folder creation to class cache tools
 * @since 4.1 renamed *imdb_widget_* to *imdb_data_* all over the website
 *
 * @phpstan-type LevelLogName 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY'
 * @phpstan-type OPTIONS_ADMIN array{'imdbplugindirectory': string, 'imdbplugindirectory_partial': string, 'imdbpluginpath': string,'imdburlpopups': string,'imdbkeepsettings': string,'imdburlstringtaxo': string,'imdbcoversize': string,'imdbcoversizewidth': string, 'imdbmaxresults': string, 'imdbdelayimdbrequest': string, 'imdbpopuptheme': string, 'imdbpopuplarg': string,'imdbpopuplong': string, 'imdbintotheposttheme': string, 'imdblinkingkill': string, 'imdbautopostwidget': string, 'imdblanguage': string, 'imdbdebug': string, 'imdbdebuglog': string, 'imdbdebuglogpath': string, 'imdbdebuglevel': string, 'imdbdebugscreen': string, 'imdbwordpress_bigmenu': string, 'imdbwordpress_tooladminmenu': string, 'imdbpopup_modal_window': string, 'imdbtaxonomy': string, 'imdbHowManyUpdates': string, 'imdbseriemovies': string}
 * @phpstan-type OPTIONS_CACHE array{imdbcacheautorefreshcron: string, imdbcachedetailsshort: string, imdbcachedir: string, imdbcachedir_partial: string, imdbcacheexpire: string, imdbcachekeepsizeunder: string, imdbcachekeepsizeunder_sizelimit: string, imdbphotodir: string, imdbphotoroot: string, imdbusecache: string}
 * @phpstan-type OPTIONS_DATA array{'imdbwidgettitle': string, 'imdbwidgetpic': string,'imdbwidgetruntime': string, 'imdbwidgetdirector': string, 'imdbwidgetcountry': string, 'imdbwidgetactor':string, 'imdbwidgetactornumber':int|string, 'imdbwidgetcreator': string, 'imdbwidgetrating': string, 'imdbwidgetlanguage': string, 'imdbwidgetgenre': string, 'imdbwidgetwriter': string, 'imdbwidgetproducer': string, 'imdbwidgetproducernumber': bool|string, 'imdbwidgetkeyword': string, 'imdbwidgetprodcompany': string, 'imdbwidgetplot': string, 'imdbwidgetplotnumber': string, 'imdbwidgetgoof': string, 'imdbwidgetgoofnumber': string|bool, 'imdbwidgetcomment': string, 'imdbwidgetquote': string, 'imdbwidgetquotenumber': string|bool, 'imdbwidgettagline': string, 'imdbwidgettaglinenumber': string|bool, 'imdbwidgetcolor': string, 'imdbwidgetalsoknow': string, 'imdbwidgetalsoknownumber': string|bool, 'imdbwidgetcomposer': string, 'imdbwidgetsoundtrack': string, 'imdbwidgetsoundtracknumber': string|bool, 'imdbwidgetofficialsites': string, 'imdbwidgetsource': string, 'imdbwidgetyear': string, 'imdbwidgettrailer': string, 'imdbwidgettrailernumber': bool|string, 'imdbwidgetorder': array<string|int>, 'imdbtaxonomycolor': string, 'imdbtaxonomycomposer': string, 'imdbtaxonomycountry': string, 'imdbtaxonomycreator': string, 'imdbtaxonomydirector': string, 'imdbtaxonomygenre': string, 'imdbtaxonomykeyword': string, 'imdbtaxonomylanguage': string, 'imdbtaxonomyproducer': string, 'imdbtaxonomyactor': string, 'imdbtaxonomywriter': string}
 */
class Settings {

	/**
	 * If those plugins are installed, it will lead to Lumière deactivation
	 * Those are crap and Lumière will not support them
	 */
	const LUMIERE_INCOMPATIBLE_PLUGINS = [ 'rss-feed-post-generator-echo/rss-feed-post-generator-echo.php' ];

	/**
	 * Name of the databases as stored in WordPress db
	 */
	const LUMIERE_ADMIN_OPTIONS = 'imdbAdminOptions';
	const LUMIERE_DATA_OPTIONS = 'imdbWidgetOptions';
	const LUMIERE_CACHE_OPTIONS = 'imdbCacheOptions';

	/**
	 * Admin options vars
	 * @phpstan-var OPTIONS_ADMIN
	 * @var array<string, string>
	 */
	private array $imdb_admin_option;

	/**
	 * Website URLs constants
	 */
	const IMDBBLOG = 'https://www.jcvignoli.com/blog';
	const IMDBBLOGENGLISH = self::IMDBBLOG . '/en';
	const IMDBHOMEPAGE = self::IMDBBLOGENGLISH . '/lumiere-movies-wordpress-plugin';
	const IMDBABOUTENGLISH = self::IMDBBLOGENGLISH . '/presentation-of-jean-claude-vignoli';
	const LUMIERE_WORDPRESS = 'https://wordpress.org/extend/plugins/lumiere-movies/';
	const LUMIERE_WORDPRESS_IMAGES = 'https://ps.w.org/lumiere-movies/assets';
	const LUMIERE_GIT = 'https://github.com/jcvignoli/lumiere-movies';
	const LUMIERE_ACTIVE = 'LUMIERE_ACTIVE';

	/**
	 * URL Strings for popups, built in lumiere_define_constants()
	 */
	public string $lumiere_urlstring;
	public string $lumiere_urlstringfilms;
	public string $lumiere_urlstringperson;
	public string $lumiere_urlstringsearch;
	public string $lumiere_urlpopupsfilms;
	public string $lumiere_urlpopupsperson;
	public string $lumiere_urlpopupsearch;

	/**
	 * URL for menu small images directory, built in lumiere_define_constants()
	 * @var string $lumiere_pics_dir
	 */
	public string $lumiere_pics_dir;

	/**
	 * URL for javascript path, built in lumiere_define_constants()
	 * @var string $lumiere_js_path
	 */
	public string $lumiere_js_path;
	/**
	 * URL for javascript dir, built in lumiere_define_constants()
	 * @var string $lumiere_js_dir
	 */
	public string $lumiere_js_dir;

	/**
	 * URL for javascript dir, built in lumiere_define_constants()
	 * @var string $lumiere_css_dir
	 */
	public string $lumiere_css_dir;
	public string $lumiere_css_path;

	/**
	 * Internal URL pages constants
	 */
	const MOVE_TEMPLATE_TAXONOMY_PAGE = 'class/admin/class-copy-template-taxonomy.php'; // not included in $lumiere_list_all_pages.
	const VIRTUAL_PAGE_MAKER = 'class/alteration/class-virtual-page.php';
	const GUTENBERG_SEARCH_PAGE = 'class/admin/class-search.php';
	const GUTENBERG_SEARCH_URL_STRING = 'lumiere/search/';
	const GUTENBERG_SEARCH_URL = '/wp-admin/' . self::GUTENBERG_SEARCH_URL_STRING;
	const POPUP_SEARCH_URL = 'class/frontend/popups/class-popup-search.php';
	const POPUP_MOVIE_URL = 'class/frontend/popups/class-popup-movie.php';
	const POPUP_PERSON_URL = 'class/frontend/popups/class-popup-person.php';
	const TAXO_PEOPLE_THEME = 'class/theme/class-taxonomy-people-standard.php'; // not included in $lumiere_list_all_pages.
	const TAXO_ITEMS_THEME = 'class/theme/class-taxonomy-items-standard.php'; // not included in $lumiere_list_all_pages.
	const UPDATE_OPTIONS_PAGE = 'class/class-updates.php'; // not included in $lumiere_list_all_pages.

	/**
	 * URL string for taxonomy, 'lumiere-' by default (built in lumiere_define_constants() )
	 */
	const URL_STRING_TAXO = 'lumiere-';

	/**
	 * Include all pages of Lumière plugin
	 * @var array<string> $lumiere_list_all_pages
	 */
	public array $lumiere_list_all_pages = [];

	/**
	 * Paths for javascript frontend javascripts.
	 */
	public string $lumiere_scripts_vars;

	/**
	 * Vars for javascripts in admin area
	 */
	public string $lumiere_scripts_admin_vars;

	/**
	 * Lumière plugin version var is built from the readme
	 * Useful for updates
	 */
	public string $lumiere_version;

	/**
	 * Number of files inside /class/updates
	 * Allows to start with a fresh installation with the right number of updates
	 * Is built in lumiere_define_nb_updates()
	 */
	public string $current_number_updates;

	/**
	 * Cache folder path.
	 * This const is utilised to determine the default cache path value in get_imdb_cache_option()
	 */
	const LUMIERE_FOLDER_CACHE = WP_CONTENT_DIR . '/cache/lumiere/';

	/**
	 * List of types of people available
	 * is built in lumiere_define_constants_after_globals()
	 * @var array<string> $array_people
	 */
	public array $array_people = [];

	/**
	 * List of types of people available
	 * is built in lumiere_define_constants_after_globals()
	 * @var array<string> $array_items
	 */
	public array $array_items = [];

	/**
	 * Constructor
	 *
	 * @since 4.0 added properties $imdb_cache_values and $imdb_data_values, checking if options are not available, creation of the options
	 */
	public function __construct() {

		// Define Lumière constants.
		$this->lumiere_define_constants();

		/**
		 * Build options, get them from database if they exist, build them otherwise.
		 * Only $imdb_admin_option is set as a property, since it is used in that class.
		 */
		$imdb_admin_option = get_option( self::LUMIERE_ADMIN_OPTIONS );
		if ( is_array( $imdb_admin_option ) === false ) {
			$imdb_admin_option = $this->get_imdb_admin_option();
			update_option( self::LUMIERE_ADMIN_OPTIONS, $imdb_admin_option );
		}
		/** @phpstan-var OPTIONS_ADMIN $imdb_admin_option */
		$this->imdb_admin_option = $imdb_admin_option;

		// Those have no class properties created.
		$imdb_data_option = get_option( self::LUMIERE_DATA_OPTIONS );
		if ( is_array( $imdb_data_option ) === false  ) {
			$imdb_data_option = $this->get_imdb_data_option();
			update_option( self::LUMIERE_DATA_OPTIONS, $imdb_data_option );
		}

		$imdb_cache_option = get_option( self::LUMIERE_CACHE_OPTIONS );
		if ( is_array( $imdb_cache_option ) === false  ) {
			$imdb_cache_option = $this->get_imdb_cache_option();
			update_option( self::LUMIERE_CACHE_OPTIONS, $imdb_cache_option );
		}

		// Define Lumière constants once global options have been created.
		$this->lumiere_define_constants_after_globals();

		/**
		 * Build list of taxonomy for people and items
		 */
		$this->array_people = $this->build_people();
		$this->array_items = $this->build_items();
	}

	/**
	 * Reset all options by instanciating the class -- Dirty
	 * Would be better to have a class that separates options and updates, instead of having such a dirtly method
	 * @since 4.1 method created
	 */
	public static function build_options(): void {
		$build_options = new self();
	}

	/**
	 * Define global constants
	 * Run before the creation of the database options, database options may need these constants
	 * @throws Exception
	 */
	private function lumiere_define_constants(): void {

		// BUILD $imdb_admin_option['imdbplugindirectory']
		$this->imdb_admin_option['imdbplugindirectory'] ??= plugin_dir_url( __DIR__ );

		/* BUILD directory for pictures */
		$this->lumiere_pics_dir = plugin_dir_url( __DIR__ ) . 'assets/pics/';

		/* BUILD directory for javascripts */
		$this->lumiere_js_path = plugin_dir_path( __DIR__ ) . 'assets/js/';
		$this->lumiere_js_dir = plugin_dir_url( __DIR__ ) . 'assets/js/';

		/* BUILD directory for css */
		$this->lumiere_css_dir = plugin_dir_url( __DIR__ ) . 'assets/css/';
		$this->lumiere_css_path = plugin_dir_path( __DIR__ ) . 'assets/css/';

		/* BUILD LUMIERE_VERSION */
		$lumiere_version_recherche = file_get_contents( plugin_dir_path( __DIR__ ) . 'README.txt' );
		if ( $lumiere_version_recherche === false ) {
			throw new Exception( esc_html__( 'Lumiere plugin: Readme file either missing or corrupted ', 'lumiere-movies' ) );
		}
		$lumiere_version = preg_match( '#Stable tag:\s(.+)\n#', $lumiere_version_recherche, $lumiere_version_match );
		$this->lumiere_version = $lumiere_version_match[1];

	}

	/**
	 * Define global constants after database options are available
	 * Why: they need database options to work
	 */
	private function lumiere_define_constants_after_globals(): void {

		/* BUILD URLSTRINGS for popups */
		$this->lumiere_urlstring = ( strlen( $this->imdb_admin_option['imdburlpopups'] ) !== 0 ) ? $this->imdb_admin_option['imdburlpopups'] : '/lumiere/';
		$this->lumiere_urlstringfilms = $this->lumiere_urlstring . 'film/';
		$this->lumiere_urlstringperson = $this->lumiere_urlstring . 'person/';
		$this->lumiere_urlstringsearch = $this->lumiere_urlstring . 'search/';
		$this->lumiere_urlpopupsfilms = site_url() . $this->lumiere_urlstringfilms;
		$this->lumiere_urlpopupsperson = site_url() . $this->lumiere_urlstringperson;
		$this->lumiere_urlpopupsearch = site_url() . $this->lumiere_urlstringsearch;

		// Build the list of all pages included in Lumière plugin
		$this->lumiere_list_all_pages = [
			self::URL_STRING_TAXO,
			$this->lumiere_urlstringfilms,
			$this->lumiere_urlstringperson,
			$this->lumiere_urlstringsearch,
			self::MOVE_TEMPLATE_TAXONOMY_PAGE,
			self::GUTENBERG_SEARCH_PAGE,
			self::GUTENBERG_SEARCH_URL,
			self::POPUP_SEARCH_URL,
			self::POPUP_MOVIE_URL,
			self::POPUP_PERSON_URL,
		];

		/* BUILD options constant for javascripts  */
		$notfalse_lumiere_scripts_admin_vars = wp_json_encode(
			[
				'imdb_path' => $this->imdb_admin_option['imdbplugindirectory'],
				'wordpress_path' => site_url(),
				'wordpress_admin_path' => admin_url(),
				'gutenberg_search_url_string' => self::GUTENBERG_SEARCH_URL_STRING,
				'gutenberg_search_url' => self::GUTENBERG_SEARCH_URL,
				'ico80' => $this->imdb_admin_option['imdbplugindirectory'] . 'assets/pics/lumiere-ico-noir80x80.png',
				'popupLarg' => $this->imdb_admin_option['imdbpopuplarg'],
				'popupLong' => $this->imdb_admin_option['imdbpopuplong'],
			]
		);
		$this->lumiere_scripts_admin_vars = $notfalse_lumiere_scripts_admin_vars !== false ? 'const lumiere_admin_vars = ' . $notfalse_lumiere_scripts_admin_vars : '';
		$notfalse_lumiere_scripts_vars = wp_json_encode(
			[
				'imdb_path' => $this->imdb_admin_option['imdbplugindirectory'],
				'urlpopup_film' => $this->lumiere_urlpopupsfilms,
				'urlpopup_person' => $this->lumiere_urlpopupsperson,
				/** Popups */
				'popup_border_colour' => $this->imdb_admin_option['imdbpopuptheme'],
				'popupLarg' => $this->imdb_admin_option['imdbpopuplarg'],
				'popupLong' => $this->imdb_admin_option['imdbpopuplong'],
			]
		);
		$this->lumiere_scripts_vars = $notfalse_lumiere_scripts_vars !== false ? 'const lumiere_vars = ' . $notfalse_lumiere_scripts_vars : '';
	}

	/**
	 * Define the type of people
	 *
	 * @return array<string, string>
	 */
	private function build_people(): array {
		return [
			'actor' => __( 'actor', 'lumiere-movies' ),
			'composer' => __( 'composer', 'lumiere-movies' ),
			'creator' => __( 'creator', 'lumiere-movies' ),
			'director' => __( 'director', 'lumiere-movies' ),
			'producer' => __( 'producer', 'lumiere-movies' ),
			'writer' => __( 'writer', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type of items
	 *
	 * @return array<string, string>
	 */
	private function build_items(): array {
		return [
			'color' => __( 'color', 'lumiere-movies' ),
			'country' => __( 'country', 'lumiere-movies' ),
			'genre' => __( 'genre', 'lumiere-movies' ),
			'keyword' => __( 'keyword', 'lumiere-movies' ),
			'language' => __( 'language', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the number of updates on first install
	 * Called in {@see \Lumiere\Settings::get_imdb_admin_option()}
	 *
	 * @return bool
	 */
	public function lumiere_define_nb_updates(): bool {

		// Get the database options, since this is called before the building of $this->imdb_admin_option.
		if ( get_option( self::LUMIERE_ADMIN_OPTIONS ) !== false ) {
			$this->imdb_admin_option = get_option( self::LUMIERE_ADMIN_OPTIONS );
		}

		// If option 'imdbHowManyUpdates' doesn't exist, make it.
		if ( ( ! isset( $this->imdb_admin_option['imdbHowManyUpdates'] ) ) || ( $this->imdb_admin_option['imdbHowManyUpdates'] === '0' ) ) {

			// Find the number of update files to get the right
			// number of updates when installing Lumière
			$files = new FilesystemIterator( plugin_dir_path( __DIR__ ) . 'class/updates/', \FilesystemIterator::SKIP_DOTS );
			$this->current_number_updates = strval( iterator_count( $files ) + 1 );

			$option_key = 'imdbHowManyUpdates';
			$option_array_search = get_option( self::LUMIERE_ADMIN_OPTIONS );
			if ( $option_array_search === false ) {
				return false;
			}
			$option_array_search[ $option_key ] = $this->current_number_updates;

			// On successful update, exit
			if ( update_option( self::LUMIERE_ADMIN_OPTIONS, $option_array_search ) ) {
				return true;
			}

			return false;

		}

		// Otherwise the option 'imdbHowManyUpdates' exists in the database, just use it.
		$this->current_number_updates = strval( $this->imdb_admin_option['imdbHowManyUpdates'] );

		return false;
	}

	/**
	 * Make an array of ADMIN options
	 *
	 * @phpstan-return non-empty-array<OPTIONS_ADMIN>
	 * @psalm-return array{imdbHowManyUpdates?: mixed|string, imdbautopostwidget?: mixed|string, imdbcoversize?: mixed|string, imdbcoversizewidth?: mixed|string, imdbdebug?: mixed|string, imdbdebuglevel?: mixed|string, imdbdebuglog?: mixed|string, imdbdebuglogpath?: mixed|string, imdbdebugscreen?: mixed|string, imdbdelayimdbrequest?: mixed|string, imdbintotheposttheme?: mixed|string, imdbkeepsettings?: mixed|string, imdblanguage?: mixed|string, imdblinkingkill?: mixed|string, imdbmaxresults?: mixed|string, imdbplugindirectory: non-falsy-string, imdbplugindirectory_partial?: mixed|string, imdbpluginpath?: mixed|string, imdbpopup_modal_window?: mixed|string, imdbpopuplarg?: mixed|string, imdbpopuplong?: mixed|string, imdbpopuptheme?: mixed|string, imdbseriemovies?: mixed|string, imdbtaxonomy?: mixed|string, imdburlpopups?: mixed|string, imdburlstringtaxo?: mixed|string, imdbwordpress_bigmenu?: mixed|string, imdbwordpress_tooladminmenu?: mixed|string, ...<array-key, mixed|string>}
	 * @return array<mixed>
	 */
	private function get_imdb_admin_option(): array {

		// Define how many updates have been runned
		$this->lumiere_define_nb_updates();

		/**
		 * Build debug path: 1/ Use it as it is if it starts with '/', it's absolute, 2/ Add ABSPATH if it doesn't start with '/'
		 */
		$debug_path = null;
		/** @phpstan-ignore-next-line -- PHPStan can't understand that WP_DEBUG_LOG is a const that can be string and bool */
		if ( defined( 'WP_DEBUG_LOG' ) && is_string( WP_DEBUG_LOG ) && str_starts_with( WP_DEBUG_LOG, '/' ) ) {
			$debug_path = WP_DEBUG_LOG;
			/** @phpstan-ignore-next-line -- PHPStan can't understand that WP_DEBUG_LOG is a const that can be string and bool */
		} elseif ( ! isset( $debug_path ) && defined( 'WP_DEBUG_LOG' ) && is_string( WP_DEBUG_LOG ) ) {
			$debug_path = ABSPATH . WP_DEBUG_LOG;
		}

		$imdb_admin_options = [

			#--------------------------------------------------=[ Basic ]=--
			'imdbplugindirectory_partial' => '/wp-content/plugins/lumiere-movies/',
			'imdbpluginpath' => plugin_dir_path( __DIR__ ),
			'imdburlpopups' => '/lumiere/',
			'imdbkeepsettings' => '1',
			'imdburlstringtaxo' => self::URL_STRING_TAXO,
			'imdbcoversize' => '1',
			'imdbcoversizewidth' => '100',
			#--------------------------------------------------=[ Technical ]=--

			'imdbmaxresults' => '10',
			'imdbdelayimdbrequest' => '0',
			'imdbpopuptheme' => 'white',
			'imdbpopuplarg' => '800',
			'imdbpopuplong' => '500',
			'imdbintotheposttheme' => 'grey',
			'imdblinkingkill' => '0',
			'imdbautopostwidget' => '0',
			'imdblanguage' => 'en',
			'imdbdebug' => '0',                                  /* Debug */
			'imdbdebuglog' => '0',                                  /* Log debug */
			/** @phpstan-ignore-next-line -- PHPStan can't understand that WP_DEBUG_LOG is a const that can be string and bool */
			'imdbdebuglogpath' => $debug_path ?? WP_CONTENT_DIR . '/debug.log',
			'imdbdebuglevel' => 'DEBUG',                                /* Debug levels: emergency, alert, critical,
													error, warning, notice, info, debug */
			'imdbdebugscreen' => '1',                               /* Show debug on screen */
			'imdbwordpress_bigmenu' => '0',                             /* Left menu */
			'imdbwordpress_tooladminmenu' => '1',                       /* Top menu */
			'imdbpopup_modal_window' => 'bootstrap',
			'imdbtaxonomy' => '1',
			'imdbHowManyUpdates' => $this->current_number_updates,          /* define the number of updates. */
			'imdbseriemovies' => 'movies+series',                       /* options: movies, series, movies+series, videogames */

		];
		$imdb_admin_options['imdbplugindirectory'] = get_site_url() . $imdb_admin_options['imdbplugindirectory_partial'];

		$imdb_options_a = get_option( self::LUMIERE_ADMIN_OPTIONS );

		if ( $imdb_options_a !== false && count( $imdb_options_a ) !== 0 ) { // if not empty.

			foreach ( $imdb_options_a as $key => $option ) {
				$imdb_admin_options[ $key ] = $option;
			}

			// Agregate var to construct 'imdbplugindirectory'
			$imdb_admin_options['imdbplugindirectory'] = get_site_url() . $imdb_admin_options['imdbplugindirectory_partial'];
		}

		// For debugging purpose.
		// Update imdbHowManyUpdates option.
		/*
		$option_array_search = get_option( self::LUMIERE_ADMIN_OPTIONS );
		$option_array_search['imdbHowManyUpdates'] = 11; // Chosen number of updates.
		update_option( self::LUMIERE_ADMIN_OPTIONS, $option_array_search );
		*/

		return $imdb_admin_options;
	}

	/**
	 * Makes an array of CACHE options
	 *
	 * @phpstan-return non-empty-array<OPTIONS_CACHE>
	 * @psalm-return array{imdbcacheautorefreshcron?: mixed|non-empty-string, imdbcachedetailsshort?: mixed|non-empty-string, imdbcachedir: 'wp-content/cache/lumiere/', imdbcachedir_partial?: mixed|non-empty-string, imdbcacheexpire?: mixed|non-empty-string, imdbcachekeepsizeunder?: mixed|non-empty-string, imdbcachekeepsizeunder_sizelimit?: mixed|non-empty-string, imdbphotodir?: mixed|non-empty-string, imdbphotoroot: 'wp-content/cache/lumiere/images/', imdbusecache?: mixed|non-empty-string, ...<array-key, mixed|non-empty-string>}
	 * @return array<mixed>
	 */
	private function get_imdb_cache_option(): array {

		// Build partial cache path, such as 'wp-content/cache/lumiere/'
		$imdbcachedir_partial = str_replace( WP_CONTENT_DIR, '', self::LUMIERE_FOLDER_CACHE );

		$imdb_cache_options = [

			'imdbcachedir_partial' => $imdbcachedir_partial,
			'imdbusecache' => '1',
			'imdbcacheexpire' => '2592000',    /* one month */
			'imdbcachedetailsshort' => '0',
			'imdbcacheautorefreshcron' => '0',
			'imdbcachekeepsizeunder' => '0', /* Disabled by default */
			'imdbcachekeepsizeunder_sizelimit' => '100', /* 100 MB */

		];

		$imdb_cache_options['imdbcachedir'] = WP_CONTENT_DIR . $imdb_cache_options['imdbcachedir_partial'];
		$imdb_cache_options['imdbphotoroot'] = $imdb_cache_options['imdbcachedir'] . 'images/';
		$imdb_cache_options['imdbphotodir'] = content_url() . '/cache/lumiere/images/';

		$imdb_options_c = get_option( self::LUMIERE_CACHE_OPTIONS );
		$imdb_options_a = get_option( self::LUMIERE_ADMIN_OPTIONS );

		if (  is_array( $imdb_options_c ) === true && count( $imdb_options_c ) !== 0 ) { // if not empty.

			foreach ( $imdb_options_c as $key => $option ) {
				$imdb_cache_options[ $key ] = $option;
			}

			// Agregate vars to construct 'imdbcachedir'
			$imdb_cache_options['imdbcachedir'] = WP_CONTENT_DIR . $imdb_cache_options['imdbcachedir_partial'];

			// Agregate vars to construct 'imdbphotoroot'
			$imdb_cache_options['imdbphotoroot'] = $imdb_cache_options['imdbcachedir'] . 'images/';
		}
		if ( is_array( $imdb_options_a ) === true && count( $imdb_options_a ) !== 0 && isset( $imdb_cache_options['imdbcachedir_partial'] ) ) { // if not empty.

			// Agregate vars to construct 'imdbphotodir'
			$imdb_cache_options['imdbphotodir'] = content_url() . $imdb_cache_options['imdbcachedir_partial'] . 'images/';
		}
		return $imdb_cache_options;
	}

	/**
	 * Makes an array of DATA options
	 *
	 * @phpstan-return non-empty-array<OPTIONS_DATA>
	 * @psalm-return non-empty-array<array-key, '0'|'1'|'10'|'2'|array{actor: '6', alsoknow: '20', color: '19', composer: '21', country: '5', creator: '7', director: '4', genre: '10', goof: '16', keyword: '13', language: '9', officialsites: '24', pic: '2', plot: '15', prodcompany: '14', producer: '12', quote: '17', rating: '8', runtime: '3', soundtrack: '22', source: '25', tagline: '18', title: '1', trailer: '23', writer: '11'}|false|mixed>
	 * @return array<mixed>
	 */
	private function get_imdb_data_option(): array {

		$imdb_data_options = [

			'imdbwidgettitle' => '1',
			'imdbwidgetpic' => '1',
			'imdbwidgetruntime' => '0',
			'imdbwidgetdirector' => '1',
			'imdbwidgetcountry' => '0',
			'imdbwidgetactor' => '1',
			'imdbwidgetactornumber' => '10',
			'imdbwidgetcreator' => '0',
			'imdbwidgetrating' => '0',
			'imdbwidgetlanguage' => '0',
			'imdbwidgetgenre' => '1',
			'imdbwidgetwriter' => '1',
			'imdbwidgetproducer' => '0',
			'imdbwidgetproducernumber' => false,
			'imdbwidgetkeyword' => '0',
			'imdbwidgetprodcompany' => '0',
			'imdbwidgetplot' => '1',
			'imdbwidgetplotnumber' => '2',
			'imdbwidgetgoof' => '0',
			'imdbwidgetgoofnumber' => false,
			'imdbwidgetcomment' => '0',
			'imdbwidgetquote' => '0',
			'imdbwidgetquotenumber' => false,
			'imdbwidgettagline' => '0',
			'imdbwidgettaglinenumber' => false,
			'imdbwidgetcolor' => '0',
			'imdbwidgetalsoknow' => '0',
			'imdbwidgetalsoknownumber' => false,
			'imdbwidgetcomposer' => '0',
			'imdbwidgetsoundtrack' => '0',
			'imdbwidgetsoundtracknumber' => false,
			'imdbwidgetofficialsites' => '0',
			'imdbwidgetsource' => '0',
			'imdbwidgetyear' => '0',
			'imdbwidgettrailer' => '0',
			'imdbwidgettrailernumber' => false,
			'imdbwidgetorder' => [
				'title' => '1',
				'pic' => '2',
				'runtime' => '3',
				'director' => '4',
				'country' => '5',
				'actor' => '6',
				'creator' => '7',
				'rating' => '8',
				'language' => '9',
				'genre' => '10',
				'writer' => '11',
				'producer' => '12',
				'keyword' => '13',
				'prodcompany' => '14',
				'plot' => '15',
				'goof' => '16',
				'quote' => '17',
				'tagline' => '18',
				'color' => '19',
				'alsoknow' => '20',
				'composer' => '21',
				'soundtrack' => '22',
				'trailer' => '23',
				'officialsites' => '24',
				'source' => '25',
			],
			'imdbtaxonomycolor' => '0',
			'imdbtaxonomycomposer' => '0',
			'imdbtaxonomycountry' => '0',
			'imdbtaxonomycreator' => '0',
			'imdbtaxonomydirector' => '1',
			'imdbtaxonomygenre' => '1',
			'imdbtaxonomykeyword' => '0',
			'imdbtaxonomylanguage' => '0',
			'imdbtaxonomyproducer' => '0',
			'imdbtaxonomyactor' => '0',
			'imdbtaxonomywriter' => '0',

		];

		$imdb_options_w = get_option( self::LUMIERE_DATA_OPTIONS );

		if ( is_array( $imdb_options_w ) === true && count( $imdb_options_w ) !== 0 ) { // if not empty.

			foreach ( $imdb_options_w as $key => $option ) {
				$imdb_data_options[ $key ] = $option;
			}
		}
		return $imdb_data_options;
	}

	/**
	 * Retrieve selected type of search in admin
	 *
	 * @return array<string>
	 * @see \Imdb\TitleSearch Contains the constants
	 */
	public function lumiere_select_type_search (): array {

		switch ( $this->imdb_admin_option['imdbseriemovies'] ) {

			case 'movies':
				return [ \Imdb\TitleSearch::MOVIE ];
			case 'movies+series':
				return [ \Imdb\TitleSearch::MOVIE, \Imdb\TitleSearch::TV_SERIES ];
			case 'series':
				return [ \Imdb\TitleSearch::TV_SERIES ];
			case 'videogames':
				return [ \Imdb\Title::GAME ];
			case 'podcasts':
				return [ \Imdb\Title::PODCAST_EPISODE ];
			default:
				return [ \Imdb\TitleSearch::MOVIE, \Imdb\TitleSearch::TV_SERIES ];

		}

	}

}

