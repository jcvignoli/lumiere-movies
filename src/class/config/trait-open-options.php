<?php declare( strict_types = 1 );
/**
 * Settings Trait for including database options
 *
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */

namespace Lumiere\Config;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) && ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Get_Options;
use Lumiere\Config\Get_Options_Movie;

/**
 * Trait for including database options
 * Most pages that need any of admin, cache or widget options are using it
 *
 * Below can't be imported, it might be related to PHPStan bug #5091
 * @phpstan-import-type OPTIONS_ADMIN from \Lumiere\Config\Settings
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Config\Settings
 * @phpstan-import-type OPTIONS_DATA_MOVIE from \Lumiere\Config\Settings_Movie
 * @psalm-import-type OPTIONS_DATA_MOVIE_PSALM from \Lumiere\Config\Settings_Movie
 * @phpstan-import-type OPTIONS_DATA_PERSON from \Lumiere\Config\Settings_Person
 * @psalm-import-type OPTIONS_DATA_PERSON_PSALM from \Lumiere\Config\Settings_Person
 */
trait Open_Options {

	/**
	 * Admin options vars
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 * @phpstan-var array{imdbHowManyUpdates: string, imdbautopostwidget: '0'|'1'|string, imdbcoversize: '0'|'1'|string, imdbcoversizewidth: string, imdbdebug: '0'|'1'|string, imdbdebuglevel: 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY', imdbdebuglog: '0'|'1'|string, imdbdebuglogpath: mixed, imdbdebugscreen:'0'|'1'|string, imdbdelayimdbrequest: '0'|'1'|string, imdbintotheposttheme: string, imdbirpdisplay: '0'|'1'|string, imdbkeepsettings: '0'|'1'|string, imdblanguage: string, imdblinkingkill: '0'|'1'|string, imdbmaxresults: string, imdbplugindirectory: string, imdbplugindirectory_partial: string, imdbpluginpath: mixed, imdbpopup_modal_window: string, imdbpopuplarg: string, imdbpopuplong: string, imdbpopuptheme: string, imdbseriemovies: 'movies'|'series'|'movies+series'|'videogames', imdbtaxonomy: '0'|'1'|string, imdburlpopups: string, imdburlstringtaxo: string, imdbwordpress_bigmenu: '0'|'1'|string, imdbwordpress_tooladminmenu: '0'|'1'|string}
	 * @psalm-var OPTIONS_ADMIN
	 */
	public array $imdb_admin_values;

	/**
	 * Data Movie options
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_DATA_MOVIE $imdb_data_values
	 * @phpstan-var array{'imdbtaxonomyactor'?:string, 'imdbtaxonomycolor'?:string, 'imdbtaxonomycomposer'?:string, 'imdbtaxonomycountry'?:string, 'imdbtaxonomycinematographer'?:string, 'imdbtaxonomydirector'?:string, 'imdbtaxonomygenre'?:string, 'imdbtaxonomykeyword'?:string, 'imdbtaxonomylanguage'?:string, 'imdbtaxonomyproducer'?:string, 'imdbtaxonomywriter'?:string, 'imdbwidgetactor'?:string, 'imdbwidgetactornumber'?:string, 'imdbwidgetalsoknow'?:string, 'imdbwidgetalsoknownumber'?:string, 'imdbwidgetcolor'?:string, 'imdbwidgetcomposer'?:string, 'imdbwidgetconnection'?:string, 'imdbwidgetconnectionnumber'?:string, 'imdbwidgetcountry'?:string, 'imdbwidgetcinematographer'?:string, 'imdbwidgetdirector'?:string, 'imdbwidgetgenre'?:string, 'imdbwidgetgoof'?:string, 'imdbwidgetgoofnumber'?:string, 'imdbwidgetkeyword'?:string, 'imdbwidgetlanguage'?:string, 'imdbwidgetextSites'?:string, 'imdbwidgetpic'?:string, 'imdbwidgetplot'?:string, 'imdbwidgetplotnumber'?:string, 'imdbwidgetprodCompany'?:string, 'imdbwidgetproducer'?:string, 'imdbwidgetproducernumber'?:string, 'imdbwidgetquote'?:string, 'imdbwidgetquotenumber'?:string, 'imdbwidgetrating'?:string, 'imdbwidgetruntime'?:string, 'imdbwidgetsoundtrack'?:string, 'imdbwidgetsoundtracknumber'?:string, 'imdbwidgetsource'?:string, 'imdbwidgettagline'?:string, 'imdbwidgettaglinenumber'?:string, 'imdbwidgettitle'?:string, 'imdbwidgettrailer'?:string, 'imdbwidgettrailernumber'?:string, 'imdbwidgettrivia'?:string, 'imdbwidgettrivianumber'?:string, 'imdbwidgetwriter'?:string, 'imdbwidgetwriternumber'?:string, 'imdbwidgetyear'?:string,'imdbwidgetorder': array{title?: string, pic?: string, runtime?: string, director?: string, connection?: string, country?: string, actor?: string, cinematographer?: string, rating?: string, language?: string, genre?: string, writer?: string, producer?: string, keyword?: string, prodCompany?: string, plot?: string, goof?: string, quote?: string, tagline?: string, trailer?: string, color?: string, alsoknow?: string, composer?: string, soundtrack?: string, extSites?: string, source?: string, trivia?: string, year?: string} }
	 * @psalm-var OPTIONS_DATA_MOVIE_PSALM
	 */
	public array $imdb_data_values;

	/**
	 * Data Person options
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_DATA_PERSON $imdb_data_person_values
	 * @phpstan-var array{ activated: array{ title_active: '1', pic_active: '1', bio_active: '0'|'1', nickname_active: '0'|'1', spouse_active?: '0'|'1', children_active?: '0'|'1', credit_active?: '0'|'1', news_active?: '0'|'1', pubinterview_active?: '0'|'1', pubmovies_active?: '0'|'1', pubportrayal_active?: '0'|'1', pubprints_active?: '0'|'1', quotes_active?: '0'|'1', trivia_active?: '0'|'1', trademark_active?: '0'|'1', award_active?: '0'|'1' }, order: array{ 'title': numeric-string, 'pic': numeric-string, 'bio': numeric-string, 'nickname': numeric-string, 'spouse': numeric-string, 'children': numeric-string, 'credit': numeric-string, 'news': numeric-string, 'pubinterview': numeric-string, 'pubmovies': numeric-string, 'pubportrayal': numeric-string, 'pubprints': numeric-string, 'quotes': numeric-string, 'trivia': numeric-string, 'trademark': numeric-string, 'award': numeric-string, 'birthname': numeric-string, 'born': numeric-string, 'died': numeric-string, 'name': numeric-string }, number: array{ award_number: numeric-string, credit_number: numeric-string, news_number: numeric-string, pubinterview_number: numeric-string, pubmovies_number: numeric-string, pubportrayal_number: numeric-string, pubprints_number: numeric-string, quotes_number: numeric-string, trivia_number: numeric-string, trademark_number: numeric-string } }
	 * @psalm-var OPTIONS_DATA_PERSON_PSALM
	 */
	public array $imdb_data_person_values;

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
		$this->imdb_admin_values       = get_option( Get_Options::get_admin_tablename(), [] );
		$this->imdb_data_values        = get_option( Get_Options_Movie::get_data_tablename(), [] );
		$this->imdb_data_person_values = get_option( Get_Options_Person::get_data_person_tablename(), [] );
		$this->imdb_cache_values       = get_option( Get_Options::get_cache_tablename(), [] );
	}
}

