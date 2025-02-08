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
	 * @phpstan-var array{imdbHowManyUpdates: string, imdbautopostwidget: '0'|'1'|string, imdbcoversize: '0'|'1'|string, imdbcoversizewidth: string, imdbdebug: '0'|'1'|string, imdbdebuglevel: 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY'|string, imdbdebuglog: '0'|'1'|string, imdbdebuglogpath: mixed, imdbdebugscreen:'0'|'1'|string, imdbdelayimdbrequest: '0'|'1'|string, imdbintotheposttheme: string, imdbirpdisplay: '0'|'1'|string, imdbkeepsettings: '0'|'1'|string, imdblanguage: string, imdblinkingkill: '0'|'1'|string, imdbmaxresults: string, imdbplugindirectory: string, imdbplugindirectory_partial: string, imdbpluginpath: mixed, imdbpopup_modal_window: string, imdbpopuplarg: string, imdbpopuplong: string, imdbpopuptheme: string, imdbseriemovies: string, imdbtaxonomy: '0'|'1'|string, imdburlpopups: string, imdburlstringtaxo: string, imdbwordpress_bigmenu: '0'|'1'|string, imdbwordpress_tooladminmenu: '0'|'1'|string}
	 * @psalm-var OPTIONS_ADMIN
	 */
	public array $imdb_admin_values;

	/**
	 * Data options
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_DATA $imdb_data_values
	 * @phpstan-var array{'imdbtaxonomyactor'?:string, 'imdbtaxonomycolor'?:string, 'imdbtaxonomycomposer'?:string, 'imdbtaxonomycountry'?:string, 'imdbtaxonomycreator'?:string, 'imdbtaxonomydirector'?:string, 'imdbtaxonomygenre'?:string, 'imdbtaxonomykeyword'?:string, 'imdbtaxonomylanguage'?:string, 'imdbtaxonomyproducer'?:string, 'imdbtaxonomywriter'?:string, 'imdbwidgetactor'?:string, 'imdbwidgetactornumber'?:string, 'imdbwidgetalsoknow'?:string, 'imdbwidgetalsoknownumber'?:string, 'imdbwidgetcolor'?:string, 'imdbwidgetcomment'?:string, 'imdbwidgetcomposer'?:string, 'imdbwidgetconnection'?:string, 'imdbwidgetconnectionnumber'?:string, 'imdbwidgetcountry'?:string, 'imdbwidgetcreator'?:string, 'imdbwidgetdirector'?:string, 'imdbwidgetgenre'?:string, 'imdbwidgetgoof'?:string, 'imdbwidgetgoofnumber'?:string, 'imdbwidgetkeyword'?:string, 'imdbwidgetlanguage'?:string, 'imdbwidgetofficialsites'?:string, 'imdbwidgetpic'?:string, 'imdbwidgetplot'?:string, 'imdbwidgetplotnumber'?:string, 'imdbwidgetprodcompany'?:string, 'imdbwidgetproducer'?:string, 'imdbwidgetproducernumber'?:string, 'imdbwidgetquote'?:string, 'imdbwidgetquotenumber'?:string, 'imdbwidgetrating'?:string, 'imdbwidgetruntime'?:string, 'imdbwidgetsoundtrack'?:string, 'imdbwidgetsoundtracknumber'?:string, 'imdbwidgetsource'?:string, 'imdbwidgettagline'?:string, 'imdbwidgettaglinenumber'?:string, 'imdbwidgettitle'?:string, 'imdbwidgettrailer'?:string, 'imdbwidgettrailernumber'?:string, 'imdbwidgetwriter'?:string, 'imdbwidgetyear'?:string,'imdbwidgetorder': array{title?: string, pic?: string, runtime?: string, director?: string, connection?: string, country?: string, actor?: string, creator?: string, rating?: string, language?: string, genre?: string, writer?: string, producer?: string, keyword?: string, prodcompany?: string, plot?: string, goof?: string, comment?: string, quote?: string, tagline?: string, trailer?: string, color?: string, alsoknow?: string, composer?: string, soundtrack?: string, officialsites?: string, source?: string, year?: string} }
	 * @psalm-var OPTIONS_DATA
	 */
	public array $imdb_data_values;

	/**
	 * Cache options
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_CACHE $imdb_cache_values
	 * @phpstan-var array{ 'imdbcacheautorefreshcron': string, 'imdbcachedetailsshort': string, 'imdbcachedir': string, 'imdbcachedir_partial': string, 'imdbcacheexpire': string, 'imdbcachekeepsizeunder': string, 'imdbcachekeepsizeunder_sizelimit': string, 'imdbphotodir': string, 'imdbphotoroot': string, 'imdbusecache': string, 'imdbcachedetailshidden': string}
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

