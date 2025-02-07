<?php declare( strict_types = 1 );
/**
 * Class of configuration
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       3.0
 * @package lumiere-movies
 */
namespace Lumiere;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { // Don't check for Settings class since it's Settings class.
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use FilesystemIterator;
use Lumiere\Tools\Get_Options;

// Needed vars for uninstall, fails otherwise.
// Use of defined() condition for PHPStan
if ( ! defined( 'LUMIERE_WP_PATH' ) ) {
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vars.php';
}

/**
 * Configuration class
 * Call create_database_options() to set the options in WP config database
 *
 * @since 4.0 Moved cache folder creation to class cache tools
 * @since 4.1 Renamed *imdb_widget_* to *imdb_data_* all over the website
 * @since 4.4 Options are created only when installing/activating the plugin
 *
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Tools\Settings_Global
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Tools\Settings_Global
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Tools\Settings_Global
 */
class Settings {

	/**
	 * Name of the databases as stored in WordPress db
	 */
	const LUMIERE_ADMIN_OPTIONS = 'lumiere_admin_options';
	const LUMIERE_DATA_OPTIONS  = 'lumiere_data_options';
	const LUMIERE_CACHE_OPTIONS = 'lumiere_cache_options';

	/**
	 * Website URLs constants
	 */
	const LUM_BLOG_PLUGIN          = 'https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin';
	const LUM_BLOG_PLUGIN_ABOUT    = 'https://www.jcvignoli.com/blog/en/presentation-of-jean-claude-vignoli';
	const LUM_WORDPRESS_URL        = 'https://wordpress.org/plugins/lumiere-movies/';
	const LUM_WORDPRESS_IMAGES_URL = 'https://ps.w.org/lumiere-movies/assets';
	const LUM_GIT_URL              = 'https://github.com/jcvignoli/lumiere-movies';

	/**
	 * URL Strings for popups, built in define_constants_after_globals()
	 */
	const URL_BIT_POPUPS_MOVIES        = 'film/';
	const URL_BIT_POPUPS_PEOPLE        = 'person/';
	const URL_BIT_POPUPS_MOVIES_SEARCH = 'movie_search/';

	/**
	 * URLs for pictures and menu images
	 */
	const LUM_PICS_URL           = LUMIERE_WP_URL . 'assets/pics/';
	const LUM_PICS_SHOWTIMES_URL = self::LUM_PICS_URL . '/showtimes/';

	/**
	 * URL and Path for javascripts
	 */
	const LUM_JS_PATH = LUMIERE_WP_PATH . 'assets/js/';
	const LUM_JS_URL  = LUMIERE_WP_URL . 'assets/js/';

	/**
	 * URL and Path for stylesheets
	 */
	const LUM_CSS_PATH = LUMIERE_WP_PATH . 'assets/css/';
	const LUM_CSS_URL  = LUMIERE_WP_URL . 'assets/css/';

	/**
	 * Internal URL pages constants
	 */
	const FILE_COPY_THEME_TAXONOMY = 'class/admin/taxo/class-copy-template-taxonomy.php';
	const GUTENBERG_SEARCH_FILE = 'class/admin/class-search.php';
	const SEARCH_URL_BIT = 'lumiere/search/';
	const SEARCH_URL_ADMIN = '/wp-admin/' . self::SEARCH_URL_BIT;
	const POPUP_SEARCH_PATH = 'class/frontend/popups/class-popup-movie-search.php';
	const POPUP_MOVIE_PATH = 'class/frontend/popups/class-popup-movie.php';
	const POPUP_PERSON_PATH = 'class/frontend/popups/class-popup-person.php';
	const TAXO_PEOPLE_THEME = 'class/theme/class-taxonomy-people-standard.php';
	const TAXO_ITEMS_THEME = 'class/theme/class-taxonomy-items-standard.php';

	/**
	 * URL string for taxonomy, 'lumiere-' by default
	 */
	const URL_STRING_TAXO = 'lumiere-';

	/**
	 * Cache folder path.
	 */
	const LUMIERE_FOLDER_CACHE = '/cache/lumiere/';

