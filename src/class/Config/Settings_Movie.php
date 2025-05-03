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
 * Is extended by Get_Options_Movie
 * If a new IMDB field is created it will automatically create new fields, be it in database and in the admin panel options
 * IMDB fields are automatically translated if plural
 * @see \Lumiere\Vendor\Imdb\Title Function and constants here are related to data coming from there
 *
 * @since 4.5 Class created, splitted from {@see \Lumiere\Config\Settings}
 *
 * @phpstan-type OPTIONS_DATA_ORDER array{ imdbwidgetorder: array{ title?: string, pic?: string, runtime?: string, director?: string, connection?: string, country?: string, actor?: string, cinematographer?: string, rating?: string, language?: string, genre?: string, writer?: string, producer?: string, keyword?: string, prodCompany?: string, plot?: string, goof?: string, quote?: string, tagline?: string, trailer?: string, color?: string, alsoknow?: string, composer?: string, soundtrack?: string, extSites?: string, source?: string, trivia?: string, year?: string } }
 * @phpstan-type OPTIONS_DATA_TAXO array{ imdbtaxonomyactor?: '0'|'1', imdbtaxonomycinematographer?: '0'|'1', imdbtaxonomycolor?: '0'|'1', imdbtaxonomycomposer?: '0'|'1', imdbtaxonomycountry?: '0'|'1', imdbtaxonomydirector?: '0'|'1', imdbtaxonomygenre?: '0'|'1', imdbtaxonomykeyword?: '0'|'1', imdbtaxonomylanguage?: '0'|'1', imdbtaxonomyproducer?: '0'|'1', imdbtaxonomywriter?: '0'|'1' }
 * @phpstan-type OPTIONS_DATA_WIDGET array{ imdbwidgetactor?: '0'|'1', imdbwidgetactornumber?: string, imdbwidgetalsoknow?: '0'|'1', imdbwidgetalsoknownumber?: string, imdbwidgetcinematographer?: '0'|'1', imdbwidgetcolor?: '0'|'1', imdbwidgetcomposer?: '0'|'1', imdbwidgetconnection?: '0'|'1', imdbwidgetconnectionnumber?: string, imdbwidgetcountry?: '0'|'1', imdbwidgetdirector?: '0'|'1', imdbwidgetextSites?: '0'|'1', imdbwidgetgenre?: '0'|'1', imdbwidgetgoof?: '0'|'1', imdbwidgetgoofnumber?: string, imdbwidgetkeyword?: '0'|'1', imdbwidgetlanguage?: '0'|'1', imdbwidgetpic?: '0'|'1', imdbwidgetplot?: '0'|'1', imdbwidgetplotnumber?: string, imdbwidgetprodCompany?: '0'|'1', imdbwidgetproducer?: '0'|'1', imdbwidgetproducernumber?: string, imdbwidgetquote?: '0'|'1', imdbwidgetquotenumber?: string, imdbwidgetrating?: '0'|'1', imdbwidgetruntime?: '0'|'1', imdbwidgetsoundtrack?: '0'|'1', imdbwidgetsoundtracknumber?: string, imdbwidgetsource?: '0'|'1', imdbwidgettagline?: '0'|'1', imdbwidgettaglinenumber?: string, imdbwidgettitle?: '0'|'1', imdbwidgettrailer?: '0'|'1', imdbwidgettrailernumber?: string, imdbwidgettrivia?: '0'|'1', imdbwidgettrivianumber?: string, imdbwidgetwriter?: '0'|'1', imdbwidgetwriternumber?: string, imdbwidgetyear?: '0'|'1' }
 *
 * // Final list of options
 * @phpstan-type OPTIONS_DATA_MOVIE \Union<OPTIONS_DATA_TAXO, OPTIONS_DATA_WIDGET, OPTIONS_DATA_ORDER>
 * @psalm-type OPTIONS_DATA_MOVIE_PSALM OPTIONS_DATA_TAXO&&OPTIONS_DATA_WIDGET&&OPTIONS_DATA_ORDER
 */
class Settings_Movie extends Settings_Helper {

	/**
	 * Name of the databases as stored in WordPress db
	 * Only used in child class, has to be called in Get_Options
	 */
	protected const LUM_DATA_MOVIE_OPTIONS          = 'lumiere_data_options';

