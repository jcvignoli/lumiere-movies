<?php declare( strict_types = 1 );
/**
 * Settings Trait for including database options
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Tools;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Tools\Get_Options;
use Lumiere\Settings;

/**
 * Trait for including database options
 * Most pages that need any of admin, cache or widget options are using it
 *
 * Below can't be imported, it might be related to PHPStan bug #5091
 * @phpstan-type LEVEL_LOG_NAME 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY'
 * @phpstan-type OPTIONS_ADMIN array{imdbplugindirectory: string, imdbplugindirectory_partial: string, imdbpluginpath: string,imdburlpopups: string,imdbkeepsettings: string,imdburlstringtaxo: string,imdbcoversize: string,imdbcoversizewidth: string, imdbmaxresults: string, imdbdelayimdbrequest: string, imdbpopuptheme: string, imdbpopuplarg: string,imdbpopuplong: string, imdbintotheposttheme: string, imdblinkingkill: string, imdbautopostwidget: string, imdblanguage: string, imdbdebug: string, imdbdebuglog: string, imdbdebuglogpath: string, imdbdebuglevel: string|LEVEL_LOG_NAME, imdbdebugscreen: string, imdbwordpress_bigmenu: string, imdbwordpress_tooladminmenu: string, imdbpopup_modal_window: string, imdbtaxonomy: string, imdbHowManyUpdates: string, imdbseriemovies: string, imdbirpdisplay: string}
 * @phpstan-type OPTIONS_CACHE array{imdbcacheautorefreshcron: string, imdbcachedetailsshort: string, imdbcachedir: string, imdbcachedir_partial: string, imdbcacheexpire: string, imdbcachekeepsizeunder: string, imdbcachekeepsizeunder_sizelimit: string, imdbphotodir: string, imdbphotoroot: string, imdbusecache: string, imdbcachedetailshidden: string}
 * @phpstan-type OPTIONS_DATA array{imdbwidgettitle: string, imdbwidgetpic: string,imdbwidgetruntime: string, imdbwidgetdirector: string, imdbwidgetcountry: string, imdbwidgetactor:string, imdbwidgetactornumber:int|string, imdbwidgetcreator: string, imdbwidgetrating: string, imdbwidgetlanguage: string, imdbwidgetgenre: string, imdbwidgetwriter: string, imdbwidgetproducer: string, imdbwidgetproducernumber: bool|string, imdbwidgetkeyword: string, imdbwidgetprodcompany: string, imdbwidgetplot: string, imdbwidgetplotnumber: string, imdbwidgetgoof: string, imdbwidgetgoofnumber: string|bool, imdbwidgetcomment: string, imdbwidgetquote: string, imdbwidgetquotenumber: string|bool, imdbwidgettagline: string, imdbwidgettaglinenumber: string|bool, imdbwidgetcolor: string, imdbwidgetalsoknow: string, imdbwidgetalsoknownumber: string|bool, imdbwidgetcomposer: string, imdbwidgetsoundtrack: string, imdbwidgetsoundtracknumber: string|bool, imdbwidgetofficialsites: string, imdbwidgetsource: string, imdbwidgetyear: string, imdbwidgettrailer: string, imdbwidgettrailernumber: bool|string, imdbwidgetorder: array<string|int>, imdbtaxonomycolor: string, imdbtaxonomycomposer: string, imdbtaxonomycountry: string, imdbtaxonomycreator: string, imdbtaxonomydirector: string, imdbtaxonomygenre: string, imdbtaxonomykeyword: string, imdbtaxonomylanguage: string, imdbtaxonomyproducer: string, imdbtaxonomyactor: string, imdbtaxonomywriter: string}
 */
trait Settings_Global {

