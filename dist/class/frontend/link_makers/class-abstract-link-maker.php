<?php declare( strict_types = 1 );
/**
 * Abstract Class for building links
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version 1.1
 * @since   3.8
 * @package lumiere-movies
 */

namespace Lumiere\Link_Makers;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Tools\Settings_Global;

/**
 * Defines abstract functions utilised in Link Maker classes
 * Includes protected functions utilised in Link Maker classes for code reuse
 * Includes Settings traits
 */
abstract class Abstract_Link_Maker {

	// Trait including the database settings.
	use Settings_Global;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get Global Settings class properties.
		$this->get_settings_class();
		$this->get_db_options();

	}

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
	 * Display mini biographical text, not all people have one
	 *
	 * 1- Cut the maximum of characters to be displayed with $click_text
	 * 2- Detect if there is html tags that can break with $esc_html_breaker
	 * 3- Build links either to internal (popups) or popups (inside posts/widgets) with $popup_links
	 *
	 * @param array<array<string, string>> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 * @param int $limit_text_bio Optional, increasing the hardcoded limit of characters before displaying "click for more"
	 */
	abstract protected function lumiere_medaillon_bio ( array $bio_array, int $limit_text_bio = 0 ): ?string;

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
	abstract protected function lumiere_imdburl_of_taxonomy( string $text ): string;

	/**
	 * Convert an IMDb url for soundtrack into the post
	 *
	 * @param string $text Text that includes IMDb URL to convert into a popup link
	 */
	abstract protected function lumiere_imdburl_of_soundtrack( string $text ): string;

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
	protected function lumiere_movies_rating_picture_abstract(
		int $rating,
		int $votes,
		string $votes_average_txt,
		string $out_of_ten_txt,
		string $votes_txt,
		int $with_imdb_element_rating
	): string {

		$output = "\n\t\t\t" . '<span class="lum_results_section_subtitle">';
		$output .= esc_html__( 'Rating', 'lumiere-movies' );
		$output .= ':</span>';

		$find_showtimes_pic = round( $rating * 2, 0 ) / 0.2;
		$output .= ' <img src="' . $this->config_class->lumiere_pics_dir . 'showtimes/' . $find_showtimes_pic . ".gif\" title=\"$votes_average_txt $rating $out_of_ten_txt\" alt=\"$votes_average_txt\" width=\"102\" height=\"12\" />" . ' (' . number_format( $votes, 0, '', "'" ) . ' ' . $votes_txt . ')';

		return $output;
	}

	/**
	 * Build picture of the movie into the post, widget and taxonomy pages
	 *
	 * @param string|bool $photo_big_cover The picture of big size
	 * @param string|bool $photo_thumb The picture of small size
	 * @param string $title_text Title of the movie/Name of the person
	 * @param int $window_type Pass 0 for Highslide or Classic (default), 1 AMP, 2 Bootstrop, 3 No Links
	 * @param string $specific_a_class Extra class to be added in the building link, none by default
	 * @param string $specific_img_class Extra class to be added in the building link, none by default
	 *
	 * @return string
	 */
	protected function lumiere_link_picture_abstract (
		string|bool $photo_big_cover,
		string|bool $photo_thumb,
		string $title_text,
		int $window_type = 0,
		string $specific_a_class = '',
		string $specific_img_class = '',
	): string {

		$output = '';
		$output .= "\n\t\t\t" . '<div class="imdbelementPIC">';

		// Make sure $photo_thumb is a string so we can use esc_html() function
		$photo_localurl = is_string( $photo_thumb ) ? $photo_thumb : '';

		// Any class but AMP
		if ( $window_type !== 1 ) {
			// Select picture: if 1/ big picture exists, so use it, use thumbnail otherwise
			$photo_localurl = is_string( $photo_big_cover ) ? $photo_big_cover : $photo_localurl;
		}

		// Picture for a href: if 2/ big/thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final_href = strlen( $photo_localurl ) === 0 ? esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) : $photo_localurl;

		// Picture for img: if 1/ thumbnail picture exists, use it, 2/ use no_pics otherwise
		$photo_url_final_img = is_string( $photo_thumb ) === false || strlen( $photo_thumb ) === 0 ? esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) : $photo_thumb;

		// Normal class or Bootstrap class
		if ( $window_type === 0 || $window_type === 2 ) {
			$output .= "\n\t\t\t\t\t" . '<a class="' . $specific_a_class . '" title="' . esc_attr( $title_text ) . '" href="' . esc_url( $photo_url_final_href ) . '">';
			// AMP or No Links class
		} elseif ( $window_type === 1 || $window_type === 3 ) {
			$output .= '';
		}

		// Build image HTML tag <img>
		$output .= "\n\t\t\t\t\t\t" . '<img ';
		// AMP class, loading="XXX" breaks AMP
		if ( $window_type !== 1 ) {
			$output .= ' loading="lazy"';
		}

		/**
		 * Add width="SizeXXXpx" and display the big cover image if "Display only thumbnail" is not selected
		 * If "display only thumbnail" is selected, thumb image is displayed and width is set to 100
		 * @since 3.7
		 */
		if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {
			$width = intval( $this->imdb_admin_values['imdbcoversizewidth'] );
			$height = $width * 1.4;
			$output .= ' class="' . $specific_img_class . '" src="' . esc_url( $photo_url_final_img ) . '" alt="'
				. esc_html__( 'Photo of', 'lumiere-movies' ) . ' '
				. esc_attr( $title_text ) . '" width="' . esc_attr( strval( $width ) ) . '" height="' . esc_attr( strval( $height ) ) . '" />';

			// set to 100 width and 160 height if "Display only thumbnail" is active
		} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {
			$output .= ' class="' . $specific_img_class . '" src="' . esc_url( $photo_url_final_img ) . '" alt="'
				. esc_html__( 'Photo of', 'lumiere-movies' ) . ' ' . esc_attr( $title_text ) . '" height="160" width="100" />';
		}

		// Not classic links, so we can close <a>
		if ( $window_type !== 3 && $window_type !== 1 ) {
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
	 * @param int $window_type Define the window_type: 0 for full (default), 1 for no links (AMP, No Link classes)
	 * @param int $limit_text_bio Optional, increasing the hardcoded limit of characters before displaying "click for more"
	 * @return string
	 *
	 * @since 4.1 added $limit_text_bio param
	 */
	protected function lumiere_medaillon_bio_abstract ( array $bio_array, int $window_type = 0, int $limit_text_bio = 0 ): string {

		if ( count( $bio_array ) === 0 ) {
			return "\n\t\t\t" . '<span class="lum_results_section_subtitle lumiere_font_small">' . esc_html__( 'No biography available', 'lumiere-movies' ) . '</span>';
		}

		/** Vars */
		$click_text = esc_html__( 'click to expand', 'lumiere-movies' ); // text for cutting.
		$max_length = $limit_text_bio !== 0 ? $limit_text_bio : 200; // maximum number of characters before cutting, 200 is perfect for popups.
		$bio_head = "\n\t\t\t" . '<span class="lum_results_section_subtitle">'
			. esc_html__( 'Biography', 'lumiere-movies' )
			. '</span>';
		$bio_text = '';

		// Calculate the number of bio results.
		$nbtotalbio = count( $bio_array );

		// Select the index array according to the number of bio results. -- 2024 10 04 set to 0 always
		// $idx = $nbtotalbio < 2 ? $idx = 0 : $idx = 1;
		$idx = 0;

		$bio_text = isset( $bio_array[ $idx ]['desc'] ) ? trim( str_replace( [ '<br>', '<br />', '<br/>', '</div>' ], ' ', $bio_array[ $idx ]['desc'] ) ) : '';

		// Medaillon is displayed in a popup person page, build internal URL.
		if ( str_contains( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), $this->config_class->lumiere_urlstringperson ) && strlen( $bio_text ) > 0 ) {
			$bio_text = $this->lumiere_imdburl_to_internalurl( $bio_text );

			// This is a taxonomy page, build popup URL.
		} elseif ( is_tax() && strlen( $bio_text ) > 0 ) {
			$bio_text = $this->lumiere_imdburl_of_taxonomy( $bio_text );
		}

		// No Links class, exit before building clickable biography, show everything at once
		if ( $window_type === 1 ) {
			return $bio_head . "\n\t\t\t" . $bio_text;
		}

		/**
		 * HTML tags break for 'read more' cutting.
		 * 1/ Max length can't be greater that the total number of chara
		 * 2/ Detects if there is a space next to $max_length; if true, increase the latter to that position.
		 *  (a) Use of htmlentities to avoid spaces inside html code (ie innerspace in '<br />').
		 */
		$max_length = $max_length > strlen( $bio_text ) ? strlen( $bio_text ) : $max_length;
		$find_html = strpos( htmlentities( $bio_text ), ' ', $max_length );
		$max_length = strlen( $bio_text ) > 0 && is_int( $find_html ) ? $find_html : $max_length;

		// Detects if there is html a tag before reaching $max_length.
		// If true increase max length up to first '/a>' + 3 chars (since the search is made with 3 chars).
		$last_a_html = strpos( $bio_text, '/a>' ) !== false ? strpos( $bio_text, '/a>', $max_length ) : false;
		$esc_html_breaker = strpos( $bio_text, '<a' ) <= $max_length && is_int( $last_a_html )
			? $last_a_html + 3
			: $max_length;

		// There is 1/ a bio, and 2/ its length is greater to above $esc_html_breaker
		if ( strlen( $bio_text ) !== 0 && strlen( $bio_text ) > $esc_html_breaker ) {

			// If $esc_html_breaker comes after $max_length, go for it.
			$max_length = $max_length < $esc_html_breaker ? $esc_html_breaker : $max_length;

			/** @psalm-suppress PossiblyFalseArgument -- Argument 3 of substr cannot be false, possibly int|null value expected => Never false! */
			$str_one = substr( $bio_text, 0, $max_length );
			/** @psalm-suppress PossiblyFalseArgument -- Argument 3 of substr cannot be false, possibly int|null value expected => Never false! */
			$str_two = substr( $bio_text, $max_length, strlen( $bio_text ) );

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
	 * @param int $window_type Define the window_type: 0 for links (default), 1 for no links
	 *
	 * @return string
	 */
	protected function lumiere_imdburl_to_internalurl_abstract( string $text, int $window_type = 0 ): string {

		$internal_link_person = '';
		$internal_link_movie = '';

		if ( intval( $window_type ) === 0 ) {
			$internal_link_person = '<a class="lum_popup_internal_link lum_add_spinner" href="' . $this->config_class->lumiere_urlpopupsperson . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';
			$internal_link_movie = '<a class="lum_popup_internal_link lum_add_spinner lum_popup_link_with_movie" href="' . $this->config_class->lumiere_urlpopupsfilms . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';
		}

		// Regexes. \D{21} 21 characters for 'https://www.imdb.com/'.
		// Common pattern.
		$rule_person = '~(<a href=")(\D{21})(name\/nm)(\d{7})(\?.+?|\/?)">~';
		$rule_movie = '~(<a href=")(\D{21})(title\/tt)(\d{7})(\?ref.+?|\/?)">~';

		// Pattern found in soundtrack.
		if ( strpos( $text, 'https://www.imdb.com/' ) === false ) {
			$rule_person = '~(<a href=")(\/name\/)(nm)(\d{7})(\?.+?|\/?)">~';
			$rule_movie = '~(<a href=")(\/title\/)(tt)(\d{7})(\?.+?|\/?)">~';
		}

		// Replace IMDb links with internal links.
		$output_one = preg_replace( $rule_person, $internal_link_person, $text ) ?? $text;
		$output_two = preg_replace( $rule_movie, $internal_link_movie, $output_one ) ?? $text;

		return $output_two;

	}

	/**
	 * Convert an IMDb url into a popup link for People and Movies in Taxonomy pages
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 * @param int $window_type Define the window_type: 0 for classic links (default), 1 regular popups, 2 for no links, 3 for bootstrap
	 * @param string $specific_class Extra class to be added in popup building link, none by default
	 *
	 * @return string
	 */
	protected function lumiere_imdburl_of_taxonomy_abstract( string $text, int $window_type = 0, string $specific_class = '' ): string {

		$popup_link_person = '';
		$popup_link_movie = '';

		switch ( $window_type ) {
			case 0: // Build modal classic window popups.
				$popup_link_person = '<a class="lum_taxo_link lum_link_with_people ' . $specific_class . '" data-modal_window_people="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>';
				$popup_link_movie = '<a class="lum_taxo_link lum_link_with_movie ' . $specific_class . '" data-modal_window_filmid="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>';
				break;
			case 1: // Build internal links with no popups.
				$popup_link_person = '<a class="lum_taxo_link" href="' . $this->config_class->lumiere_urlpopupsperson . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . ' ${6}">${6}</a>';
				$popup_link_movie = '<a class="lum_taxo_link" href="' . $this->config_class->lumiere_urlpopupsfilms . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . ' ${6}">${6}</a>';
				break;
			case 2: // No links class
				$popup_link_person = '${6}';
				$popup_link_movie = '${6}';
				break;
			case 3: // Bootstrap popups
				$popup_link_person = '<a class="lum_taxo_link lum_link_with_people" data-modal_window_people="${4}" data-target="#theModal${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>'
				. $this->bootstrap_modal( '${4}', '${6}' );

				$popup_link_movie = '<a class="lum_taxo_link lum_link_with_movie" data-modal_window_filmid="${4}" data-target="#theModal${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>'
				. $this->bootstrap_modal( '${4}', '${6}' );
				break;
		}

		// Regexes. \D{21} 21 characters for 'https://www.imdb.com/'.
		$rule_name = '~(<a href=")(\D{21})(name\/nm)(\d{7})(\/\?.+?|\?.+?|\/?)">(.*?)<\/a>~';
		$rule_title = '~(<a href=")(\D{21})(title\/tt)(\d{7})(\?ref.+?|\/?)">(.*?)<\/a>~';

		// Replace IMDb links with popup links.
		$output_one = preg_replace( $rule_name, $popup_link_person, $text ) ?? $text;
		$output_two = preg_replace( $rule_title, $popup_link_movie, $output_one ) ?? $text;

		return $output_two;
	}

	/**
	 * Convert an IMDb source url of posts -- basically, no URL
	 *
	 * @param string $text Text that includes IMDb URL to convert
	 * @param int $window_type Define the window_type: 0 for classic links (default), 1 regular popups, 2 for no links, 3 for bootstrap
	 * @param string $specific_class Extra class to be added in popup building link, none by default
	 *
	 * @return string
	 */
	protected function lumiere_imdburl_of_soundtrack_abstract( string $text, int $window_type = 0, string $specific_class = '' ): string {

		$rule_name = '~(<a href=")(\/name\/)(nm)(\d{7})(\?.+?|\/?)">(.*?)<\/a>~';
		$rule_title = '~(<a href=")(\/title\/)(tt)(\d{7})(\?.+?|\/?)">(.*?)<\/a>~';

		// Replace IMDb links with popup links.
		$output_one = preg_replace( $rule_name, '${6}', $text ) ?? $text;
		$output_two = preg_replace( $rule_title, '${6}', $output_one ) ?? $text;

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
	 * @since 4.0.1 Added spinner and dialog size
	 * @return string
	 */
	private function bootstrap_modal( string $imdb_id, string $imdb_data ): string {

		return "\n\t\t\t" . '<span class="modal fade" id="theModal' . $imdb_id . '">'
			. "\n\t\t\t\t" . '<span id="bootstrapp' . $imdb_id . '" class="modal-dialog modal-dialog-centered' . esc_attr( $this->bootstrap_convert_modal_size() ) . '" role="dialog">'
			. "\n\t\t\t\t\t" . '<span class="modal-content">'
			. "\n\t\t\t\t\t\t" . '<span class="modal-header bootstrap_black"><span id="lumiere_bootstrap_spinner_id" role="status" class="spinner-border"><span class="sr-only"></span></span>'
			// . esc_html__( 'Informations about', 'lumiere-movies' ) . ' ' . sanitize_text_field( ucfirst( $imdb_data ) )
			. '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-target="theModal' . $imdb_id . '"></button>'
			. "\n\t\t\t\t\t\t" . '</span>'
			. "\n\t\t\t\t\t\t" . '<span class="modal-body embed-responsive embed-responsive-16by9"></span>'
			. "\n\t\t\t\t\t" . '</span>'
			. "\n\t\t\t\t" . '</span>'
			. "\n\t\t\t" . '</span>';

	}

	/**
	 * Get popup width and convert it to an incremental bootstrap size
	 *
	 * @return string
	 * @since 4.0.1
	 */
	private function bootstrap_convert_modal_size(): string {

		$modal_standard_with = [
			300 => 'modal-sm',
			500 => '',
			800 => 'modal-lg',
			1140 => 'modal-xl',
		];

		$modal_size_name = '';
		foreach ( $modal_standard_with as $size_width => $size_name ) {
			if ( $this->imdb_admin_values['imdbpopuplarg'] >= $size_width ) {
				$modal_size_name = ' ' . $size_name;
			}
		}

		return strlen( $modal_size_name ) > 0 ? $modal_size_name : '';
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
	 * @param int $window_type Define the window_type: 0 for highslide & classic links (default), 1 bootstrap popups, 2 for no links, 3 for AMP
	 * @param string $specific_a_class Extra class to be added in popup building link, none by default
	 *
	 * @return string
	 */
	protected function lumiere_link_popup_people_abstract ( string $imdbid, string $imdbname, int $window_type = 0, string $specific_a_class = '' ): string {

		// No link creation, exit
		if ( intval( $window_type ) === 2 ) {
			return esc_attr( $imdbname );
		}

		// Building link.
		$txt = "\n\t\t\t" . '<a class="' . $specific_a_class . '"' . " id=\"link-$imdbid\""
		. ' data-modal_window_people="' . sanitize_text_field( $imdbid ) . '"'
		// Data target is utilised by bootstrap only, but should be safe to keep it.
		. ' data-target="#theModal' . sanitize_text_field( $imdbid ) . '"'
		. ' title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '"';
		// AMP, build a HREF.
		if ( intval( $window_type ) === 3 ) {
			$txt .= ' href="' . esc_url( $this->config_class->lumiere_urlpopupsperson . '?mid=' . $imdbid ) . '"';
		}
		$txt .= '>' . sanitize_text_field( $imdbname ) . '</a>';

		// Modal bootstrap HTML part.
		if ( intval( $window_type ) === 1 ) {
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
	 * @param int $window_type Define the window_type: 0 for highslide & classic links (default), 1 bootstrap popups, 2 for no links & AMP
	 * @param string $specific_class Extra class to be added in popup building link, none by default
	 *
	 * @return string
	 */
	protected function lumiere_popup_film_link_abstract ( array $link_parsed, ?string $popuplarg = null, ?string $popuplong = null, int $window_type = 0, string $specific_class = '' ): string {

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
		if ( $window_type === 0 ) {

			$txt = '<a class="lum_link_with_movie" data-modal_window_film="' . $title_attr . '" title="' . esc_html__( 'Open a new window with IMDb informations', 'lumiere-movies' ) . '">' . $title_esc . '</a>';

			// Bootstrap modal
		} elseif ( $window_type === 1 ) {

			$txt = '<a class="lum_link_with_movie" data-modal_window_film="' . $title_attr . '" data-target="#theModal' . $title_attr . '" title="' . esc_html__( 'Open a new window with IMDb informations', 'lumiere-movies' ) . '">' . $title_esc . '</a>'
			. $this->bootstrap_modal( $title_attr, $title_esc );

			// AMP & No Link modal
		} elseif ( $window_type === 2 ) {

			$txt = '<a class="lum_link_make_popup lum_link_with_movie" href="' . $this->config_class->lumiere_urlpopupsfilms . '?film=' . $title_attr . '" title="' . esc_html__( 'No Links', 'lumiere-movies' ) . '">' . $title_esc . '</a>';

		}

		return $txt;

	}

	/**
	 * Trailer data details
	 *
	 * @param string $url Url to the trailer
	 * @param string $website_title website name
	 * @param int $window_type Define the window_type: 0 for highslide, bootstrap, AMP & classic links (default), 1 for no links
	 * @return string
	 */
	protected function lumiere_movies_trailer_details_abstract ( string $url, string $website_title, int $window_type = 0 ): string {

		// No Links class, do not display any link.
		if ( $window_type === 1 ) {
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
	 * @param int $window_type Define the window_type: 0 for highslide, bootstrap classic links (default), 1 for no links & AMP
	 * @return string
	 */
	protected function lumiere_movies_prodcompany_details_abstract ( string $name, string $url = '', string $notes = '', int $window_type = 0 ): string {

		// No Links class or AMP, do not display any link.
		if ( $window_type === 1 ) {
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
	 * @param int $window_type Define the window_type: 0 for highslide, bootstrap, AMP & classic links (default), 1 for no links
	 * @return string
	 */
	protected function lumiere_movies_officialsites_details_abstract ( string $url, string $name, int $window_type = 0 ): string {

		// No Links class, do not display any link.
		if ( $window_type === 1 ) {
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
	 * @param int $window_type Define the window_type: 0 for AMP, highslide, bootstrap & classic links (default), 1 for No links
	 * @param null|string $class extra class to add, only AMP does not use it
	 * @return string
	 */
	protected function lumiere_movies_source_details_abstract ( string $mid, int $window_type = 0, ?string $class = null ): string {

		// No Links class, do not return links.
		if ( $window_type === 1 ) {
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
			. '<a class="lum_link_sourceimdb" title="'
			. esc_html__( 'Go to IMDb website for this movie', 'lumiere-movies' ) . '" href="'
			. esc_url( 'https://www.imdb.com/title/tt' . $mid ) . '" >'
			. '&nbsp;&nbsp;'
			. esc_html__( "IMDb's page for this movie", 'lumiere-movies' ) . '</a>';

		return $return;

	}
}
