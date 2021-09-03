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

use \Lumiere\Utils;
use \Lumiere\Logger;
use \Lumiere\Imdbphp;

trait Frontend {

	use \Lumiere\Settings_Global;

	/**
	 * Class \Lumiere\Utils
	 *
	 */
	public Utils $utils_class;

	/**
	 * Class \Lumiere\Imdbphp
	 *
	 */
	public Imdbphp $imdbphp_class;

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

		// Build Global settings.
		$this->settings_open();

		// Start Logger class.
		$this->logger = new Logger( $logger_name, $screenOutput );

		// Start Utils class.
		$this->utils_class = new Utils();

		// Start Imdbphp class.
		$this->imdbphp_class = new Imdbphp();

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

	/**
	 * Convert an IMDbPHP result linking to IMDb website into a highslide/classic popup link
	 *
	 * @param string $convert Link to be converted into popup highslide link
	 */

	protected function lumiere_convert_txtwithhtml_into_popup_people ( string $convert ): string {

		// highslide popup.
		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {
			$result = '<a class="link-imdblt-highslidepeople highslide" data-highslidepeople="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">';

			// classic popup.
		} else {
			$result = '<a class="link-imdblt-classicpeople" data-classicpeople="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">';
		}

		$convert = preg_replace( '~(<a href=)(.+?)(name\/nm)(\d{7})\/\"\>~', $result, $convert ) ?? $convert;

		return $convert;
	}

	/**
	 * Convert an IMDbPHP result linking to IMDb website into a highslide/classic popup link
	 *
	 * @param string $convert Link to be converted into popup highslide link
	 */

	protected function lumiere_convert_txtwithhtml_into_internal_person ( string $convert ): string {

		$result = '<a class="link-popup" href="' . $this->config_class->lumiere_urlpopupsperson . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';

		$convert = preg_replace( '~(<a href=)(.+?)(name\/nm)(\d{7})\/\"\>~', $result, $convert ) ?? $convert;

		return $convert;
	}

}

