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
	 * Wrap the debugging process and logging
	 */
	public function lumiere_frontend_is_editor(): void {

		$referer = strlen( $_SERVER['REQUEST_URI'] ) > 0 ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$pages_prohibited = [ '/wp-admin/admin-ajax.php', '/wp-admin/post.php', '/wp-json/wp/v2/posts' ];
		if ( Utils::lumiere_array_contains_term( $pages_prohibited, $_SERVER['REQUEST_URI'] ) ) {

			$this->is_editor_page = true;

		}

	}

	/**
	 * Start debug if conditions are met
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
	 * Convert an IMDb url into an internal link for People and Movies
	 * Meant to be used inside popups (not in posts or widgets)
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 */
	protected function lumiere_imdburl_to_internalurl ( string $text ): string {

		// Internal links.
		$internal_link_person = '<a class="link-popup" href="' . $this->config_class->lumiere_urlpopupsperson . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';
		$internal_link_movie = '<a class="link-popup" href="' . $this->config_class->lumiere_urlpopupsfilms . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';

		// Regexes. \D{21} 21 characters for 'https://www.imdb.com/'.
		$rule_name = '~(<a href=\")(\D{21})(name\/nm)(\d{7})(\?.+?|\/?)\"\>~';
		$rule_title = '~(<a href=\")(\D{21})(title\/tt)(\d{7})(\?ref.+?|\/?)\"\>~';

		// Replace IMDb links with internal links.
		$output_one = preg_replace( $rule_name, $internal_link_person, $text ) ?? $text;
		$output_two = preg_replace( $rule_title, $internal_link_movie, $output_one ) ?? $text;

		return $output_two;
	}

	/**
	 * Convert an IMDb url into a Popup link for People and Movies
	 * Meant to be used inside in posts or widgets (not in Popups)
	 * Build links using either highslide or classic popup
	 *
	 * @param string $text Text that includes IMDb URL to convert into a popup link
	 */
	protected function lumiere_imdburl_to_popupurl ( string $text ): string {

		// Initialize variables.
		$popup_link_person = '';
		$popup_link_movie = '';

		// highslide popup.
		if ( $this->imdb_admin_values['imdbpopup_highslide'] === '1' ) {
			$popup_link_person = '<a class="link-imdblt-highslidepeople highslide" data-highslidepeople="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">';
			$popup_link_movie = '<a class="link-imdblt-highslidefilm highslide" data-highslidefilm-id="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">';

			// classic popup.
		} elseif ( $this->imdb_admin_values['imdbpopup_highslide'] === '0' ) {
			$popup_link_person = '<a class="link-imdblt-classicpeople" data-classicpeople="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">';
			$popup_link_movie = '<a class="link-imdblt-classicfilm" data-classicfilm-id="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">';
		}

		// Regexes. \D{21} 21 characters for 'https://www.imdb.com/'.
		$rule_name = '~(<a href=\")(\D{21})(name\/nm)(\d{7})(\/\?.+?|\?.+?|\/?)\"\>~';
		$rule_title = '~(<a href=\")(\D{21})(title\/tt)(\d{7})(\?ref.+?|\/?)\"\>~';

		// Replace IMDb links with popup links.
		$output_one = preg_replace( $rule_name, $popup_link_person, $text ) ?? $text;
		$output_two = preg_replace( $rule_title, $popup_link_movie, $output_one ) ?? $text;

		return $output_two;
	}

	/**
	 * Display biographical text
	 * 1- Cut the maximum of characters to be displayed with $click_text
	 * 2- Detect if there is html tags that can break with $esc_html_breaker
	 * 3- Build links either to internal (popups) or popups (inside posts/widgets) with $popup_links
	 *
	 * @param array<array> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 * @param bool $popup_links  If links should be internal or popups. Internal (false) by default.
	 * @phpstan-ignore-next-line PHPStan complains about $bio_array not defined, but it is!
	 */
	protected function lumiere_medaillon_bio ( array $bio_array, bool $popup_links = false ): ?string {

		/** Vars */
		$click_text = esc_html__( 'click to expand', 'lumiere-movies' ); // text for cutting.
		$max_length = 200; // maximum number of characters before cutting.

		$nbtotalbio = count( $bio_array );
		$bio = $nbtotalbio !== 0 ? $bio_array : null;

		// If no bio, exit.
		if ( $nbtotalbio === 0 ) {

			return null;

		}

		// Make sure that bio description returns internal links and no IMDb's.
		$bio_head = '';
		$bio_text = '';
		if ( $popup_links === false && $bio !== null ) {

			$bio_text = $this->lumiere_imdburl_to_internalurl( $bio[0]['desc'] );

		} elseif ( $popup_links === true && $bio !== null ) {

			$bio_text = $this->lumiere_imdburl_to_popupurl( $bio[0]['desc'] );
		}

		// HTML tags break the 'read more'. Detects if there is html and reduce the $max_length.
		$esc_html_breaker = strpos( $bio_text, '<' ) !== false ? strpos( $bio_text, '<' ) : $max_length;

		if ( strlen( $bio_text ) !== 0 && strlen( $bio_text ) > $esc_html_breaker ) {

			$bio_head = "\n\t\t\t" . '<span class="imdbincluded-subtitle">'
				. esc_html__( 'Biography', 'lumiere-movies' )
				. '</span>';

			$str_one = substr( $bio_text, 0, $esc_html_breaker );
			$str_two = substr( $bio_text, $esc_html_breaker, strlen( $bio_text ) );

			$bio_text = "\n\t\t\t" . $str_one
				. "\n\t\t\t" . '<span class="activatehidesection"><strong>&nbsp;(' . $click_text . ')</strong></span> '
				. "\n\t\t\t" . '<span class="hidesection">'
				. "\n\t\t\t" . $str_two
				. "\n\t\t\t" . '</span>';

		}

		return $bio_head . $bio_text;

	}
}

