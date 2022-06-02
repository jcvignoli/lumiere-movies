<?php declare( strict_types = 1 );
/**
 * Abstract Class for building links
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
	 * @param bool $popup_links  If links should be internal or popups. Internal (false) by default.
	 */
	abstract protected function lumiere_medaillon_bio ( array $bio_array, bool $popup_links = false ): ?string;

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
	 * @param string $popuplarg -> window width, if nothing passed takes database value
	 * @param string $popuplong -> window height, if nothing passed takes database value
	 */
	abstract protected function lumiere_popup_film_link ( array $link_parsed, string $popuplarg = null, string $popuplong = null ): string;

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

		$output .= ' src="' . $this->imdb_admin_values['imdbplugindirectory'] . 'pics/showtimes/' . ( round( $rating * 2, 0 ) / 0.2 ) . ".gif\" title='$votes_average_txt $rating $out_of_ten_txt' alt='$votes_average_txt' />" . ' (' . number_format( $votes, 0, '', "'" ) . ' ' . $votes_txt . ')';

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
		$photo_localurl = is_string( $photo_localurl_true ) ? $photo_localurl_true : '';

		// Any class but AMP
		if ( $pictures !== 1 ) {
			// Select picture: if 1/ big picture exists, so use it, use thumbnail otherwise
			$photo_localurl = $photo_localurl_false !== false && is_string( $photo_localurl_false ) ? esc_html( $photo_localurl_false ) : esc_html( $photo_localurl );
		}

		// Select picture: if 2/ big/thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final = strlen( $photo_localurl ) === 0 ? esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ) : $photo_localurl;

		// Normal class or Bootstrap class
		if ( $pictures === 0 || $pictures === 2 ) {
			// removed class="highslide_pic"
			$output .= "\n\t\t\t\t\t" . '<a title="' . esc_attr( $person_name ) . '" href="' . esc_url( $photo_url_final ) . '">';
			// AMP class
		} elseif ( $pictures === 1 ) {
			$output .= "\n\t\t\t\t\t" . '<a class="nolinks_pic" title="' . esc_attr( $person_name ) . '" href="' . esc_url( $photo_url_final ) . '">';
			// No Links class
		} elseif ( $pictures === 3 ) {
			$output .= '';
		}

		// Build image HTML tag <img>
		// Any class but AMP
		if ( $pictures !== 1 ) {
			$output .= "\n\t\t\t\t\t\t" . '<img loading="eager" class="imdbincluded-picture lumiere_float_right"';
			// AMP class, loading="eager" breaks AMP
		} elseif ( $pictures === 1 ) {
			$output .= "\n\t\t\t\t\t\t" . '<img class="imdbincluded-picture lumiere_float_right"';
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

}
