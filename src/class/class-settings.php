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
 * Settings class
 * Call create_database_options() to set the options in WP config database
 *
 * @since 4.0 Moved cache folder creation to class cache tools
 * @since 4.1 Renamed *imdb_widget_* to *imdb_data_* all over the website
 * @since 4.4 Options are created only when installing/activating the plugin, widely rewritten and simplified. OPTIONS_DATA is dynamically created according to the arrays of items/people added. Use of Get_Options class.
 *
 * @phpstan-type LEVEL_LOG_NAME 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY'
 *
 * @phpstan-type OPTIONS_ADMIN array{imdbHowManyUpdates: string, imdbautopostwidget: '0'|'1'|string, imdbcoversize: '0'|'1'|string, imdbcoversizewidth: string, imdbdebug: '0'|'1'|string, imdbdebuglevel: LEVEL_LOG_NAME|string, imdbdebuglog: '0'|'1'|string, imdbdebuglogpath: mixed, imdbdebugscreen:'0'|'1'|string, imdbdelayimdbrequest: '0'|'1'|string, imdbintotheposttheme: string, imdbirpdisplay: '0'|'1'|string, imdbkeepsettings: '0'|'1'|string, imdblanguage: string, imdblinkingkill: '0'|'1'|string, imdbmaxresults: string, imdbplugindirectory: string, imdbplugindirectory_partial: string, imdbpluginpath: mixed, imdbpopup_modal_window: string, imdbpopuplarg: string, imdbpopuplong: string, imdbpopuptheme: string, imdbseriemovies: string, imdbtaxonomy: '0'|'1'|string, imdburlpopups: string, imdburlstringtaxo: string, imdbwordpress_bigmenu: '0'|'1'|string, imdbwordpress_tooladminmenu: '0'|'1'|string}
 *
 * @phpstan-type OPTIONS_CACHE array{ 'imdbcacheautorefreshcron': string, 'imdbcachedetailsshort': string, 'imdbcachedir': string, 'imdbcachedir_partial': string, 'imdbcacheexpire': string, 'imdbcachekeepsizeunder': string, 'imdbcachekeepsizeunder_sizelimit': string, 'imdbphotodir': string, 'imdbphotoroot': string, 'imdbusecache': string, 'imdbcachedetailshidden': string}
 *
 * @phpstan-type OPTIONS_DATA array{'imdbtaxonomyactor'?:string, 'imdbtaxonomycolor'?:string, 'imdbtaxonomycomposer'?:string, 'imdbtaxonomycountry'?:string, 'imdbtaxonomycreator'?:string, 'imdbtaxonomydirector'?:string, 'imdbtaxonomygenre'?:string, 'imdbtaxonomykeyword'?:string, 'imdbtaxonomylanguage'?:string, 'imdbtaxonomyproducer'?:string, 'imdbtaxonomywriter'?:string, 'imdbwidgetactor'?:string, 'imdbwidgetactornumber'?:string, 'imdbwidgetalsoknow'?:string, 'imdbwidgetalsoknownumber'?:string, 'imdbwidgetcolor'?:string, 'imdbwidgetcomment'?:string, 'imdbwidgetcomposer'?:string, 'imdbwidgetconnection'?:string, 'imdbwidgetconnectionnumber'?:string, 'imdbwidgetcountry'?:string, 'imdbwidgetcreator'?:string, 'imdbwidgetdirector'?:string, 'imdbwidgetgenre'?:string, 'imdbwidgetgoof'?:string, 'imdbwidgetgoofnumber'?:string, 'imdbwidgetkeyword'?:string, 'imdbwidgetlanguage'?:string, 'imdbwidgetofficialsites'?:string, 'imdbwidgetpic'?:string, 'imdbwidgetplot'?:string, 'imdbwidgetplotnumber'?:string, 'imdbwidgetprodcompany'?:string, 'imdbwidgetproducer'?:string, 'imdbwidgetproducernumber'?:string, 'imdbwidgetquote'?:string, 'imdbwidgetquotenumber'?:string, 'imdbwidgetrating'?:string, 'imdbwidgetruntime'?:string, 'imdbwidgetsoundtrack'?:string, 'imdbwidgetsoundtracknumber'?:string, 'imdbwidgetsource'?:string, 'imdbwidgettagline'?:string, 'imdbwidgettaglinenumber'?:string, 'imdbwidgettitle'?:string, 'imdbwidgettrailer'?:string, 'imdbwidgettrailernumber'?:string, 'imdbwidgetwriter'?:string, 'imdbwidgetyear'?:string,'imdbwidgetorder': array{title?: string, pic?: string, runtime?: string, director?: string, connection?: string, country?: string, actor?: string, creator?: string, rating?: string, language?: string, genre?: string, writer?: string, producer?: string, keyword?: string, prodcompany?: string, plot?: string, goof?: string, comment?: string, quote?: string, tagline?: string, trailer?: string, color?: string, alsoknow?: string, composer?: string, soundtrack?: string, officialsites?: string, source?: string, year?: string} }
  */
