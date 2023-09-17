<?php declare( strict_types = 1 );
/**
 * Abstract Class for building links
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.0
 * @since   3.8
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

/**
 * Defines abstract functions utilised in Link Maker classes
 * Includes protected functions utilised in Link Maker classes for code reuse
 * Includes Settings traits
 */
abstract class Abstract_Link_Maker {

	// Trait including the database settings.
	use \Lumiere\Settings_Global;

	/**
	 * Build link to popup for IMDb people
	 *
	 * @param array<int, array<string, string>> $imdb_data_people Array with IMDB people data
	 * @param int $number The number of the loop $i
	 * @return string
	 */
	abstract protected function lumiere_link_popup_people ( array $imdb_data_people, int $number ): string;

	/**
	 * Build picture of the movie
	 *
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $movie_title Title of the movie
	 * @return string
	 */
	abstract protected function lumiere_link_picture ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title ): string;

	/**
	 * Build picture of the movie in taxonomy pages
	 *
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $person_name Name of the person
	 * @return string
	 */
	abstract protected function lumiere_link_picture_taxonomy ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $person_name ): string;

	/**
	 * Display mini biographical text, not all people have one
	 *
	 * 1- Cut the maximum of characters to be displayed with $click_text
	 * 2- Detect if there is html tags that can break with $esc_html_breaker
	 * 3- Build links either to internal (popups) or popups (inside posts/widgets) with $popup_links
	 *
	 * @param array<array<string, string>> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 */
	abstract protected function lumiere_medaillon_bio ( array $bio_array ): ?string;

	/**
	 * Convert an IMDb url into an internal link for People and Movies
	 * Meant to be used inside popups (not in posts or widgets)
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 */
	abstract protected function lumiere_imdburl_to_internalurl ( string $text ): string;

	/**
	 * Convert an IMDb url into a Popup link for People and Movies
	 * Meant to be used inside in posts or widgets (not in Popups)
	 *
	 * @param string $text Text that includes IMDb URL to convert into a popup link
	 */
	abstract protected function lumiere_imdburl_to_popupurl ( string $text ): string;

	/**
	 * Build an HTML link to open a popup for searching a movie
	 *
	 * @param array<int, string> $link_parsed html tags and text to be modified
	 * @param null|string $popuplarg -> window width, if nothing passed takes database value
	 * @param null|string $popuplong -> window height, if nothing passed takes database value
	 */
	abstract protected function lumiere_popup_film_link ( array $link_parsed, ?string $popuplarg = null, ?string $popuplong = null ): string;

	/**
	 * Trailer data details
	 *
	 * @param string $url Url to the trailer
	 * @param string $website_title website name
	 */
	abstract protected function lumiere_movies_trailer_details ( string $url, string $website_title ): string;

	/**
	 * Production company data details
	 *
	 * @param string $name prod company name
	 * @param string $url Url to the prod company
	 * @param string $notes prod company notes
	 */
	abstract protected function lumiere_movies_prodcompany_details ( string $name, string $url, string $notes ): string;

	/**
	 * Official websites data details
	 *
	 * @param string $url Url to the prod company
	 * @param string $name prod company name
	 */
	abstract protected function lumiere_movies_officialsites_details ( string $url, string $name ): string;

	/**
	 * Plots data details
	 *
	 * @param string $plot Text of the plot
	 */
	abstract protected function lumiere_movies_plot_details ( string $plot ): string;

	/**
	 * Source data details
	 *
	 * @param string $mid IMDb ID of the movie
	 */
	abstract protected function lumiere_movies_source_details ( string $mid ): string;

	/**
	 * Remove html links <a></a>
	 *
	 * @param string $text text to be cleaned from every html link
	 * @return string $output text that has been cleaned from every html link
	 */
	protected function lumiere_remove_link ( string $text ): string {

		$output = preg_replace( '/<a(.*?)>/', '', $text ) ?? $text;
		$output = preg_replace( '/<\/a>/', '', $output ) ?? $output;

		return $output;

	}

	/**
	 * Image for the ratings, meant to be used by child classes for code reusing
	 *
	 * @param int $rating mandatory Rating number
	 * @param int $votes mandatory Number of votes
	 * @param string $votes_average_txt mandatory Text mentionning "vote average"
	 * @param string $out_of_ten_txt mandatory Text mentionning "out of ten"
	 * @param string $votes_txt mandatory Text mentionning "votes"
	 * @param int $with_imdb_element_rating Pass 1 to add class="imdbelementRATING-picture"
	 *
	 * @return string
	 */
	protected function lumiere_movies_rating_picture_abstract ( int $rating, int $votes, string $votes_average_txt, string $out_of_ten_txt, string $votes_txt, int $with_imdb_element_rating ): string {

		$output = "\n\t\t\t" . '<span class="imdbincluded-subtitle">';
		$output .= esc_html__( 'Rating', 'lumiere-movies' );
		$output .= ':</span>';

		$output .= ' <img';

		// imdbelementRATING-picture class breaks AMP, added only if
		if ( $with_imdb_element_rating === 1 ) {
			$output .= ' class="imdbelementRATING-picture"';
		}

		$output .= ' src="' . $this->config_class->lumiere_pics_dir . '/showtimes/' . ( round( $rating * 2, 0 ) / 0.2 ) . ".gif\" title='$votes_average_txt $rating $out_of_ten_txt' alt='$votes_average_txt' />" . ' (' . number_format( $votes, 0, '', "'" ) . ' ' . $votes_txt . ')';

		return $output;

	}

	/**
	 * Build picture of the movie in taxonomy pages
	 *
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $person_name Name of the person
	 * @param int $pictures Pass 0 for Highslide or Classic, 1 AMP, 2 Bootstrop, 3 No Links
	 *
	 * @return string
	 */
	protected function lumiere_link_picture_taxonomy_abstract ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $person_name, int $pictures ): string {

		$output = '';
		$output .= "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- star photo -->';
		$output .= "\n\t\t\t\t" . '<div class="lumiere-lines-common lumiere-lines-common_'
			. esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] )
			. ' lumiere-padding-lines-common-picture">';

		// Make sure $photo_localurl_true is a string so we can use esc_html() function
		$photo_localurl_small = is_string( $photo_localurl_true ) && strlen( $photo_localurl_true ) > 0 ? $photo_localurl_true : '';

		// Any class but AMP
		if ( $pictures !== 1 ) {
			// Select picture: if 1/ big picture exists, so use it, use thumbnail otherwise
			$photo_localurl =
				$photo_localurl_false !== false && is_string( $photo_localurl_false ) && strlen( $photo_localurl_false ) > 0
				? $photo_localurl_false
				: $photo_localurl_small;
		}

		// Select picture: if 2/ big/thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final = !isset( $photo_localurl ) || strlen( $photo_localurl ) === 0
			? esc_url( $this->config_class->lumiere_pics_dir . '/no_pics.gif' )
			: $photo_localurl;

		// Normal class or Bootstrap class
		if ( $pictures === 0 || $pictures === 2 ) {
			$output .= "\n\t\t\t\t\t" . '<a title="' . esc_attr( $person_name ) . '" href="' . esc_url( $photo_url_final ) . '">';
			// AMP class
		} elseif ( $pictures === 1 ) {
			$output .= "\n\t\t\t\t\t" . '<a class="nolinks_pic" title="' . esc_attr( $person_name ) . '" href="' . esc_url( $photo_url_final ) . '">';
			// No Links class
		} elseif ( $pictures === 3 ) {
			$output .= '';
		}

		// Build image HTML tag <img>
		// AMP class, loading="eager" breaks AMP
		if ( $pictures === 1 ) {
			$output .= "\n\t\t\t\t\t\t" . '<img class="imdbincluded-picture lumiere_float_right"';
			// Any class but AMP
		} else {
			$output .= "\n\t\t\t\t\t\t" . '<img loading="eager" class="imdbincluded-picture lumiere_float_right"';
		}
		$output .= ' src="' . esc_url( $photo_url_final ) . '" alt="'
			. esc_html__( 'Photo of', 'lumiere-movies' ) . ' '
			. esc_attr( $person_name ) . '"';

		// add width only if "Display only thumbnail" is unactive
		// @since 3.7
		if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {
			$output .= ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '" />';
			// add 100px width if "Display only thumbnail" is active
		} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {
			$output .= ' width="100em" />';
		}

		// Not classic links, so we can close <a>
		if ( $pictures !== 3 ) {
			$output .= "\n\t\t\t\t\t" . '</a>';
		}

		$output .= "\n\t\t\t\t" . '</div>';

		return $output;

	}

	/**
	 * Build picture of the movie into the post pages
	 *
	 * @param string|bool $photo_localurl_false The picture of big size
	 * @param string|bool $photo_localurl_true The picture of small size
	 * @param string $movie_title Title of the movie
	 * @param int $pictures Pass 0 for Highslide or Classic (default), 1 AMP, 2 Bootstrop, 3 No Links
	 * @param string $specific_a_class Extra class to be added in the building link, none by default
	 * @param string $specific_img_class Extra class to be added in the building link, none by default
	 *
	 * @return string
	 */
	protected function lumiere_link_picture_abstract ( string|bool $photo_localurl_false, string|bool $photo_localurl_true, string $movie_title, int $pictures = 0, string $specific_a_class = '', string $specific_img_class = '' ): string {

		$output = '';
		$output .= "\n\t\t\t" . '<div class="imdbelementPIC">';

		// Make sure $photo_localurl_true is a string so we can use esc_html() function
		$photo_localurl = is_string( $photo_localurl_true ) ? $photo_localurl_true : '';

		// Any class but AMP
		if ( $pictures !== 1 ) {
			// Select picture: if 1/ big picture exists, so use it, use thumbnail otherwise
			$photo_localurl = $photo_localurl_false !== false && is_string( $photo_localurl_false ) ? esc_html( $photo_localurl_false ) : esc_html( $photo_localurl );
		}

		// Select picture: if 2/ big/thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final = strlen( $photo_localurl ) === 0 ? esc_url( $this->config_class->lumiere_pics_dir . '/no_pics.gif' ) : $photo_localurl;

		// Normal class or Bootstrap class
		if ( $pictures === 0 || $pictures === 2 ) {
			$output .= "\n\t\t\t\t\t" . '<a class="' . $specific_a_class . '" title="' . esc_attr( $movie_title ) . '" href="' . esc_url( $photo_url_final ) . '">';
			// AMP or No Links class
		} elseif ( $pictures === 1 || $pictures === 3 ) {
			$output .= '';
		}

		// Build image HTML tag <img>
		$output .= "\n\t\t\t\t\t" . '<img ';
		// AMP class, loading="eager" breaks AMP
		if ( $pictures !== 1 ) {
			$output .= ' loading="eager"';
		}
		$output .= ' class="' . $specific_img_class . '" src="' . esc_url( $photo_url_final ) . '" alt="'
			. esc_html__( 'Photo of', 'lumiere-movies' ) . ' '
			. esc_attr( $movie_title ) . '"';

		// add width only if "Display only thumbnail" is unactive
		// @since 3.7
		if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {
			$output .= ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '" />';
			// add 100px width if "Display only thumbnail" is active
		} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {
			$output .= ' width="100em" />';
		}

		// Not classic links, so we can close <a>
		if ( $pictures !== 3 && $pictures !== 1 ) {
			$output .= "\n\t\t\t\t\t" . '</a>';
		}

		$output .= "\n\t\t\t" . '</div>';

		return $output;

	}

	/**
	 * Display mini biographical text, not all people have one
	 *
	 * 1- Cut the maximum of characters to be displayed with $click_text
	 * 2- Detect if there is html tags that can break with $esc_html_breaker
	 * 3- Build links either to popups (if taxonomy) or internal links (if popup people)
	 *
	 * @param array<array<string, string>> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 * @param int $output Define the output: 0 for full (default), 1 for no links (AMP, No Link classes)
	 *
	 * @return ?string
	 */
	protected function lumiere_medaillon_bio_abstract ( array $bio_array, int $output = 0 ): ?string {

		/** Vars */
		$click_text = esc_html__( 'click to expand', 'lumiere-movies' ); // text for cutting.
		$max_length = 200; // maximum number of characters before cutting.
		$bio_head = "\n\t\t\t" . '<span class="imdbincluded-subtitle">'
			. esc_html__( 'Biography', 'lumiere-movies' )
			. '</span>';
		$bio_text = '';

		// Calculate the number of bio results.
		$nbtotalbio = count( $bio_array );
		$bio = $nbtotalbio !== 0 ? $bio_array : null;

		// Select the index array according to the number of bio results.
		$idx = $nbtotalbio < 2 ? $idx = 0 : $idx = 1;

		// Medaillon is displayed in a popup person page, build internal URL
		if ( str_contains( $_SERVER['REQUEST_URI'], $this->config_class->lumiere_urlstringperson ) && ( $bio !== null ) ) {
			$bio_text = $this->lumiere_imdburl_to_internalurl( $bio[ $idx ]['desc'] );
			// Medaillon is displayed in a taxonomy page, build popup URL
		} elseif ( is_tax() && $bio !== null ) {
			$bio_text = $this->lumiere_imdburl_to_popupurl( $bio[ $idx ]['desc'] );
		}

		// HTML tags break for 'read more' cutting.
		// Detects if there is a space next to $max_length; if true, increase the latter to that position.
		// Use of htmlentities to avoid spaces inside html code (ie innerspace in '<br />').
		$max_length = strlen( $bio_text ) !== 0 && is_int( strpos( htmlentities( $bio_text ), ' ', $max_length ) ) === true ? strpos( htmlentities( $bio_text ), ' ', $max_length ) : $max_length;

		// Detects if there is html a tag before reaching $max_length.
		// If true increase max length up to first '/a>' + 3 chars (since the search is made with 3 chars).
		$esc_html_breaker = strpos( $bio_text, '<a' ) <= $max_length && is_int( strpos( $bio_text, '/a>' ) ) === true ? strpos( $bio_text, '/a>' ) + 3 : $max_length;

		// No Links class, exit before building clickable biography, show everything at once
		if ( $output === 1 ) {
			return $bio_head . "\n\t\t\t" . $bio_text;
		}

		// There is 1/ a bio, and 2/ its length is superior to above $esc_html_breaker
		if ( strlen( $bio_text ) !== 0 && strlen( $bio_text ) > $esc_html_breaker ) {

			$str_one = substr( $bio_text, 0, $esc_html_breaker );
			$str_two = substr( $bio_text, $esc_html_breaker, strlen( $bio_text ) );

			$bio_text = "\n\t\t\t" . $str_one
				. "\n\t\t\t" . '<span class="activatehidesection"><strong>&nbsp;(' . $click_text . ')</strong></span> '
				. "\n\t\t\t" . '<span class="hidesection">'
				. "\n\t\t\t" . $str_two
				. "\n\t\t\t" . '</span>';

		}

		return strlen( $bio_text ) > 0 ? $bio_head . $bio_text : '';

	}

	/**
	 * Convert an IMDb url into an internal link for People and Movies
	 * Meant to be used inside popups (not in posts or widgets)
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 * @param int $output Define the output: 0 for links (default), 1 for no links
	 *
	 * @return string
	 */
	protected function lumiere_imdburl_to_internalurl_abstract ( string $text, int $output = 0 ): string {

		$internal_link_person = '';
		$internal_link_movie = '';

		if ( intval( $output ) === 0 ) {
			$internal_link_person = '<a class="linkpopup" href="' . $this->config_class->lumiere_urlpopupsperson . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';
			$internal_link_movie = '<a class="linkpopup" href="' . $this->config_class->lumiere_urlpopupsfilms . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';
		}

		// Regexes. \D{21} 21 characters for 'https://www.imdb.com/'.
		// Common pattern.
		$rule_name = '~(<a href=\")(\D{21})(name\/nm)(\d{7})(\?.+?|\/?)\"\>~';
		$rule_title = '~(<a href=\")(\D{21})(title\/tt)(\d{7})(\?ref.+?|\/?)\"\>~';

		// Pattern found in soundtrack.
		if ( strpos( $text, 'https://www.imdb.com/' ) === false ) {
			$rule_name = '~(<a href=\")(\/name\/)(nm)(\d{7})(\?.+?|\/?)\"\>~';
			$rule_title = '~(<a href=\")(\/title\/)(tt)(\d{7})(\?.+?|\/?)\"\>~';
		}

		// Replace IMDb links with internal links.
		$output_one = preg_replace( $rule_name, $internal_link_person, $text ) ?? $text;
		$output_two = preg_replace( $rule_title, $internal_link_movie, $output_one ) ?? $text;

		return $output_two;

	}

	/**
	 * Convert an IMDb url into a popup link for People and Movies
	 * Meant to be used inside popups (not in posts or widgets)
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 * @param int $output Define the output: 0 for links (default), 1 regular popups, 2 for no links, 3 for bootstrap
	 * @param string $specific_class Extra class to be added in popup building link, none by default
	 *
	 * @return string
	 */
	protected function lumiere_imdburl_to_popupurl_abstract ( string $text, int $output = 0, string $specific_class = '' ): string {

		$popup_link_person = '';
		$popup_link_movie = '';

		switch ( intval( $output ) ) {
			case 0: // Build modal window popups.
				$popup_link_person = '<a class="modal_window_people ' . $specific_class . '" data-modal_window_people="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>';
				$popup_link_movie = '<a class="modal_window_film ' . $specific_class . '" data-modal_window_filmid="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>';
				break;
			case 1: // Build internal links with no popups.
				$popup_link_person = '<a class="linkpopup" href="' . $this->config_class->lumiere_urlpopupsperson . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">${6}</a>';
				$popup_link_movie = '<a class="linkpopup" href="' . $this->config_class->lumiere_urlpopupsfilms . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">${6}</a>';
				break;
			case 2: // No links class
				$popup_link_person = '${6}';
				$popup_link_movie = '${6}';
				break;
			case 3: // Bootstrap popups
				$popup_link_person = '<a class="linkpopup" data-modal_window_people="${4}" data-target="#theModal${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>'
				. $this->bootstrap_modal( '${4}', '${6}' );

				$popup_link_movie = '<a class="modal_window_film" data-modal_window_filmid="${4}" data-target="#theModal${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>'
				. $this->bootstrap_modal( '${4}', '${6}' );
				break;
		}

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
	 * Build bootstrap HTML part
	 * This HTML code enable to display bootstrap modal window
	 * Using spans instead of divs to not break the regex replace in content (WP adds extra <p> when divs are used)
	 *
	 * @param string $imdb_id Id of the IMDB person/movie
	 * @param string $imdb_data Name/title of the IMDB person/movie
	 *
	 * @return string
	 */
	private function bootstrap_modal ( string $imdb_id, string $imdb_data ): string {

		return "\n\t\t\t" . '<span class="modal fade" id="theModal' . $imdb_id . '">'
			. "\n\t\t\t\t" . '<span class="modal-dialog modal-dialog-centered" id="bootstrapp' . $imdb_id . '">'
			. "\n\t\t\t\t\t" . '<span class="modal-content">'
			. "\n\t\t\t\t\t\t" . '<span class="modal-header black">'
			// . esc_html__( 'Informations about', 'lumiere-movies' ) . ' ' . sanitize_text_field( ucfirst( $imdb_data ) )
			. '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-target="theModal' . $imdb_id . '"></button>'
			. "\n\t\t\t\t\t\t" . '</span>'
			. "\n\t\t\t\t\t\t" . '<span class="modal-body"></span>'
			. "\n\t\t\t\t\t" . '</span>'
			. "\n\t\t\t\t" . '</span>'
			. "\n\t\t\t" . '</span>';

	}

	/**
	 * Plots data details, removing all links
	 *
	 * @param string $plot Text of the plot
	 *
	 * @return string
	 */
	protected function lumiere_movies_plot_details_abstract ( string $plot ): string {

		return "\n\t\t\t\t" . wp_strip_all_tags( $plot );
	}

	/**
	 * Inside a post Popup people builder
	 * Build an HTML link to open a popup for searching a person inside the posts
	 *
	 * @param string $imdbid IMDB id
	 * @param string $imdbname Name of the person
	 * @param int $output Define the output: 0 for highslide & classic links (default), 1 bootstrap popups, 2 for no links, 3 for AMP
	 * @param string $specific_a_class Extra class to be added in popup building link, none by default
	 *
	 * @return string
	 */
	protected function lumiere_link_popup_people_abstract ( string $imdbid, string $imdbname, int $output = 0, string $specific_a_class = '' ): string {

		// No link creation, exit
		if ( intval( $output ) === 2 ) {
			return esc_attr( $imdbname );
		}

		// Building link.
		$txt = "\n\t\t\t" . '<a class="' . $specific_a_class . '"' . " id=\"link-$imdbid\""
		. ' data-modal_window_people="' . sanitize_text_field( $imdbid ) . '"'
		// Data target is utilised by bootstrap only, but should be safe to keep it.
		. ' data-target="#theModal' . sanitize_text_field( $imdbid ) . '"'
		. ' title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '"';
		// AMP, build a HREF.
		if ( intval( $output ) === 3 ) {
			$txt .= ' href="' . esc_attr( $this->config_class->lumiere_urlpopupsperson . '?mid=' . $imdbid ) . '"';
		}
		$txt .= '>' . sanitize_text_field( $imdbname ) . '</a>';

		// Modal bootstrap HTML part.
		if ( intval( $output ) === 1 ) {
			$txt .= $this->bootstrap_modal( $imdbid, $imdbname );
		}

		return $txt;

	}

	/**
	 * Inside a post Popup movie builder
	 * Build an HTML link to open a popup for searching a movie inside the posts
	 *
	 * @param array<int, string> $link_parsed html tags and text to be modified
	 * @param null|string $popuplarg Modal window width, if nothing passed takes database value
	 * @param null|string $popuplong Modal window height, if nothing passed takes database value
	 * @param int $output Define the output: 0 for highslide & classic links (default), 1 bootstrap popups, 2 for no links & AMP
	 * @param string $specific_class Extra class to be added in popup building link, none by default
	 *
	 * @return string

	 */
	protected function lumiere_popup_film_link_abstract ( array $link_parsed, ?string $popuplarg = null, ?string $popuplong = null, int $output = 0, string $specific_class = '' ): string {

		$txt = '';
		$title_attr = sanitize_title( $link_parsed[1] );
		$title_esc = esc_html( $link_parsed[1] );

		if ( $popuplarg !== null ) {
			$popuplarg = $this->imdb_admin_values['imdbpopuplarg'];
		}

		if ( $popuplong !== null ) {
			$popuplong = $this->imdb_admin_values['imdbpopuplong'];
		}

		// Highslide & Classic modal
		if ( intval( $output ) === 0 ) {

			$txt = '<a class="modal_window_film" data-modal_window_film="' . $title_attr . '" title="' . esc_html__( 'Open a new window with IMDb informations', 'lumiere-movies' ) . '">' . $title_esc . '</a>';

			// Bootstrap modal
		} elseif ( intval( $output ) === 1 ) {

			$txt = '<a class="modal_window_film" data-modal_window_film="' . $title_attr . '" data-target="#theModal' . $title_attr . '" title="' . esc_html__( 'Open a new window with IMDb informations', 'lumiere-movies' ) . '">' . $title_esc . '</a>'
			. $this->bootstrap_modal( $title_attr, $title_esc );

			// AMP & No Link modal
		} elseif ( intval( $output ) === 2 ) {

			$txt = '<a class="link-imdblt-classicfilm" href="' . $this->config_class->lumiere_urlpopupsfilms . '?film=' . $title_attr . '" title="' . esc_html__( 'No Links', 'lumiere-movies' ) . '">' . $title_esc . '</a>';

		}

		return $txt;

	}

	/**
	 * Trailer data details
	 *
	 * @param string $url Url to the trailer
	 * @param string $website_title website name
	 * @param int $output Define the output: 0 for highslide, bootstrap, AMP & classic links (default), 1 for no links
	 * @return string
	 */
	protected function lumiere_movies_trailer_details_abstract ( string $url, string $website_title, int $output = 0 ): string {

		// No Links class, do not display any link.
		if ( $output === 1 ) {
			return "\n\t\t\t" . sanitize_text_field( $website_title ) . ', ' . esc_url( $url );
		}

		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_html__( 'Watch on IMBb website the trailer for ', 'lumiere-movies' ) . esc_html( $website_title ) . "'>" . sanitize_text_field( $website_title ) . '</a>';

	}

	/**
	 * Production company data details
	 *
	 * @param string $name prod company name
	 * @param string $url Url to the prod company
	 * @param string $notes prod company notes
	 * @param int $output Define the output: 0 for highslide, bootstrap classic links (default), 1 for no links & AMP
	 * @return string
	 */
	protected function lumiere_movies_prodcompany_details_abstract ( string $name, string $url = '', string $notes = '', int $output = 0 ): string {

		// No Links class or AMP, do not display any link.
		if ( $output === 1 ) {
			return esc_attr( $name ) . '<br />';
		}

		$return = "\n\t\t\t" . '<div align="center" class="lumiere_container">'
			. "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">'
			. "\n\t\t\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_html( $name ) . "'>"
			. esc_attr( $name )
			. '</a>'
			. "\n\t\t\t\t</div>"
			. "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';

		if ( strlen( $notes ) !== 0 ) {
			$return .= esc_attr( $notes );
		} else {
			$return .= '&nbsp;';
		}

		$return .= '</div>'
			. "\n\t\t\t</div>";

		return $return;

	}

	/**
	 * Official websites data details
	 *
	 * @param string $url Url to the offical website
	 * @param string $name Offical website name
	 * @param int $output Define the output: 0 for highslide, bootstrap, AMP & classic links (default), 1 for no links
	 * @return string
	 */
	protected function lumiere_movies_officialsites_details_abstract ( string $url, string $name, int $output = 0 ): string {

		// No Links class, do not display any link.
		if ( $output === 1 ) {
			return "\n\t\t\t" . sanitize_text_field( $name ) . ', ' . esc_url( $url );
		}

		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_attr( $name ) . "'>"
			. esc_html( $name )
			. '</a>';

	}

	/**
	 * Source data details
	 *
	 * @param string $mid IMDb ID of the movie
	 * @param int $output Define the output: 0 for AMP, highslide, bootstrap & classic links (default), 1 for No links
	 * @param null|string $class extra class to add, only AMP does not use it
	 * @return string
	 */
	protected function lumiere_movies_source_details_abstract ( string $mid, int $output = 0, ?string $class = null ): string {

		// No Links class, do not return links.
		if ( $output === 1 ) {
			return "\n\t\t\t"
				. '<img class="imdbelementSOURCE-picture" alt="link to imdb" width="33" height="15" src="'
				. esc_url(
					$this->config_class->lumiere_pics_dir
					. '/imdb-link.png'
				) . '" />'
				. ' https://www.imdb.com/title/tt' . $mid;
		}

		$return = "\n\t\t\t" . '<img';

		// Add a class if requested, should be imdbelementSOURCE-picture, which breaks AMP
		if ( $class !== null ) {
			$return .= ' class="' . $class . '"';
		}

		$return .= ' alt="link to imdb" width="33" height="15" src="'
			. esc_url( $this->config_class->lumiere_pics_dir . '/imdb-link.png' ) . '" />'
			. '<a class="link-incmovie-sourceimdb" title="'
			. esc_html__( 'Go to IMDb website for this movie', 'lumiere-movies' ) . '" href="'
			. esc_url( 'https://www.imdb.com/title/tt' . $mid ) . '" >'
			. '&nbsp;&nbsp;'
			. esc_html__( "IMDb's page for this movie", 'lumiere-movies' ) . '</a>';

		return $return;

	}
}
