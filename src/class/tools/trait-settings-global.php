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

/**
 * Trait for including database options
 * Most pages that need any of admin, cache or widget options are using it
 *
 * Below can't be imported, it might be related to PHPStan bug #5091
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Settings
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Settings
 * @phpstan-import-type OPTIONS_DATA from \Lumiere\Settings
 */
trait Settings_Global {

	/**
	 * Admin options vars
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 * @phpstan-var array{imdbplugindirectory: string, imdbplugindirectory_partial: string, imdbpluginpath: string,imdburlpopups: string,imdbkeepsettings: string,imdburlstringtaxo: string,imdbcoversize: string,imdbcoversizewidth: string, imdbmaxresults: string, imdbdelayimdbrequest: string, imdbpopuptheme: string, imdbpopuplarg: string,imdbpopuplong: string, imdbintotheposttheme: string, imdblinkingkill: string, imdbautopostwidget: string, imdblanguage: string, imdbdebug: string, imdbdebuglog: string, imdbdebuglogpath: string, imdbdebuglevel: string|'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY', imdbdebugscreen: string, imdbwordpress_bigmenu: string, imdbwordpress_tooladminmenu: string, imdbpopup_modal_window: string, imdbtaxonomy: string, imdbHowManyUpdates: string, imdbseriemovies: string, imdbirpdisplay: string }
	 * @psalm-var OPTIONS_ADMIN
	 */
	public array $imdb_admin_values;

	/**
	 * Data options
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_DATA $imdb_data_values
	 * @phpstan-var array{'imdbtaxonomyactor':string, 'imdbtaxonomycolor':string, 'imdbtaxonomycomposer':string, 'imdbtaxonomycountry':string, 'imdbtaxonomycreator':string, 'imdbtaxonomydirector':string, 'imdbtaxonomygenre':string, 'imdbtaxonomykeyword':string, 'imdbtaxonomylanguage':string, 'imdbtaxonomyproducer':string, 'imdbtaxonomywriter':string, 'imdbwidgetactor':string, 'imdbwidgetactornumber':numeric-string, 'imdbwidgetalsoknow':string, 'imdbwidgetalsoknownumber':numeric-string, 'imdbwidgetcolor':string, 'imdbwidgetcomment':string, 'imdbwidgetcomposer':string, 'imdbwidgetconnection':string, 'imdbwidgetconnectionnumber':string, 'imdbwidgetcountry':string, 'imdbwidgetcreator':string, 'imdbwidgetdirector':string, 'imdbwidgetgenre':string, 'imdbwidgetgoof':string, 'imdbwidgetgoofnumber':numeric-string, 'imdbwidgetkeyword':string, 'imdbwidgetlanguage':string, 'imdbwidgetofficialsites':string, 'imdbwidgetpic':string, 'imdbwidgetplot':string, 'imdbwidgetplotnumber':numeric-string, 'imdbwidgetprodcompany':string, 'imdbwidgetproducer':string, 'imdbwidgetproducernumber':numeric-string, 'imdbwidgetquote':string, 'imdbwidgetquotenumber':numeric-string, 'imdbwidgetrating':string, 'imdbwidgetruntime':string, 'imdbwidgetsoundtrack':string, 'imdbwidgetsoundtracknumber':numeric-string, 'imdbwidgetsource':string, 'imdbwidgettagline':string, 'imdbwidgettaglinenumber':numeric-string, 'imdbwidgettitle':string, 'imdbwidgettrailer':string, 'imdbwidgettrailernumber':numeric-string, 'imdbwidgetwriter':string, 'imdbwidgetyear':string,'imdbwidgetorder': array{'actor': numeric-string, 'alsoknow': numeric-string, 'color': numeric-string, 'composer': numeric-string, 'connection': numeric-string, 'country': numeric-string, 'creator': numeric-string, 'director': numeric-string, 'genre': numeric-string, 'goof': numeric-string, 'keyword': numeric-string, 'language': numeric-string, 'officialsites': numeric-string, 'pic': numeric-string, 'plot': numeric-string, 'prodcompany': numeric-string, 'producer': numeric-string, 'quote': numeric-string, 'rating': numeric-string, 'runtime': numeric-string, 'soundtrack': numeric-string, 'source': numeric-string, 'tagline': numeric-string, 'title': numeric-string, 'trailer': numeric-string, 'writer': numeric-string } }
	 * @psalm-var OPTIONS_DATA
	 */
	public array $imdb_data_values;

	/**
	 * Cache options
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_CACHE $imdb_cache_values
	 * @phpstan-var array{imdbcachedir_partial: string, imdbusecache: string, imdbcacheexpire: string, imdbcacheautorefreshcron: string, imdbcachedetailsshort: string,imdbcachedir: string,imdbphotoroot: string, imdbphotodir: string, imdbcachekeepsizeunder: string, imdbcachekeepsizeunder_sizelimit: string, imdbcachedetailshidden: string}
	 * @psalm-var OPTIONS_CACHE
	 */
	public array $imdb_cache_values;

	/**
	 * Build database options properties
	 * @since 4.4 Added fake checks and Settings::create_database_options(), since during a first install the Frontend may fail (according to WP Plugin Check)
	 */
	public function get_db_options(): void {

		$admin_values = get_option( Get_Options::get_admin_tablename() );
		$data_values = get_option( Get_Options::get_data_tablename() );
		$cache_values = get_option( Get_Options::get_cache_tablename() );

		if ( $admin_values === false || $data_values === false || $cache_values === false ) {
			Get_Options::create_database_options();
			$admin_values = get_option( Get_Options::get_admin_tablename() );
			$data_values = get_option( Get_Options::get_data_tablename() );
			$cache_values = get_option( Get_Options::get_cache_tablename() );
		}

		$this->imdb_admin_values = $admin_values;
		$this->imdb_data_values = $data_values;
		$this->imdb_cache_values = $cache_values;
	}
}

