<?php declare( strict_types = 1 );
/**
 * Movie Settings class
 *
 * @copyright     (c) 2022, Lost Highway
 *
 * @version       3.0
 * @package       lumieremovies
 */
namespace Lumiere\Config;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { // Don't check for Settings class since it's Settings class.
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Settings_Helper;

// Needed vars for uninstall, fails otherwise.
// Use of defined() condition for PHPStan
if ( ! defined( 'LUM_WP_PATH' ) ) {
	require_once plugin_dir_path( dirname( __DIR__ ) ) . 'vars.php';
}

/**
 * Movie settings
 * Method create_database_options() to set the options in WP config database
 * Is extended by Get_Options, extends Settings_Build
 * If a new IMDB field is created it will automatically create new fields, be it in database and in the admin panel options
 * IMDB fields are automatically translated if plural
 *
 * @since 4.0 Moved cache folder creation to class cache tools
 * @since 4.1 Renamed *imdb_widget_* to *imdb_data_* all over the website
 * @since 4.4 Options are created only when installing/activating the plugin, widely rewritten and simplified. OPTIONS_DATA is dynamically created according to the arrays of items/people added. Using {@see Get_Options} class as child class for all external calls. {@see Settings_Build} is the class that includes helper methods.
 *
 * @phpstan-type ORDER_OPTIONS_DATA array{title?: string, pic?: string, runtime?: string, director?: string, connection?: string, country?: string, actor?: string, cinematographer?: string, rating?: string, language?: string, genre?: string, writer?: string, producer?: string, keyword?: string, prodCompany?: string, plot?: string, goof?: string, quote?: string, tagline?: string, trailer?: string, color?: string, alsoknow?: string, composer?: string, soundtrack?: string, extSites?: string, source?: string, trivia?: string, year?: string}
 * @phpstan-type OPTIONS_DATA array{imdbtaxonomyactor?: '0'|'1', imdbtaxonomycinematographer?: '0'|'1', imdbtaxonomycolor?: '0'|'1', imdbtaxonomycomposer?: '0'|'1', imdbtaxonomycountry?: '0'|'1', imdbtaxonomydirector?: '0'|'1', imdbtaxonomygenre?: '0'|'1', imdbtaxonomykeyword?: '0'|'1', imdbtaxonomylanguage?: '0'|'1', imdbtaxonomyproducer?: '0'|'1', imdbtaxonomywriter?: '0'|'1', imdbwidgetactor?: '0'|'1', imdbwidgetactornumber?: string, imdbwidgetalsoknow?: '0'|'1', imdbwidgetalsoknownumber?: string, imdbwidgetcinematographer?: '0'|'1', imdbwidgetcolor?: '0'|'1', imdbwidgetcomposer?: '0'|'1', imdbwidgetconnection?: '0'|'1', imdbwidgetconnectionnumber?: string, imdbwidgetcountry?: '0'|'1', imdbwidgetdirector?: '0'|'1', imdbwidgetextSites?: '0'|'1', imdbwidgetgenre?: '0'|'1', imdbwidgetgoof?: '0'|'1', imdbwidgetgoofnumber?: string, imdbwidgetkeyword?: '0'|'1', imdbwidgetlanguage?: '0'|'1', imdbwidgetpic?: '0'|'1', imdbwidgetplot?: '0'|'1', imdbwidgetplotnumber?: string, imdbwidgetprodCompany?: '0'|'1', imdbwidgetproducer?: '0'|'1', imdbwidgetproducernumber?: string, imdbwidgetquote?: '0'|'1', imdbwidgetquotenumber?: string, imdbwidgetrating?: '0'|'1', imdbwidgetruntime?: '0'|'1', imdbwidgetsoundtrack?: '0'|'1', imdbwidgetsoundtracknumber?: string, imdbwidgetsource?: '0'|'1', imdbwidgettagline?: '0'|'1', imdbwidgettaglinenumber?: string, imdbwidgettitle?: '0'|'1', imdbwidgettrailer?: '0'|'1', imdbwidgettrailernumber?: string, imdbwidgettrivia?: '0'|'1', imdbwidgettrivianumber?: string, imdbwidgetwriter?: '0'|'1', imdbwidgetwriternumber?: string, imdbwidgetyear?: '0'|'1', 'imdbwidgetorder': ORDER_OPTIONS_DATA }
  */
class Settings_Movie extends Settings_Helper {

	/**
	 * Name of the databases as stored in WordPress db
	 * Only used in child class, has to be called in Get_Options
	 */
	protected const LUM_DATA_OPTIONS                = 'lumiere_data_options';

	/**
	 * Internal URL pages constants
	 * Must be public, used everywhere
	 */
	public const POPUP_MOVIE_PATH                   = 'class/frontend/popups/class-popup-movie.php';
	public const POPUP_SEARCH_PATH                  = 'class/frontend/popups/class-popup-movie-search.php';
	public const TAXO_ITEMS_THEME                   = 'class/theme/class-taxonomy-items-standard.php';
	public const SEARCH_MOVIE_FILE                  = 'class/admin/class-search-movie.php';
	public const SEARCH_MOVIE_URL_ADMIN             = '/wp-admin/lumiere/search-movie/';
	public const SEARCH_MOVIE_QUERY_STRING          = 'moviesearched'; // in url, such as ?moviesearched=

	/**
	 * Partial namespace of modules
	 * Used to build the full film namespace
	 * @see \Lumiere\Frontend\Popup\Popup_Film
	 */
	public const LUM_FILM_MODULE_CLASS              = '\Lumiere\Frontend\Module\Movie\Movie_';

	/**
	 * Default imdb fields when creating DATA_OPTIONS
	 * @see Settings::get_default_data_option()
	 * @see parent::define_list_items_with_numbers() to build list with DATA_DEFAULT_WITHNUMBER
	 */
	public const DATA_DEFAULT_TAXO_ACTIVE          = [ 'director', 'genre' ];
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
	public const DATA_DEFAULT_WIDGET_ACTIVE        = [ 'title', 'pic', 'actor', 'connection', 'director', 'genre', 'goof', 'plot', 'tagline', 'writer' ];

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
	 * @since 4.5 method added
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
}