class Settings {

	/**
	 * Name of the databases as stored in WordPress db
	 * Only used in child class
	 */
	protected const LUMIERE_ADMIN_OPTIONS = 'lumiere_admin_options';
	protected const LUMIERE_DATA_OPTIONS  = 'lumiere_data_options';
	protected const LUMIERE_CACHE_OPTIONS = 'lumiere_cache_options';

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
	 * Default options when creating DATA_OPTIONS
	 * @see self::get_data_option()
	 */
	private const DATA_OPTION_TAXO_ACTIVE_DEFAULT = [ 'director', 'genre' ];
	private const DATA_OPTION_WITHNUMBER_DEFAULT = [
		'actor' => '10',
		'plot' => '3',
	];
	private const DATA_OPTION_WIDGET_ACTIVE_DEFAULT = [ 'title', 'pic', 'director', 'actor', 'genre', 'writer', 'plot' ];

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
	 * All items in type people are actually taxonomy
	 * @see self::get_data_option() use this list to create the options
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
	 * @see self::get_data_option() use this list to create the options
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
	 *
	 * @return array<string, string>
	 * @phpstan-return array{ 'officialsites':string,'prodcompany':string, 'rating':string,'runtime':string, 'source':string, 'year':string, 'title': string, 'pic':string, 'alsoknow': string, 'connection':string, 'goof': string, 'plot':string, 'quote':string, 'soundtrack':string, 'tagline':string, 'trailer':string }
	 */
	protected static function define_list_non_taxo_items(): array {
		return [
			'title'         => __( 'title', 'lumiere-movies' ),
			'pic'           => __( 'pic', 'lumiere-movies' ),
			'runtime'       => __( 'runtime', 'lumiere-movies' ),
			'connection'    => __( 'connected movies', 'lumiere-movies' ),          /* @since 4.4 added */
			'rating'        => __( 'rating', 'lumiere-movies' ),
			'prodcompany'   => __( 'production company', 'lumiere-movies' ),
			'plot'          => __( 'plot', 'lumiere-movies' ),
			'goof'          => __( 'goof', 'lumiere-movies' ),
			'quote'         => __( 'quote', 'lumiere-movies' ),
			'tagline'       => __( 'tagline', 'lumiere-movies' ),
			'alsoknow'      => __( 'also known as', 'lumiere-movies' ),
			'trailer'       => __( 'trailer', 'lumiere-movies' ),
			'soundtrack'    => __( 'soundtrack', 'lumiere-movies' ),
			'officialsites' => __( 'official websites', 'lumiere-movies' ),
			'source'        => __( 'source', 'lumiere-movies' ),
			'year'          => __( 'year of release', 'lumiere-movies' ),
		];
	}

