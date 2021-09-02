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

use \Lumiere\Settings;

trait Settings_Global {

	/**
	 * Admin options vars
	 * @var array{ 'imdbplugindirectory': string, 'imdbplugindirectory_partial': string, 'imdbpluginpath': string,'imdburlpopups': string,'imdbkeepsettings': string,'imdburlstringtaxo': string,'imdbcoversize': string,'imdbcoversizewidth': string, 'imdbmaxresults': int, 'imdbpopuptheme': string, 'imdbpopuplarg': string,'imdbpopuplong': string, 'imdbintotheposttheme': string, 'imdblinkingkill': string, 'imdbautopostwidget': string, 'imdblanguage': string, 'imdbdebug': string, 'imdbdebuglog': string, 'imdbdebuglogpath': string, 'imdbdebuglevel': string, 'imdbdebugscreen': string, 'imdbwordpress_bigmenu': string, 'imdbwordpress_tooladminmenu': string, 'imdbpopup_highslide': string, 'imdbtaxonomy': string, 'imdbHowManyUpdates': int, 'imdbseriemovies': string } $imdb_admin_values
	 */
	public array $imdb_admin_values;

	/**
	 * Widget options
	 * @var array{'imdbwidgettitle': string, 'imdbwidgetpic': string,'imdbwidgetruntime': string, 'imdbwidgetdirector': string, 'imdbwidgetcountry': string, 'imdbwidgetactor':string, 'imdbwidgetactornumber':int, 'imdbwidgetcreator': string, 'imdbwidgetrating': string, 'imdbwidgetlanguage': string, 'imdbwidgetgenre': string, 'imdbwidgetwriter': string, 'imdbwidgetproducer': string, 'imdbwidgetproducernumber': bool|string, 'imdbwidgetkeyword': string, 'imdbwidgetprodcompany': string, 'imdbwidgetplot': string, 'imdbwidgetplotnumber': string, 'imdbwidgetgoof': string, 'imdbwidgetgoofnumber': string|bool, 'imdbwidgetcomment': string, 'imdbwidgetquote': string, 'imdbwidgetquotenumber': string|bool, 'imdbwidgettagline': string, 'imdbwidgettaglinenumber': string|bool, 'imdbwidgetcolor': string, 'imdbwidgetalsoknow': string, 'imdbwidgetalsoknownumber': string|bool, 'imdbwidgetcomposer': string, 'imdbwidgetsoundtrack': string, 'imdbwidgetsoundtracknumber': string|bool, 'imdbwidgetofficialsites': string, 'imdbwidgetsource': string, 'imdbwidgetyear': string, 'imdbwidgettrailer': string, 'imdbwidgettrailernumber': bool|string, 'imdbwidgetorder': array<string>, 'imdbtaxonomycolor': string, 'imdbtaxonomycomposer': string, 'imdbtaxonomycountry': string, 'imdbtaxonomycreator': string, 'imdbtaxonomydirector': string, 'imdbtaxonomygenre': string, 'imdbtaxonomykeyword': string, 'imdbtaxonomylanguage': string, 'imdbtaxonomyproducer': string, 'imdbtaxonomyactor': string, 'imdbtaxonomywriter': string} $imdb_widget_values
	 */
	public array $imdb_widget_values;

	/**
	 * Cache options
	 * @var array{'imdbcachedir_partial': string, 'imdbstorecache': bool, 'imdbusecache': string, 'imdbconverttozip': bool, 'imdbusezip': bool, 'imdbcacheexpire': string, 'imdbcachedetailsshort': string,'imdbcachedir': string,'imdbphotoroot': string, 'imdbphotodir': string} $imdb_cache_values
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

