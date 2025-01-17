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
if ( ! defined( 'WPINC' ) ) { // Don't check for Settings class since it's Settings class.
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use FilesystemIterator;
use Exception;
use Lumiere\Tools\Files;

// Needed vars for uninstall, fails otherwise.
// Use of defined() condition for PHPStan
if ( ! defined( 'LUMIERE_WP_PATH' ) ) {
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vars.php';
}

/**
 * Configuration class
 * Some settings are created from the outset, others are processed in __construct()
 * On calling __construct(), options are created in database
 *
 * @TODO options should be created only when installing/activating the plugin
 * @since 4.0 moved cache folder creation to class cache tools
 * @since 4.1 renamed *imdb_widget_* to *imdb_data_* all over the website
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Tools\Settings_Global
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Tools\Settings_Global
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Tools\Settings_Global
 */
class Settings {

	/**
	 * Traits
	 */
	use Files;

	/**
	 * If those plugins are installed, Lumière will be deactivated and could not be activated again
	 * Those plugins are crap and Lumière will not support them
	 */
	const LUMIERE_INCOMPATIBLE_PLUGINS = [ 'rss-feed-post-generator-echo/rss-feed-post-generator-echo.php' ];

	/**
	 * Name of the databases as stored in WordPress db
	 */
	const LUMIERE_ADMIN_OPTIONS = 'lumiere_admin_options';
	const LUMIERE_DATA_OPTIONS = 'lumiere_data_options';
	const LUMIERE_CACHE_OPTIONS = 'lumiere_cache_options';

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
	const LUMIERE_WORDPRESS = 'https://wordpress.org/plugins/lumiere-movies/';
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
	 * URLs for menu small images directory
	 */
	public string $lumiere_pics_dir = LUMIERE_WP_URL . 'assets/pics/';
	public string $lumiere_showtimes_dir = LUMIERE_WP_URL . 'assets/pics/showtimes/';

	/**
	 * URL for javascript path
	 * @var string $lumiere_js_path
	 */
	public string $lumiere_js_path = LUMIERE_WP_PATH . 'assets/js/';
	/**
	 * URL for javascript dir
	 * @var string $lumiere_js_dir
	 */
	public string $lumiere_js_dir = LUMIERE_WP_URL . 'assets/js/';

	/**
	 * URL for javascript dir
	 * @var string $lumiere_css_dir
	 */
	public string $lumiere_css_dir = LUMIERE_WP_URL . 'assets/css/';
	public string $lumiere_css_path = LUMIERE_WP_PATH . 'assets/css/';

	/**
	 * Internal URL pages constants
	 */
	const MOVE_TEMPLATE_TAXONOMY_PAGE = 'class/admin/class-copy-template-taxonomy.php'; // not included in $lumiere_list_all_pages.
	const VIRTUAL_PAGE_MAKER = 'class/alteration/class-virtual-page.php';
	const GUTENBERG_SEARCH_PAGE = 'class/admin/class-search.php';
	const GUTENBERG_SEARCH_URL_STRING = 'lumiere/search/';
	const GUTENBERG_SEARCH_URL = '/wp-admin/' . self::GUTENBERG_SEARCH_URL_STRING;
	const POPUP_SEARCH_URL = 'class/frontend/popups/class-popup-movie-search.php';
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
	 * This const is utilised to determine the default cache path value in get_cache_option()
	 */
	const LUMIERE_FOLDER_CACHE = WP_CONTENT_DIR . '/cache/lumiere/';

	/**
	 * List of types of people available
	 * is built in define_constants_after_globals()
	 * @var array<string> $array_people
	 */
	public array $array_people = [];

	/**
	 * List of types of people available
	 * is built in define_constants_after_globals()
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
		 * Only $lum_admin_option is set as a property, since it is used in that class.
		 */
		$lum_admin_option = get_option( self::get_admin_tablename() );
		if ( is_array( $lum_admin_option ) === false ) {
			$lum_admin_option = $this->get_admin_option();
			update_option( self::get_admin_tablename(), $lum_admin_option );
		}
		/** @phpstan-var OPTIONS_ADMIN $lum_admin_option */
		$this->imdb_admin_option = $lum_admin_option;

		// Those have no class properties created.
		$lum_data_option = get_option( self::get_data_tablename() );
		if ( is_array( $lum_data_option ) === false  ) {
			$lum_data_option = $this->get_data_option();
			update_option( self::get_data_tablename(), $lum_data_option );
		}

		$lum_cache_option = get_option( self::get_cache_tablename() );
		if ( is_array( $lum_cache_option ) === false  ) {
			$lum_cache_option = $this->get_cache_option();
			update_option( self::get_cache_tablename(), $lum_cache_option );
		}

		// Define Lumière constants once global options have been created.
		$this->define_constants_after_globals();

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
		$this->imdb_admin_option['imdbplugindirectory'] ??= LUMIERE_WP_URL;

		/**
		 * BUILD LUMIERE_VERSION
		 * @since 4.3 use of $wp_filesystem instead of file_get_contents()
		 */
		global $wp_filesystem;
		$readme_file = LUMIERE_WP_PATH . 'README.txt';
		$this->lumiere_wp_filesystem_cred( $readme_file );
		$lumiere_version_recherche = $wp_filesystem->get_contents( $readme_file );
		if ( $lumiere_version_recherche === false ) {
			throw new Exception( esc_html__( 'Lumiere plugin: Readme file either missing or corrupted ', 'lumiere-movies' ) );
		}
		$lumiere_version = preg_match( '#Stable tag:\s(.+)\n#', $lumiere_version_recherche, $lumiere_version_match );
		$this->lumiere_version = $lumiere_version_match[1] ?? '0';

	}

	/**
	 * Define global constants after database options are available
	 * Why: they need database options to work
	 */
	private function define_constants_after_globals(): void {

		/* Build URLSTRINGS for popups */
		$this->lumiere_urlstring = strlen( $this->imdb_admin_option['imdburlpopups'] ) !== 0 ? $this->imdb_admin_option['imdburlpopups'] : '/lumiere/';
		$this->lumiere_urlstringfilms = $this->lumiere_urlstring . 'film/';
		$this->lumiere_urlstringperson = $this->lumiere_urlstring . 'person/';
		$this->lumiere_urlstringsearch = $this->lumiere_urlstring . 'movie_search/';
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
	 * Called in {@see \Lumiere\Settings::get_admin_option()}
	 *
	 * @return bool
	 */
	public function lumiere_define_nb_updates(): bool {

		// Get the database options, since this is called before the building of $this->imdb_admin_option.
		if ( get_option( self::get_admin_tablename() ) !== false ) {
			$this->imdb_admin_option = get_option( self::get_admin_tablename() );
		}

		// If option 'imdbHowManyUpdates' doesn't exist, make it.
		if ( ( ! isset( $this->imdb_admin_option['imdbHowManyUpdates'] ) ) || ( $this->imdb_admin_option['imdbHowManyUpdates'] === '0' ) ) {

			// Find the number of update files to get the right
			// number of updates when installing Lumière
			$files = new FilesystemIterator( LUMIERE_WP_PATH . 'class/updates/', \FilesystemIterator::SKIP_DOTS );
			$this->current_number_updates = strval( iterator_count( $files ) + 1 );

			$option_key = 'imdbHowManyUpdates';
			$option_array_search = get_option( self::get_admin_tablename() );
			if ( $option_array_search === false ) {
				return false;
			}
			$option_array_search[ $option_key ] = $this->current_number_updates;

			// On successful update, exit
			if ( update_option( self::get_admin_tablename(), $option_array_search ) ) {
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
	 * @psalm-return array{imdbHowManyUpdates?: mixed|string, imdbautopostwidget?: mixed|string, imdbcoversize?: mixed|string, imdbcoversizewidth?: mixed|string, imdbdebug?: mixed|string, imdbdebuglevel?: mixed|string, imdbdebuglog?: mixed|string, imdbdebuglogpath?: mixed|string, imdbdebugscreen?: mixed|string, imdbdelayimdbrequest?: mixed|string, imdbintotheposttheme?: mixed|string, imdbkeepsettings?: mixed|string, imdblanguage?: mixed|string, imdblinkingkill?: mixed|string, imdbmaxresults?: mixed|string, imdbplugindirectory: non-falsy-string, imdbplugindirectory_partial?: mixed|string, imdbpluginpath?: mixed|string, imdbpopup_modal_window?: mixed|string, imdbpopuplarg?: mixed|string, imdbpopuplong?: mixed|string, imdbpopuptheme?: mixed|string, imdbseriemovies?: mixed|string, imdbtaxonomy?: mixed|string, imdburlpopups?: mixed|string, imdburlstringtaxo?: mixed|string, imdbwordpress_bigmenu?: mixed|string, imdbwordpress_tooladminmenu?: mixed|string, imdbirpdisplay?: mixed|string, ...<array-key, mixed|string>}
	 * @return array<mixed>
	 */
	private function get_admin_option(): array {

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
			'imdbpluginpath' => LUMIERE_WP_PATH,
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
			'imdblanguage' => 'US',
			'imdbdebug' => '0',                                         /* Debug */
			'imdbdebuglog' => '0',                                      /* Log debug */
			/** @phpstan-ignore nullCoalesce.variable (PHPStan can't understand that WP_DEBUG_LOG is a const that can be string and bool) */
			'imdbdebuglogpath' => $debug_path ?? WP_CONTENT_DIR . '/debug.log',
			'imdbdebuglevel' => 'DEBUG',                                /* Debug levels: emergency, alert, critical,
													error, warning, notice, info, debug */
			'imdbdebugscreen' => '1',                                   /* Show debug on screen */
			'imdbwordpress_bigmenu' => '0',                             /* Left menu */
			'imdbwordpress_tooladminmenu' => '1',                       /* Top menu */
			'imdbpopup_modal_window' => 'bootstrap',
			'imdbtaxonomy' => '1',
			'imdbHowManyUpdates' => $this->current_number_updates,      /* define the number of updates. */
			'imdbseriemovies' => 'movies+series',                       /* options: movies, series, movies+series, videogames */
			'imdbirpdisplay' => '0',                                    /* intelly related post plugin, overrides normal Lumiere behaviour */
		];
		$imdb_admin_options['imdbplugindirectory'] = get_site_url() . $imdb_admin_options['imdbplugindirectory_partial'];

		$imdb_options_a = get_option( self::get_admin_tablename() );

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
		$option_array_search = get_option( self::get_admin_tablename() );
		$option_array_search['imdbHowManyUpdates'] = 18; // Chosen number of updates.
		update_option( self::get_admin_tablename(), $option_array_search );
		*/

		return $imdb_admin_options;
	}

	/**
	 * Makes an array of CACHE options
	 *
	 * @phpstan-return non-empty-array<OPTIONS_CACHE>
	 * @psalm-return array{imdbcacheautorefreshcron?: non-empty-string, imdbcachedetailshidden?: non-empty-string, imdbcachedetailsshort?: non-empty-string, imdbcachedir: 'wp-content/cache/lumiere/', imdbcachedir_partial?: non-empty-string, imdbcacheexpire?: non-empty-string, imdbcachekeepsizeunder?: non-empty-string, imdbcachekeepsizeunder_sizelimit?: non-empty-string, imdbphotodir?: non-empty-string, imdbphotoroot: 'wp-content/cache/lumiere/images/', imdbusecache?: non-empty-string, ...<array-key, mixed|non-empty-string>}
	 * @return array<mixed>
	 */
	private function get_cache_option(): array {

		// Build partial cache path, such as 'wp-content/cache/lumiere/'
		$imdbcachedir_partial = str_replace( WP_CONTENT_DIR, '', self::LUMIERE_FOLDER_CACHE );

		$imdb_cache_options = [

			'imdbcachedir_partial' => $imdbcachedir_partial,
			'imdbusecache' => '1',
			'imdbcacheexpire' => '2592000',                 /* one month */
			'imdbcachedetailsshort' => '0',
			'imdbcacheautorefreshcron' => '0',
			'imdbcachekeepsizeunder' => '0',                /* Disabled by default */
			'imdbcachekeepsizeunder_sizelimit' => '100',    /* 100 MB */
			'imdbcachedetailshidden' => '0',

		];

		$imdb_cache_options['imdbcachedir'] = WP_CONTENT_DIR . $imdb_cache_options['imdbcachedir_partial'];
		$imdb_cache_options['imdbphotoroot'] = $imdb_cache_options['imdbcachedir'] . 'images/';
		$imdb_cache_options['imdbphotodir'] = content_url() . '/cache/lumiere/images/';

		$imdb_options_c = get_option( self::get_cache_tablename() );
		$imdb_options_a = get_option( self::get_admin_tablename() );

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
	private function get_data_option(): array {

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

		$imdb_options_w = get_option( self::get_data_tablename() );

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
	 * @return string
	 * @see \Imdb\TitleSearch For the options
	 */
	public function lumiere_select_type_search (): string {

		switch ( $this->imdb_admin_option['imdbseriemovies'] ) {

			case 'movies':
				return 'MOVIE';
			case 'movies+series':
				return 'MOVIE,TV';
			case 'series':
				return 'TV';
			case 'videogames':
				return 'VIDEO_GAME';
			case 'podcasts':
				return 'PODCAST_EPISODE';
			default:
				return 'MOVIE,TV';

		}

	}

	/**
	 * Get Admin options row name as in wp_options
	 *
	 * @return string
	 * @since 4.2.1 method added, returning old row name if exists, new name otherwise
	 * @since 4.2.2 method renamed and returns only the new row name
	 */
	public static function get_admin_tablename(): string {
		return self::LUMIERE_ADMIN_OPTIONS;
	}

	/**
	 * Get Data options row name as in wp_options
	 *
	 * @return string
	 * @since 4.2.1 method added, returning old row name if exists, new name otherwise
	 * @since 4.2.2 method renamed and returns only the new row name
	 */
	public static function get_data_tablename(): string {
		return self::LUMIERE_DATA_OPTIONS;
	}

	/**
	 * Get Cache options row name as in wp_options
	 *
	 * @return string
	 * @since 4.2.1 method added, returning old row name if exists, new name otherwise
	 * @since 4.2.2 method renamed and returns only the new row name
	 */
	public static function get_cache_tablename(): string {
		return self::LUMIERE_CACHE_OPTIONS;
	}
}

