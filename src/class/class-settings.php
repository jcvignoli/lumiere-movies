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
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Plugins\Logger;
// use PHP library.
use FilesystemIterator;

/**
 * @phpstan-type LevelLogName 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY'
 * @phpstan-type OPTIONS_ADMIN array{'imdbplugindirectory': string, 'imdbplugindirectory_partial': string, 'imdbpluginpath': string,'imdburlpopups': string,'imdbkeepsettings': string,'imdburlstringtaxo': string,'imdbcoversize': string,'imdbcoversizewidth': string, 'imdbmaxresults': int, 'imdbpopuptheme': string, 'imdbpopuplarg': string,'imdbpopuplong': string, 'imdbintotheposttheme': string, 'imdblinkingkill': string, 'imdbautopostwidget': string, 'imdblanguage': string, 'imdbdebug': string, 'imdbdebuglog': string, 'imdbdebuglogpath': string, 'imdbdebuglevel': LevelLogName, 'imdbdebugscreen': string, 'imdbwordpress_bigmenu': string, 'imdbwordpress_tooladminmenu': string, 'imdbpopup_modal_window': string, 'imdbtaxonomy': string, 'imdbHowManyUpdates': int, 'imdbseriemovies': string}
 * @phpstan-type OPTIONS_CACHE array{'imdbcachedir_partial': string, 'imdbstorecache': bool, 'imdbusecache': string, 'imdbconverttozip': bool, 'imdbusezip': bool, 'imdbcacheexpire': string, 'imdbcachedetailsshort': string,'imdbcachedir': string,'imdbphotoroot': string, 'imdbphotodir': string}
 * @phpstan-type OPTIONS_WIDGET array{'imdbwidgettitle': string, 'imdbwidgetpic': string,'imdbwidgetruntime': string, 'imdbwidgetdirector': string, 'imdbwidgetcountry': string, 'imdbwidgetactor':string, 'imdbwidgetactornumber':int, 'imdbwidgetcreator': string, 'imdbwidgetrating': string, 'imdbwidgetlanguage': string, 'imdbwidgetgenre': string, 'imdbwidgetwriter': string, 'imdbwidgetproducer': string, 'imdbwidgetproducernumber': bool|string, 'imdbwidgetkeyword': string, 'imdbwidgetprodcompany': string, 'imdbwidgetplot': string, 'imdbwidgetplotnumber': string, 'imdbwidgetgoof': string, 'imdbwidgetgoofnumber': string|bool, 'imdbwidgetcomment': string, 'imdbwidgetquote': string, 'imdbwidgetquotenumber': string|bool, 'imdbwidgettagline': string, 'imdbwidgettaglinenumber': string|bool, 'imdbwidgetcolor': string, 'imdbwidgetalsoknow': string, 'imdbwidgetalsoknownumber': string|bool, 'imdbwidgetcomposer': string, 'imdbwidgetsoundtrack': string, 'imdbwidgetsoundtracknumber': string|bool, 'imdbwidgetofficialsites': string, 'imdbwidgetsource': string, 'imdbwidgetyear': string, 'imdbwidgettrailer': string, 'imdbwidgettrailernumber': bool|string, 'imdbwidgetorder': array<string>, 'imdbtaxonomycolor': string, 'imdbtaxonomycomposer': string, 'imdbtaxonomycountry': string, 'imdbtaxonomycreator': string, 'imdbtaxonomydirector': string, 'imdbtaxonomygenre': string, 'imdbtaxonomykeyword': string, 'imdbtaxonomylanguage': string, 'imdbtaxonomyproducer': string, 'imdbtaxonomyactor': string, 'imdbtaxonomywriter': string}
*/
class Settings {

	/**
	 * Those plugins results in Lumière deactivation
	 * Those are crap and Lumière will not support them
	 */
	const LUMIERE_INCOMPATIBLE_PLUGINS = [ 'rss-feed-post-generator-echo/rss-feed-post-generator-echo.php' ];

	/**
	 * Name of the databases as stored in WordPress db
	 */
	const LUMIERE_ADMIN_OPTIONS = 'imdbAdminOptions';
	const LUMIERE_WIDGET_OPTIONS = 'imdbWidgetOptions';
	const LUMIERE_CACHE_OPTIONS = 'imdbCacheOptions';

