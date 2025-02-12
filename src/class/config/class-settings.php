<?php declare( strict_types = 1 );
/**
 * Settings
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       3.0
 * @package lumiere-movies
 */
namespace Lumiere\Config;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { // Don't check for Settings class since it's Settings class.
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Config\Settings_Build;

// Needed vars for uninstall, fails otherwise.
// Use of defined() condition for PHPStan
if ( ! defined( 'LUMIERE_WP_PATH' ) ) {
	require_once plugin_dir_path( dirname( __DIR__ ) ) . 'vars.php';
}

/**
 * Settings class
 * Call create_database_options() to set the options in WP config database
 * Is extended by Get_Options
 *
 * @since 4.0 Moved cache folder creation to class cache tools
 * @since 4.1 Renamed *imdb_widget_* to *imdb_data_* all over the website
 * @since 4.4 Options are created only when installing/activating the plugin, widely rewritten and simplified. OPTIONS_DATA is dynamically created according to the arrays of items/people added. Use of {@see Get_Options} class as child class. {@see Settings_Build} is parent class.
 *
 * @phpstan-type OPTIONS_ADMIN array{imdbHowManyUpdates: string, imdbautopostwidget: '0'|'1'|string, imdbcoversize: '0'|'1'|string, imdbcoversizewidth: string, imdbdebug: '0'|'1'|string, imdbdebuglevel: 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY', imdbdebuglog: '0'|'1'|string, imdbdebuglogpath: mixed, imdbdebugscreen:'0'|'1'|string, imdbdelayimdbrequest: '0'|'1'|string, imdbintotheposttheme: string, imdbirpdisplay: '0'|'1'|string, imdbkeepsettings: '0'|'1'|string, imdblanguage: string, imdblinkingkill: '0'|'1'|string, imdbmaxresults: string, imdbplugindirectory: string, imdbplugindirectory_partial: string, imdbpluginpath: mixed, imdbpopup_modal_window: string, imdbpopuplarg: string, imdbpopuplong: string, imdbpopuptheme: string, imdbseriemovies: 'movies'|'series'|'movies+series'|'videogames', imdbtaxonomy: '0'|'1'|string, imdburlpopups: string, imdburlstringtaxo: string, imdbwordpress_bigmenu: '0'|'1'|string, imdbwordpress_tooladminmenu: '0'|'1'|string}
 *
 * @phpstan-type OPTIONS_CACHE array{ 'imdbcacheautorefreshcron': string, 'imdbcachedetailsshort': string, 'imdbcachedir': string, 'imdbcachedir_partial': string, 'imdbcacheexpire': string, 'imdbcachekeepsizeunder': string, 'imdbcachekeepsizeunder_sizelimit': string, 'imdbphotodir': string, 'imdbphotoroot': string, 'imdbusecache': string, 'imdbcachedetailshidden': string}
 *
 * @phpstan-type OPTIONS_DATA array{'imdbtaxonomyactor'?:string, 'imdbtaxonomycolor'?:string, 'imdbtaxonomycomposer'?:string, 'imdbtaxonomycountry'?:string, 'imdbtaxonomycreator'?:string, 'imdbtaxonomydirector'?:string, 'imdbtaxonomygenre'?:string, 'imdbtaxonomykeyword'?:string, 'imdbtaxonomylanguage'?:string, 'imdbtaxonomyproducer'?:string, 'imdbtaxonomywriter'?:string, 'imdbwidgetactor'?:string, 'imdbwidgetactornumber'?:string, 'imdbwidgetalsoknow'?:string, 'imdbwidgetalsoknownumber'?:string, 'imdbwidgetcolor'?:string, 'imdbwidgetcomment'?:string, 'imdbwidgetcomposer'?:string, 'imdbwidgetconnection'?:string, 'imdbwidgetconnectionnumber'?:string, 'imdbwidgetcountry'?:string, 'imdbwidgetcreator'?:string, 'imdbwidgetdirector'?:string, 'imdbwidgetgenre'?:string, 'imdbwidgetgoof'?:string, 'imdbwidgetgoofnumber'?:string, 'imdbwidgetkeyword'?:string, 'imdbwidgetlanguage'?:string, 'imdbwidgetofficialsites'?:string, 'imdbwidgetpic'?:string, 'imdbwidgetplot'?:string, 'imdbwidgetplotnumber'?:string, 'imdbwidgetprodcompany'?:string, 'imdbwidgetproducer'?:string, 'imdbwidgetproducernumber'?:string, 'imdbwidgetquote'?:string, 'imdbwidgetquotenumber'?:string, 'imdbwidgetrating'?:string, 'imdbwidgetruntime'?:string, 'imdbwidgetsoundtrack'?:string, 'imdbwidgetsoundtracknumber'?:string, 'imdbwidgetsource'?:string, 'imdbwidgettagline'?:string, 'imdbwidgettaglinenumber'?:string, 'imdbwidgettitle'?:string, 'imdbwidgettrailer'?:string, 'imdbwidgettrailernumber'?:string, 'imdbwidgetwriter'?:string, 'imdbwidgetyear'?:string,'imdbwidgetorder': array{title?: string, pic?: string, runtime?: string, director?: string, connection?: string, country?: string, actor?: string, creator?: string, rating?: string, language?: string, genre?: string, writer?: string, producer?: string, keyword?: string, prodcompany?: string, plot?: string, goof?: string, comment?: string, quote?: string, tagline?: string, trailer?: string, color?: string, alsoknow?: string, composer?: string, soundtrack?: string, officialsites?: string, source?: string, year?: string} }
  */
class Settings extends Settings_Build {

