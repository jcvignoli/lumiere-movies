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
use Lumiere\Config\Settings_Helper;

// Needed vars for uninstall, fails otherwise.
// Use of defined() condition for PHPStan
if ( ! defined( 'LUM_WP_PATH' ) ) {
	require_once plugin_dir_path( dirname( __DIR__ ) ) . 'vars.php';
}

/**
 * Settings class
 * Method create_database_options() to set the options in WP config database
 * Is extended by Get_Options, extends Settings_Build
 * If a new IMDB field is created it will automatically create new fields, be it in database and in the admin panel options
 * IMDB fields are automatically translated if plural
 *
 * @since 4.0 Moved cache folder creation to class cache tools
 * @since 4.1 Renamed *imdb_widget_* to *imdb_data_* all over the website
 * @since 4.4 Options are created only when installing/activating the plugin, widely rewritten and simplified. OPTIONS_DATA is dynamically created according to the arrays of items/people added. Using {@see Get_Options} class as child class for all external calls. {@see Settings_Build} is the class that includes helper methods.
 *
 * @phpstan-type OPTIONS_ADMIN array{imdbHowManyUpdates: string, imdbautopostwidget: '0'|'1'|string, imdbcoversize: '0'|'1'|string, imdbcoversizewidth: string, imdbdebug: '0'|'1'|string, imdbdebuglevel: 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY', imdbdebuglog: '0'|'1'|string, imdbdebuglogpath: mixed, imdbdebugscreen:'0'|'1'|string, imdbdelayimdbrequest: '0'|'1'|string, imdbintotheposttheme: string, imdbirpdisplay: '0'|'1'|string, imdbkeepsettings: '0'|'1'|string, imdblanguage: string, imdblinkingkill: '0'|'1'|string, imdbmaxresults: string, imdbplugindirectory: string, imdbplugindirectory_partial: string, imdbpluginpath: mixed, imdbpopup_modal_window: string, imdbpopuplarg: string, imdbpopuplong: string, imdbpopuptheme: string, imdbseriemovies: 'movies'|'series'|'movies+series'|'videogames', imdbtaxonomy: '0'|'1'|string, imdburlpopups: string, imdburlstringtaxo: string, imdbwordpress_bigmenu: '0'|'1'|string, imdbwordpress_tooladminmenu: '0'|'1'|string}
 *
 * @phpstan-type OPTIONS_CACHE array{ 'imdbcacheautorefreshcron': string, 'imdbcachedetailsshort': string, 'imdbcachedir': string, 'imdbcachedir_partial': string, 'imdbcacheexpire': string, 'imdbcachekeepsizeunder': string, 'imdbcachekeepsizeunder_sizelimit': string, 'imdbphotodir': string, 'imdbphotoroot': string, 'imdbusecache': string, 'imdbcachedetailshidden': string}
 *
 * @phpstan-type ORDER_OPTIONS_DATA array{title?: string, pic?: string, runtime?: string, director?: string, connection?: string, country?: string, actor?: string, cinematographer?: string, rating?: string, language?: string, genre?: string, writer?: string, producer?: string, keyword?: string, prodCompany?: string, plot?: string, goof?: string, quote?: string, tagline?: string, trailer?: string, color?: string, alsoknow?: string, composer?: string, soundtrack?: string, extSites?: string, source?: string, trivia?: string, year?: string}
 * @phpstan-type OPTIONS_DATA array{imdbtaxonomyactor?: '0'|'1', imdbtaxonomycinematographer?: '0'|'1', imdbtaxonomycolor?: '0'|'1', imdbtaxonomycomposer?: '0'|'1', imdbtaxonomycountry?: '0'|'1', imdbtaxonomydirector?: '0'|'1', imdbtaxonomygenre?: '0'|'1', imdbtaxonomykeyword?: '0'|'1', imdbtaxonomylanguage?: '0'|'1', imdbtaxonomyproducer?: '0'|'1', imdbtaxonomywriter?: '0'|'1', imdbwidgetactor?: '0'|'1', imdbwidgetactornumber?: string, imdbwidgetalsoknow?: '0'|'1', imdbwidgetalsoknownumber?: string, imdbwidgetcinematographer?: '0'|'1', imdbwidgetcolor?: '0'|'1', imdbwidgetcomposer?: '0'|'1', imdbwidgetconnection?: '0'|'1', imdbwidgetconnectionnumber?: string, imdbwidgetcountry?: '0'|'1', imdbwidgetdirector?: '0'|'1', imdbwidgetextSites?: '0'|'1', imdbwidgetgenre?: '0'|'1', imdbwidgetgoof?: '0'|'1', imdbwidgetgoofnumber?: string, imdbwidgetkeyword?: '0'|'1', imdbwidgetlanguage?: '0'|'1', imdbwidgetpic?: '0'|'1', imdbwidgetplot?: '0'|'1', imdbwidgetplotnumber?: string, imdbwidgetprodCompany?: '0'|'1', imdbwidgetproducer?: '0'|'1', imdbwidgetproducernumber?: string, imdbwidgetquote?: '0'|'1', imdbwidgetquotenumber?: string, imdbwidgetrating?: '0'|'1', imdbwidgetruntime?: '0'|'1', imdbwidgetsoundtrack?: '0'|'1', imdbwidgetsoundtracknumber?: string, imdbwidgetsource?: '0'|'1', imdbwidgettagline?: '0'|'1', imdbwidgettaglinenumber?: string, imdbwidgettitle?: '0'|'1', imdbwidgettrailer?: '0'|'1', imdbwidgettrailernumber?: string, imdbwidgettrivia?: '0'|'1', imdbwidgettrivianumber?: string, imdbwidgetwriter?: '0'|'1', imdbwidgetwriternumber?: string, imdbwidgetyear?: '0'|'1', 'imdbwidgetorder': ORDER_OPTIONS_DATA }
  */
