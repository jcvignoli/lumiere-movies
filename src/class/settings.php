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

// use Monolog library in /vendor/
use \Monolog\Logger;
use \Monolog\Handler\NullHandler;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Processor\IntrospectionProcessor;

// use WordPress library
use \FilesystemIterator;

class Settings extends Config {

	/* Main class Options, saved in WordPress Database
	*/
	public $imdbAdminOptionsName = 'imdbAdminOptions';
	public $imdbWidgetOptionsName = 'imdbWidgetOptions';
	public $imdbCacheOptionsName = 'imdbCacheOptions';
	/* New way, just giving constants */
	const LUMIERE_ADMIN_OPTIONS = 'imdbAdminOptions';
	const LUMIERE_WIDGET_OPTIONS = 'imdbWidgetOptions';
	const LUMIERE_CACHE_OPTIONS = 'imdbCacheOptions';

	/* Options vars
	*/
	public $imdb_admin_values;
	public $imdb_widget_values;
	public $imdb_cache_values;

	/* Websites constants
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

	/* URL Strings for popups, built in lumiere_define_constants()
	*/
	public $lumiere_urlstring;
	public $lumiere_urlstringfilms;
	public $lumiere_urlstringperson;
	public $lumiere_urlstringsearch;
	public $lumiere_urlpopupsfilms;
	public $lumiere_urlpopupsperson;
	public $lumiere_urlpopupsearch;

	/* URL for menu small images directory, built in lumiere_define_constants()
	*/
	public $lumiere_pics_dir;

	/* URL for javascript dir & path, built in lumiere_define_constants()
	*/
	public $lumiere_js_path;
	public $lumiere_js_dir;

	/* URL for blocks dir, built in lumiere_define_constants()
	*/
	public $lumiere_blocks_dir;

	/* URL for javascript dir, built in lumiere_define_constants()
	*/
	public $lumiere_css_dir;

	/* Internal URL pages constants
	*/
	const MOVE_TEMPLATE_TAXONOMY_PAGE = 'inc/move-template-taxonomy.php';
	const HIGHSLIDE_DOWNLOAD_PAGE = 'inc/highslide-download.php';
	const GUTENBERG_SEARCH_PAGE = 'inc/gutenberg-search.php';
	const GUTENBERG_SEARCH_URL_STRING = 'lumiere/search/';
	const GUTENBERG_SEARCH_URL = '/wp-admin/' . self::GUTENBERG_SEARCH_URL_STRING;
	const POPUP_SEARCH_URL = 'inc/popup-search.php';
	const POPUP_MOVIE_URL = 'inc/popup-imdb-movie.php';
	const POPUP_PERSON_URL = 'inc/popup-imdb-person.php';
	const TAXO_PEOPLE_THEME = 'taxonomy-lumiere-people.php';
	const TAXO_ITEMS_THEME = 'taxonomy-lumiere-items.php';

	/**
	 * URL string for taxonomy, 'imdblt_' by default (built in lumiere_define_constants() )
	 */
	const URL_STRING_TAXO = 'lumiere-';

	/**
	 * Include all pages of Lumière plugin
	 */
	public $lumiere_list_all_pages;

	/**
	 * Paths for javascript frontpage javascripts
	 */
	public $lumiere_scripts_vars;

	/**
	 * Options for highslide javascript
	 */
	public $lumiere_scripts_highslide_vars;

	/**
	 * Vars for javascripts in admin zone
	 */
	public $lumiere_scripts_admin_vars;

	/**
	 * Store Lumière plugin version
	 */
	public $lumiere_version;

	/**
	 * Logger class built by lumiere_start_logger() and __construct()
	 * Meant to be utilised through all the plugin
	 */
	public $loggerclass;
	public $logger_name;
	public $screenOutput;
	/* Where to write the log (WordPress default path here) */
	const DEBUG_LOG_PATH = ABSPATH . 'wp-content/debug.log';

	/**
	 * Is the current page WordPress Gutenberg editor?
	 */
	public $isGutenberg;