	/**
	 * Name of the databases as stored in WordPress db
	 * Only used in child class, has to be called in Get_Options
	 */
	protected const LUMIERE_ADMIN_OPTIONS           = 'lumiere_admin_options';
	protected const LUMIERE_DATA_OPTIONS            = 'lumiere_data_options';
	protected const LUMIERE_CACHE_OPTIONS           = 'lumiere_cache_options';

	/**
	 * Website URLs constants
	 */
	public const LUM_BLOG_PLUGIN                    = 'https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin';
	public const LUM_BLOG_PLUGIN_ABOUT              = 'https://www.jcvignoli.com/blog/en/presentation-of-jean-claude-vignoli';
	public const LUM_WORDPRESS_URL                  = 'https://wordpress.org/plugins/lumiere-movies/';
	public const LUM_WORDPRESS_IMAGES_URL           = 'https://ps.w.org/lumiere-movies/assets';
	public const LUM_GIT_URL                        = 'https://github.com/jcvignoli/lumiere-movies';

	/**
	 * URL Strings for popups
	 * This helps build automatically the links to popups and checks if in the URL string a correct string was passed
	 *
	 * @see \Lumiere\Config\Get_Options::get_popup_url() Build a URL including those bits
	 * @see \Lumiere\Frontend\Popups\Popup_Select::build_class_name() Use to call the relevant popup class
	 *
	 * @var array<string, string> First column should never change, the second is the final URL string that will be used to build the links
	 */
	public const URL_BIT_POPUPS                     = [
		'film'                => 'film',
		'person'              => 'person',
		'movie_search'        => 'movie_search',
	];

	/**
	 * Name of the var to look for in URL
	 *
	 * @see \Lumiere\Alteration\Rewrite_Rules
	 * @see \Lumiere\Frontend\Popups\Popup_Select
	 */
	public const POPUP_STRING = 'popup';

	/**
	 * Rules to be added in add_rewrite_rule()
	 * @see \Lumiere\Alteration\Rewrite_Rules
	 */
	public const LUM_REWRITE_RULES = [
		// Popups.
		'lumiere/([^/]+)/?'                    => 'index.php?' . self::POPUP_STRING . '=$matches[1]',
		//'index.php/lumiere/([^/]+)/?$'         => 'index.php?' . self::POPUP_STRING . '=$matches[1]',
		// Popups with Polylang.
		'([a-zA-Z]{2}\|?+)/?lumiere/([^/]+)/?' => 'index.php?lang=$matches[1]&' . self::POPUP_STRING . '=$matches[2]',
	];