	/**
	 * Admin options vars
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 * @phpstan-var array{imdbplugindirectory: string, imdbplugindirectory_partial: string, imdbpluginpath: string,imdburlpopups: string,imdbkeepsettings: string,imdburlstringtaxo: string,imdbcoversize: string,imdbcoversizewidth: string, imdbmaxresults: string, imdbdelayimdbrequest: string, imdbpopuptheme: string, imdbpopuplarg: string,imdbpopuplong: string, imdbintotheposttheme: string, imdblinkingkill: string, imdbautopostwidget: string, imdblanguage: string, imdbdebug: string, imdbdebuglog: string, imdbdebuglogpath: string, imdbdebuglevel: string|'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY', imdbdebugscreen: string, imdbwordpress_bigmenu: string, imdbwordpress_tooladminmenu: string, imdbpopup_modal_window: string, imdbtaxonomy: string, imdbHowManyUpdates: string, imdbseriemovies: string, imdbirpdisplay: string }
	 */
	public array $imdb_admin_values;

	/**
	 * Data options
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_DATA $imdb_data_values
	 * @phpstan-var array{imdbwidgettitle: string, imdbwidgetpic: string,imdbwidgetruntime: string, imdbwidgetdirector: string, imdbwidgetcountry: string, imdbwidgetactor:string, imdbwidgetactornumber:int|string, imdbwidgetcreator: string, imdbwidgetrating: string, imdbwidgetlanguage: string, imdbwidgetgenre: string, imdbwidgetwriter: string, imdbwidgetproducer: string, imdbwidgetproducernumber: bool|string, imdbwidgetkeyword: string, imdbwidgetprodcompany: string, imdbwidgetplot: string, imdbwidgetplotnumber: string, imdbwidgetgoof: string, imdbwidgetgoofnumber: string|bool, imdbwidgetcomment: string, imdbwidgetquote: string, imdbwidgetquotenumber: string|bool, imdbwidgettagline: string, imdbwidgettaglinenumber: string|bool, imdbwidgetcolor: string, imdbwidgetalsoknow: string, imdbwidgetalsoknownumber: string|bool, imdbwidgetcomposer: string, imdbwidgetsoundtrack: string, imdbwidgetsoundtracknumber: string|bool, imdbwidgetofficialsites: string, imdbwidgetsource: string, imdbwidgetyear: string, imdbwidgettrailer: string, imdbwidgettrailernumber: bool|string, imdbwidgetorder: array<int|string>, imdbtaxonomycolor: string, imdbtaxonomycomposer: string, imdbtaxonomycountry: string, imdbtaxonomycreator: string, imdbtaxonomydirector: string, imdbtaxonomygenre: string, imdbtaxonomykeyword: string, imdbtaxonomylanguage: string, imdbtaxonomyproducer: string, imdbtaxonomyactor: string, imdbtaxonomywriter: string} $imdb_data_values
	 */
	public array $imdb_data_values;

	/**
	 * Cache options
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_CACHE $imdb_cache_values
	 * @phpstan-var array{imdbcachedir_partial: string, imdbusecache: string, imdbcacheexpire: string, imdbcacheautorefreshcron: string, imdbcachedetailsshort: string,imdbcachedir: string,imdbphotoroot: string, imdbphotodir: string, imdbcachekeepsizeunder: string, imdbcachekeepsizeunder_sizelimit: string, imdbcachedetailshidden: string} $imdb_cache_values
	 */
	public array $imdb_cache_values;

	/**
	 * Build database options properties
	 * @since 4.4 Added false checks and Settings::create_database_options(), since during a first install the Frontend may fail (according to WP Plugin Check)
	 */
	public function get_db_options(): void {

		$admin_values = get_option( Get_Options::get_admin_tablename() );
		$data_values = get_option( Get_Options::get_data_tablename() );
		$cache_values = get_option( Get_Options::get_cache_tablename() );

		if ( $admin_values === false || $data_values === false || $cache_values === false ) {
			Settings::create_database_options();
			$admin_values = get_option( Get_Options::get_admin_tablename() );
			$data_values = get_option( Get_Options::get_data_tablename() );
			$cache_values = get_option( Get_Options::get_cache_tablename() );
		}

		$this->imdb_admin_values = $admin_values;
		$this->imdb_data_values = $data_values;
		$this->imdb_cache_values = $cache_values;
	}
}

