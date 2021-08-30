<?php declare( strict_types = 1 );
/**
 * Class of configuration. Extends imdbphp library \Imdb\Config
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       2.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

// use IMDbPHP config class in /vendor/
use \Imdb\Config;

use \Lumiere\Logger;

// use PHP library.
use \FilesystemIterator;

class Settings extends Config {

	/**
	 * Admin Options, saved in WordPress Database
	 * @var string $imdbAdminOptionsName
	 */
	public string $imdbAdminOptionsName = 'imdbAdminOptions';

	/**
	 * Widget Options, saved in WordPress Database
	 * @var string $imdbWidgetOptionsName
	 */
	public string $imdbWidgetOptionsName = 'imdbWidgetOptions';

	/**
	 * Cache Options, saved in WordPress Database
	 * @var string $imdbCacheOptionsName
	 */
	public string $imdbCacheOptionsName = 'imdbCacheOptions';

	/** New way, just giving constants */
	const LUMIERE_ADMIN_OPTIONS = 'imdbAdminOptions';
	const LUMIERE_WIDGET_OPTIONS = 'imdbWidgetOptions';
	const LUMIERE_CACHE_OPTIONS = 'imdbCacheOptions';

	/**
	 * Admin options vars
	 * @var array{'imdbplugindirectory': string, 'imdbplugindirectory_partial': string, 'imdbpluginpath': string,'imdburlpopups': string,'imdbkeepsettings': string,'imdburlstringtaxo': string,'imdbcoversize': string,'imdbcoversizewidth': string, 'imdbmaxresults': int, 'imdbpopuptheme': string, 'imdbpopuplarg': string,'imdbpopuplong': string, 'imdbintotheposttheme': string, 'imdblinkingkill': string, 'imdbautopostwidget': string, 'imdblanguage': string, 'imdbdebug': string, 'imdbdebuglog': string, 'imdbdebuglogpath': string, 'imdbdebuglevel': string, 'imdbdebugscreen': string, 'imdbwordpress_bigmenu': string, 'imdbwordpress_tooladminmenu': string, 'imdbpopup_highslide': string, 'imdbtaxonomy': string, 'imdbHowManyUpdates': int, 'imdbseriemovies': string} $imdb_admin_values
	*/
	private array $imdb_admin_values;

	/**
	 * Widget options
	 * @var array{'imdbwidgettitle': string, 'imdbwidgetpic': string,'imdbwidgetruntime': string, 'imdbwidgetdirector': string, 'imdbwidgetcountry': string, 'imdbwidgetactor':string, 'imdbwidgetactornumber':int, 'imdbwidgetcreator': string, 'imdbwidgetrating': string, 'imdbwidgetlanguage': string, 'imdbwidgetgenre': string, 'imdbwidgetwriter': string, 'imdbwidgetproducer': string, 'imdbwidgetproducernumber': bool|string, 'imdbwidgetkeyword': string, 'imdbwidgetprodcompany': string, 'imdbwidgetplot': string, 'imdbwidgetplotnumber': string, 'imdbwidgetgoof': string, 'imdbwidgetgoofnumber': string|bool, 'imdbwidgetcomment': string, 'imdbwidgetquote': string, 'imdbwidgetquotenumber': string|bool, 'imdbwidgettagline': string, 'imdbwidgettaglinenumber': string|bool, 'imdbwidgetcolor': string, 'imdbwidgetalsoknow': string, 'imdbwidgetalsoknownumber': string|bool, 'imdbwidgetcomposer': string, 'imdbwidgetsoundtrack': string, 'imdbwidgetsoundtracknumber': string|bool, 'imdbwidgetofficialsites': string, 'imdbwidgetsource': string, 'imdbwidgetyear': string, 'imdbwidgettrailer': string, 'imdbwidgettrailernumber': bool|string, 'imdbwidgetorder': array<string>, 'imdbtaxonomycolor': string, 'imdbtaxonomycomposer': string, 'imdbtaxonomycountry': string, 'imdbtaxonomycreator': string, 'imdbtaxonomydirector': string, 'imdbtaxonomygenre': string, 'imdbtaxonomykeyword': string, 'imdbtaxonomylanguage': string, 'imdbtaxonomyproducer': string, 'imdbtaxonomyactor': string, 'imdbtaxonomywriter': string} $imdb_widget_values
	 * @phpstan-ignore-next-line reported as never used, but I want to keep it
	 */
	private array $imdb_widget_values;

	/**
	 * Cache options
	 * @var array{'imdbcachedir_partial': string, 'imdbstorecache': bool, 'imdbusecache': string, 'imdbconverttozip': bool, 'imdbusezip': bool, 'imdbcacheexpire': string, 'imdbcachedetailsshort': string,'imdbcachedir': string,'imdbphotoroot': string, 'imdbphotodir': string} $imdb_cache_values
	 */
	private array $imdb_cache_values;

	/**
	 * Websites constants
	 */
	const IMDBBLOG = 'https://www.jcvignoli.com/blog';
	const IMDBBLOGENGLISH = self::IMDBBLOG . '/en';
	const IMDBBLOGHIGHSLIDE = self::IMDBBLOG . '/wp-content/files/wordpress-lumiere-highslide-5.0.0.zip';
	const IMDBHOMEPAGE = self::IMDBBLOGENGLISH . '/lumiere-movies-wordpress-plugin';
	const IMDBABOUTENGLISH = self::IMDBBLOGENGLISH . '/presentation-of-jean-claude-vignoli';
	const IMDBPHPGIT = 'https://github.com/tboothman/imdbphp/';
	const LUMIERE_WORDPRESS = 'https://wordpress.org/extend/plugins/lumiere-movies/';
	const LUMIERE_WORDPRESS_IMAGES = 'https://ps.w.org/lumiere-movies/assets';
	const LUMIERE_GIT = 'https://github.com/jcvignoli/lumiere-movies';
	const LUMIERE_ACTIVE = 'LUMIERE_ACTIVE';

	/**
	 * URL Strings for popups, built in lumiere_define_constants()
	 */
	/**
	 * @var string $lumiere_urlstring
	 */
	public string $lumiere_urlstring;
	/**
	 * @var string $lumiere_urlstringfilms
	 */
	public string $lumiere_urlstringfilms;
	/**
	 * @var string $lumiere_urlstringperson
	 */
	public string $lumiere_urlstringperson;
	/**
	 * @var string $lumiere_urlstringsearch
	 */
	public string $lumiere_urlstringsearch;
	/**
	 * @var string $lumiere_urlpopupsfilms
	 */
	public string $lumiere_urlpopupsfilms;
	/**
	 * @var string $lumiere_urlpopupsperson
	 */
	public string $lumiere_urlpopupsperson;
	/**
	 * @var string $lumiere_urlpopupsearch
	 */
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
	 * URL for blocks dir, built in lumiere_define_constants()
	 * @var string $lumiere_blocks_dir
	 */
	public string $lumiere_blocks_dir;

	/**
	 * URL for javascript dir, built in lumiere_define_constants()
	 * @var string $lumiere_css_dir
	 */
	public string $lumiere_css_dir;

	/* Internal URL pages constants
	*/
	const MOVE_TEMPLATE_TAXONOMY_PAGE = 'class/tools/class-copy-template-taxonomy.php'; // not included in $lumiere_list_all_pages.
	const HIGHSLIDE_DOWNLOAD_PAGE = 'class/tools/highslide-download.php';
	const GUTENBERG_SEARCH_PAGE = 'class/tools/class-search.php';
	const GUTENBERG_SEARCH_URL_STRING = 'lumiere/search/';
	const GUTENBERG_SEARCH_URL = '/wp-admin/' . self::GUTENBERG_SEARCH_URL_STRING;
	const POPUP_SEARCH_URL = 'class/frontend/class-popup-search.php';
	const POPUP_MOVIE_URL = 'class/frontend/class-popup-movie.php';
	const POPUP_PERSON_URL = 'class/frontend/class-popup-person.php';
	const TAXO_PEOPLE_THEME = 'class/theme/class-taxonomy-people-standard.php'; // not included in $lumiere_list_all_pages.
	const TAXO_ITEMS_THEME = 'class/theme/class-taxonomy-items-standard.php'; // not included in $lumiere_list_all_pages.
	const UPDATE_OPTIONS_PAGE = 'class/class-update-options.php'; // not included in $lumiere_list_all_pages.

	/**
	 * URL string for taxonomy, 'imdblt_' by default (built in lumiere_define_constants() )
	 */
	const URL_STRING_TAXO = 'lumiere-';

	/**
	 * Include all pages of Lumière plugin
	 * @var array<string> $lumiere_list_all_pages
	 */
	public array $lumiere_list_all_pages = [];

	/**
	 * Paths for javascript frontpage javascripts
	 */
	public string $lumiere_scripts_vars;

	/**
	 * Options for highslide javascript
	 */
	public string $lumiere_scripts_highslide_vars;

	/**
	 * Vars for javascripts in admin zone
	 */
	public string $lumiere_scripts_admin_vars;

	/**
	 * Store Lumière plugin version
	 */
	public string $lumiere_version;

	/**
	 * Logger class
	 */
	public Logger $logger;
	/* Where to write the log (WordPress default path here) */
	const DEBUG_LOG_PATH = ABSPATH . 'wp-content/debug.log';

	/**
	 * Is the current page an editing page?
	 */
	public bool $is_editor_page;

	/**
	 * List of types of people available
	 * is build in lumiere_define_constants_after_globals()
	 * @var array<string> $array_people
	 */
	public array $array_people = [];

	/**
	 * List of types of people available
	 * is build in lumiere_define_constants_after_globals()
	 * @var array<string> $array_items
	 */
	public array $array_items = [];

	/**
	 * Store the number of files inside /class/updates
	 * Allows to start with a fresh installation with the right number of updates
	 * Is built in lumiere_define_nb_updates()
	 */
	public int $current_number_updates;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct parent class so we can send the options to IMDbPHP class.
		parent::__construct();

		// Define Lumière constants.
		$this->lumiere_define_constants();

		// Build options, get them from database and then send to properties.
		$this->get_imdb_admin_option();
		$this->get_imdb_widget_option();
		$this->get_imdb_cache_option();
		$this->imdb_admin_values = get_option( self::LUMIERE_ADMIN_OPTIONS );
		$this->imdb_widget_values = get_option( self::LUMIERE_WIDGET_OPTIONS );
		$this->imdb_cache_values = get_option( self::LUMIERE_CACHE_OPTIONS );

		// Define Lumière constants once global options have been created.
		$this->lumiere_define_constants_after_globals();

		// Call the plugin translation
		load_plugin_textdomain( 'lumiere-movies', false, plugin_dir_url( __DIR__ ) . 'languages' );

		// Call the function to send the selected settings to imdbphp library.
		$this->lumiere_send_config_imdbphp();

	}

	/**
	 * Define global constants
	 * Run before the creation of the global options, global options may need these constants
	 */
	private function lumiere_define_constants(): void {

		/* BUILD $imdb_admin_values['imdbplugindirectory'] */
		$this->imdb_admin_values['imdbplugindirectory'] = isset( $this->imdb_admin_values['imdbplugindirectory'] ) ? $this->imdb_admin_values['imdbplugindirectory'] : plugin_dir_url( __DIR__ );

		/* BUILD directory for pictures */
		$this->lumiere_pics_dir = plugin_dir_url( __DIR__ ) . 'pics/';

		/* BUILD directory for javascripts */
		$this->lumiere_js_path = plugin_dir_path( __DIR__ ) . 'js/';
		$this->lumiere_js_dir = plugin_dir_url( __DIR__ ) . 'js/';

		/* BUILD directory for css */
		$this->lumiere_css_dir = plugin_dir_url( __DIR__ ) . 'css/';

		/* BUILD directory for blocks */
		$this->lumiere_blocks_dir = plugin_dir_url( __DIR__ ) . 'blocks/';

		/* BUILD LUMIERE_VERSION */
		$lumiere_version_recherche = file_get_contents( plugin_dir_path( __DIR__ ) . 'README.txt' );
		if ( $lumiere_version_recherche === false ) {
			wp_die( 'Lumiere plugin: ' . esc_html__( 'Readme file either missing or corrupted ', 'lumiere-movies' ) );
		}
		$lumiere_version = preg_match( '#Stable tag:\s(.+)\n#', $lumiere_version_recherche, $lumiere_version_match );
		$this->lumiere_version = $lumiere_version_match[1];

	}

	/**
	 * Define global constants after global options are available
	 * Why: they need global options to work
	 */
	private function lumiere_define_constants_after_globals(): void {

		/* BUILD URLSTRINGS for popups */
		$this->lumiere_urlstring = ( strlen( $this->imdb_admin_values['imdburlpopups'] ) !== 0 ) ? $this->imdb_admin_values['imdburlpopups'] : '/imdblt/';
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
			self::HIGHSLIDE_DOWNLOAD_PAGE,
			self::GUTENBERG_SEARCH_PAGE,
			self::GUTENBERG_SEARCH_URL,
			self::POPUP_SEARCH_URL,
			self::POPUP_MOVIE_URL,
			self::POPUP_PERSON_URL,
		];

		/* BUILD options constant for javascripts  */
		$this->lumiere_scripts_admin_vars = 'const lumiere_admin_vars = ' . wp_json_encode(
			[
				'imdb_path' => $this->imdb_admin_values['imdbplugindirectory'],
				'wordpress_path' => site_url(),
				'wordpress_admin_path' => admin_url(),
				'gutenberg_search_url_string' => self::GUTENBERG_SEARCH_URL_STRING,
				'gutenberg_search_url' => self::GUTENBERG_SEARCH_URL,
			]
		);
		$this->lumiere_scripts_vars = 'const lumiere_vars = ' . wp_json_encode(
			[
				'popupLarg' => $this->imdb_admin_values['imdbpopuplarg'],
				'popupLong' => $this->imdb_admin_values['imdbpopuplong'],
				'imdb_path' => $this->imdb_admin_values['imdbplugindirectory'],
				'urlpopup_film' => $this->lumiere_urlpopupsfilms,
				'urlpopup_person' => $this->lumiere_urlpopupsperson,
			]
		);

		$this->lumiere_scripts_highslide_vars = 'const highslide_vars = ' . wp_json_encode(
			[
				'imdb_path' => $this->imdb_admin_values['imdbplugindirectory'],
				'popup_border_colour' => $this->imdb_admin_values['imdbpopuptheme'],
			]
		);

		// Build list of taxonomy for people and items
		$this->array_people = [
			__( 'actor', 'lumiere-movies' ) => __( 'actor', 'lumiere-movies' ),
			__( 'composer', 'lumiere-movies' ) => __( 'composer', 'lumiere-movies' ),
			__( 'creator', 'lumiere-movies' ) => __( 'creator', 'lumiere-movies' ),
			__( 'director', 'lumiere-movies' ) => __( 'director', 'lumiere-movies' ),
			__( 'producer', 'lumiere-movies' ) => __( 'producer', 'lumiere-movies' ),
			__( 'writer', 'lumiere-movies' ) => __( 'writer', 'lumiere-movies' ),
		];
		$this->array_items = [
			__( 'color', 'lumiere-movies' ) => __( 'color', 'lumiere-movies' ),
			__( 'country', 'lumiere-movies' ) => __( 'country', 'lumiere-movies' ),
			__( 'genre', 'lumiere-movies' ) => __( 'genre', 'lumiere-movies' ),
			__( 'keyword', 'lumiere-movies' ) => __( 'keyword', 'lumiere-movies' ),
			__( 'language', 'lumiere-movies' ) => __( 'language', 'lumiere-movies' ),
		];

	}

	/**
	 * Define the number of updates on first install
	 * Called in function get_imdb_admin_option()
	 *
	 * @return bool
	 */
	public function lumiere_define_nb_updates(): bool {

		// Get the database options, since this is called before the building of $this->imdb_admin_values.
		if ( get_option( $this->imdbAdminOptionsName ) !== false ) {
			$this->imdb_admin_values = get_option( $this->imdbAdminOptionsName );
		}

		// If option 'imdbHowManyUpdates' doesn't exist, make it.
		if ( ( ! isset( $this->imdb_admin_values['imdbHowManyUpdates'] ) ) || ( $this->imdb_admin_values['imdbHowManyUpdates'] === 0 ) ) {

			// Find the number of update files to get the right
			// number of updates when installing Lumière
			$files = new FilesystemIterator( plugin_dir_path( __DIR__ ) . 'class/updates/', \FilesystemIterator::SKIP_DOTS );
			$this->current_number_updates = intval( iterator_count( $files ) + 1 );

			$option_array = $this->imdbAdminOptionsName;
			$option_key = 'imdbHowManyUpdates';
			$option_array_search = get_option( $option_array );
			$option_array_search[ $option_key ] = intval( $this->current_number_updates );

			// On successful update, exit
			if ( update_option( $option_array, $option_array_search ) ) {
				return true;
			}

			return false;

		}

		// Otherwishe the option 'imdbHowManyUpdates' exists in the database, just use it.
		$this->current_number_updates = $this->imdb_admin_values['imdbHowManyUpdates'];

		return false;
	}

	/**
	 * Makes an array of ADMIN options
	 *
	 * Multidimensional array
	 */
	private function get_imdb_admin_option(): void {

		// Define how many updates have been runned
		$this->lumiere_define_nb_updates();

		$imdbAdminOptions = [

			#--------------------------------------------------=[ Basic ]=--
			'imdbplugindirectory_partial' => '/wp-content/plugins/lumiere-movies/',
			'imdbpluginpath' => plugin_dir_path( __DIR__ ),
			'imdburlpopups' => '/imdblt/',
			'imdbkeepsettings' => '1',
			'imdburlstringtaxo' => self::URL_STRING_TAXO,
			'imdbcoversize' => '0',
			'imdbcoversizewidth' => '100',
			#--------------------------------------------------=[ Technical ]=--

			'imdbmaxresults' => 10,
			'imdbpopuptheme' => 'white',
			'imdbpopuplarg' => '540',
			'imdbpopuplong' => '350',
			'imdbintotheposttheme' => 'grey',
			'imdblinkingkill' => '0',
			'imdbautopostwidget' => '0',
			'imdblanguage' => 'en',
			'imdbdebug' => '0',                       /* Debug */
			'imdbdebuglog' => '0',                  /* Log debug */
			'imdbdebuglogpath' => self::DEBUG_LOG_PATH,
			'imdbdebuglevel' => 'DEBUG',              /* Debug levels: emergency, alert, critical,
									error, warning, notice, info, debug */
			'imdbdebugscreen' => '1',                /* Show debug on screen */
			'imdbwordpress_bigmenu' => '0',        /* Left menu */
			'imdbwordpress_tooladminmenu' => '1',    /* Top menu */
			'imdbpopup_highslide' => '1',
			'imdbtaxonomy' => '1',
			'imdbHowManyUpdates' => $this->current_number_updates, /* define the number of updates. */
			'imdbseriemovies' => 'movies+series',     /* options: movies, series, movies+series, videogames */

		];
		$imdbAdminOptions['imdbplugindirectory'] = get_site_url()
									. $imdbAdminOptions['imdbplugindirectory_partial'];

		$imdbOptions = get_option( $this->imdbAdminOptionsName );

		if ( count( $imdbOptions ) !== 0 ) { // if not empty.

			foreach ( $imdbOptions as $key => $option ) {
				$imdbAdminOptions[ $key ] = $option;
			}

			// Agregate var to construct 'imdbplugindirectory'
			$imdbAdminOptions['imdbplugindirectory'] = get_site_url()
										. $imdbAdminOptions['imdbplugindirectory_partial'];
		}

		update_option( $this->imdbAdminOptionsName, $imdbAdminOptions );

		// For debugging purpose.
		// Update imdbHowManyUpdates option.
		/*
		$option_array_search = get_option($this->imdbAdminOptionsName);
		$option_array_search['imdbHowManyUpdates'] = 5; // Chosen number of updates.
		update_option($this->imdbAdminOptionsName, $option_array_search);
		*/

	}

	/* Makes an array of CACHE options
	 *
	 * Multidimensional array
	 */
	private function get_imdb_cache_option(): void {

		$imdbCacheOptions = [

			'imdbcachedir_partial' => 'wp-content/cache/lumiere/',
			'imdbstorecache' => true,          /* not available in the admin interface */
			'imdbusecache' => '1',
			'imdbconverttozip' => true,        /* not available in the admin interface */
			'imdbusezip' => true,              /* not available in the admin interface */
			'imdbcacheexpire' => '2592000',    /* one month */
			'imdbcachedetailsshort' => '0',

		];

		$imdbCacheOptions['imdbcachedir'] = ABSPATH . $imdbCacheOptions['imdbcachedir_partial'];
		$imdbCacheOptions['imdbphotoroot'] = $imdbCacheOptions['imdbcachedir'] . 'images/';
		$imdbCacheOptions['imdbphotodir'] = content_url() . '/cache/lumiere/images/';

		$imdbOptionsc = get_option( $this->imdbCacheOptionsName );
		$imdbOptions = get_option( $this->imdbAdminOptionsName );

		if ( count( $imdbOptionsc ) !== 0 ) { // if not empty.

			foreach ( $imdbOptionsc as $key => $option ) {
				$imdbCacheOptions[ $key ] = $option;
			}

			// Agregate vars to construct 'imdbcachedir
			$imdbCacheOptions['imdbcachedir'] = ABSPATH . $imdbCacheOptions['imdbcachedir_partial'];

			// Agregate vars to construct 'imdbphotoroot
			$imdbCacheOptions['imdbphotoroot'] = $imdbCacheOptions['imdbcachedir'] . 'images/';
		}
		if ( count( $imdbOptions ) !== 0 ) { // if not empty.

			// Agregate vars to construct 'imdbphotodir'
			$imdbCacheOptions['imdbphotodir'] = get_site_url()
									. '/'
									. $imdbCacheOptions['imdbcachedir_partial']
									. 'images/';
		}

		update_option( $this->imdbCacheOptionsName, $imdbCacheOptions );

	}

	/* Makes an array of WIDGET options
	 *
	 * Multidimensional array
	 */
	private function get_imdb_widget_option(): void {

		$imdbWidgetOptions = [

			'imdbwidgettitle' => '1',
			'imdbwidgetpic' => '1',
			'imdbwidgetruntime' => '0',
			'imdbwidgetdirector' => '1',
			'imdbwidgetcountry' => '0',
			'imdbwidgetactor' => '1',
			'imdbwidgetactornumber' => 10,
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
				'comment' => '17',
				'quote' => '18',
				'tagline' => '19',
				'color' => '20',
				'alsoknow' => '21',
				'composer' => '22',
				'soundtrack' => '23',
				'trailer' => '24',
				'officialsites' => '25',
				'source' => '26',
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

		$imdbOptionsw = get_option( $this->imdbWidgetOptionsName );

		if ( count( $imdbOptionsw ) !== 0 ) { // if not empty.

			foreach ( $imdbOptionsw as $key => $option ) {
				$imdbWidgetOptions[ $key ] = $option;
			}
		}

		update_option( $this->imdbWidgetOptionsName, $imdbWidgetOptions );

	}

	/**
	 * Send Lumiere options to IMDbPHP parent class
	 *
	 */
	private function lumiere_send_config_imdbphp(): void {

		// @TODO: return here an \Imdb\Config, not this object
		// $imdb_config = new \Imdb\Config();
		// Build a dedicated class

		$this->language = $this->imdb_admin_values['imdblanguage'];
		$this->cachedir = rtrim( $this->imdb_cache_values['imdbcachedir'], '/' ); #get rid of last '/'
		$this->photodir = $this->imdb_cache_values['imdbphotoroot'];// ?imdbphotoroot? Bug imdbphp?
		$this->cache_expire = intval( $this->imdb_cache_values['imdbcacheexpire'] );
		$this->photoroot = $this->imdb_cache_values['imdbphotodir']; // ?imdbphotodir? Bug imdbphp?
		$this->storecache = $this->imdb_cache_values['imdbstorecache'];
		$this->usecache = boolval( $this->imdb_cache_values['imdbusecache'] ) ? true : false;
		$this->converttozip = $this->imdb_cache_values['imdbconverttozip'];
		$this->usezip = $this->imdb_cache_values['imdbusezip'];

		/** Where the local IMDB images reside (look for the "showtimes/" directory)
		*  This should be either a relative, an absolute, or an URL including the
		*  protocol (e.g. when a different server shall deliver them)
		* Cannot be changed in Lumière admin panel
		*/
		$this->imdb_img_url = isset( $this->imdb_admin_values['imdbplugindirectory'] ) . '/pics/showtimes';

		// @TODO: return here an \Imdb\Config, not this object
		// return $imdb_config;
	}

	/**
	 * Create cache folder if it does not exist
	 *
	 * @param bool $screen_log whether to display logging on screen or not
	 * @return bool false if cache already exist, true if created cache folders
	 */
	public function lumiere_create_cache( bool $screen_log = false ): bool {

		$imdb_admin_values = $this->imdb_admin_values;

		// Start logger
		$this->logger = new Logger( 'settingsClass', $screen_log /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );
		do_action( 'lumiere_logger' );
		$logger = $this->logger->log();

		/* Cache folder paths */
		$lumiere_folder_cache = WP_CONTENT_DIR . '/cache/lumiere/';
		$lumiere_folder_cache_images = WP_CONTENT_DIR . '/cache/lumiere/images';

		// Cache folders exist with good permissions, exit
		if ( ( is_dir( $lumiere_folder_cache ) ) && ( is_dir( $lumiere_folder_cache_images ) ) && wp_mkdir_p( $lumiere_folder_cache ) ) {

			$logger->debug( '[Lumiere][config][cachefolder] Cache folders exist and permissions are ok.' );
			return false;

		}

		// If we can write in wp-content/cache, make sure permissions are ok
		if ( wp_mkdir_p( $lumiere_folder_cache ) ) {

			chmod( $lumiere_folder_cache, 0777 );

			$logger->debug( "[Lumiere][settings][cachefolder] Cache folder $lumiere_folder_cache created." );

			// We can't write in wp-content/cache, so write in wp-content/plugins/lumiere/cache instead
		} else {

			$lumiere_folder_cache = plugin_dir_path( __DIR__ ) . 'cache';
			if ( wp_mkdir_p( $lumiere_folder_cache ) ) {

				chmod( $lumiere_folder_cache, 0777 );

				// Update the option imdbcachedir for new cache path
				$option_array_search = get_option( $this->imdbCacheOptionsName );
				$option_array_search['imdbcachedir'] = $lumiere_folder_cache;
				update_option( $this->imdbCacheOptionsName, $option_array_search );

				$logger->info( "[Lumiere][settings][cachefolder] Alternative cache folder $lumiere_folder_cache_images created." );
			}
		}

		// We can write in wp-content/cache/images
		if ( wp_mkdir_p( $lumiere_folder_cache_images ) ) {

			chmod( $lumiere_folder_cache_images, 0777 );

			$logger->debug( "[Lumiere][settings][cachefolder] Image folder $lumiere_folder_cache_images created." );

			// We can't write in wp-content/cache/images, so write in wp-content/plugins/lumiere/cache/images instead
		} else {

			$lumiere_folder_cache = plugin_dir_path( __DIR__ ) . 'cache';
			$lumiere_folder_cache_images = $lumiere_folder_cache . '/images';
			if ( wp_mkdir_p( $lumiere_folder_cache_images ) ) {

				chmod( $lumiere_folder_cache_images, 0777 );

				$logger->info( "[Lumiere][settings][cachefolder] Alternative image folder $lumiere_folder_cache_images created." );

			}

		}

		return true;
	}

	/**
	 * Detect if the current page is an editor page (post.php or post-new.php)
	 *
	 */
	public function lumiere_is_screen_editor(): bool {

		/*
		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once ABSPATH . '/wp-admin/includes/screen.php';
		}

		$screen = get_current_screen();
		$wp_is_block_editor = ( isset( $screen ) && ! is_null( $screen->is_block_editor() ) ) ? $screen->is_block_editor() : null;
		$post_type = ( isset( $screen ) && ! is_null( $screen->post_type ) ) ? $screen->post_type : null;
		*/
		if ( ! isset( $GLOBALS['hook_suffix'] ) || $GLOBALS['hook_suffix'] !== 'post.php' && $GLOBALS['hook_suffix'] !== 'post-new.php' ) {

			$this->is_editor_page = false;
			return false;

		}

		$this->is_editor_page = true;
		return true;

	}

	/**
	 * Retrieve selected type of search in admin
	 *
	 * Depends on $imdb_admin_values['imdbseriemovies'] option
	 *
	 * @return array<string>
	 */
	public function lumiere_select_type_search (): array {

		switch ( $this->imdb_admin_values['imdbseriemovies'] ) {

			case 'movies':
				return [ \Imdb\TitleSearch::MOVIE ];
			case 'movies+series':
				return [ \Imdb\TitleSearch::MOVIE, \Imdb\TitleSearch::TV_SERIES ];
			case 'series':
				return [ \Imdb\TitleSearch::TV_SERIES ];
			case 'videogames':
				return [ \Imdb\Title::GAME ];
			default:
				return [ \Imdb\TitleSearch::MOVIE, \Imdb\TitleSearch::TV_SERIES ];

		}

	}

}

