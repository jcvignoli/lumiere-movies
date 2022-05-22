<?php declare( strict_types = 1 );
/**
 * Class to build no HTML Links
 *
 * This class is also used for AMP pages
 *
 * No external HTML link, no popup will be made
 * Links to 1/ taxonomy pages or 2/ internal links are kept
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since 3.7.1
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Settings;
use \Lumiere\Utils;

class No_Links {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		// Construct Global Settings trait.
		$this->settings_open();

	}

	/**
	 * Build link to popup for IMDb people
	 * @param array<int, array<string, string>> $imdb_data_people
	 * @param int $number
	 * @return string
	 */
	public function lumiere_link_popup_people ( array $imdb_data_people, int $number ): string {

		return esc_attr( $imdb_data_people[ $number ]['name'] );

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

		// Select picture: if 1/ use always thumbnail
		// @since 3.7
		$photo_url = is_string( $photo_localurl_true ) ? $photo_localurl_true : '';

		// Select picture: if 2/ thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final = strlen( $photo_url ) === 0 ? esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ) : $photo_url;

		$output .= "\n\t\t\t" . '<div class="imdbelementPIC">';

		// Build image HTML tag <img>
		$output .= "\n\t\t\t\t\t" . '<img class="imdbelementPICimg" src="';

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

		// Select picture: if 1/ use always thumbnail
		// @since 3.7
		$photo_url = is_string( $photo_localurl_true ) ? esc_html( $photo_localurl_true ) : '';

		// Select picture: if 2/ thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final = strlen( $photo_url ) === 0 ? esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ) : $photo_url;

		$output .= "\n\t\t\t\t\t" . '<a id="nolinks_pic" href="' . esc_url( $photo_url_final ) . '">';

		// Build image HTML tag <img>
		$output .= "\n\t\t\t\t\t\t" . '<img class="imdbincluded-picture lumiere_float_right" src="'
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

		// Make sure it is always false, since the purpose of the class is to not have popups
		$popup_links = false;

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
		if ( $bio !== null ) {

			$bio_text = $this->lumiere_imdburl_to_internalurl( $bio[ $idx ]['desc'] );

		}

		$bio_head = "\n\t\t\t" . '<span class="imdbincluded-subtitle">'
			. esc_html__( 'Biography', 'lumiere-movies' )
			. '</span>';

		if ( strlen( $bio_text ) !== 0 ) {

			$bio_text = "\n\t\t\t" . $bio_text;

			return $bio_head . $bio_text;

		}

		// No biography text found.
		return $bio_head . 'No biography found';

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
		$rule_name = '~(<a href=\")(\D{21})(name\/nm)(\d{7})(\?.+?|\/?)\"\>~';
		$rule_title = '~(<a href=\")(\D{21})(title\/tt)(\d{7})(\?ref.+?|\/?)\"\>~';

		// Replace IMDb links with internal links.
		$output_one = preg_replace( $rule_name, $internal_link_person, $text ) ?? $text;
		$output_two = preg_replace( $rule_title, $internal_link_movie, $output_one ) ?? $text;

		return $output_two;
	}

	/**
	 * Remove an IMDb url from the text
	 * In this class, no popup is allowed so no popup link is built
	 *
	 * @param string $text Text that includes IMDb URL to be removed
	 */
	public function lumiere_imdburl_to_popupurl ( string $text ): string {

		// Regexes. \D{21} 21 characters for 'https://www.imdb.com/'.
		$rule_name = '~(<a href=\")(\D{21})(name\/nm)(\d{7})(\/\?.+?|\?.+?|\/?)\"\>~';
		$rule_title = '~(<a href=\")(\D{21})(title\/tt)(\d{7})(\?ref.+?|\/?)\"\>~';

		// Replace IMDb links with popup links.
		$output_one = preg_replace( $rule_name, '', $text ) ?? $text;
		$output_two = preg_replace( $rule_title, '', $output_one ) ?? $text;

		return $output_two;
	}

	/**
	 * No Link function for movies links
	 * Builds an internal when movie's are entered, because if not, the whole purpose of the plugins is killed
	 *
	 * @param array<int, string> $link_parsed html tags and text to be modified
	 */
	public function lumiere_popup_film_link ( array $link_parsed ): string {

		return '<a class="link-imdblt-classicfilm" href="' . $this->config_class->lumiere_urlpopupsfilms . $link_parsed[1] . '?film=' . $link_parsed[1] . '" title="' . esc_html__( 'No Links', 'lumiere-movies' ) . '">' . $link_parsed[1] . '</a>&nbsp;';

	}

}