	/**
	 * URLs for pictures and menu images
	 */
	public const LUM_PICS_URL                       = LUMIERE_WP_URL . 'assets/pics/';
	public const LUM_PICS_SHOWTIMES_URL             = self::LUM_PICS_URL . '/showtimes/';

	/**
	 * URL and Path for javascripts
	 */
	public const LUM_JS_PATH                        = LUMIERE_WP_PATH . 'assets/js/';
	public const LUM_JS_URL                         = LUMIERE_WP_URL . 'assets/js/';

	/**
	 * URL and Path for stylesheets
	 */
	public const LUM_CSS_PATH                       = LUMIERE_WP_PATH . 'assets/css/';
	public const LUM_CSS_URL                        = LUMIERE_WP_URL . 'assets/css/';

	/**
	 * Internal URL pages constants
	 * Must be public, used everywhere
	 */
	public const FILE_COPY_THEME_TAXONOMY           = 'class/admin/taxo/class-copy-template-taxonomy.php';
	public const GUTENBERG_SEARCH_FILE              = 'class/admin/class-search.php';
	public const SEARCH_URL_BIT                     = 'lumiere/search/';
	public const SEARCH_URL_ADMIN                   = '/wp-admin/' . self::SEARCH_URL_BIT;
	public const POPUP_SEARCH_PATH                  = 'class/frontend/popups/class-popup-movie-search.php';
	public const POPUP_MOVIE_PATH                   = 'class/frontend/popups/class-popup-movie.php';
	public const POPUP_PERSON_PATH                  = 'class/frontend/popups/class-popup-person.php';
	public const TAXO_PEOPLE_THEME                  = 'class/theme/class-taxonomy-people-standard.php';
	public const TAXO_ITEMS_THEME                   = 'class/theme/class-taxonomy-items-standard.php';

	/**
	 * URL string for taxonomy
	 * Must be public, used in parent class
	 */
	public const URL_STRING_TAXO                    = 'lumiere-';

	/**
	 * Cache folder path.
	 * Must be public, used in parent class
	 */
	public const UPDATES_PATH                       = 'class/updates/';

	/**
	 * Cache folder path.
	 */
	public const LUMIERE_FOLDER_CACHE               = '/cache/lumiere/';

	/**
	 * Default options when creating DATA_OPTIONS
	 * DATA_OPTION_WITHNUMBER_DEFAULT must be in the same order as Settings::define_list_items_with_numbers(), otherwise number are not saved
	 * @see Settings::get_default_data_option()
	 */
	private const DATA_OPTION_TAXO_ACTIVE_DEFAULT   = [ 'director', 'genre' ];
	private const DATA_OPTION_WITHNUMBER_DEFAULT    = [
		'actor'       => '10',
		'alsoknow'    => '5',
		'connection'  => '3',
		'goof'        => '3',
		'plot'        => '3',
		'producer'    => '10',
		'quote'       => '3',
		'soundtrack'  => '10',
		'tagline'     => '1',
		'trailer'     => '5',
	];
	private const DATA_OPTION_WIDGET_ACTIVE_DEFAULT = [ 'title', 'pic', 'actor', 'connection', 'director', 'genre', 'goof', 'plot', 'tagline', 'writer' ];