	/**
	 * List of types of people available
	 * is build in lumiere_define_constants_after_globals()
	 */
	public $array_people = '';

	/**
	 * List of types of people available
	 * is build in lumiere_define_constants_after_globals()
	 */
	public $array_items = '';

	/**
	 * Store the number of files inside /class/updates
	 * Allows to start with a fresh installation with the right number of updates
	 * Is built in lumiere_define_constants()
	 */
	public $current_number_updates;

	/**
	 * Constructor
	 *
	 * @param optional string $logger_name Title of Monolog logger
	 * @param optional string $screenOutput whether output Monolog on screen
	 */
	public function __construct( string $logger_name = 'unknownOrigin', bool $screenOutput = true ) {

		// Construct parent class so we can send the settings
		parent::__construct();

		// Detect if it is gutenberg, but doesn't work
		add_action( 'current_screen', [ $this, 'lumiere_is_gutenberg' ] );

		// Define Lumière constants
		$this->lumiere_define_constants();

		// Send options to the global vars
		$this->imdb_admin_values = $this->get_imdb_admin_option();
		$this->imdb_widget_values = $this->get_imdb_widget_option();
		$this->imdb_cache_values = $this->get_imdb_cache_option();

		// Define Lumière constants once global options have been created
		$this->lumiere_define_constants_after_globals();

		// Call the plugin translation
		load_plugin_textdomain( 'lumiere-movies', false, plugin_dir_url( __DIR__ ) . 'languages' );

		// Call the function to send the selected settings to imdbphp library
		$this->lumiere_send_config_imdbphp();

		// Initiate the logger class
		$this->logger_name = $logger_name;
		$this->screenOutput = $screenOutput;
		add_action( 'init', [ $this, 'lumiere_start_logger' ], 2, 0 );

	}

	/**
	 * Define global constants
	 * Run before the creation of the global options, global options may need these constants
	 */
	private function lumiere_define_constants() {

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
		$lumiere_version = preg_match( '#Stable tag:\s(.+)\n#', $lumiere_version_recherche, $lumiere_version_match );
		$this->lumiere_version = $lumiere_version_match[1];

		/* BUILD URLSTRINGS for popups */
		$this->lumiere_urlstring = ( isset( $this->imdb_admin_values['imdburlpopups'] ) ) ? $this->imdb_admin_values['imdburlpopups'] : '/imdblt/';
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

	}