class Settings extends Settings_Helper {

	/**
	 * Name of the databases as stored in WordPress db
	 * Only used in child class, has to be called in Get_Options
	 */
	protected const LUM_ADMIN_OPTIONS               = 'lumiere_admin_options';
	protected const LUM_DATA_OPTIONS                = 'lumiere_data_options';
	protected const LUM_CACHE_OPTIONS               = 'lumiere_cache_options';

	/**
	 * Lumière related website URLs
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
	 * @see \Lumiere\Frontend\Main::is_popup_page detect if popup
	 *
	 * @var array<string, string> First column should never change, the second is the final URL string that will be used to build the links
	 */
	public const LUM_URL_BIT_POPUPS                 = [
		'film'                => 'film',
		'person'              => 'person',
		'movie_search'        => 'movie_search',
	];

	/**
	 * Internal search categories for IMDb search
	 *
	 * @see \Lumiere\Config\Get_Options::get_type_search() Build a URL including those bits
	 *
	 * @var array<string, string> First column is Lumière category, second column IMDBPHP's
	 */
	protected const LUM_IMDB_SEARCH_CATEGORY        = [
		'movies'        => 'MOVIE',
		'movies+series' => 'MOVIE,TV',
		'series'        => 'TV',
		'videogames'    => 'VIDEO_GAME',
		'podcasts'      => 'PODCAST_EPISODE',
		'default'       => 'MOVIE,TV',
	];

	/**
	 * Name of the var to look for in URL
	 *
	 * @see \Lumiere\Alteration\Rewrite_Rules
	 * @see \Lumiere\Frontend\Popups\Popup_Select
	 */
	public const LUM_POPUP_STRING                   = 'popup';

	/**
	 * Rules to be added in add_rewrite_rule()
	 * @see \Lumiere\Alteration\Rewrite_Rules
	 */
	public const LUM_REWRITE_RULES                  = [
		// Popups.
		'lumiere/([^/]+)/?'                    => 'index.php?' . self::LUM_POPUP_STRING . '=$matches[1]',
		//'index.php/lumiere/([^/]+)/?$'       => 'index.php?' . self::LUM_POPUP_STRING . '=$matches[1]', // Nobody keeps index.php, right?
		// Popups with Polylang.
		'([a-zA-Z]{2}\|?+)/?lumiere/([^/]+)/?' => 'index.php?lang=$matches[1]&' . self::LUM_POPUP_STRING . '=$matches[2]',
	];

	/**
	 * URLs for pictures and menu images
	 */
	public const LUM_PICS_URL                       = LUM_WP_URL . 'assets/pics/';
	public const LUM_PICS_SHOWTIMES_URL             = self::LUM_PICS_URL . '/showtimes/';

	/**
	 * URL and Path for javascripts and stylesheets
	 */
	public const LUM_JS_PATH                        = LUM_WP_PATH . 'assets/js/';
	public const LUM_JS_URL                         = LUM_WP_URL . 'assets/js/';
	public const LUM_CSS_PATH                       = LUM_WP_PATH . 'assets/css/';
	public const LUM_CSS_URL                        = LUM_WP_URL . 'assets/css/';

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
	 * Word starting the file name of taxonomy theme
	 * @see \Lumiere\Uninstall
	 * @see \Lumiere\Admin\Copy_Templates\Copy_Theme
	 * @see \Lumiere\Admin\Copy_Templates\Detect_New_Theme
	 * @see \Lumiere\Admin\Submenu\Data
	 */
	public const LUM_THEME_TAXO_FILENAME_START      = 'taxonomy-';

