<?php declare( strict_types = 1 );
/**
 * Settings Trait for including database options
 * All pages that need any of admin, cache or widget options are using it
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Settings;

/**
 * Below doesn't work, it's a PHPStan bug #5091
 * @phpstan-import-type OPTIONS_ADMIN from Settings
 * @phpstan-import-type OPTIONS_CACHE from Settings
 * @phpstan-import-type OPTIONS_WIDGET from Settings
 */
trait Settings_Global {

	/**
	 * Admin options vars
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_ADMIN $imdb_admin_values
	 * @phpstan-var array{'imdbplugindirectory': string, 'imdbplugindirectory_partial': string, 'imdbpluginpath': string,'imdburlpopups': string,'imdbkeepsettings': string,'imdburlstringtaxo': string,'imdbcoversize': string,'imdbcoversizewidth': string, 'imdbmaxresults': int, 'imdbdelayimdbrequest': int, 'imdbpopuptheme': string, 'imdbpopuplarg': string,'imdbpopuplong': string, 'imdbintotheposttheme': string, 'imdblinkingkill': string, 'imdbautopostwidget': string, 'imdblanguage': string, 'imdbdebug': string, 'imdbdebuglog': string, 'imdbdebuglogpath': string, 'imdbdebuglevel': 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY', 'imdbdebugscreen': string, 'imdbwordpress_bigmenu': string, 'imdbwordpress_tooladminmenu': string, 'imdbpopup_modal_window': string, 'imdbtaxonomy': string, 'imdbHowManyUpdates': int, 'imdbseriemovies': string}
	 */
	public array $imdb_admin_values;

	/**
	 * Widget options
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_WIDGET $imdb_widget_values
	 * @phpstan-var array{'imdbwidgettitle': string, 'imdbwidgetpic': string,'imdbwidgetruntime': string, 'imdbwidgetdirector': string, 'imdbwidgetcountry': string, 'imdbwidgetactor':string, 'imdbwidgetactornumber':int, 'imdbwidgetcreator': string, 'imdbwidgetrating': string, 'imdbwidgetlanguage': string, 'imdbwidgetgenre': string, 'imdbwidgetwriter': string, 'imdbwidgetproducer': string, 'imdbwidgetproducernumber': bool|string, 'imdbwidgetkeyword': string, 'imdbwidgetprodcompany': string, 'imdbwidgetplot': string, 'imdbwidgetplotnumber': string, 'imdbwidgetgoof': string, 'imdbwidgetgoofnumber': string|bool, 'imdbwidgetcomment': string, 'imdbwidgetquote': string, 'imdbwidgetquotenumber': string|bool, 'imdbwidgettagline': string, 'imdbwidgettaglinenumber': string|bool, 'imdbwidgetcolor': string, 'imdbwidgetalsoknow': string, 'imdbwidgetalsoknownumber': string|bool, 'imdbwidgetcomposer': string, 'imdbwidgetsoundtrack': string, 'imdbwidgetsoundtracknumber': string|bool, 'imdbwidgetofficialsites': string, 'imdbwidgetsource': string, 'imdbwidgetyear': string, 'imdbwidgettrailer': string, 'imdbwidgettrailernumber': bool|string, 'imdbwidgetorder': array<string>, 'imdbtaxonomycolor': string, 'imdbtaxonomycomposer': string, 'imdbtaxonomycountry': string, 'imdbtaxonomycreator': string, 'imdbtaxonomydirector': string, 'imdbtaxonomygenre': string, 'imdbtaxonomykeyword': string, 'imdbtaxonomylanguage': string, 'imdbtaxonomyproducer': string, 'imdbtaxonomyactor': string, 'imdbtaxonomywriter': string} $imdb_widget_values
	 */
	public array $imdb_widget_values;

	/**
	 * Cache options
	 * // PHPStan bug #5091, remove below line later @phpstan-var OPTIONS_CACHE $imdb_widget_values
	 * @phpstan-var array{'imdbcachedir_partial': string, 'imdbusecache': string, 'imdbcacheexpire': string, 'imdbcacheautorefreshcron': string, 'imdbcachedetailsshort': string,'imdbcachedir': string,'imdbphotoroot': string, 'imdbphotodir': string, 'imdbcachekeepsizeunder': string, 'imdbcachekeepsizeunder_sizelimit': string } $imdb_cache_values
	 */
	public array $imdb_cache_values;

	/**
	 * Class \Lumiere\Settings
	 *
	 */
	public Settings $config_class;

	/**
	 * Constructor function
	 *
	 * Build the trait properties
	 */
	public function settings_open(): void {

		// Start Utils class.
		$this->config_class = new Settings();

		// Get database options.
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		$this->imdb_widget_values = get_option( Settings::LUMIERE_WIDGET_OPTIONS );
		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

	}

}