	/**
	 * Define global constants after global options are available
	 * Why: they need global options to work
	 */
	private function lumiere_define_constants_after_globals() {

		/* BUILD options constant for javascripts  */
		$this->lumiere_scripts_admin_vars = 'const lumiere_admin_vars = ' . wp_json_encode(
			[
				'imdb_path' => $this->imdb_admin_values['imdbplugindirectory'],
				'wordpress_path' => site_url(),
				'wordpress_admin_path' => admin_url(),
				'gutenberg_search_url_string' => \Lumiere\Settings::GUTENBERG_SEARCH_URL_STRING,
				'gutenberg_search_url' => \Lumiere\Settings::GUTENBERG_SEARCH_URL,
			]
		);
		$this->lumiere_scripts_vars = 'const lumiere_vars = ' . wp_json_encode(
			[
				'popupLarg' => $this->imdb_admin_values['popupLarg'],
				'popupLong' => $this->imdb_admin_values['popupLong'],
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

		// Build list of people and items
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
	 * Not built from __construct(), called from \Lumiere\Core on installation
	 *
	 * @return bool
	 */
	public function lumiere_define_nb_updates(): bool {

		new \Lumiere\Settings();

		// If var current_number_updates doesn't exist, make it
		if ( ( ! isset( $this->imdb_admin_values['imdbHowManyUpdates'] ) ) || ( empty( $this->imdb_admin_values['imdbHowManyUpdates'] ) ) ) {

			// Find the number of update files to get the right
			// number of updates when installing Lumière
			$files = new FilesystemIterator( plugin_dir_path( __DIR__ ) . 'class/updates/', \FilesystemIterator::SKIP_DOTS );
			$this->current_number_updates = iterator_count( $files ) + 1;

			$option_array = $this->imdbAdminOptionsName;
			$option_key = 'imdbHowManyUpdates';
			$option_array_search = get_option( $option_array );
			$option_array_search[ $option_key ] = $this->current_number_updates;

			// On successful update, exit
			if ( update_option( $option_array, $option_array_search ) ) {
				return true;
			}

		}

		return false;
	}

	/**
	 * Makes an array of ADMIN options
	 *
	 * Multidimensional array
	 */
	public function get_imdb_admin_option(): array {

		$imdbAdminOptions = [

			#--------------------------------------------------=[ Basic ]=--
			'blog_adress' => get_bloginfo( 'url' ),    /* @TODO useless, remove */
			'imdbplugindirectory_partial' => '/wp-content/plugins/lumiere-movies/',
			'imdbpluginpath' => plugin_dir_path( __DIR__ ),
			'imdburlpopups' => '/imdblt/',
			'imdbkeepsettings' => true,
			'imdburlstringtaxo' => self::URL_STRING_TAXO,
			'imdbwebsite' => 'www.imdb.com',          /* @TODO useless, remove */
			'imdbcoversize' => false,
			'imdbcoversizewidth' => '100',
			#--------------------------------------------------=[ Technical ]=--

			'imdb_utf8recode' => true,                /* @TODO useless, remove */
			'imdbmaxresults' => 10,
			'imdbpopuptheme' => 'white',
			'popupLarg' => '540',
			'popupLong' => '350',
			'imdbintotheposttheme' => 'grey',
			'imdblinkingkill' => false,
			'imdbautopostwidget' => false,
			'imdbimgdir' => 'pics/',                  /* @TODO useless, remove */
			'imdblanguage' => 'en',
			/*'imdbsourceout' => false,*/
			'imdbdebug' => false,                     /* Debug */
			'imdbdebuglog' => false,                  /* Log debug */
			'imdbdebuglogpath' => self::DEBUG_LOG_PATH,
			'imdbdebuglevel' => 'DEBUG',              /* Debug levels: emergency, alert, critical,
									error, warning, notice, info, debug */
			'imdbdebugscreen' => true,                /* Show debug on screen */
			'imdbwordpress_bigmenu' => false,         /* Left menu */
			'imdbwordpress_tooladminmenu' => true,    /* Top menu */
			'imdbpopup_highslide' => true,
			'imdbtaxonomy' => true,
			'imdbHowManyUpdates' => $this->current_number_updates, # for use in class UpdateOptions
			'imdbseriemovies' => 'movies+series',     /* options: movies, series, movies+series, videogames */

		];
		$imdbAdminOptions['imdbplugindirectory'] = $imdbAdminOptions['blog_adress']
									. $imdbAdminOptions['imdbplugindirectory_partial'];

		$imdbOptions = get_option( $this->imdbAdminOptionsName );

		if ( ! empty( $imdbOptions ) ) {

			foreach ( $imdbOptions as $key => $option ) {
				$imdbAdminOptions[ $key ] = $option;
			}

			// Agregate var to construct 'imdbplugindirectory'
			$imdbAdminOptions['imdbplugindirectory'] = $imdbAdminOptions['blog_adress']
										. $imdbAdminOptions['imdbplugindirectory_partial'];
		}

		update_option( $this->imdbAdminOptionsName, $imdbAdminOptions );

		return $imdbAdminOptions;

	}

	/* Makes an array of CACHE options
	 *
	 * Multidimensional array
	 */
	public function get_imdb_cache_option(): array {

		$imdbCacheOptions = [

			'imdbcachedir_partial' => 'wp-content/cache/lumiere/',
			'imdbstorecache' => true,        /* not available in the admin interface */
			'imdbusecache' => true,
			'imdbconverttozip' => true,        /* not available in the admin interface */
			'imdbusezip' => true,        /* not available in the admin interface */
			'imdbcacheexpire' => '2592000',   /* one month */
			'imdbcachedetails' => true,
			'imdbcachedetailsshort' => false,

		];

		$imdbCacheOptions['imdbcachedir'] = ABSPATH . $imdbCacheOptions['imdbcachedir_partial'];
		$imdbCacheOptions['imdbphotoroot'] = $imdbCacheOptions['imdbcachedir'] . 'images/';
		$imdbCacheOptions['imdbphotodir'] = content_url() . '/cache/lumiere/images/';

		$imdbOptionsc = get_option( $this->imdbCacheOptionsName );
		$imdbOptions = get_option( $this->imdbAdminOptionsName );

		if ( ! empty( $imdbOptionsc ) ) {

			foreach ( $imdbOptionsc as $key => $option ) {
				$imdbCacheOptions[ $key ] = $option;
			}

			// Agregate vars to construct 'imdbcachedir
			$imdbCacheOptions['imdbcachedir'] = ABSPATH . $imdbCacheOptions['imdbcachedir_partial'];

			// Agregate vars to construct 'imdbphotoroot
			$imdbCacheOptions['imdbphotoroot'] = $imdbCacheOptions['imdbcachedir'] . 'images/';
		}
		if ( ! empty( $imdbOptions ) ) {

			// Agregate vars to construct 'imdbphotodir'
			$imdbCacheOptions['imdbphotodir'] = $imdbOptions['blog_adress']
									. '/'
									. $imdbCacheOptions['imdbcachedir_partial']
									. 'images/';
		}

		update_option( $this->imdbCacheOptionsName, $imdbCacheOptions );

		return $imdbCacheOptions;

	}

	/* Makes an array of WIDGET options
	 *
	 * Multidimensional array
	 */
	public function get_imdb_widget_option(): array {

		$imdbWidgetOptions = [

			'imdbwidgettitle' => true,
			'imdbwidgetpic' => true,
			'imdbwidgetruntime' => false,
			'imdbwidgetdirector' => true,
			'imdbwidgetcountry' => false,
			'imdbwidgetactor' => true,
			'imdbwidgetactornumber' => '10',
			'imdbwidgetcreator' => false,
			'imdbwidgetrating' => false,
			'imdbwidgetlanguage' => false,
			'imdbwidgetgenre' => true,
			'imdbwidgetwriter' => true,
			'imdbwidgetproducer' => false,
			'imdbwidgetproducernumber' => false,
			'imdbwidgetkeyword' => false,
			'imdbwidgetprodcompany' => false,
			'imdbwidgetplot' => false,
			'imdbwidgetplotnumber' => false,
			'imdbwidgetgoof' => false,
			'imdbwidgetgoofnumber' => false,
			'imdbwidgetcomment' => false,
			'imdbwidgetquote' => false,
			'imdbwidgetquotenumber' => false,
			'imdbwidgettagline' => false,
			'imdbwidgettaglinenumber' => false,
			'imdbwidgetcolor' => false,
			'imdbwidgetalsoknow' => false,
			'imdbwidgetalsoknownumber' => false,
			'imdbwidgetcomposer' => false,
			'imdbwidgetsoundtrack' => false,
			'imdbwidgetsoundtracknumber' => false,
			'imdbwidgetofficialsites' => false,
			'imdbwidgetsource' => false,
			'imdbwidgetonpost' => true,
			'imdbwidgetonpage' => true,
			'imdbwidgetyear' => false,
			'imdbwidgettrailer' => false,
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

			'imdbtaxonomycolor' => false,
			'imdbtaxonomycomposer' => false,
			'imdbtaxonomycountry' => false,
			'imdbtaxonomycreator' => false,
			'imdbtaxonomydirector' => false,
			'imdbtaxonomygenre' => true,
			'imdbtaxonomykeyword' => false,
			'imdbtaxonomylanguage' => false,
			'imdbtaxonomyproducer' => false,
			'imdbtaxonomyactor' => false,
			'imdbtaxonomywriter' => false,

		];

		$imdbOptionsw = get_option( $this->imdbWidgetOptionsName );

		if ( ! empty( $imdbOptionsw ) ) {
			foreach ( $imdbOptionsw as $key => $option ) {
				$imdbWidgetOptions[ $key ] = $option;
			}
		}

		update_option( $this->imdbWidgetOptionsName, $imdbWidgetOptions );

		return $imdbWidgetOptions;

	}

	/* Send Lumiere options to IMDbPHP parent class
	 *
	 *
	 */
	private function lumiere_send_config_imdbphp() {

		// @TODO: return here an \Imdb\Config, not this object
		// $imdb_config = new \Imdb\Config();

		$this->language = $this->imdb_admin_values['imdblanguage'] ?? null;
		$this->cachedir = rtrim( $this->imdb_cache_values['imdbcachedir'], '/' ) ?? null; #get rid of last '/'
		$this->photodir = $this->imdb_cache_values['imdbphotoroot'] ?? null;// ?imdbphotoroot? Bug imdbphp?
		$this->cache_expire = $this->imdb_cache_values['imdbcacheexpire'] ?? null;
		$this->photoroot = $this->imdb_cache_values['imdbphotodir'] ?? null; // ?imdbphotodir? Bug imdbphp?
		$this->storecache = $this->imdb_cache_values['imdbstorecache'] ?? null;
		$this->usecache = $this->imdb_cache_values['imdbusecache'] ?? null;
		$this->converttozip = $this->imdb_cache_values['imdbconverttozip'] ?? null;
		$this->usezip = $this->imdb_cache_values['imdbusezip'] ?? null;

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
	 * @return false if cache already exist, true if created cache folders
	 */
	public function lumiere_create_cache(): bool {

		$imdb_admin_values = $this->imdb_admin_values;

		// Start logger
		if ( ! isset( $this->loggerclass ) ) {
			$this->lumiere_start_logger( 'configMain', false /* Deactivate the onscreen log, so WordPress activation doesn't trigger any error if debug is activated */ );
		}

		/* Cache folder paths */
		$lumiere_folder_cache = WP_CONTENT_DIR . '/cache/lumiere/';
		$lumiere_folder_cache_images = WP_CONTENT_DIR . '/cache/lumiere/images';

		// Cache folders exist with good permissions, exit
		if ( ( is_dir( $lumiere_folder_cache ) ) && ( is_dir( $lumiere_folder_cache_images ) ) && wp_mkdir_p( $lumiere_folder_cache ) ) {

			$this->loggerclass->debug( '[Lumiere][config][cachefolder] Cache folders exist and permissions are ok.' );
			return false;

		}

		// If we can write in wp-content/cache, make sure permissions are ok
		if ( wp_mkdir_p( $lumiere_folder_cache ) ) {

			chmod( $lumiere_folder_cache, 0777 );

			$this->loggerclass->debug( "[Lumiere][config][cachefolder] Cache folder $lumiere_folder_cache created." );

			// We can't write in wp-content/cache, so write in wp-content/plugins/lumiere/cache instead
		} else {

			$lumiere_folder_cache = plugin_dir_path( __DIR__ ) . 'cache';
			if ( wp_mkdir_p( $lumiere_folder_cache ) ) {

				chmod( $lumiere_folder_cache, 0777 );

				// Update the option imdbcachedir for new cache path
				$option_array_search = get_option( $this->imdbCacheOptionsName );
				$option_array_search['imdbcachedir'] = $lumiere_folder_cache;
				update_option( $this->imdbCacheOptionsName, $option_array_search );

				$this->loggerclass->info( "[Lumiere][config][cachefolder] Alternative cache folder $lumiere_folder_cache_images created." );
			}
		}

		// We can write in wp-content/cache/images
		if ( wp_mkdir_p( $lumiere_folder_cache_images ) ) {

			chmod( $lumiere_folder_cache_images, 0777 );

			$this->loggerclass->debug( "[Lumiere][config][cachefolder] Image folder $lumiere_folder_cache_images created." );

			// We can't write in wp-content/cache/images, so write in wp-content/plugins/lumiere/cache/images instead
		} else {

			$lumiere_folder_cache = plugin_dir_path( __DIR__ ) . 'cache';
			$lumiere_folder_cache_images = $lumiere_folder_cache . '/images';
			if ( wp_mkdir_p( $lumiere_folder_cache_images ) ) {

				chmod( $lumiere_folder_cache_images, 0777 );

				$this->loggerclass->info( "[Lumiere][config][cachefolder] Alternative image folder $lumiere_folder_cache_images created." );

			}

		}

		return true;
	}

	/* Try to detect if the current page is gutenberg editor
	 * Doesn't work
	 */
	public function lumiere_is_gutenberg() {

		global $current_screen;

		$screen = get_current_screen();

		if ( $screen->is_block_editor() ) {

			return $this->isGutenberg === true;

		}

		return $this->isGutenberg === false;

	}

	/**
	 *  Start and select which Logger to use
	 *
	 * @param (string) optional $logger_name: title applied to the logger in the logs under origin
	 * @param (bool) optional $screenOutput: whether to display the screen output. Useful for plugin activation.
	 *
	 * @return the logger in $loggerclass
	 */
	public function lumiere_start_logger ( string $logger_name = null, bool $screenOutput = true ) {

		// Get local vars if passed in the function, if empty get the global vars
		$logger_name = isset( $logger_name ) ? $logger_name : $this->logger_name;
		$screenOutput = isset( $screenOutput ) ? $screenOutput : $this->screenOutput;

		// Start Monolog logger
		if ( ( current_user_can( 'manage_options' ) && $this->imdb_admin_values['imdbdebug'] === '1' ) || ( $this->imdb_admin_values['imdbdebug'] === '1' && defined( 'DOING_CRON' ) && DOING_CRON ) ) {

			$this->loggerclass = new Logger( $logger_name );

			// Get the verbosity from options and build the constant
			$logger_verbosity = isset( $this->imdb_admin_values['imdbdebuglevel'] ) ? constant( '\Monolog\Logger::' . $this->imdb_admin_values['imdbdebuglevel'] ) : constant( '\Monolog\Logger::DEBUG' );

			/* Save log if option activated
			 */
			if ( $this->imdb_admin_values['imdbdebuglog'] === '1' ) {

				// Add current url and referrer to the log
				//$logger->pushProcessor(new \Monolog\Processor\WebProcessor(NULL, array('url','referrer') ));

				// Add the file, the line, the class, the function to the log
				$this->loggerclass->pushProcessor( new IntrospectionProcessor( $logger_verbosity ) );
				$filelogger = new StreamHandler( $this->imdb_admin_values['imdbdebuglogpath'], $logger_verbosity );

				// Change the date and output formats of the log
				$dateFormat = 'd-M-Y H:i:s e';
				$output = "[%datetime%] %channel%.%level_name%: %message% %extra%\n";
				$screenformater = new LineFormatter( $output, $dateFormat );
				$filelogger->setFormatter( $screenformater );

				// Utilise the new format and processor
				$this->loggerclass->pushHandler( $filelogger );
			}

			/* Display errors on screen if option activated
			 */
			if ( ( $this->imdb_admin_values['imdbdebugscreen'] === '1' ) && ( $screenOutput === true ) ) {

				// Change the format
				$output = "[%level_name%] %message%<br />\n";
				$screenformater = new LineFormatter( $output );

				// Change the handler, php://output is the only working (on my machine)
				$screenlogger = new StreamHandler( 'php://output', $logger_verbosity );
				$screenlogger->setFormatter( $screenformater );

				// Utilise the new handler and format
				$this->loggerclass->pushHandler( $screenlogger );

			}

			return;

		}

		// Run null logger
		$this->loggerclass = new Logger( $logger_name );
		$this->loggerclass->pushHandler( new NullHandler() );

	}

	/* Retrieve selected type of search in admin
	 *
	 * Depends on $imdb_admin_values['imdbseriemovies'] option
	 *
	 * @return false or the selection
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
		}

	}

}