	/**
	 * Cache folder path.
	 * Must be public, used in parent class
	 */
	public const LUM_UPDATES_PATH                   = 'class/updates/';

	/**
	 * Cache folder path.
	 */
	public const LUM_FOLDER_CACHE                   = '/cache/lumiere/';

	/**
	 * Partial namespace of modules
	 * Used to build the full film namespace
	 * @see \Lumiere\Frontend\Popup\Popup_Film
	 */
	public const LUM_FILM_MODULE_CLASS = '\Lumiere\Frontend\Module\Movie_';

	/**
	 * Default imdb fields when creating DATA_OPTIONS
	 * @see Settings::get_default_data_option()
	 * @see parent::define_list_items_with_numbers() to build list with DATA_DEFAULT_WITHNUMBER
	 */
	private const DATA_DEFAULT_TAXO_ACTIVE          = [ 'director', 'genre' ];
	public const DATA_DEFAULT_WITHNUMBER            = [ // Public visibility as class {@see Settings_Helper::get_data_rows_withnumbers()} needs it
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
		'trivia'      => '3',
		'writer'      => '10',
	];
	private const DATA_DEFAULT_WIDGET_ACTIVE        = [ 'title', 'pic', 'actor', 'connection', 'director', 'genre', 'goof', 'plot', 'tagline', 'writer' ];

	/**
	 * Create database options if they don't exist
	 *
	 * @see \Lumiere\Core::lumiere_on_activation() On first plugin activation, create the options
	 * @see \Lumiere\Save_Options On every reset, calling this method
	 * @see \Lumiere\Config\Open_Options::get_db_options() if options are not yet available, which may happend on first install (according to WP Plugin Check)
	 *
	 * @since 4.4 method updated, simplifing the process
	 */
	public static function create_database_options(): void {

		$that = new self();

		$lum_admin_option = get_option( self::LUM_ADMIN_OPTIONS );
		if ( is_array( $lum_admin_option ) === false ) {
			update_option( self::LUM_ADMIN_OPTIONS, $that->get_default_admin_option() );
		}

		$lum_data_option = get_option( self::LUM_DATA_OPTIONS );
		if ( is_array( $lum_data_option ) === false  ) {
			update_option( self::LUM_DATA_OPTIONS, $that->get_default_data_option() );
		}

		$lum_cache_option = get_option( self::LUM_CACHE_OPTIONS );
		if ( is_array( $lum_cache_option ) === false  ) {
			update_option( self::LUM_CACHE_OPTIONS, $that->get_default_cache_option() );
		}
	}

