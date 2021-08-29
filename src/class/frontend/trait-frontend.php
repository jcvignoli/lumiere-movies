<?php declare( strict_types = 1 );
/**
 * Frontend Trait for pages including movies
 * Popups, movies are using this trait
 * Allow to use the logger, function utilities, and settings
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere;

use \Lumiere\Settings;
use \Lumiere\Utils;
use \Lumiere\Logger;

trait Frontend {

	/**
	 * Admin options
	 * @var array{ 'imdbplugindirectory': string, 'imdbplugindirectory_partial': string, 'imdbpluginpath': string,'imdburlpopups': string,'imdbkeepsettings': string,'imdburlstringtaxo': string,'imdbcoversize': string,'imdbcoversizewidth': string, 'imdbmaxresults': int, 'imdbpopuptheme': string, 'imdbpopuplarg': string,'imdbpopuplong': string, 'imdbintotheposttheme': string, 'imdblinkingkill': string, 'imdbautopostwidget': string, 'imdblanguage': string, 'imdbdebug': string, 'imdbdebuglog': string, 'imdbdebuglogpath': string, 'imdbdebuglevel': string, 'imdbdebugscreen': string, 'imdbwordpress_bigmenu': string, 'imdbwordpress_tooladminmenu': string, 'imdbpopup_highslide': string, 'imdbtaxonomy': string, 'imdbHowManyUpdates': int, 'imdbseriemovies': string } $imdb_admin_values
	 */
	public array $imdb_admin_values;

	/**
	 * Widget options
	 * @var array<string> $imdb_widget_values
	 */
	public array $imdb_widget_values;

	/**
	 * Cache options
	 * @var array<string> $imdb_cache_values
	 */
	public array $imdb_cache_values;

	/**
	 * Class \Lumiere\Utils
	 *
	 */
	public Utils $utils_class;

	/**
	 * Class \Lumiere\Settings
	 *
	 */
	public Settings $config_class;

	/**
	 * Class \Lumiere\Logger
	 *
	 */
	public Logger $logger;

	/**
	 * Is the current page an editing page?
	 */
	private bool $is_editor_page = false;

	/**
	 * Constructor
	 *
	 * @param string $logger_name Title of Monolog logger
	 * @param bool $screenOutput whether to output Monolog on screen or not
	 */
	public function __construct( ?string $logger_name = 'unknownOrigin', ?bool $screenOutput = true ) {

		// Get database options
		$this->imdb_admin_values = get_option( Settings::LUMIERE_ADMIN_OPTIONS );
		$this->imdb_widget_values = get_option( Settings::LUMIERE_WIDGET_OPTIONS );
		$this->imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

		// Start Logger class.
		$this->logger = new Logger( $logger_name, $screenOutput );

		// Start Settings class.
		$this->config_class = new Settings();

		// Start Utils class.
		$this->utils_class = new Utils();

		// Start the debugging
		add_action( 'init', [ $this, 'lumiere_frontend_is_editor' ], 0 );

		// Start the debugging
		add_action( 'init', [ $this, 'lumiere_frontend_maybe_start_debug' ], 1 );
	}

	/**
	 *  Wrap the debugging process and logging
	 */
	public function lumiere_frontend_is_editor(): void {

		$referer = strlen( $_SERVER['REQUEST_URI'] ) > 0 ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$pages_prohibited = [ '/wp-admin/admin-ajax.php', '/wp-admin/post.php', '/wp-json/wp/v2/posts' ];
		if ( Utils::lumiere_array_contains_term( $pages_prohibited, $_SERVER['REQUEST_URI'] ) ) {

			$this->is_editor_page = true;

		}

	}

	/**
	 *  Start debug if conditions are met
	 */
	public function lumiere_frontend_maybe_start_debug(): bool {

		// If editor page, exit.
		// Useful for block editor pages (gutenberg).
		if ( $this->is_editor_page === true ) {
			return false;
		}

		// If the user can't manage options and it's not a cron, exit.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// If debug is active.
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( '1' === $this->imdb_admin_values['imdbdebug'] ) && ( $this->utils_class->debug_is_active === false ) ) {

			$this->utils_class->lumiere_activate_debug();
			return true;

		}

		return false;
	}

}

