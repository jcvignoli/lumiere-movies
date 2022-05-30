<?php declare( strict_types = 1 );
/**
 * Class to build Highslide links
 * Is called by the Link Factory class, implements abstract Link Maker class
 *
 * This class is used when highslide option is ticked
 *
 * Highslide Popup links are created, included in taxonomy
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Settings;
use \Lumiere\Utils;

class Bootstrap_Links extends Abstract_Link_Maker {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

		// Registers javascripts and styles.
		add_action( 'init', [ $this, 'lumiere_bootstrap_register_assets' ], 0 );

		// Execute javascripts and styles only if the vars in lumiere_bootstrap_core were not already enqueued
		// (prevents a bug if the vars are displayed twice, the popup doesn't open).
		add_action(
			'wp_enqueue_scripts',
			function (): void {
				if ( ! wp_script_is( 'lumiere_bootstrap_core', 'enqueued' ) ) {
					$this->lumiere_bootstrap_execute_assets();
				}
			},
			0
		);

	}

	/**
	 *  Register frontpage scripts and styles
	 *
	 */
	public function lumiere_bootstrap_register_assets(): void {

		// Register bootstrap scripts and styles
		wp_register_script(
			'lumiere_bootstrap_core',
			$this->config_class->lumiere_js_dir . 'bootstrap/bootstrap.bundle.min.js',
			[],
			$this->config_class->lumiere_version,
			true
		);
		wp_enqueue_style(
			'lumiere_bootstrap_core',
			$this->config_class->lumiere_css_dir . 'bootstrap/bootstrap.min.css',
			[],
			$this->config_class->lumiere_version
		);
		wp_enqueue_style(
			'lumiere_bootstrap_custom',
			$this->config_class->lumiere_css_dir . 'bootstrap.min.css',
			[],
			$this->config_class->lumiere_version
		);

		// Register frontpage script
		wp_register_script(
			'lumiere_bootstrap_scripts',
			$this->config_class->lumiere_js_dir . 'lumiere_bootstrap_links.min.js',
			[],
			$this->config_class->lumiere_version,
			true
		);

		// Register frontpage script Popper
		wp_register_script(
			'lumiere_bootstrap_popper_scripts',
			$this->config_class->lumiere_js_dir . 'bootstrap/popper.min.js',
			[ 'lumiere_bootstrap_scripts' ],
			$this->config_class->lumiere_version,
			true
		);
	}

	/**
	 * Add the stylesheet & javascript to frontpage.
	 *
	 */
	public function lumiere_bootstrap_execute_assets (): void {

		// Prevent to load twice the script and lumiere_vars which breaks JS
		// Remove the script if the pages is a popup page
		if ( 0 === stripos( $_SERVER['REQUEST_URI'], site_url( '', 'relative' ) . $this->imdb_admin_values['imdburlpopups'] ) ) {
			wp_dequeue_script( 'lumiere_scripts' );
		}

		// Popper script must load before core
		// wp_enqueue_script( 'lumiere_bootstrap_popper_scripts' );

		wp_enqueue_style( 'lumiere_bootstrap_core' );

		wp_enqueue_style( 'lumiere_bootstrap_custom' );

		wp_enqueue_script( 'lumiere_bootstrap_core' );

		// Pass variables to javascript highslide-options.js.
		wp_add_inline_script(
			'lumiere_highslide_options',
			$this->config_class->lumiere_scripts_highslide_vars,
			'before',
		);

		wp_enqueue_script( 'lumiere_highslide_options' );

		wp_enqueue_script( 'lumiere_bootstrap_scripts' );

	}

	/**
	 * Build link to popup for IMDb people
	 *
	 * @param array<int, array<string, string>> $imdb_data_people Array with IMDB people data
	 * @param int $number The number of the loop $i
	 *
	 * @return string
	 */
	public function lumiere_link_popup_people ( array $imdb_data_people, int $number ): string {

		$output = '';

		// Building link.
		$output = "\n\t\t\t" . '<a class="linkincmovie link-imdblt-highslidepeople" data-bootstrappeople="' . sanitize_text_field( $imdb_data_people[ $number ]['imdb'] ) . '" data-target="#theModal' . sanitize_text_field( $imdb_data_people[ $number ]['imdb'] ) . '" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">' . sanitize_text_field( $imdb_data_people[ $number ]['name'] ) . '</a>';

		// Modal bootstrap HTML part.
		$output .= $this->bootstrap_modal( $imdb_data_people[ $number ]['imdb'], $imdb_data_people[ $number ]['name'] );
		return $output;

	}

	/**
	 * Build picture of the movie
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $movie_title Title of the movie
	 * @return string
	 */
	public function lumiere_link_picture ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string {

		$output = '';

		// Make sure $photo_localurl_true is a string so we can use esc_html() function
		$photo_localurl_true = is_string( $photo_localurl_true ) ? $photo_localurl_true : '';

		// Select picture: if 1/ big picture exists, so use it, use thumbnail otherwise
		$photo_url = $photo_localurl_false !== false && is_string( $photo_localurl_false ) ? esc_html( $photo_localurl_false ) : esc_html( $photo_localurl_true );

		// Select picture: if 2/ big/thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final = strlen( $photo_url ) === 0 ? esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ) : $photo_url;

		$output .= "\n\t\t\t" . '<div class="imdbelementPIC">';

		// Link
		$output .= "\n\t\t\t\t" . '<a class="bootstrap_pic" href="'
			. $photo_url_final
			. '" title="'
			. esc_attr( $movie_title )
			. '">';

		// Build image HTML tag <img>
		$output .= "\n\t\t\t\t\t" . '<img loading="eager" ';

		$output .= 'class="imdbelementPICimg" src="';

		$output .= $photo_url_final
			. '" alt="'
			. esc_html__( 'Photo of', 'lumiere-movies' )
			. ' '
			. esc_attr( $movie_title ) . '"';

		// add width only if "Display only thumbnail" is unactive
		// @since 3.7
		if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {

			$output .= ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '"';

			// add 100px width if "Display only thumbnail" is active
		} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

			$output .= ' width="100em"';

		}

		$output .= ' />';

		$output .= "\n\t\t\t\t</a>";

		$output .= "\n\t\t\t" . '</div>';

		return $output;

	}

	/**
	 * Build picture of the movie in taxonomy pages
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $person_name Name of the person
	 * @return string
	 */
	public function lumiere_link_picture_taxonomy ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $person_name ): string {

		$output = '';

		$output .= "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- star photo -->';

		$output .= "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		$output .= ' lumiere-lines-common_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		$output .= ' lumiere-padding-lines-common-picture">';

		// Make sure $photo_localurl_true is a string so we can use esc_html() function
		$photo_localurl_true = is_string( $photo_localurl_true ) ? $photo_localurl_true : '';

		// Select picture: if 1/ big picture exists, so use it, use thumbnail otherwise
		$photo_url = $photo_localurl_false !== false && is_string( $photo_localurl_false ) ? esc_html( $photo_localurl_false ) : esc_html( $photo_localurl_true );

		// Select picture: if 2/ big/thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final = strlen( $photo_url ) === 0 ? esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ) : $photo_url;

		$output .= "\n\t\t\t\t\t" . '<a id="bootstrap_pic" href="' . esc_url( $photo_url_final ) . '">';

		// Build image HTML tag <img>
		$output .= "\n\t\t\t\t\t\t" . '<img loading="eager" class="imdbincluded-picture lumiere_float_right" src="'
			. esc_url( $photo_url_final )
			. '" alt="'
			. esc_html__( 'Photo of', 'lumiere-movies' )
			. ' '
			. esc_attr( $person_name ) . '"';

		// add width only if "Display only thumbnail" is unactive
		// @since 3.7
		if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {

			$output .= ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '"';

			// add 100px width if "Display only thumbnail" is active
		} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

			$output .= ' width="100em"';

		}

		$output .= ' />';
		$output .= "\n\t\t\t\t\t" . '</a>';
		$output .= "\n\t\t\t\t" . '</div>';

		return $output;

	}

	/**
	 * Display mini biographical text, not all people have one
	 *
	 * 1- Cut the maximum of characters to be displayed with $click_text
	 * 2- Detect if there is html tags that can break with $esc_html_breaker
	 * 3- Build links either to internal (popups) or popups (inside posts/widgets) with $popup_links
	 *
	 * @param array<array<string, string>> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 * @param bool $popup_links  If links should be internal or popups. Internal (false) by default.
	 */
	public function lumiere_medaillon_bio ( array $bio_array, bool $popup_links = false ): ?string {

		/** Vars */
		$click_text = esc_html__( 'click to expand', 'lumiere-movies' ); // text for cutting.
		$max_length = 200; // maximum number of characters before cutting.

		// Calculate the number of bio results.
		$nbtotalbio = count( $bio_array );
		$bio = $nbtotalbio !== 0 ? $bio_array : null;

		// Select the index array according to the number of bio results.
		$idx = $nbtotalbio < 2 ? $idx = 0 : $idx = 1;

		// Make sure that bio description returns internal links and no IMDb's.
		$bio_head = '';
		$bio_text = '';
		if ( $popup_links === false && $bio !== null ) {

			$bio_text = $this->lumiere_imdburl_to_internalurl( $bio[ $idx ]['desc'] );

		} elseif ( $popup_links === true && $bio !== null ) {

			$bio_text = $this->lumiere_imdburl_to_popupurl( $bio[ $idx ]['desc'] );
		}

		// HTML tags break for 'read more' cutting.
		// Detects if there is a space next to $max_length; if true, increase the latter to that position.
		// Use of htmlentities to avoid spaces inside html code (ie innerspace in '<br />').
		$max_length = strlen( $bio_text ) !== 0 && is_int( strpos( htmlentities( $bio_text ), ' ', $max_length ) ) === true ? strpos( htmlentities( $bio_text ), ' ', $max_length ) : $max_length;
		// Detects if there is html a tag before reaching $max_length.
		// If true increase max length up to first '/a>' + 3 chars (since the search is made with 3 chars).
		$esc_html_breaker = strpos( $bio_text, '<a' ) <= $max_length && is_int( strpos( $bio_text, '/a>' ) ) === true ? strpos( $bio_text, '/a>' ) + 3 : $max_length;

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

	/**
	 * Convert an IMDb url into an internal link for People and Movies
	 * Meant to be used inside popups (not in posts or widgets)
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 */
	public function lumiere_imdburl_to_internalurl ( string $text ): string {

		// Internal links.
		$internal_link_person = '<a class="linkpopup" href="' . $this->config_class->lumiere_urlpopupsperson . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';
		$internal_link_movie = '<a class="linkpopup" href="' . $this->config_class->lumiere_urlpopupsfilms . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';

		// Regexes. \D{21} 21 characters for 'https://www.imdb.com/'.
		// Common pattern.
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
	 * Build links using highslide popup
	 *
	 * @param string $text Text that includes IMDb URL to convert into a popup link
	 */
	public function lumiere_imdburl_to_popupurl ( string $text ): string {

		$popup_link_person = '<a class="linkpopup" data-bootstrappeople="${4}" data-target="#theModal${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>'
			. // Bootstrap modal
			$this->bootstrap_modal( '${4}', '${6}' );

		$popup_link_movie = '<a class="link-imdblt-highslidefilm" data-bootstrapfilmid="${4}" data-target="#theModal${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>'
			. // Bootstrap modal
			$this->bootstrap_modal( '${4}', '${6}' );

		// Regexes. \D{21} 21 characters for 'https://www.imdb.com/'.
		$rule_name = '~(<a href=\")(\D{21})(name\/nm)(\d{7})(\/\?.+?|\?.+?|\/?)\"\>(.*?)<\/a>~';
		$rule_title = '~(<a href=\")(\D{21})(title\/tt)(\d{7})(\?ref.+?|\/?)\"\>(.*?)<\/a>~';

		// Pattern found in soundtrack.
		if ( strpos( $text, 'https://www.imdb.com/' ) === false ) {
			$rule_name = '~(<a href=\")(\/name\/)(nm)(\d{7})(\?.+?|\/?)\"\>(.*?)<\/a>~';
			$rule_title = '~(<a href=\")(\/title\/)(tt)(\d{7})(\?.+?|\/?)\"\>(.*?)<\/a>~';
		}

		// Replace IMDb links with popup links.
		$output_one = preg_replace( $rule_name, $popup_link_person, $text ) ?? $text;
		$output_two = preg_replace( $rule_title, $popup_link_movie, $output_one ) ?? $text;

		return $output_two;
	}

	/**
	 * Bootsrap popup function
	 * Build an HTML link to open a popup with bootstrap for searching a movie (using js/lumiere_bootstrap_links.js)
	 *
	 * @param array<int, string> $link_parsed html tags and text to be modified
	 * @param string $popuplarg -> window width, if nothing passed takes database value
	 * @param string $popuplong -> window height, if nothing passed takes database value
	 */
	public function lumiere_popup_film_link ( array $link_parsed, string $popuplarg = null, string $popuplong = null ): string {

		if ( $popuplarg !== null ) {
			$popuplarg = $this->imdb_admin_values['imdbpopuplarg'];
		}

		if ( $popuplong !== null ) {
			$popuplong = $this->imdb_admin_values['imdbpopuplong'];
		}

		$output = '<a class="link-imdblt-highslidefilm" data-bootstrapfilm="' . Utils::lumiere_name_htmlize( $link_parsed[1] ) . '" data-target="#theModal' . Utils::lumiere_name_htmlize( $link_parsed[1] ) . '" title="' . esc_html__( 'Open a new window with IMDb informations', 'lumiere-movies' ) . '">' . $link_parsed[1] . '</a>&nbsp;';

		// Bootstrap modal
		$output .= $this->bootstrap_modal( $link_parsed[1], $link_parsed[1] );

		return $output;

	}

	/**
	 * Build bootstrap HTML part
	 * This HTML code enable to display bootstrap modal window
	 * Private function as it is only utilised by this class
	 * Using spans instead of divs to not break the regex replace in content (WP adds <p>)
	 *
	 * @param string $imdb_id Id of the IMDB person/movie
	 * @param string $imdb_data Name/title of the IMDB person/movie
	 *
	 * @return string
	 */
	private function bootstrap_modal ( string $imdb_id, string $imdb_data ): string {

		return "\n\t\t\t" . '<span class="modal fade" id="theModal' . sanitize_text_field( $imdb_id ) . '">'
			. "\n\t\t\t\t" . '<span class="modal-dialog modal-dialog-centered" id="bootstrapp' . sanitize_text_field( $imdb_id ) . '">'
			. "\n\t\t\t\t\t" . '<span class="modal-content">'
			. "\n\t\t\t\t\t\t" . '<span class="modal-body"></span>'
			. "\n\t\t\t\t\t\t" . '<span class="modal-footer black">'
			// . esc_html__( 'Informations about', 'lumiere-movies' ) . ' ' . sanitize_text_field( ucfirst( $imdb_data ) )
			. '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-target="theModal' . sanitize_text_field( $imdb_id ) . '"></button>'
			. "\n\t\t\t\t\t\t" . '</span>'
			. "\n\t\t\t\t\t" . '</span>'
			. "\n\t\t\t\t" . '</span>'
			. "\n\t\t\t" . '</span>';

	}

	/**
	 * Trailer data details
	 *
	 * @param string $url Url to the trailer
	 * @param string $website website name
	 */
	public function lumiere_movies_trailer_details ( string $url, string $website_title ): string {

		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_html__( 'Watch on IMBb website the trailer for ', 'lumiere-movies' ) . esc_html( $website_title ) . "'>" . sanitize_text_field( $website_title ) . '</a>';

	}

	/**
	 * Production company data details
	 *
	 * @param string $name prod company name
	 * @param string $url Url to the prod company
	 * @param string $notes prod company notes
	 */
	public function lumiere_movies_prodcompany_details ( string $name, string $url, string $notes ): string {

		$output = '';
		$output .= "\n\t\t\t" . '<div align="center" class="lumiere_container">';
		$output .= "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">';
		$output .= "\n\t\t\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_html( $name ) . "'>";
		$output .= esc_attr( $name );
		$output .= '</a>';
		$output .= "\n\t\t\t\t</div>";
		$output .= "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';
		if ( strlen( $notes ) !== 0 ) {
			$output .= esc_attr( $notes );
		} elseif ( strlen( $notes ) === 0 ) {
			$output .= '&nbsp;';
		}
		$output .= '</div>';
		$output .= "\n\t\t\t</div>";

		return $output;

	}

	/**
	 * Official websites data details
	 *
	 * @param string $url Url to the prod company
	 * @param string $name prod company name
	 */
	public function lumiere_movies_officialsites_details ( string $url, string $name ): string {

		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_attr( $name ) . "'>"
			. esc_html( $name )
			. '</a>';
	}

	/**
	 * Plots data details
	 *
	 * @param string $plot Text of the plot
	 */
	public function lumiere_movies_plot_details ( string $plot ): string {

		return "\n\t\t\t\t" . $plot;
	}

	/**
	 * Source data details
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	public function lumiere_movies_source_details ( string $mid ): string {

		return "\n\t\t\t" . '<img class="imdbelementSOURCE-picture" alt="link to imdb" width="33" height="15" src="' . esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/imdb-link.png' ) . '" />'
		. '<a class="link-incmovie-sourceimdb" title="'
				. esc_html__( 'Go to IMDb website for this movie', 'lumiere-movies' ) . '" href="'
				. esc_url( 'https://www.imdb.com/title/tt' . $mid ) . '" >'
				. '&nbsp;&nbsp;'
				. esc_html__( "IMDb's page for this movie", 'lumiere-movies' ) . '</a>';

	}
}