	/**
	 * Get ADMIN vars for javascript
	 * @see \Lumiere\Admin\Admin::lumiere_execute_admin_assets() Add this to wp_add_inline_script()
	 *
	 * @return string The full javascript piece to be included
	 */
	public static function get_scripts_admin_vars(): string {
		$imdb_admin_option = get_option( self::LUM_ADMIN_OPTIONS );
		$scripts_admin_vars = wp_json_encode(
			[
				'imdb_path'                   => LUM_WP_URL,
				'wordpress_path'              => site_url(),
				'wordpress_admin_path'        => admin_url(),
				'gutenberg_search_url_string' => self::SEARCH_URL_BIT,
				'gutenberg_search_url'        => self::SEARCH_URL_ADMIN,
				'ico80'                       => LUM_WP_URL . 'assets/pics/lumiere-ico-noir80x80.png',
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
		$imdb_admin_option = get_option( self::LUM_ADMIN_OPTIONS );
		$scripts_vars = wp_json_encode(
			[
				'imdb_path'           => LUM_WP_URL,
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
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 * @phpstan-return array{ 'actor': string, 'composer': string, 'cinematographer':string, 'director':string, 'producer':string, 'writer':string }
	 */
	protected static function define_list_taxo_people( int $number = 1 ): array {
		return [
			'director'         => _n( 'director', 'directors', $number, 'lumiere-movies' ),
			'actor'            => _n( 'actor', 'actors', $number, 'lumiere-movies' ),
			'cinematographer'  => _n( 'cinematographer', 'cinematographers', $number, 'lumiere-movies' ),
			'composer'         => _n( 'composer', 'composers', $number, 'lumiere-movies' ),
			'writer'           => _n( 'writer', 'writers', $number, 'lumiere-movies' ),
			'producer'         => _n( 'producer', 'producers', $number, 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type items that are used for taxonomy
	 * Complements define_list_non_taxo_items() which are for non-taxo items
	 * @see Settings::get_default_data_option() use this list to create the options
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 * @phpstan-return array{ 'color': string, 'country': string, 'genre':string, 'keyword':string, 'language':string }
	 */
	protected static function define_list_taxo_items( int $number = 1 ): array {
		return [
			'country'  => _n( 'country', 'countries', $number, 'lumiere-movies' ),
			'language' => _n( 'language', 'language', $number, 'lumiere-movies' ),
			'genre'    => _n( 'genre', 'genres', $number, 'lumiere-movies' ),
			'keyword'  => _n( 'keyword', 'keywords', $number, 'lumiere-movies' ),
			'color'    => _n( 'color', 'colors', $number, 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type methods available for persons
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 * @phpstan-return array{award: string, bio: string, birthname: string, born: string, children: string, credit: string, died: string, name: string, news: string, nickname: string, pubinterview: string, pubmovies: string, pubportrayal: string, pubprints: string, quotes: string, spouse: string, trademark: string, trivia: string }
	 */
	protected static function define_list_person_methods( int $number = 1 ): array {
		return [
			'award'        => _n( 'award', 'awards', $number, 'lumiere-movies' ),
			'bio'          => _n( 'biographical movie', 'biographical movies', $number, 'lumiere-movies' ),
			'birthname'    => __( 'birthname', 'lumiere-movies' ),
			'born'         => __( 'born', 'lumiere-movies' ),
			'children'     => _n( 'child', 'children', $number, 'lumiere-movies' ),
			'credit'       => _n( 'credit', 'credits', $number, 'lumiere-movies' ),
			'died'         => __( 'died', 'lumiere-movies' ),
			'name'         => __( 'name', 'lumiere-movies' ),
			'news'         => __( 'news', 'lumiere-movies' ),
			'nickname'     => _n( 'nickname', 'nicknames', $number, 'lumiere-movies' ),
			'pubinterview' => _n( 'interview', 'interviews', $number, 'lumiere-movies' ),
			'pubmovies'    => _n( 'public movie', 'public movies', $number, 'lumiere-movies' ),
			'pubprints'    => _n( 'printed ad', 'printed ads', $number, 'lumiere-movies' ),
			'pubportrayal' => __( 'Portrayed in', 'lumiere-movies' ),
			'quotes'       => _n( 'quote', 'quotes', $number, 'lumiere-movies' ),
			'spouse'       => _n( 'spouse', 'spouses', $number, 'lumiere-movies' ),
			'trivia'       => _n( 'trivia', 'trivias', $number, 'lumiere-movies' ),
			'trademark'    => _n( 'trademark', 'trademarks', $number, 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type items that are NOT used for taxonomy
	 * Complements define_list_taxo_items() which are for taxo items
	 * The order will define the "Display order" in admin data options (except if translated, the order will be in local lang)
	 *
	 * @param int $number Optional: a number to turn into plural if needed
	 * @return array<string, string>
	 * @phpstan-return array{ 'extSites':string,'prodCompany':string, 'rating':string,'runtime':string, 'source':string, 'year':string, 'title': string, 'pic':string, 'alsoknow': string, 'connection':string, 'goof': string, 'plot':string, 'quote':string, 'soundtrack':string, 'tagline':string, 'trailer':string, 'trivia': string }
	 */
	protected static function define_list_non_taxo_items( int $number = 1 ): array {
		return [
			'title'         => _n( 'title', 'titles', $number, 'lumiere-movies' ),
			'pic'           => _n( 'pic', 'pics', $number, 'lumiere-movies' ),
			'runtime'       => __( 'runtime', 'lumiere-movies' ),                                           /* always singular */
			'alsoknow'      => __( 'also known as', 'lumiere-movies' ),                                     /* always singular */
			'rating'        => _n( 'rating', 'ratings', $number, 'lumiere-movies' ),
			'prodCompany'   => _n( 'production company', 'production companies', $number, 'lumiere-movies' ),
			'connection'    => _n( 'related movie', 'related movies', $number, 'lumiere-movies' ),          /* @since 4.4 added */
			'goof'          => _n( 'goof', 'goofs', $number, 'lumiere-movies' ),
			'quote'         => _n( 'quote', 'quotes', $number, 'lumiere-movies' ),                          /* @since 4.4 back in use */
			'tagline'       => _n( 'tagline', 'taglines', $number, 'lumiere-movies' ),
			'plot'          => _n( 'plot', 'plots', $number, 'lumiere-movies' ),
			'trailer'       => _n( 'trailer', 'trailers', $number, 'lumiere-movies' ),
			'soundtrack'    => _n( 'soundtrack', 'soundtracks', $number, 'lumiere-movies' ),
			'extSites'      => _n( 'official website', 'official websites', $number, 'lumiere-movies' ),
			'source'        => _n( 'source', 'sources', $number, 'lumiere-movies' ),
			'year'          => __( 'year of release', 'lumiere-movies' ),                                   /* always singular */
			'trivia'        => _n( 'trivia', 'trivias', $number, 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type of items to show in connected/related movies (used when display it in movies)
	 * @see Get_Options::get_list_connect_cat() Call this class
	 *
	 * @since 4.4 method added
	 *
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
	 * Define the type of items to show in goofs (used when display it in movies)
	 * Some are deactivated, not returned in movies
	 * @see Get_Options::get_list_goof_cat() Call this class
	 * @see \IMDb\Title::goof() List of trivia categories
	 *
	 * @since 4.4 method added
	 *
	 * @return array<string, string>
	 */
	public static function define_list_goof_cat(): array {
		return [
			'continuity'                  => __( 'continuity', 'lumiere-movies' ),
			'factualError'                => __( 'factual error', 'lumiere-movies' ),
			//'notAGoof'                  => __( 'not a goof', 'lumiere-movies' ),
			'revealingMistake'            => __( 'revealing mistake', 'lumiere-movies' ),
			//'miscellaneous'             => __( 'miscellaneous', 'lumiere-movies' ),
			'anachronism'                 => __( 'anachronism', 'lumiere-movies' ),
			//'audioVisualUnsynchronized' => __( 'audio visual unsynchronized', 'lumiere-movies' ),
			//'crewOrEquipmentVisible'    => __( 'stuff visible', 'lumiere-movies' ),
			'errorInGeography'            => __( 'error in geography', 'lumiere-movies' ),
			'plotHole'                    => __( 'plot hole', 'lumiere-movies' ),
			//'boomMicVisible'            => __( 'boom mic visible', 'lumiere-movies' ),
			//'characterError'            => __( 'character error', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type of items to show in trivias (used when display it in movies)
	 * Some are deactivated, not returned in movies
	 * @see Get_Options::get_list_trivia_cat() Call this class
	 * @see \IMDb\Title::trivia() List of trivia categories
	 *
	 * @since 4.4.3 method added
	 *
	 * @return array<string, string>
	 */
	public static function define_list_trivia_cat(): array {
		return [
			'uncategorized'        => __( 'uncategorized', 'lumiere-movies' ),
			'cameo'                => __( 'cameo', 'lumiere-movies' ),
			'directorTrademark'    => __( 'director trademark', 'lumiere-movies' ),
			//'directorCameo'        => __( 'director cameo', 'lumiere-movies' ),
			//'smithee'              => __( 'smithee', 'lumiere-movies' ),
			'actorTrademark'       => __( 'actor trademark', 'lumiere-movies' ),
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
			'imdbpluginpath'              => LUM_WP_PATH,
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
		$option_array_search = get_option( Settings::LUM_ADMIN_OPTIONS );
		$option_array_search['imdbHowManyUpdates'] = 18; // Chosen number of updates.
		update_option( Settings::LUM_ADMIN_OPTIONS, $option_array_search );
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
			'imdbcachedir_partial'             => self::LUM_FOLDER_CACHE,
			'imdbcachedir'                     => WP_CONTENT_DIR . self::LUM_FOLDER_CACHE,
			'imdbphotoroot'                    => WP_CONTENT_DIR . self::LUM_FOLDER_CACHE . 'images/',
			'imdbphotodir'                     => content_url() . self::LUM_FOLDER_CACHE . 'images/',
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
	 * @since 4.4 Totally rewritten and automatized
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
			parent::get_data_rows_widget( self::DATA_DEFAULT_WIDGET_ACTIVE    /* Activated rows by default */ ),
			parent::get_data_rows_imdbwidgetorder(),
			parent::get_data_rows_taxo( self::DATA_DEFAULT_TAXO_ACTIVE        /* Activated rows by default */ ),
			parent::get_data_rows_withnumbers( self::DATA_DEFAULT_WITHNUMBER  /* Rows that must have a specific number */ ),
		);
	}
}