	/**
	 * Admin options vars
	 * @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	*/
	private array $imdb_admin_values;

	/**
	 * Website URLs constants
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

	/**
	 * Internal URL pages constants
	 */
	const MOVE_TEMPLATE_TAXONOMY_PAGE = 'class/tools/class-copy-template-taxonomy.php'; // not included in $lumiere_list_all_pages.
	const HIGHSLIDE_DOWNLOAD_PAGE = 'class/plugins/highslide-download.php';
	const VIRTUAL_PAGE_MAKER = 'class/tools/class-virtual-page.php';
	const GUTENBERG_SEARCH_PAGE = 'class/tools/class-search.php';
	const GUTENBERG_SEARCH_URL_STRING = 'lumiere/search/';
	const GUTENBERG_SEARCH_URL = '/wp-admin/' . self::GUTENBERG_SEARCH_URL_STRING;
	const POPUP_SEARCH_URL = 'class/frontend/class-popup-search.php';
	const POPUP_MOVIE_URL = 'class/frontend/class-popup-movie.php';
	const POPUP_PERSON_URL = 'class/frontend/class-popup-person.php';
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
	public int $current_number_updates;

	/**
	 * Where to write the log
	 * WordPress default path
	 */
	const DEBUG_LOG_PATH = WP_CONTENT_DIR . '/debug.log';

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
	 */
	public function __construct() {

		// Define Lumière constants.
		$this->lumiere_define_constants();

		// Build options, get them from database and then send to properties.
		$this->get_imdb_admin_option();
		$this->get_imdb_widget_option();
		$this->get_imdb_cache_option();
		$this->imdb_admin_values = get_option( self::LUMIERE_ADMIN_OPTIONS );

		// Define Lumière constants once global options have been created.
		$this->lumiere_define_constants_after_globals();

		// Call the plugin translation
		load_plugin_textdomain( 'lumiere-movies', false, plugin_dir_url( __DIR__ ) . 'languages' );

	}

	/**
	 * Define global constants
	 * Run before the creation of the database options, database options may need these constants
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
	 * Define global constants after database options are available
	 * Why: they need database options to work
	 */
	private function lumiere_define_constants_after_globals(): void {

		/* BUILD URLSTRINGS for popups */
		$this->lumiere_urlstring = ( strlen( $this->imdb_admin_values['imdburlpopups'] ) !== 0 ) ? $this->imdb_admin_values['imdburlpopups'] : '/lumiere/';
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
				'imdb_path' => $this->imdb_admin_values['imdbplugindirectory'],
				'urlpopup_film' => $this->lumiere_urlpopupsfilms,
				'urlpopup_person' => $this->lumiere_urlpopupsperson,
				/** Popups */
				'popup_border_colour' => $this->imdb_admin_values['imdbpopuptheme'],
				'popupLarg' => $this->imdb_admin_values['imdbpopuplarg'],
				'popupLong' => $this->imdb_admin_values['imdbpopuplong'],
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
		if ( get_option( self::LUMIERE_ADMIN_OPTIONS ) !== false ) {
			$this->imdb_admin_values = get_option( self::LUMIERE_ADMIN_OPTIONS );
		}

		// If option 'imdbHowManyUpdates' doesn't exist, make it.
		if ( ( ! isset( $this->imdb_admin_values['imdbHowManyUpdates'] ) ) || ( $this->imdb_admin_values['imdbHowManyUpdates'] === 0 ) ) {

			// Find the number of update files to get the right
			// number of updates when installing Lumière
			$files = new FilesystemIterator( plugin_dir_path( __DIR__ ) . 'class/updates/', \FilesystemIterator::SKIP_DOTS );
			$this->current_number_updates = intval( iterator_count( $files ) + 1 );

			$option_key = 'imdbHowManyUpdates';
			$option_array_search = get_option( self::LUMIERE_ADMIN_OPTIONS );
			$option_array_search[ $option_key ] = intval( $this->current_number_updates );

			// On successful update, exit
			if ( update_option( self::LUMIERE_ADMIN_OPTIONS, $option_array_search ) ) {
				return true;
			}

			return false;

		}

		// Otherwise the option 'imdbHowManyUpdates' exists in the database, just use it.
		$this->current_number_updates = $this->imdb_admin_values['imdbHowManyUpdates'];

		return false;
	}