	/**
	 * Internal URL pages constants
	 * @see \Lumiere\Config\Get_Options used there, so visibility must be public
	 */
	public const LUM_POPUP_MOVIE_PATH               = 'class/Frontend/Popups/Popup_Movie.php';
	public const LUM_POPUP_SEARCH_PATH              = 'class/Frontend/Popups/Popup_Movie_Search.php';

	/**
	 * Partial namespace of movie modules
	 * Used to build the full film namespace
	 * @see \Lumiere\Frontend\Popup\Popup_Film
	 */
	public const LUM_FILM_MODULE_CLASS              = '\Lumiere\Frontend\Module\Movie\Movie_';

	/**
	 * Default imdb fields when creating DATA_OPTIONS
	 *
	 * @see Settings::get_default_data_movie_option() Use these lists to build the database
	 * @see Settings_Helper::get_data_rows_withnumbers() to build list with LUM_DATA_DEFAULT_WITHNUMBER
	 */
	public const LUM_DATA_DEFAULT_TAXO_ACTIVE       = [ 'director', 'genre' ];
	public const LUM_DATA_DEFAULT_WITHNUMBER        = [ // Public visibility as class {@see Settings_Helper::get_data_rows_withnumbers()} uses it
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
	public const LUM_DATA_DEFAULT_WIDGET_ACTIVE     = [ 'title', 'pic', 'actor', 'connection', 'director', 'genre', 'goof', 'plot', 'tagline', 'writer' ];

	/**
	 * List of modules that have not method in Imdb\Title (a module may or may not exist, but do not execute it since it calls Title methods)
	 * The list removes methods from {@see Get_Options_Movie::get_list_all_items())
	 *
	 * @see \Lumiere\Admin\Cache\Cache_Files_Management::create_movie_file() use this list so doesn't call those methods in \IMDB\Title
	 */
	public const LUM_DATA_MOVIE_NO_METHOD           = [
		'pic',          /* local module, this doesn't exist in Title */
		'source',       /* doesn't exist in Title */
		'actor',        /* must be replaced by cast(), actor() is no method in Title */
	];

	/**
	 * List of items that are not available as module but are available as methods in Imdb\Title
	 * The list add methods missing in {@see Get_Options_Movie::get_list_all_items()) which are not in define_*()
	 *
	 * @see \Lumiere\Vendor\Imdb\Title List of methods
	 * @see \Lumiere\Admin\Cache\Cache_Files_Management::create_movie_file() use this list so doesn't call those methods in \IMDB\Name
	 */
	public const LUM_DATA_MOVIE_EXTRA_GENERATION  = [
		'votes',        /* There is no module named this in the define_*(), but we want it */
		'cast',         /* Replaces "actor" in defines_*() */
		'video',        /* There is no module named this in the define_*(), but we want it */
	];

	/**
	 * Define the type of (Title movie) people items that are used for taxonomy
	 * All items in type people are actually taxonomy
	 * @see Settings::get_default_data_movie_option() use this list to build the database
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
	 * Define the type (Title movie) items that are used for taxonomy
	 * Complements define_list_non_taxo_items() which are for non-taxo items
	 * @see Settings::get_default_data_movie_option() use this list to build the database
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
	 * Define the type (Title movie) items that are NOT used for taxonomy
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
	 * Define the type of (Title movie) items to show in connected/related movies (used when display it in movies)
	 * @see Get_Options_Movie::get_list_connect_cat() Call this class
	 * @see \IMDb\Title::connection() List of connection categories
	 *
	 * @since 4.4 method added
	 *
	 * @return array<string, string>
	 */
	protected static function define_list_connect_cat(): array {
		return [
			//'alternateLanguageVersionOf' => __( 'Alternate language version of', 'lumiere-movies' ),
			//'editedFrom'    => __( 'Edited from', 'lumiere-movies' ),
			//'editedInto'    => __( 'Edited into', 'lumiere-movies' ),
			'featured'        => __( 'Featured in', 'lumiere-movies' ),
			'follows'         => __( 'Follows', 'lumiere-movies' ),
			'followedBy'      => __( 'Followed by', 'lumiere-movies' ),
			//'referenced'    => __( 'Referenced', 'lumiere-movies' ),
			//'references'    => __( 'References', 'lumiere-movies' ),
			//'remadeAs'      => __( 'Remade as', 'lumiere-movies' ),
			'remakeOf'        => __( 'Remake of', 'lumiere-movies' ),
			//'sameFranchise' => __( 'Same franchise', 'lumiere-movies' ),
			//'spinOff'       => __( 'Spin off', 'lumiere-movies' ),
			//'spinOffFrom'   => __( 'Spin off from', 'lumiere-movies' ),
			//'spoofed'       => __( 'Spoofed', 'lumiere-movies' ),
			//'spoofs'        => __( 'Spoofs', 'lumiere-movies' ),
			//'versionOf'     => __( 'Version of', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type of (Title movie) items to show in goofs (used when display it in movies)
	 * Some are deactivated, not returned in movies
	 * @see Get_Options_Movie::get_list_goof_cat() Call this class
	 * @see \IMDb\Title::goof() List of trivia categories
	 *
	 * @since 4.4 method added
	 *
	 * @return array<string, string>
	 */
	protected static function define_list_goof_cat(): array {
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
	 * Define the type of (Title movie) items to show in trivias (used when display it in movies)
	 * Some are deactivated, not returned in movies
	 * @see Get_Options_Movie::get_list_trivia_cat() Call this class
	 * @see \IMDb\Title::trivia() List of trivia categories
	 *
	 * @since 4.5 method added
	 *
	 * @return array<string, string>
	 */
	protected static function define_list_trivia_cat(): array {
		return [
			'uncategorized'        => __( 'uncategorized', 'lumiere-movies' ),
			'cameo'                => __( 'cameo', 'lumiere-movies' ),
			'directorTrademark'    => __( 'director trademark', 'lumiere-movies' ),
			//'directorCameo'      => __( 'director cameo', 'lumiere-movies' ),
			//'smithee'            => __( 'smithee', 'lumiere-movies' ),
			'actorTrademark'       => __( 'actor trademark', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the list of data comments
	 * This comment is display in admin data, explaining the purpose of selecting an item
	 * @see \Lumiere\Admin\Submenu\Data::get_details_comments() Call this class
	 *
	 * @since 4.6 method added
	 *
	 * @return array<string, string>
	 */
	protected static function define_items_details_comments(): array {
		return [
			'actor'           => __( 'Display (a number of) actors', 'lumiere-movies' ),
			'alsoknow'        => __( 'Display (a number of) alternative movie names and in other languages', 'lumiere-movies' ),
			'color'           => __( 'Display colors', 'lumiere-movies' ),
			'composer'        => __( 'Display composer', 'lumiere-movies' ),
			'connection'      => __( 'Display (a number of) related movies', 'lumiere-movies' ),
			'country'         => __( 'Display country', 'lumiere-movies' ),
			'cinematographer' => __( 'Display cinematographers', 'lumiere-movies' ),
			'director'        => __( 'Display directors', 'lumiere-movies' ),
			'genre'           => __( 'Display genre', 'lumiere-movies' ),
			'goof'            => __( 'Display (a number of) goofs (per category limit)', 'lumiere-movies' ),
			'keyword'         => __( 'Display keywords', 'lumiere-movies' ),
			'language'        => __( 'Display languages', 'lumiere-movies' ),
			'extSites'        => __( 'Display official websites', 'lumiere-movies' ),
			'pic'             => __( 'Display the main poster', 'lumiere-movies' ),
			'plot'            => __( 'Display plots. This field may require much size in your page.', 'lumiere-movies' ),
			'producer'        => __( 'Display (a number of) producers', 'lumiere-movies' ),
			'prodCompany'     => __( 'Display the production companies', 'lumiere-movies' ),
			'quote'           => __( 'Display (a number of) quotes', 'lumiere-movies' ),
			'rating'          => __( 'Display rating', 'lumiere-movies' ),
			'runtime'         => __( 'Display the runtime', 'lumiere-movies' ),
			'soundtrack'      => __( 'Display (a number of) soundtracks', 'lumiere-movies' ),
			'source'          => __( 'Display IMDb website source of the movie', 'lumiere-movies' ),
			'tagline'         => __( 'Display (a number of) taglines', 'lumiere-movies' ),
			'title'           => __( 'Display the title', 'lumiere-movies' ),
			'trailer'         => __( 'Display (a number of) trailers', 'lumiere-movies' ),
			'trivia'          => __( 'Display (a number of) trivias (per category limit)', 'lumiere-movies' ),
			'writer'          => __( 'Display writers', 'lumiere-movies' ),
			'year'            => __( 'Display release year. The release year will appear next to the movie title into brackets', 'lumiere-movies' ),
		];
	}
}

