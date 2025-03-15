<?php declare( strict_types = 1 );
/**
 * Main Settings class
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

use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Config\Settings_Helper;

// Needed vars for uninstall, fails otherwise.
// Use of defined() condition for PHPStan
if ( ! defined( 'LUM_WP_PATH' ) ) {
	require_once plugin_dir_path( dirname( __DIR__ ) ) . 'vars.php';
}

/**
 * Main settings
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
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Config\Settings_Movie
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
	public const LUM_FILE_COPY_THEME_TAXONOMY       = 'class/admin/taxo/class-copy-template-taxonomy.php';
	public const LUM_TAXO_ITEMS_THEME               = 'class/theme/class-taxonomy-items-standard.php';

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
	 * @see \Lumiere\Admin\Admin::lumiere_execute_admin_assets() Include the vars
	 * Used in wp_add_inline_script() function
	 *
	 * @return string The full javascript piece to be included
	 */
	public static function get_scripts_admin_vars(): string {
		$imdb_admin_option = get_option( self::LUM_ADMIN_OPTIONS );
		$scripts_admin_vars = wp_json_encode(
			[
				'lum_path'                    => LUM_WP_URL,
				'wordpress_path'              => site_url(),
				'admin_movie_search_url'      => Get_Options_Movie::LUM_SEARCH_MOVIE_URL_ADMIN,
				'admin_movie_search_qstring'  => Get_Options_Movie::LUM_SEARCH_MOVIE_QUERY_STRING,
				'ico80'                       => LUM_WP_URL . 'assets/pics/lumiere-ico-noir80x80.png',
				'popupLarg'                   => $imdb_admin_option['imdbpopuplarg'],
				'popupLong'                   => $imdb_admin_option['imdbpopuplong'],
			]
		);
		return $scripts_admin_vars !== false ? 'const lumiere_admin_vars = ' . $scripts_admin_vars : '';
	}

	/**
	 * Get FRONTEND vars for javascript
	 * @see \Lumiere\Frontend\Frontend::frontpage_execute_assets() Include the vars
	 * Used in wp_add_inline_script() function
	 *
	 * @return string The full javascript piece to be included
	 */
	public static function get_scripts_frontend_vars(): string {
		$imdb_admin_option = get_option( self::LUM_ADMIN_OPTIONS );
		$scripts_vars = wp_json_encode(
			[
				'lum_path'            => LUM_WP_URL,
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
	 * @see Settings_Helper::get_data_rows_taxo() Import automatically taxonomy built vars
	 * @see Settings_Helper::get_data_rows_withnumbers() Import automatically with numbers built vars
	 * @see Settings_Helper::get_data_rows_widget() Import automatically 'imdbwidget...' built vars
	 * @see Settings_Helper::get_data_rows_imdbwidgetorder() Import automatically array 'imdbwidgetorder' built vars
	 *
	 * @phpstan-return OPTIONS_DATA
	 * @return array<string, string|array<string, string>>
	 */
	private function get_default_data_option(): array {
		return array_merge(
			parent::get_data_rows_widget( Get_Options_Movie::LUM_DATA_DEFAULT_WIDGET_ACTIVE    /* Activated rows by default */ ),
			parent::get_data_rows_imdbwidgetorder(),
			parent::get_data_rows_taxo( Get_Options_Movie::LUM_DATA_DEFAULT_TAXO_ACTIVE        /* Activated rows by default */ ),
			parent::get_data_rows_withnumbers( Get_Options_Movie::LUM_DATA_DEFAULT_WITHNUMBER  /* Rows that must have a specific number */ ),
		);
	}
}