	/**
	 * Create database options if they don't exist
	 *
	 * @see \Lumiere\Core::lumiere_on_activation() On first plugin activation, create the options
	 * @see \Lumiere\Save_Options On every reset, calling this method
	 * @see \Lumiere\Tools\Settings_Global::get_db_options() if options are not yet available, which may happend on first install (according to WP Plugin Check)
	 *
	 * @since 4.4 method created
	 */
	public static function create_database_options(): void {

		$that = new self();

		$lum_admin_option = get_option( self::LUMIERE_ADMIN_OPTIONS );
		if ( is_array( $lum_admin_option ) === false ) {
			update_option( self::LUMIERE_ADMIN_OPTIONS, $that->get_admin_option() );
		}

		$lum_data_option = get_option( self::LUMIERE_DATA_OPTIONS );
		if ( is_array( $lum_data_option ) === false  ) {
			update_option( self::LUMIERE_DATA_OPTIONS, $that->get_data_option() );
		}

		$lum_cache_option = get_option( self::LUMIERE_CACHE_OPTIONS );
		if ( is_array( $lum_cache_option ) === false  ) {
			update_option( self::LUMIERE_CACHE_OPTIONS, $that->get_cache_option() );
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
		/* BUILD options constant for javascripts  */
		$scripts_admin_vars = wp_json_encode(
			[
				'imdb_path' => LUMIERE_WP_URL,
				'wordpress_path' => site_url(),
				'wordpress_admin_path' => admin_url(),
				'gutenberg_search_url_string' => self::SEARCH_URL_BIT,
				'gutenberg_search_url' => self::SEARCH_URL_ADMIN,
				'ico80' => LUMIERE_WP_URL . 'assets/pics/lumiere-ico-noir80x80.png',
				'popupLarg' => $imdb_admin_option['imdbpopuplarg'],
				'popupLong' => $imdb_admin_option['imdbpopuplong'],
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
				'imdb_path' => LUMIERE_WP_URL,
				'urlpopup_film' => Get_Options::get_popup_url( 'movies', site_url() ),
				'urlpopup_person' => Get_Options::get_popup_url( 'people', site_url() ),
				/** Popups */
				'popup_border_colour' => $imdb_admin_option['imdbpopuptheme'],
				'popupLarg' => $imdb_admin_option['imdbpopuplarg'],
				'popupLong' => $imdb_admin_option['imdbpopuplong'],
			]
		);
		return $scripts_vars !== false ? 'const lumiere_vars = ' . $scripts_vars : '';
	}

	/**
	 * Define all the pages of Lumiere
	 * @see \Lumiere\Admin\Admin:lumiere_execute_admin_assets()
	 *
	 * @return array<string>
	 */
	public static function get_all_lumiere_pages(): array {
		$imdb_admin_option = get_option( self::LUMIERE_ADMIN_OPTIONS );
		return [
			$imdb_admin_option !== false ? $imdb_admin_option['imdburlstringtaxo'] : self::URL_STRING_TAXO, // dunno if self is really needed
			Get_Options::get_popup_url( 'movies' ),
			Get_Options::get_popup_url( 'people' ),
			Get_Options::get_popup_url( 'movies_search' ),
			self::FILE_COPY_THEME_TAXONOMY,
			self::GUTENBERG_SEARCH_FILE, // For access to search in clicking a link (ie gutenberg)
			self::SEARCH_URL_ADMIN, // For access to search in URL lumiere/search
			self::POPUP_SEARCH_PATH,
			self::POPUP_MOVIE_PATH,
			self::POPUP_PERSON_PATH,
		];
	}

	/**
	 * Define the type of people items that are used for taxonomy
	 *
	 * @return array<string, string>
	 */
	public static function define_list_taxo_people(): array {
		return [
			'actor'    => __( 'actor', 'lumiere-movies' ),
			'composer' => __( 'composer', 'lumiere-movies' ),
			'creator'  => __( 'creator', 'lumiere-movies' ),
			'director' => __( 'director', 'lumiere-movies' ),
			'producer' => __( 'producer', 'lumiere-movies' ),
			'writer'   => __( 'writer', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type items that are used for taxonomy
	 *
	 * @return array<string, string>
	 */
	public static function define_list_taxo_items(): array {
		return [
			'color'    => __( 'color', 'lumiere-movies' ),
			'country'  => __( 'country', 'lumiere-movies' ),
			'genre'    => __( 'genre', 'lumiere-movies' ),
			'keyword'  => __( 'keyword', 'lumiere-movies' ),
			'language' => __( 'language', 'lumiere-movies' ),
		];
	}

	/**
	 * Define all types of items
	 * This lists merge taxonomy items with those that are not meant for taxo
	 *
	 * @return array<string, string>
	 */
	public static function define_list_all_items(): array {
		return array_merge(
			self::define_list_taxo_items(),
			[
				'officialsites' => __( 'official websites', 'lumiere-movies' ),
				'prodcompany'   => __( 'production company', 'lumiere-movies' ),
				'rating'        => __( 'rating', 'lumiere-movies' ),
				'runtime'       => __( 'runtime', 'lumiere-movies' ),
				'source'        => __( 'source', 'lumiere-movies' ),
				'year'          => __( 'year of release', 'lumiere-movies' ),
			]
		);
	}

	/**
	 * Define the type items to show in connected/related movies
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
	 * Define the number of updates on first install
	 * Find the number of files in updates folder
	 *
	 * @return string The number of files found
	 */
	private function define_nb_updates(): string {
		$files = new FilesystemIterator( LUMIERE_WP_PATH . 'class/updates/', \FilesystemIterator::SKIP_DOTS );
		return strval( iterator_count( $files ) + 1 );
	}

	/**
	 * Make an array of ADMIN options
	 *
	 * @phpstan-return OPTIONS_ADMIN
	 * @psalm-return array{imdbHowManyUpdates?: mixed|string, imdbautopostwidget?: mixed|string, imdbcoversize?: mixed|string, imdbcoversizewidth?: mixed|string, imdbdebug?: mixed|string, imdbdebuglevel?: mixed|string, imdbdebuglog?: mixed|string, imdbdebuglogpath?: mixed|string, imdbdebugscreen?: mixed|string, imdbdelayimdbrequest?: mixed|string, imdbintotheposttheme?: mixed|string, imdbkeepsettings?: mixed|string, imdblanguage?: mixed|string, imdblinkingkill?: mixed|string, imdbmaxresults?: mixed|string, imdbplugindirectory: non-falsy-string, imdbplugindirectory_partial?: mixed|string, imdbpluginpath?: mixed|string, imdbpopup_modal_window?: mixed|string, imdbpopuplarg?: mixed|string, imdbpopuplong?: mixed|string, imdbpopuptheme?: mixed|string, imdbseriemovies?: mixed|string, imdbtaxonomy?: mixed|string, imdburlpopups?: mixed|string, imdburlstringtaxo?: mixed|string, imdbwordpress_bigmenu?: mixed|string, imdbwordpress_tooladminmenu?: mixed|string, imdbirpdisplay?: mixed|string, ...<array-key, mixed|string>}
	 * @return array<mixed>
	 */
	private function get_admin_option(): array {

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
			#--------------------------------------------------=[ Basic ]=--
			'imdbplugindirectory_partial' => '/wp-content/plugins/lumiere-movies/',
			'imdbpluginpath'              => LUMIERE_WP_PATH,
			'imdburlpopups'               => '/lumiere/',
			'imdbkeepsettings'            => '1',
			'imdburlstringtaxo'           => self::URL_STRING_TAXO,
			'imdbcoversize'               => '1',
			'imdbcoversizewidth'          => '100',

			#--------------------------------------------------=[ Technical ]=--
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
			'imdbHowManyUpdates'          => $this->define_nb_updates(),  /* define the number of updates. */
			'imdbseriemovies'             => 'movies+series',             /* options: movies, series, movies+series, videogames */
			'imdbirpdisplay'              => '0',                         /* intelly related post plugin, overrides normal Lumiere behaviour */
		];

		// Needs an option from above.
		$imdb_admin_options['imdbplugindirectory'] = get_site_url() . $imdb_admin_options['imdbplugindirectory_partial'];

		// For debugging purpose.
		// Update imdbHowManyUpdates option.
		/*
		$option_array_search = get_option( self::LUMIERE_ADMIN_OPTIONS );
		$option_array_search['imdbHowManyUpdates'] = 18; // Chosen number of updates.
		update_option( self::LUMIERE_ADMIN_OPTIONS, $option_array_search );
		*/

		return $imdb_admin_options;
	}

	/**
	 * Makes an array of CACHE options
	 *
	 * @phpstan-return OPTIONS_CACHE
	 * @psalm-return array{imdbcacheautorefreshcron: '0', imdbcachedetailshidden: '0', imdbcachedetailsshort: '0', imdbcachedir: non-falsy-string, imdbcachedir_partial: '/cache/lumiere/', imdbcacheexpire: '2592000', imdbcachekeepsizeunder: '0', imdbcachekeepsizeunder_sizelimit: '100', imdbphotodir: non-falsy-string, imdbphotoroot: non-falsy-string, imdbusecache: '1'}
	 * @return array<mixed>
	 */
	private function get_cache_option(): array {

		$imdb_cache_options = [
			'imdbcachedir_partial'             => self::LUMIERE_FOLDER_CACHE,
			'imdbusecache'                     => '1',
			'imdbcacheexpire'                  => '2592000',                 /* one month */
			'imdbcachedetailsshort'            => '0',
			'imdbcacheautorefreshcron'         => '0',
			'imdbcachekeepsizeunder'           => '0',                /* Disabled by default */
			'imdbcachekeepsizeunder_sizelimit' => '100',    /* 100 MB */
			'imdbcachedetailshidden'           => '0',
			'imdbphotodir'                     => content_url() . '/cache/lumiere/images/',
		];

		// Needs an option from above.
		$imdb_cache_options['imdbcachedir']        = WP_CONTENT_DIR . $imdb_cache_options['imdbcachedir_partial'];
		$imdb_cache_options['imdbphotoroot']       = $imdb_cache_options['imdbcachedir'] . 'images/';

		return $imdb_cache_options;
	}

	/**
	 * Makes an array of DATA options
	 *
	 * @phpstan-return OPTIONS_DATA
	 * @psalm-return non-empty-array<array-key, '0'|'1'|'10'|'2'|array{actor: '6', alsoknow: '21', color: '20', composer: '22', connection: '15', country: '5', creator: '7', director: '4', genre: '10', goof: '17', keyword: '13', language: '9', officialsites: '25', pic: '2', plot: '16', prodcompany: '14', producer: '12', quote: '18', rating: '8', runtime: '3', soundtrack: '23', source: '26', tagline: '19', title: '1', trailer: '24', writer: '11'}>
	 * @return array<mixed>
	 */
	private function get_data_option(): array {
		return [
			'imdbwidgettitle'            => '1',
			'imdbwidgetpic'              => '1',
			'imdbwidgetruntime'          => '0',
			'imdbwidgetdirector'         => '1',
			'imdbwidgetconnection'       => '0',                           /* @since 4.4 */
			'imdbwidgetconnectionnumber' => '0',                           /* @since 4.4 */
			'imdbwidgetcountry'          => '0',
			'imdbwidgetactor'            => '1',
			'imdbwidgetactornumber'      => '10',
			'imdbwidgetcreator'          => '0',
			'imdbwidgetrating'           => '0',
			'imdbwidgetlanguage'         => '0',
			'imdbwidgetgenre'            => '1',
			'imdbwidgetwriter'           => '1',
			'imdbwidgetproducer'         => '0',
			'imdbwidgetproducernumber'   => '0',
			'imdbwidgetkeyword'          => '0',
			'imdbwidgetprodcompany'      => '0',
			'imdbwidgetplot'             => '1',
			'imdbwidgetplotnumber'       => '2',
			'imdbwidgetgoof'             => '0',
			'imdbwidgetgoofnumber'       => '0',
			'imdbwidgetcomment'          => '0',
			'imdbwidgetquote'            => '0',
			'imdbwidgetquotenumber'      => '0',
			'imdbwidgettagline'          => '0',
			'imdbwidgettaglinenumber'    => '0',
			'imdbwidgetcolor'            => '0',
			'imdbwidgetalsoknow'         => '0',
			'imdbwidgetalsoknownumber'   => '0',
			'imdbwidgetcomposer'         => '0',
			'imdbwidgetsoundtrack'       => '0',
			'imdbwidgetsoundtracknumber' => '0',
			'imdbwidgetofficialsites'    => '0',
			'imdbwidgetsource'           => '0',
			'imdbwidgetyear'             => '0',
			'imdbwidgettrailer'          => '0',
			'imdbwidgettrailernumber'    => '0',
			'imdbwidgetorder' => [
				'title'         => '1',
				'pic'           => '2',
				'runtime'       => '3',
				'director'      => '4',
				'country'       => '5',
				'actor'         => '6',
				'creator'       => '7',
				'rating'        => '8',
				'language'      => '9',
				'genre'         => '10',
				'writer'        => '11',
				'producer'      => '12',
				'keyword'       => '13',
				'prodcompany'   => '14',
				'connection'    => '15',                                    /* @since 4.4 */
				'plot'          => '16',
				'goof'          => '17',
				'quote'         => '18',
				'tagline'       => '19',
				'color'         => '20',
				'alsoknow'      => '21',
				'composer'      => '22',
				'soundtrack'    => '23',
				'trailer'       => '24',
				'officialsites' => '25',
				'source'        => '26',
			],
			'imdbtaxonomycolor'          => '0',
			'imdbtaxonomycomposer'       => '0',
			'imdbtaxonomycountry'        => '0',
			'imdbtaxonomycreator'        => '0',
			'imdbtaxonomydirector'       => '1',
			'imdbtaxonomygenre'          => '1',
			'imdbtaxonomykeyword'        => '0',
			'imdbtaxonomylanguage'       => '0',
			'imdbtaxonomyproducer'       => '0',
			'imdbtaxonomyactor'          => '0',
			'imdbtaxonomywriter'         => '0',
		];
	}
}