	/**
	 * Make an array of ADMIN options
	 *
	 * Multidimensional array
	 */
	private function get_imdb_admin_option(): void {

		// Define how many updates have been runned
		$this->lumiere_define_nb_updates();

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
			'imdbpopup_modal_window' => 'bootstrap',
			'imdbtaxonomy' => '1',
			'imdbHowManyUpdates' => $this->current_number_updates, /* define the number of updates. */
			'imdbseriemovies' => 'movies+series',     /* options: movies, series, movies+series, videogames */

		];
		$imdb_admin_options['imdbplugindirectory'] = get_site_url()
									. $imdb_admin_options['imdbplugindirectory_partial'];

		$imdb_options_a = get_option( self::LUMIERE_ADMIN_OPTIONS );

		if ( count( $imdb_options_a ) !== 0 ) { // if not empty.

			foreach ( $imdb_options_a as $key => $option ) {
				$imdb_admin_options[ $key ] = $option;
			}

			// Agregate var to construct 'imdbplugindirectory'
			$imdb_admin_options['imdbplugindirectory'] = get_site_url()
										. $imdb_admin_options['imdbplugindirectory_partial'];
		}

		update_option( self::LUMIERE_ADMIN_OPTIONS, $imdb_admin_options );

		// For debugging purpose.
		// Update imdbHowManyUpdates option.
		/*
		$option_array_search = get_option( self::LUMIERE_ADMIN_OPTIONS );
		$option_array_search['imdbHowManyUpdates'] = 11; // Chosen number of updates.
		update_option( self::LUMIERE_ADMIN_OPTIONS, $option_array_search );
		*/

	}

	/**
	 * Makes an array of CACHE options
	 *
	 * Multidimensional array
	 */
	private function get_imdb_cache_option(): void {

		// Build partial cache path, such as 'wp-content/cache/lumiere/'
		$imdbcachedir_partial = str_replace( WP_CONTENT_DIR, '', self::LUMIERE_FOLDER_CACHE );

		$imdb_cache_options = [

			'imdbcachedir_partial' => $imdbcachedir_partial,
			'imdbusecache' => '1',
			'imdbconverttozip' => true,        /* not available in the admin interface */
			'imdbusezip' => true,              /* not available in the admin interface */
			'imdbcacheexpire' => '2592000',    /* one month */
			'imdbcachedetailsshort' => '0',

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
		if ( is_array( $imdb_options_a ) === true && count( $imdb_options_a ) !== 0 ) { // if not empty.

			// Agregate vars to construct 'imdbphotodir'
			$imdb_cache_options['imdbphotodir'] = content_url()
									. $imdb_cache_options['imdbcachedir_partial']
									. 'images/';
		}

		update_option( self::LUMIERE_CACHE_OPTIONS, $imdb_cache_options );

	}

	/**
	 * Makes an array of WIDGET options
	 *
	 * Multidimensional array
	 */
	private function get_imdb_widget_option(): void {

		$imdb_widget_options = [

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

		$imdb_options_w = get_option( self::LUMIERE_WIDGET_OPTIONS );

		if ( is_array( $imdb_options_w ) === true && count( $imdb_options_w ) !== 0 ) { // if not empty.

			foreach ( $imdb_options_w as $key => $option ) {
				$imdb_widget_options[ $key ] = $option;
			}
		}

		update_option( self::LUMIERE_WIDGET_OPTIONS, $imdb_widget_options );

	}

	/**
	 * Create cache folder if it does not exist
	 * Create folder based on 'imdbcachedir' cache option value, if not using alternative folders (inside plugin)
	 * Return false if: 1/ Cache is not active; 2/ Can't created alternative cache folders inside Lumière plugin
	 * 3/ Cache folders already exist & are writable
	 *
	 * @info Can't use $wp_system at this stage, since it is also called during plugin activation in class core
	 *
	 * @param bool $screen_log whether to display logging on screen or not
	 * @return bool false if cache already exist or can't be created, true if cache folders were created
	 */
	public function lumiere_create_cache( bool $screen_log = false ): bool {

		// Start logger
		$logger_class = new Logger( 'settingsClass', $screen_log /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated, such as upon plugin activation */ );
		do_action( 'lumiere_logger' );
		$logger = $logger_class->log();

		// Cache folder paths.
		$options_cache = get_option( self::LUMIERE_CACHE_OPTIONS );
		$lumiere_folder_cache = $options_cache['imdbcachedir'];
		$lumiere_folder_cache_images = $options_cache['imdbphotoroot'];

		// If cache is not active, exit.
		if ( $options_cache['imdbusecache'] !== '1' ) {
			$logger->debug( '[Lumiere][config][cachefolder] Cache is inactive, folders are not checked.' );
			return false;
		}

		// Cache folders exist with good permissions, exit.
		if ( ( is_writable( $lumiere_folder_cache ) ) && ( is_writable( $lumiere_folder_cache_images ) ) && wp_mkdir_p( $lumiere_folder_cache ) && wp_mkdir_p( $lumiere_folder_cache_images ) ) {

			$logger->debug( '[Lumiere][config][cachefolder] Cache folders exist and permissions are ok.' );
			return false;

		}

		$lumiere_alt_folder_cache = plugin_dir_path( __DIR__ ) . 'cache';
		$lumiere_alt_folder_cache_images = $lumiere_alt_folder_cache . '/images';

		// If we can write in $options_cache['imdbcachedir'] (ie: wp-content/cache), make sure permissions are ok
		if ( wp_mkdir_p( $lumiere_folder_cache ) && chmod( $lumiere_folder_cache, 0755 ) ) {

			$logger->debug( "[Lumiere][config][cachefolder] Cache folder $lumiere_folder_cache created." );

			// We can't write in $options_cache['imdbphotoroot'], so write in wp-content/plugins/lumiere/cache instead
		} elseif ( wp_mkdir_p( $lumiere_alt_folder_cache ) && chmod( $lumiere_alt_folder_cache, 0755 ) ) {

			// Create partial var
			$lumiere_alt_folder_cache_partial = str_replace( WP_CONTENT_DIR, '', plugin_dir_path( __DIR__ ) ) . 'cache/';

			// Update the option imdbcachedir for new cache path values
			$options_cache['imdbcachedir'] = $lumiere_alt_folder_cache;
			$options_cache['imdbcachedir_partial'] = $lumiere_alt_folder_cache_partial;
			update_option( self::LUMIERE_CACHE_OPTIONS, $options_cache );

			$logger->info( "[Lumiere][config][cachefolder] Alternative cache folder $lumiere_alt_folder_cache created." );
		} else {

			$logger->error( "[Lumiere][config][cachefolder] Cannot create alternative cache folder $lumiere_alt_folder_cache." );
			return false;

		}

		// We can write in wp-content/cache/images
		if ( wp_mkdir_p( $lumiere_folder_cache_images ) && chmod( $lumiere_folder_cache_images, 0755 ) ) {

			$logger->debug( "[Lumiere][config][cachefolder] Image folder $lumiere_folder_cache_images created." );

			// We can't write in wp-content/cache/images, so write in wp-content/plugins/lumiere/cache/images instead
		} elseif ( wp_mkdir_p( $lumiere_alt_folder_cache_images ) && chmod( $lumiere_alt_folder_cache_images, 0755 ) ) {

			$lumiere_folder_cache_partial = str_replace( WP_CONTENT_DIR, '', plugin_dir_path( __DIR__ ) ) . 'cache/';

			// Update the option imdbcachedir for new cache path values
			$options_cache['imdbcachedir_partial'] = $lumiere_folder_cache_partial;
			$options_cache['imdbphotodir'] = get_site_url() . '/' . $lumiere_folder_cache_partial . '/images/';
			$options_cache['imdbphotoroot'] = $lumiere_alt_folder_cache_images;
			update_option( self::LUMIERE_CACHE_OPTIONS, $options_cache );

			$logger->info( "[Lumiere][config][cachefolder] Alternative cache image folder $lumiere_alt_folder_cache_images created." );

		} else {

			$logger->error( "[Lumiere][config][cachefolder] Cannot create alternative cache image folder $lumiere_alt_folder_cache_images." );
			return false;

		}

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