	/**
	 * Define the type items that can get an extra number in admin data options
	 *
	 * @return array<string, string>
	 * @phpstan-return array{ 'actor': string, 'alsoknow': string, 'connection':string, 'goof':string, 'plot':string, 'producer':string, 'quote':string, 'soundtrack':string, 'tagline':string, 'trailer':string }
	 */
	protected static function define_list_items_with_numbers(): array {
		return [
			'actor'      => __( 'actor', 'lumiere-movies' ),
			'alsoknow'   => __( 'also known as', 'lumiere-movies' ),
			'connection' => __( 'connected movies', 'lumiere-movies' ),         /* @since 4.4 added */
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
	 * Define all types of items
	 * This lists merge taxonomy items with those that are not meant for taxo
	 *
	 * @return array<string, string>
	 */
	protected static function define_list_all_items(): array {
		return array_merge(
			self::define_list_taxo_people(), // Taxo_people is all people options, since there are no people options that are not taxonomy.
			self::define_list_taxo_items(),
			self::define_list_non_taxo_items(),
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
	 * Return standard ADMIN options
	 *
	 * @phpstan-return OPTIONS_ADMIN
	 * @return array<string, string|array<string, string>>
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
	 * Return standard CACHE options
	 *
	 * @phpstan-return OPTIONS_CACHE
	 * @return array<string, string|array<string, string>>
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
	 * Return standard DATA options
	 *
	 * @see self::get_data_rows_taxo() Import automatically taxonomy built vars
	 * @see self::get_data_rows_withnumbers() Import automatically with numbers built vars
	 * @see self::get_data_rows_widget() Import automatically 'imdbwidget...' built vars
	 * @see self::get_data_rows_imdbwidgetorder() Import automatically array 'imdbwidgetorder' built vars
	 *
	 * @phpstan-return OPTIONS_DATA
	 * @return array<string, string|array<string, string>>
	 */
	private function get_data_option(): array {
		return array_merge(
			$this->get_data_rows_taxo( self::DATA_OPTION_TAXO_ACTIVE_DEFAULT /* activated rows by default */ ),
			$this->get_data_rows_withnumbers( self::DATA_OPTION_WITHNUMBER_DEFAULT /* rows that must have a specific number */ ),
			$this->get_data_rows_widget( self::DATA_OPTION_WIDGET_ACTIVE_DEFAULT /* activated rows by default */ ),
			$this->get_data_rows_imdbwidgetorder(),
		);
	}

	/**
	 * Create rows for 'imdbtaxonomy' using internal methods
	 *
	 * @see self::get_data_option() Meant to be used there
	 * @param list<string>|null $activated List of taxonomy to activate by default
	 * @return array<string, string>
	 *
	 * @phpstan-return array{imdbtaxonomyactor?: '0'|'1', imdbtaxonomycolor?: '0'|'1', imdbtaxonomycomposer?: '0'|'1', imdbtaxonomycountry?: '0'|'1', imdbtaxonomycreator?: '0'|'1', imdbtaxonomydirector?: '0'|'1', imdbtaxonomygenre?: '0'|'1', imdbtaxonomykeyword?: '0'|'1', imdbtaxonomylanguage?: '0'|'1', imdbtaxonomyproducer?: '0'|'1', imdbtaxonomywriter?: '0'|'1'}
	 */
	private function get_data_rows_taxo( ?array $activated ): array {
		$taxonomy_keys = array_merge( array_keys( self::define_list_taxo_people() ), array_keys( self::define_list_taxo_items() ) );
		$array_taxonomy = [];
		foreach ( $taxonomy_keys as $row_number => $taxonomy_key ) {
			if ( isset( $activated ) && in_array( $taxonomy_key, $activated, true ) ) {
				$array_taxonomy[ 'imdbtaxonomy' . $taxonomy_key ] = '1';
				continue;
			}
			$array_taxonomy[ 'imdbtaxonomy' . $taxonomy_key ] = '0';
		}
		return $array_taxonomy;
	}

	/**
	 * Create rows for 'imdbwidget' using internal methods
	 *
	 * @see self::get_data_option() Meant to be used there
	 * @param list<string>|null $activated List of taxonomy to activate by default
	 * @return array<string, string>
	 *
	 * @phpstan-return array{imdbwidgettitle?: '0'|'1', imdbwidgetpic?: '0'|'1', imdbwidgetruntime?: '0'|'1', imdbwidgetdirector?: '0'|'1', imdbwidgetconnection?: '0'|'1', imdbwidgetcountry?: '0'|'1', imdbwidgetactor?: '0'|'1', imdbwidgetcreator?: '0'|'1', imdbwidgetrating?: '0'|'1', imdbwidgetlanguage?: '0'|'1', imdbwidgetgenre?: '0'|'1', imdbwidgetwriter?: '0'|'1', imdbwidgetproducer?: '0'|'1', imdbwidgetkeyword?: '0'|'1', imdbwidgetprodcompany?: '0'|'1', imdbwidgetplot?: '0'|'1', imdbwidgetgoof?: '0'|'1', imdbwidgetcomment?: '0'|'1', imdbwidgetquote?: '0'|'1', imdbwidgettagline?: '0'|'1', imdbwidgettrailer?: '0'|'1', imdbwidgetcolor?: '0'|'1', imdbwidgetalsoknow?: '0'|'1', imdbwidgetcomposer?: '0'|'1', imdbwidgetsoundtrack?: '0'|'1', imdbwidgetofficialsites?: '0'|'1', imdbwidgetsource?: '0'|'1', imdbwidgetyear?: '0'|'1'}
	 */
	private function get_data_rows_widget( ?array $activated ): array {
		$widget_keys = array_merge( array_keys( self::define_list_non_taxo_items() ), array_keys( self::define_list_taxo_items() ), array_keys( self::define_list_taxo_people() ) );
		$array_widget = [];
		foreach ( $widget_keys as $row_number => $widget_key ) {
			if ( isset( $activated ) && in_array( $widget_key, $activated, true ) ) {
				$array_widget[ 'imdbwidget' . $widget_key ] = '1';
				continue;
			}
			$array_widget[ 'imdbwidget' . $widget_key ] = '0';
		}
		return $array_widget;
	}

	/**
	 * Create rows for 'imdbwidgetorder' using internal methods
	 *
	 * @see self::get_data_option() Meant to be used there
	 * @return array<string, string>
	 *
	 * @phpstan-return array{ imdbwidgetorder: array{title?: string, pic?: string, runtime?: string, director?: string, connection?: string, country?: string, actor?: string, creator?: string, rating?: string, language?: string, genre?: string, writer?: string, producer?: string, keyword?: string, prodcompany?: string, plot?: string, goof?: string, comment?: string, quote?: string, tagline?: string, trailer?: string, color?: string, alsoknow?: string, composer?: string, soundtrack?: string, officialsites?: string, source?: string, year?: string} }
	 */
	private function get_data_rows_imdbwidgetorder(): array {
		$widget_keys = array_merge( array_keys( self::define_list_non_taxo_items() ), array_keys( self::define_list_taxo_items() ), array_keys( self::define_list_taxo_people() ) );
		$array_imdbwidgetorder = [];
		$i = 0;
		foreach ( $widget_keys as $row_number => $imdbwidgetorder_key ) {
			$array_imdbwidgetorder['imdbwidgetorder'][ $imdbwidgetorder_key ] = strval( $i );
			$i++;
		}
		return $array_imdbwidgetorder;
	}

	/**
	 * Create rows for 'imdbtaxonomy' using internal methods
	 *
	 * @see self::get_data_option() Meant to be used there
	 * @param array<string, string>|null $activated List of taxonomy to activate by default
	 * @return non-empty-array<string, string>
	 * @phpstan-return array{imdbwidgetactornumber?: string, imdbwidgetalsoknownumber?: string, imdbwidgetconnectionnumber?: string, imdbwidgetgoofnumber?: string, imdbwidgetplotnumber?: string, imdbwidgetproducernumber?: string, imdbwidgetquotenumber?: string, imdbwidgetsoundtracknumber?: string, imdbwidgettaglinenumber?: string, imdbwidgettrailernumber?: string}
	 */
	private function get_data_rows_withnumbers( ?array $activated ): array {
		$array_with_numbers = [];
		$count = isset( $activated ) ? count( $activated ) - 1 : 0; // Remove 1 to total count since arrays start at 0.
		$loop = array_keys( self::define_list_items_with_numbers() );
		$reversed = isset( $activated ) ? array_reverse( $activated, true ) : [];
		$reversed_array = [];
		foreach ( $reversed as $k => $v ) {
			$reversed_array[] = [ $k => $v ];
		}
		foreach ( $loop as $key => $withnumber_key ) {
			if ( in_array( $withnumber_key, array_keys( $reversed ), true ) && $count > -1 ) {
				$array_with_numbers[ 'imdbwidget' . $withnumber_key . 'number' ] = $reversed_array[ $count ][ $withnumber_key ];
				$count--;
				continue;
			}
			$array_with_numbers[ 'imdbwidget' . $withnumber_key . 'number' ] = '0';
		}
		return $array_with_numbers;
	}
}