	/**
	 * Create database options if they don't exist
	 *
	 * @see \Lumiere\Core::lumiere_on_activation() On first plugin activation, create the options
	 * @see \Lumiere\Save_Options On every reset, calling this method
	 * @see \Lumiere\Config\Open_Options::get_db_options() if options are not yet available, which may happend on first install (according to WP Plugin Check)
	 *
	 * @since 4.4 method created
	 */
	public static function create_database_options(): void {

		$that = new self();

		$lum_admin_option = get_option( self::LUMIERE_ADMIN_OPTIONS );
		if ( is_array( $lum_admin_option ) === false ) {
			update_option( self::LUMIERE_ADMIN_OPTIONS, $that->get_default_admin_option() );
		}

		$lum_data_option = get_option( self::LUMIERE_DATA_OPTIONS );
		if ( is_array( $lum_data_option ) === false  ) {
			update_option( self::LUMIERE_DATA_OPTIONS, $that->get_default_data_option() );
		}

		$lum_cache_option = get_option( self::LUMIERE_CACHE_OPTIONS );
		if ( is_array( $lum_cache_option ) === false  ) {
			update_option( self::LUMIERE_CACHE_OPTIONS, $that->get_default_cache_option() );
		}
	}

	/**
	 * Get ADMIN vars for javascript
	 * @see \Lumiere\Admin\Admin::lumiere_execute_admin_assets() Add this to wp_add_inline_script()
	 *
	 * @return string The full javascript piece to be included
	 */
	public static function get_scripts_admin_vars(): string {
		$imdb_admin_option = get_option( self::LUMIERE_ADMIN_OPTIONS );
		$scripts_admin_vars = wp_json_encode(
			[
				'imdb_path'                   => LUMIERE_WP_URL,
				'wordpress_path'              => site_url(),
				'wordpress_admin_path'        => admin_url(),
				'gutenberg_search_url_string' => self::SEARCH_URL_BIT,
				'gutenberg_search_url'        => self::SEARCH_URL_ADMIN,
				'ico80'                       => LUMIERE_WP_URL . 'assets/pics/lumiere-ico-noir80x80.png',
				'popupLarg'                   => $imdb_admin_option['imdbpopuplarg'],
				'popupLong'                   => $imdb_admin_option['imdbpopuplong'],
			]
		);
		return $scripts_admin_vars !== false ? 'const lumiere_admin_vars = ' . $scripts_admin_vars : '';
	}

	/**
	 * Get FRONTEND vars for javascript
	 * @see \Lumiere\Frontend\Frontend::frontpage_execute_assets() Add this to wp_add_inline_script()
	 *
	 * @return string The full javascript piece to be included
	 */
	public static function get_scripts_frontend_vars(): string {
		$imdb_admin_option = get_option( self::LUMIERE_ADMIN_OPTIONS );
		$scripts_vars = wp_json_encode(
			[
				'imdb_path'           => LUMIERE_WP_URL,
				'urlpopup_film'       => Get_Options::get_popup_url( 'film', site_url() ),
				'urlpopup_person'     => Get_Options::get_popup_url( 'person', site_url() ),
				'popup_border_colour' => $imdb_admin_option['imdbpopuptheme'],
				'popupLarg'           => $imdb_admin_option['imdbpopuplarg'],
				'popupLong'           => $imdb_admin_option['imdbpopuplong'],
			]
		);
		return $scripts_vars !== false ? 'const lumiere_vars = ' . $scripts_vars : '';
	}

