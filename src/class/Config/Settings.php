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
 * Fields are automatically created, be it in database and in the admin panel options
 * IMDB fields are automatically translated if plural
 *
 * @since 4.0 Moved cache folder creation to class cache tools
 * @since 4.1 Renamed *imdb_widget_* to *imdb_data_* all over the website
 * @since 4.4 Options are created only when installing/activating the plugin, widely rewritten and simplified. OPTIONS_DATA is dynamically created according to the arrays of items/people added. Using {@see Get_Options} class as child class for all external calls. {@see Settings_Build} is the class that includes helper methods.
 * @since 4.6 Added {@see Settings::get_default_data_person_option()} method
 *
 * @see \Lumiere\Config\Settings_Movie complement this class
 * @see \Lumiere\Config\Settings_Person complement this class
 * @see \Lumiere\Config\Settings_Popup complement this class
 *
 * @phpstan-type OPTIONS_ADMIN array{imdbHowManyUpdates: string, imdbautopostwidget: '0'|'1'|string, imdbcoversize: '0'|'1'|string, imdbcoversizewidth: string, imdbdebug?: '0'|'1'|string, imdbdebuglevel: 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY', imdbdebuglog: '0'|'1'|string, imdbdebuglogpath: mixed, imdbdebugscreen:'0'|'1'|string, imdbdelayimdbrequest: '0'|'1'|string, imdbintotheposttheme: string, imdbirpdisplay: '0'|'1'|string, imdbkeepsettings: '0'|'1'|string, imdblanguage: string, imdblinkingkill: '0'|'1'|string, imdbmaxresults: string, imdbplugindirectory: string, imdbplugindirectory_partial: string, imdbpluginpath: mixed, imdbpopup_modal_window: string, imdbpopuplarg: string, imdbpopuplong: string, imdbpopuptheme: string, imdbseriemovies: 'movies'|'series'|'movies+series'|'videogames', imdbtaxonomy: '0'|'1'|string, imdburlpopups: string, imdburlstringtaxo: string, imdbwordpress_bigmenu: '0'|'1'|string, imdbwordpress_tooladminmenu: '0'|'1'|string}
 *
 * @phpstan-type OPTIONS_CACHE array{ 'imdbcacheautorefreshcron': string, 'imdbcachedetailsshort': string, 'imdbcachedir': string, 'imdbcachedir_partial': string, 'imdbcacheexpire': string, 'imdbcachekeepsizeunder': string, 'imdbcachekeepsizeunder_sizelimit': string, 'imdbphotodir': string, 'imdbphotoroot': string, 'imdbusecache': string, 'imdbcachedetailshidden': string}
 *
 * @phpstan-import-type OPTIONS_DATA_MOVIE from \Lumiere\Config\Settings_Movie
 * @phpstan-import-type OPTIONS_DATA_MOVIE_PSALM from \Lumiere\Config\Settings_Movie
 * @phpstan-import-type OPTIONS_DATA_PERSON from \Lumiere\Config\Settings_Person
 * @phpstan-import-type OPTIONS_DATA_PERSON_PSALM from \Lumiere\Config\Settings_Person
 */
class Settings extends Settings_Helper {

	/**
	 * Name of the databases as stored in WordPress db
	 * Only used in child class, has to be called in Get_Options
	 */
	protected const LUM_ADMIN_OPTIONS               = 'lumiere_admin_options';
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
	 * Admin search
	 */
	public const LUM_SEARCH_ITEMS_FILE              = 'class/admin/Search_Items.php';
	public const LUM_SEARCH_ITEMS_URL_ADMIN         = '/wp-admin/lumiere/search-items/';
	public const LUM_SEARCH_ITEMS_QUERY_STRING      = 'itemsearched'; // search string in url, such as ?moviesearched=

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
	 * @TODO: remove, most certainely useless!
	 */
	public const LUM_FILE_COPY_THEME_TAXONOMY       = 'class/Admin/Copy_Template/Copy_Theme.php';
	public const LUM_TAXO_ITEMS_THEME               = 'class/Theme/Taxonomy_Items_Standard.php';

	/**
	 * The name of the custom meta data field used for movie auto title widget
	 * Utilised in Gutenberg
	 * @see \Lumiere\Frontend\Widget\Widget_Frontpage::lum_get_widget() use it to check and display the auto title widget
	 * @see \Lumiere\Admin\Metabox_Selection::register_post_meta_sidebar() use it to register the custom meta data
	 */
	public const LUM_AUTOTITLE_METADATA_FIELD_NAME  = '_lum_autotitle_perpost';

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
	public const LUM_UPDATES_PATH                   = 'class/Updates/';

	/**
	 * Cache folder path.
	 */
	public const LUM_FOLDER_CACHE                   = '/cache/lumiere/';

	/**
	 * Create database options if they don't exist
	 *
	 * @see \Lumiere\Core::lumiere_on_activation() On first plugin activation, create the options
	 * @see \Lumiere\Admin\Save\Save_Options On every reset, calling this method
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

		$lum_data_movie_option = get_option( Get_Options_Movie::get_data_tablename() );
		if ( is_array( $lum_data_movie_option ) === false  ) {
			update_option( Get_Options_Movie::get_data_tablename(), $that->get_default_data_movie_option() );
		}

		$lum_data_person_option = get_option( Get_Options_Person::get_data_person_tablename() );
		if ( is_array( $lum_data_person_option ) === false  ) {
			update_option( Get_Options_Person::get_data_person_tablename(), $that->get_default_data_person_option() );
		}

		$lum_cache_option = get_option( self::LUM_CACHE_OPTIONS );
		if ( is_array( $lum_cache_option ) === false  ) {
			update_option( self::LUM_CACHE_OPTIONS, $that->get_default_cache_option() );
		}
	}

	/**
	 * Define an array of available selection for type of search
	 * The 'value' column must include either movie or person in _X_, it must end with 'id' if it is id related.
	 *
	 * @see Settings::get_scripts_admin_vars() Used in wp_add_inline_script() function
	 * @see Get_Options::get_lum_all_type_search()
	 * @see Get_Options::get_lum_all_type_search_widget()
	 * @see \Lumiere\Admin\Metabox_Selection
	 * @since 4.6.1
	 *
	 * @return array<array<string, string>>
	 */
	protected static function define_lum_all_type_search(): array {
		return [
			[
				'label' => ucwords( __( 'By movie ID', 'lumiere-movies' ) ),
				'value' => 'lum_movie_id',
			],
			[
				'label' => ucwords( __( 'By movie title', 'lumiere-movies' ) ),
				'value' => 'lum_movie_title',
			],
			[
				'label' => ucwords( __( 'By person name', 'lumiere-movies' ) ),
				'value' => 'lum_person_name',
			],
			[
				'label' => ucwords( __( 'By person ID', 'lumiere-movies' ) ),
				'value' => 'lum_person_id',
			],
		];
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
				'admin_movie_search_url'      => self::LUM_SEARCH_ITEMS_URL_ADMIN,
				'admin_movie_search_qstring'  => self::LUM_SEARCH_ITEMS_QUERY_STRING,
				'ico80'                       => LUM_WP_URL . 'assets/pics/lumiere-ico-noir80x80.png',
				'popupLarg'                   => $imdb_admin_option['imdbpopuplarg'],
				'popupLong'                   => $imdb_admin_option['imdbpopuplong'],
				'select_type_search'          => Get_Options::get_lum_all_type_search(),
				'auto_title_field_name'       => self::LUM_AUTOTITLE_METADATA_FIELD_NAME,
				'auto_title_activated'        => $imdb_admin_option['imdbautopostwidget'],
			]
		);
		return $scripts_admin_vars !== false ? 'const lumiere_admin_vars = ' . $scripts_admin_vars : '';
	}

	/**
	 * Get FRONTEND vars for javascript
	 * @see \Lumiere\Frontend\Frontend::frontpage_execute_assets() Include the vars
	 * Used in wp_add_inline_script() function
	 * @since 4.6.2 added polylang filter, that ensure the links take into accout Polylang /lang if Polylang is active
	 *
	 * @return string The full javascript piece to be included
	 */
	public static function get_scripts_frontend_vars(): string {
		$imdb_admin_option = get_option( self::LUM_ADMIN_OPTIONS );
		$urlpopup_film = apply_filters( 'lum_polylang_rewrite_url_with_lang', Get_Options::get_popup_url( 'film', site_url() ) );
		$urlpopup_person = apply_filters( 'lum_polylang_rewrite_url_with_lang', Get_Options::get_popup_url( 'person', site_url() ) );
		$scripts_vars = wp_json_encode(
			[
				'lum_path'            => LUM_WP_URL,
				'urlpopup_film'       => $urlpopup_film,
				'urlpopup_person'     => $urlpopup_person,
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
			/** @psalm-suppress FalseOperand (psalm can't either) */
			$debug_path = ABSPATH . WP_DEBUG_LOG;
		}

		$lang = str_replace( '-', '_', get_bloginfo( 'language' ) );

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
			'imdblanguage'                => $lang,
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
	 * Return default DATA Movies options
	 *
	 * @since 4.4 Totally rewritten and automatized
	 * @see Settings_Helper::get_data_rows_taxo() Import taxonomy array
	 * @see Settings_Helper::get_data_rows_withnumbers() Import with numbers array
	 * @see Settings_Helper::get_data_rows_widget() Import 'imdbwidget...' array
	 * @see Settings_Helper::get_data_rows_imdbwidgetorder() Import 'imdbwidgetorder' array
	 *
	 * @phpstan-return OPTIONS_DATA_MOVIE
	 * @psalm-return OPTIONS_DATA_MOVIE_PSALM
	 * @return array<string, string|array<string, string>>
	 */
	private function get_default_data_movie_option(): array {
		return array_merge(
			parent::get_data_rows_widget( Get_Options_Movie::LUM_DATA_DEFAULT_WIDGET_ACTIVE    /* Activated rows by default */ ),
			parent::get_data_rows_imdbwidgetorder(),
			parent::get_data_rows_taxo( Get_Options_Movie::LUM_DATA_DEFAULT_TAXO_ACTIVE        /* Activated rows by default */ ),
			parent::get_data_rows_withnumbers( Get_Options_Movie::LUM_DATA_DEFAULT_WITHNUMBER  /* Rows that must have a specific number */ ),
		);
	}

	/**
	 * Return default DATA Person options
	 *
	 * @since 4.6 new
	 * @see Settings_Helper::get_data_person_order() Import person order array
	 * @see Settings_Helper::get_data_person_activated() Import person activated array
	 *
	 * @return array<string, array<string, string>>
	 * @phpstan-return OPTIONS_DATA_PERSON (use of Union)
	 * @psalm-return OPTIONS_DATA_PERSON_PSALM (use of | )
	 */
	private function get_default_data_person_option(): array {
		return [
			...parent::get_data_person_order(),
			...parent::get_data_person_activated(),
			...parent::get_data_person_number(),
		];
	}
}