	/**
	 * Define the type of people items that are used for taxonomy
	 * All items in type people are actually taxonomy
	 * @see Settings::get_default_data_option() use this list to create the options
	 *
	 * @return array<string, string>
	 * @phpstan-return array{ 'actor': string, 'composer': string, 'creator':string, 'director':string, 'producer':string, 'writer':string }
	 */
	protected static function define_list_taxo_people(): array {
		return [
			'director' => __( 'director', 'lumiere-movies' ),
			'actor'    => __( 'actor', 'lumiere-movies' ),
			'creator'  => __( 'creator', 'lumiere-movies' ),
			'composer' => __( 'composer', 'lumiere-movies' ),
			'writer'   => __( 'writer', 'lumiere-movies' ),
			'producer' => __( 'producer', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type items that are used for taxonomy
	 * Complements define_list_non_taxo_items() which are for non-taxo items
	 * @see Settings::get_default_data_option() use this list to create the options
	 *
	 * @return array<string, string>
	 * @phpstan-return array{ 'color': string, 'country': string, 'genre':string, 'keyword':string, 'language':string }
	 */
	protected static function define_list_taxo_items(): array {
		return [
			'country'  => __( 'country', 'lumiere-movies' ),
			'language' => __( 'language', 'lumiere-movies' ),
			'genre'    => __( 'genre', 'lumiere-movies' ),
			'keyword'  => __( 'keyword', 'lumiere-movies' ),
			'color'    => __( 'color', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type items that are NOT used for taxonomy
	 * Complements define_list_taxo_items() which are for taxo items
	 * The order will define the "Display order" in admin data options (except if translated, the order will be in local lang)
	 *
	 * @return array<string, string>
	 * @phpstan-return array{ 'officialsites':string,'prodcompany':string, 'rating':string,'runtime':string, 'source':string, 'year':string, 'title': string, 'pic':string, 'alsoknow': string, 'connection':string, 'goof': string, 'plot':string, 'quote':string, 'soundtrack':string, 'tagline':string, 'trailer':string }
	 */
	protected static function define_list_non_taxo_items(): array {
		return [
			'title'         => __( 'title', 'lumiere-movies' ),
			'pic'           => __( 'pic', 'lumiere-movies' ),
			'runtime'       => __( 'runtime', 'lumiere-movies' ),
			'alsoknow'      => __( 'also known as', 'lumiere-movies' ),
			'rating'        => __( 'rating', 'lumiere-movies' ),
			'prodcompany'   => __( 'production company', 'lumiere-movies' ),
			'connection'    => __( 'connected movies', 'lumiere-movies' ),          /* @since 4.4 added */
			'goof'          => __( 'goof', 'lumiere-movies' ),
			'quote'         => __( 'quote', 'lumiere-movies' ),                     /* @since 4.4 back in use */
			'tagline'       => __( 'tagline', 'lumiere-movies' ),
			'plot'          => __( 'plot', 'lumiere-movies' ),
			'trailer'       => __( 'trailer', 'lumiere-movies' ),
			'soundtrack'    => __( 'soundtrack', 'lumiere-movies' ),
			'officialsites' => __( 'official websites', 'lumiere-movies' ),
			'source'        => __( 'source', 'lumiere-movies' ),
			'year'          => __( 'year of release', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type items that can get an extra number in admin data options
	 * Must also be defined in define_list_non_taxo_items() or define_list_taxo_items()
	 *
	 * @return array<string, string>
	 * @phpstan-return array{ 'actor': string, 'alsoknow': string, 'connection':string, 'goof':string, 'plot':string, 'producer':string, 'quote':string, 'soundtrack':string, 'tagline':string, 'trailer':string }
	 */
	protected static function define_list_items_with_numbers(): array {
		return [
			'actor'      => __( 'actor', 'lumiere-movies' ),
			'alsoknow'   => __( 'also known as', 'lumiere-movies' ),
			'connection' => __( 'connected movies', 'lumiere-movies' ),             /* @since 4.4 added */
			'goof'       => __( 'goof', 'lumiere-movies' ),
			'plot'       => __( 'plot', 'lumiere-movies' ),
			'producer'   => __( 'producer', 'lumiere-movies' ),
			'quote'      => __( 'quote', 'lumiere-movies' ),
			'soundtrack' => __( 'soundtrack', 'lumiere-movies' ),
			'tagline'    => __( 'tagline', 'lumiere-movies' ),
			'trailer'    => __( 'trailer', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type items to show in connected/related movies
	 * @see Get_Options::get_list_connect_cat() Call this class
	 *
	 * @since 4.4 method added
	 * @return array<string, string>
	 */
	public static function define_list_connect_cat(): array {
		return [
			'featured'   => __( 'Featured in', 'lumiere-movies' ),
			'follows'    => __( 'Follows', 'lumiere-movies' ),
			'followedBy' => __( 'Followed by', 'lumiere-movies' ),
			'remakeOf'   => __( 'Remake of', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type items to show in goofs
	 * @see Get_Options::get_list_goofs_cat() Call this class
	 *
	 * @since 4.4 method added
	 * @return array<string, string>
	 */
	public static function define_list_goofs_cat(): array {
		return [
			'continuity'                  => __( 'continuity', 'lumiere-movies' ),
			'factualError'                => __( 'factual error', 'lumiere-movies' ),
			//'notAGoof'                    => __( 'not a goof', 'lumiere-movies' ),
			'revealingMistake'            => __( 'revealing mistake', 'lumiere-movies' ),
			//'miscellaneous'               => __( 'miscellaneous', 'lumiere-movies' ),
			'anachronism'                 => __( 'anachronism', 'lumiere-movies' ),
			//'audioVisualUnsynchronized'   => __( 'audio visual unsynchronized', 'lumiere-movies' ),
			//'crewOrEquipmentVisible'      => __( 'stuff visible', 'lumiere-movies' ),
			'errorInGeography'            => __( 'error in geography', 'lumiere-movies' ),
			'plotHole'                    => __( 'plot hole', 'lumiere-movies' ),
			//'boomMicVisible'              => __( 'boom mic visible', 'lumiere-movies' ),
			//'characterError'              => __( 'character error', 'lumiere-movies' ),
		];
	}

	/**
	 * Return default ADMIN options
	 *
	 * @phpstan-return OPTIONS_ADMIN
	 * @return array<string, string|array<string, string>>
	 */
	private function get_default_admin_option(): array {

		/**
		 * Build debug path: 1/ Use it as it is if it starts with '/', it's absolute, 2/ Add ABSPATH if it doesn't start with '/'
		 */
		$debug_path = null;
		/**
		 * @psalm-suppress InvalidArgument (Psalm can't understand that WP_DEBUG_LOG is a const that can be string and bool)
		 * @phpstan-ignore-next-line -- PHPStan can't understand that WP_DEBUG_LOG is a const that can be string and bool
		 */
		if ( defined( 'WP_DEBUG_LOG' ) && is_string( WP_DEBUG_LOG ) && str_starts_with( WP_DEBUG_LOG, '/' ) ) {
			$debug_path = WP_DEBUG_LOG;
			/** @phpstan-ignore-next-line -- PHPStan can't understand that WP_DEBUG_LOG is a const that can be string and bool */
		} elseif ( ! isset( $debug_path ) && defined( 'WP_DEBUG_LOG' ) && is_string( WP_DEBUG_LOG ) ) {
			$debug_path = ABSPATH . WP_DEBUG_LOG;
		}

		$imdb_admin_options = [
			//--------------------------------------------------=[ Basic ]=--
			'imdbplugindirectory_partial' => '/wp-content/plugins/lumiere-movies/',
			'imdbpluginpath'              => LUMIERE_WP_PATH,
			'imdburlpopups'               => '/lumiere/',
			'imdbkeepsettings'            => '1',
			'imdburlstringtaxo'           => self::URL_STRING_TAXO,
			'imdbcoversize'               => '1',
			'imdbcoversizewidth'          => '100',

			//--------------------------------------------------=[ Technical ]=--
			'imdbmaxresults'              => '10',
			'imdbdelayimdbrequest'        => '0',
			'imdbpopuptheme'              => 'white',
			'imdbpopuplarg'               => '800',
			'imdbpopuplong'               => '500',
			'imdbintotheposttheme'        => 'grey',
			'imdblinkingkill'             => '0',
			'imdbautopostwidget'          => '0',
			'imdblanguage'                => 'US',
			'imdbdebug'                   => '0',                        /* Debug */
			'imdbdebuglog'                => '0',                        /* Log debug */
			/** @phpstan-ignore nullCoalesce.variable (PHPStan can't understand that WP_DEBUG_LOG is a const that can be string and bool) */
			'imdbdebuglogpath'            => $debug_path ?? WP_CONTENT_DIR . '/debug.log',
			'imdbdebuglevel'              => 'DEBUG',                    /* Debug levels: emergency, alert, critical,
											error, warning, notice, info, debug */
			'imdbdebugscreen'             => '1',                        /* Show debug on screen */
			'imdbwordpress_bigmenu'       => '0',                        /* Left menu */
			'imdbwordpress_tooladminmenu' => '1',                        /* Top menu */
			'imdbpopup_modal_window'      => 'bootstrap',
			'imdbtaxonomy'                => '1',
			'imdbHowManyUpdates'          => parent::get_nb_updates(),    /* define the number of updates. */
			'imdbseriemovies'             => 'movies+series',             /* options: movies, series, movies+series, videogames */
			'imdbirpdisplay'              => '0',                         /* intelly related post plugin, overrides normal Lumiere behaviour */
		];

		// Needs an option from above.
		$imdb_admin_options['imdbplugindirectory'] = get_site_url() . $imdb_admin_options['imdbplugindirectory_partial'];

		// For debugging purpose.
		// Update imdbHowManyUpdates option.
		/*
		$option_array_search = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		$option_array_search['imdbHowManyUpdates'] = 18; // Chosen number of updates.
		update_option( Settings::LUMIERE_ADMIN_OPTIONS, $option_array_search );
		*/

		return $imdb_admin_options;
	}

	/**
	 * Return default CACHE options
	 *
	 * @phpstan-return OPTIONS_CACHE
	 * @return array<string, string|array<string, string>>
	 */
	private function get_default_cache_option(): array {
		return [
			'imdbcachedir_partial'             => self::LUMIERE_FOLDER_CACHE,
			'imdbcachedir'                     => WP_CONTENT_DIR . self::LUMIERE_FOLDER_CACHE,
			'imdbphotoroot'                    => WP_CONTENT_DIR . self::LUMIERE_FOLDER_CACHE . 'images/',
			'imdbphotodir'                     => content_url() . self::LUMIERE_FOLDER_CACHE . 'images/',
			'imdbusecache'                     => '1',
			'imdbcacheexpire'                  => '2592000',                     /* one month */
			'imdbcacheautorefreshcron'         => '0',                           /* Cron refresh cache automatically */
			'imdbcachekeepsizeunder'           => '0',                           /* Cron remove all data above X MB */
			'imdbcachekeepsizeunder_sizelimit' => '100',                         /* 100 MB */
			'imdbcachedetailshidden'           => '0',                           /* Do not display cache */
			'imdbcachedetailsshort'            => '0',                           /* Keep the cache limited to movie's name (no pics) */
		];
	}

	/**
	 * Return default DATA options
	 *
	 * @see Settings_Build::get_data_rows_taxo() Import automatically taxonomy built vars
	 * @see Settings_Build::get_data_rows_withnumbers() Import automatically with numbers built vars
	 * @see Settings_Build::get_data_rows_widget() Import automatically 'imdbwidget...' built vars
	 * @see Settings_Build::get_data_rows_imdbwidgetorder() Import automatically array 'imdbwidgetorder' built vars
	 *
	 * @phpstan-return OPTIONS_DATA
	 * @return array<string, string|array<string, string>>
	 */
	private function get_default_data_option(): array {
		return array_merge(
			parent::get_data_rows_widget( self::DATA_OPTION_WIDGET_ACTIVE_DEFAULT    /* Activated rows by default */ ),
			parent::get_data_rows_imdbwidgetorder(),
			parent::get_data_rows_taxo( self::DATA_OPTION_TAXO_ACTIVE_DEFAULT        /* Activated rows by default */ ),
			parent::get_data_rows_withnumbers( self::DATA_OPTION_WITHNUMBER_DEFAULT  /* Rows that must have a specific number */ ),
		);
	}
}

