<?php declare( strict_types = 1 );
/**
 * Class for building links
 *
 * @copyright (c) 2022, Lost Highway
 *
 * @version       2.0
 * @package       lumieremovies
 */

namespace Lumiere\Frontend\Link_Maker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Config\Open_Options;
use Lumiere\Config\Get_Options;
use Lumiere\Frontend\Layout\Output_Linkmaker;
use Exception;

/**
 * Polyphormism using the interface
 * The child classes, called in the factory, use then this parent class adding only the extra LINK_OPTIONS so we know what process should be run
 * Child classes also take care of calling styles and javascripts they need
 *
 * @since 3.8
 * @since 4.5 renamed methods to make them shorter and more meaningful, new constant LINK_OPTIONS (using it in child classes for code simplification), use of Output_linkmaker class
 */
class Implement_Methods {

	/**
	 * Traits
	 */
	use Open_Options;

	/**
	 * Numbers to identify the link class used
	 * Used by child classes
	 */
	protected const LINK_OPTIONS = [
		'classic'   => 0,
		'highslide' => 1,
		'bootstrap' => 2,
		'nolinks'   => 3,
		'amp'       => 4,
	];

	/**
	 * Constructor
	 */
	public function __construct(
		private Output_Linkmaker $output_linkmaker_class = new Output_Linkmaker(),
	) {
		// Get global settings class properties.
		$this->get_db_options(); // In Open_Options trait.
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
	protected function get_rating_picture_details(
		int $rating,
		int $votes,
		string $votes_average_txt,
		string $out_of_ten_txt,
		string $votes_txt,
		int $with_imdb_element_rating
	): string {
		$find_showtimes_pic = round( $rating * 2, 0 ) / 0.2;
		return "\n\t\t\t\t" . '<span class="lum_results_section_subtitle">' . esc_html__( 'Rating', 'lumiere-movies' ) . ':</span>&nbsp;' . "\n\t\t\t\t" . '<img class="imdbelementRATING-picture" src="' . Get_Options::LUM_PICS_SHOWTIMES_URL . $find_showtimes_pic . ".gif\" title=\"$votes_average_txt $rating $out_of_ten_txt\" alt=\"$votes_average_txt\" width=\"102\" height=\"12\" />" . ' (' . number_format( $votes, 0, '', "'" ) . ' ' . $votes_txt . ')';
	}

	/**
	 * Build picture of the movie into the post, widget and taxonomy pages
	 *
	 * @param string|bool $photo_big_cover The picture of big size
	 * @param string|bool $photo_thumb The picture of small size
	 * @param string $title_text Title of the movie/Name of the person
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @param string $a_class Extra class to be added in the building link, none by default
	 * @param string $img_class Extra class to be added in the building link, none by default
	 *
	 * @return string
	 */
	protected function get_picture_details(
		string|bool $photo_big_cover,
		string|bool $photo_thumb,
		string $title_text,
		int $window_type,
		string $a_class = '',
		string $img_class = '',
	): string {

		$output = '';

		// Make sure $photo_thumb is a string so we can use esc_html() function
		$photo_localurl = is_string( $photo_thumb ) ? esc_html( $photo_thumb ) : '';

		// Any class but AMP
		if ( $window_type !== self::LINK_OPTIONS['amp'] ) {
			// Select picture: if 1/ big picture exists, so use it, use thumbnail otherwise
			$photo_localurl = is_string( $photo_big_cover ) && strlen( $photo_big_cover ) > 1 ? $photo_big_cover : $photo_localurl;
		}

		// Picture for a href: if 2/ big/thumbnail picture exists, use it (in 1), use no_pics otherwise
		$photo_url_final_href = strlen( $photo_localurl ) === 0 ? esc_url( Get_Options::LUM_PICS_URL . 'no_pics.gif' ) : $photo_localurl;

		// Picture for img: if 1/ thumbnail picture exists, use it, 2/ use no_pics otherwise
		$photo_url_final_img = is_string( $photo_thumb ) === false || strlen( $photo_thumb ) === 0 ? esc_url( Get_Options::LUM_PICS_URL . 'no_pics.gif' ) : $photo_thumb;

		// Highslide, classic or Bootstrap class
		if ( $window_type === self::LINK_OPTIONS['highslide'] || $window_type === self::LINK_OPTIONS['classic'] || $window_type === self::LINK_OPTIONS['bootstrap'] ) {
			$output .= "\n\t\t\t\t\t" . '<a class="' . esc_attr( $a_class ) . '" title="' . esc_attr( $title_text ) . '" href="' . esc_url( $photo_url_final_href ) . '">';
			// AMP or No Links class
		} elseif ( $window_type === self::LINK_OPTIONS['nolinks'] || $window_type === self::LINK_OPTIONS['amp'] ) {
			$output .= '';
		}

		// Build image HTML tag <img>
		$output .= "\n\t\t\t\t\t\t" . '<img ';
		// AMP class, loading="XXX" breaks AMP
		if ( $window_type !== self::LINK_OPTIONS['amp'] ) {
			$output .= 'loading="lazy"';
		}

		/**
		 * Add width="SizeXXXpx" and display the big cover image if "Display only thumbnail" is not selected
		 * If "display only thumbnail" is selected, thumb image is displayed and width is set to 100
		 * @since 3.7
		 */
		if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {
			$width = intval( $this->imdb_admin_values['imdbcoversizewidth'] );
			$height = $width * 1.4;
			$output .= ' class="' . esc_attr( $img_class ) . '" src="' . esc_url( $photo_url_final_img ) . '" alt="'
				/* Translators: %1s is a name, ie Stanley Kubrick */
				. wp_sprintf( __( 'Photo of %1s', 'lumiere-movies' ), $title_text )
				. esc_attr( $title_text ) . '" width="' . esc_attr( strval( $width ) ) . '" height="' . esc_attr( strval( $height ) ) . '" />';

			// set to 100 width and 160 height if "Display only thumbnail" is active
		} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {
			$output .= ' class="' . esc_attr( $img_class ) . '" src="' . esc_url( $photo_url_final_img ) . '" alt="'
				/* Translators: %1s is a name, ie Stanley Kubrick */
				. wp_sprintf( __( 'Photo of %1s', 'lumiere-movies' ), $title_text ) . '" height="160" width="100" />';
		}

		// Not classic links, so we can close <a>
		if ( $window_type === self::LINK_OPTIONS['highslide'] || $window_type === self::LINK_OPTIONS['classic'] || $window_type === self::LINK_OPTIONS['bootstrap'] ) {
			$output .= "\n\t\t\t\t\t" . '</a>';
		}

		return $this->output_linkmaker_class->main_layout( 'item_picture', $output );
	}

	/**
	 * Display mini biographical text, not all people have one
	 *
	 * 1- Cut the maximum of characters to be displayed with 'click_more_start' and 'click_more_end'
	 * 2- Detect if there is html tags that can break with $esc_html_breaker
	 * 3- Build links either to popups (if taxonomy) or internal links (if popup people)
	 *
	 * @param array<array<string, string>> $bio_array Array of the object _IMDBPHPCLASS_->bio()
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @param int $limit_text_bio Optional, increasing the hardcoded limit of characters before displaying "click for more"
	 * @return string
	 *
	 * @since 4.1 added $limit_text_bio param
	 */
	protected function get_medaillon_bio_details( array $bio_array, int $window_type, int $limit_text_bio = 0 ): string {

		if ( count( $bio_array ) === 0 ) {
			return $this->output_linkmaker_class->misc_layout( 'frontend_no_results', __( 'No biography available', 'lumiere-movies' ) );
		}

		/** Vars */
		$max_length = $limit_text_bio !== 0 ? $limit_text_bio : 200; // maximum number of characters before cutting, 200 is perfect for popups.
		$bio_head = $this->output_linkmaker_class->misc_layout( 'frontend_subtitle_item', __( 'Biography', 'lumiere-movies' ) );
		$bio_text = '';

		// Get the first bio result.
		$bio_text = $bio_array[0]['desc'] ?? '';

		// Medaillon is displayed in a popup person page, build internal URL.
		/**
		 * @deprecated, biography doesn't include links anymore
		if ( str_contains( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), Get_Options::get_popup_url( 'person' ) ) && strlen( $bio_text ) > 0 ) {
			$bio_text = $this->convert_imdburl_to_internalurl( $bio_text );

			// This is a taxonomy page, build popup URL.
		} elseif ( is_tax() && strlen( $bio_text ) > 0 ) {
			$bio_text = $this->convert_imdburl_to_taxonomy( $bio_text );
		}*/

		// No Links class, exit before building clickable biography, show everything at once
		if ( $window_type === self::LINK_OPTIONS['nolinks'] || $window_type === self::LINK_OPTIONS['amp'] ) {
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

		// There is 1/ a bio, and 2/ its length is greater that above $esc_html_breaker
		if ( strlen( $bio_text ) > 0 && strlen( $bio_text ) > $esc_html_breaker ) {

			// If $esc_html_breaker comes after $max_length, go for it.
			$max_length = $max_length < $esc_html_breaker ? $esc_html_breaker : $max_length;

			/** @psalm-suppress PossiblyFalseArgument -- Argument 3 of substr cannot be false, possibly int|null value expected => Never false! */
			$str_one = substr( $bio_text, 0, $max_length );
			/** @psalm-suppress PossiblyFalseArgument -- Argument 3 of substr cannot be false, possibly int|null value expected => Never false! */
			$str_two = substr( $bio_text, $max_length, strlen( $bio_text ) );

			$bio_text = "\n\t\t\t" . $str_one . $this->output_linkmaker_class->misc_layout( 'see_all_start' ) . "\n\t\t\t" . $str_two . $this->output_linkmaker_class->misc_layout( 'see_all_end' );

		}
		return strlen( $bio_text ) > 0 ? $bio_head . "\n\t\t\t" . $bio_text : '';
	}

	/**
	 * Convert an IMDb url into an internal link for People and Movies
	 * Meant to be used inside popups (not in posts or widgets)
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 *
	 * @return string
	 *
	 * @see Implement_Link_Maker::get_medaillon_bio_details() used only there!
	 * @deprecated No more in use, was utilised to parse things like trivia, trademarks, etc, that don't included imdb links anymore
	 */
	/*private function convert_imdburl_to_internalurl( string $text, int $window_type ): string {

		$internal_link_person = '';
		$internal_link_movie = '';

		if ( $window_type === self::LINK_OPTIONS['highslide'] || $window_type === self::LINK_OPTIONS['classic'] || $window_type === self::LINK_OPTIONS['bootstrap'] ) {
			$internal_link_person = '<a class="lum_popup_internal_link lum_add_spinner" href="' . wp_nonce_url( Get_Options::get_popup_url( 'person', site_url() ) . '?mid=${4}' ) . '" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';
			$internal_link_movie = '<a class="lum_popup_internal_link lum_add_spinner lum_popup_link_with_movie" href="' . wp_nonce_url( Get_Options::get_popup_url( 'film', site_url() ) . '?mid=${4}' ) . '" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . '">';
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

	}*/

	/**
	 * Convert an IMDb url into a popup link for People and Movies in Taxonomy pages
	 *
	 * @param string $text Text that includes IMDb URL to convert into an internal link
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @param string $specific_class Extra class to be added in popup building link, none by default
	 *
	 * @return string
	 * @see Implement_Link_Maker::get_medaillon_bio_details() used only there!
	 * @deprecated, medaillon text doesn't include links anymore, remove it
	 */
	/*private function convert_imdburl_to_taxonomy( string $text, int $window_type, string $specific_class = '' ): string {

		$popup_link_person = '';
		$popup_link_movie = '';

		switch ( $window_type ) {
			case 0: // Build modal classic window popups.
				$popup_link_person = '<a class="lum_taxo_link lum_link_with_people ' . $specific_class . '" data-modal_window_people="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>';
				$popup_link_movie = '<a class="lum_taxo_link lum_link_with_movie ' . $specific_class . '" data-modal_window_filmid="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>';
			case 1: // Build modal highslide window popups (=classic).
				$popup_link_person = '<a class="lum_taxo_link lum_link_with_people ' . $specific_class . '" data-modal_window_people="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>';
				$popup_link_movie = '<a class="lum_taxo_link lum_link_with_movie ' . $specific_class . '" data-modal_window_filmid="${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>';
				break;
			case 2: // Bootstrap popups
				$popup_link_person = '<a class="lum_taxo_link lum_link_with_people" data-modal_window_people="${4}" data-target="#theModal${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>'
				. $this->output_linkmaker_class->bootstrap_modal( '${4}', '${6}', $this->imdb_admin_values );
				$popup_link_movie = '<a class="lum_taxo_link lum_link_with_movie" data-modal_window_filmid="${4}" data-target="#theModal${4}" title="' . esc_html__( 'open a new window with IMDb informations', 'lumiere-movies' ) . '">${6}</a>'
				. $this->output_linkmaker_class->bootstrap_modal( '${4}', '${6}', $this->imdb_admin_values );
				break;
			case 3: // No links class
				$popup_link_person = '${6}';
				$popup_link_movie = '${6}';
				break;
			case 4: // Build internal links with no popups.
				$popup_link_person = '<a class="lum_taxo_link" href="' . Get_Options::get_popup_url( 'person', site_url() ) . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . ' ${6}">${6}</a>';
				$popup_link_movie = '<a class="lum_taxo_link" href="' . Get_Options::get_popup_url( 'film', site_url() ) . '?mid=${4}" title="' . esc_html__( 'internal link to', 'lumiere-movies' ) . ' ${6}">${6}</a>';
				break;
		}

		// Regexes. \D{21} 21 characters for 'https://www.imdb.com/'.
		$rule_name = '~(<a href=")(\D{21})(name\/nm)(\d{7})(\/\?.+?|\?.+?|\/?)">(.*?)<\/a>~';
		$rule_title = '~(<a href=")(\D{21})(title\/tt)(\d{7})(\?ref.+?|\/?)">(.*?)<\/a>~';

		// Replace IMDb links with popup links.
		$output_one = preg_replace( $rule_name, $popup_link_person, $text ) ?? $text;
		$output_two = preg_replace( $rule_title, $popup_link_movie, $output_one ) ?? $text;

		return $output_two;
	}*/

	/**
	 * Plots data details, removing all links => no links anymore, removed wp_strip_all_tags()
	 *
	 * @param string $plot Text of the plot
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @return string
	 */
	protected function get_plot_details( string $plot, int $window_type ): string {
		// return "\n\t\t\t\t" . wp_strip_all_tags( $plot );
		return "\n\t\t\t\t" . $plot;
	}

	/**
	 * Build a Popup person link based on the imdbid
	 *
	 * @param string $imdbid IMDB id
	 * @param string $imdbname Name of the person
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @param string $a_class Extra class to be added in popup building link, none by default
	 * @return string
	 */
	protected function get_popup_people_details( string $imdbid, string $imdbname, int $window_type, string $a_class = '' ): string {

		// No link creation, exit
		if ( $window_type === self::LINK_OPTIONS['nolinks'] ) {
			return esc_html( $imdbname );
		}

		// Building link.
		$output = "\n\t\t\t\t\t" . '<a class="add_cursor ' . esc_attr( $a_class ) . '"' . " id=\"link-$imdbid\""
		. ' data-modal_window_nonce="' . wp_create_nonce() . '"'
		. ' data-modal_window_people="' . esc_attr( $imdbid ) . '"'
		// Data target is utilised by bootstrap only, but should be safe to keep it.
		. ' data-target="#theModal' . esc_attr( $imdbid ) . '"'
		/* Translators: %1s is a name, ie Stanley Kubrick */
		. ' title="' . esc_attr( wp_sprintf( __( 'Open a new window with IMDb informations for %1s', 'lumiere-movies' ), $imdbname ) ) . '"';

		// AMP, build a HREF.
		if ( $window_type === self::LINK_OPTIONS['amp'] ) {
			$output .= ' href="' . esc_url( wp_nonce_url( Get_Options::get_popup_url( 'person', site_url() ) . '?mid=' . $imdbid ) ) . '"';
		}

		$output .= '>' . esc_html( $imdbname ) . '</a>';

		// Modal bootstrap HTML part.
		if ( $window_type === self::LINK_OPTIONS['bootstrap'] ) {
			$output .= $this->output_linkmaker_class->bootstrap_modal( $imdbid, $imdbname, $this->imdb_admin_values );
		}

		return $output;
	}

	/**
	 * Build a Popup movie link based on the title
	 *
	 * @param string $title Either the movie's title or person name found in inside the post
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @param string $a_class Class to be added in popup building link, none by default
	 *
	 * @return string
	 * @see \Lumiere\Frontend\Movie\Front_Parser::build_popup_link() uses this method
	 */
	protected function get_popup_film_title_details( string $title, int $window_type, string $a_class = '' ): string {

		// Highslide & Classic modal.
		if ( $window_type === self::LINK_OPTIONS['classic'] || $window_type === self::LINK_OPTIONS['highslide'] ) {
			/* Translators: %1s is a movie's name, ie Full Metal Jacket */
			return '<a class="add_cursor ' . esc_attr( $a_class ) . '" data-modal_window_nonce="' . wp_create_nonce() . '" data-modal_window_film="' . sanitize_title( $title ) . '" title="' . esc_attr( wp_sprintf( __( 'Open a new window with IMDb informations for %1s', 'lumiere-movies' ), ucfirst( $title ) ) ) . '">' . esc_html( $title ) . '</a>';

			// Bootstrap modal.
		} elseif ( $window_type === self::LINK_OPTIONS['bootstrap'] ) {
			/* Translators: %1s is a movie's name, ie Full Metal Jacket */
			return '<a class="add_cursor ' . esc_attr( $a_class ) . '" data-modal_window_nonce="' . wp_create_nonce() . '" data-modal_window_film="' . sanitize_title( $title ) . '" data-target="#theModal' . sanitize_title( $title ) . '" title="' . esc_attr( wp_sprintf( __( 'Open a new window with IMDb informations for %1s', 'lumiere-movies' ), ucfirst( $title ) ) ) . '">' . esc_html( $title ) . '</a>'
			. $this->output_linkmaker_class->bootstrap_modal( sanitize_title( $title ), '', $this->imdb_admin_values );

			// No Link modal.
		} elseif ( $window_type === self::LINK_OPTIONS['nolinks'] ) {
			return esc_html( $title );

			// AMP modal.
		} elseif ( $window_type === self::LINK_OPTIONS['amp'] ) {
			return '<a class="add_cursor lum_link_make_popup ' . esc_attr( $a_class ) . '" href="' . wp_nonce_url( Get_Options::get_popup_url( 'film', site_url() ) . '?film=' . sanitize_title( $title ) ) . '" title="' . esc_html__( 'No Links', 'lumiere-movies' ) . '">' . esc_html( $title ) . '</a>';
		}

		throw new Exception( 'No window_type found' );
	}

	/**
	 * Build a Popup movie link based on the title/name *ID*
	 *
	 * @param string $title The movie's title
	 * @param string $imdbid The movie's imdb ID
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @param string $a_class A class to be added in popup building link, none by default
	 * @return string
	 *
	 * @see \Lumiere\Frontend\Module\Parent_Module::get_popup_film() uses this method
	 */
	protected function get_popup_film_id_details( string $title, string $imdbid, int $window_type, string $a_class = '' ): string {

		// Highslide & Classic modal
		if ( $window_type === self::LINK_OPTIONS['highslide'] || $window_type === self::LINK_OPTIONS['classic'] ) {
			/* Translators: %1s is a movie's name, ie Full Metal Jacket */
			return '<a class="add_cursor ' . esc_attr( $a_class ) . '" data-modal_window_nonce="' . wp_create_nonce() . '" data-modal_window_filmid="' . esc_attr( $imdbid ) . '" title="' . esc_attr( wp_sprintf( __( 'Open a new window with IMDb informations for %1s', 'lumiere-movies' ), ucfirst( $title ) ) ) . '">' . esc_html( $title ) . '</a>';

			// Bootstrap modal.
		} elseif ( $window_type === self::LINK_OPTIONS['bootstrap'] ) {
			/* Translators: %1s is a movie's name, ie Full Metal Jacket */
			return '<a class="add_cursor ' . esc_attr( $a_class ) . '" data-modal_window_nonce="' . wp_create_nonce() . '" data-modal_window_filmid="' . esc_attr( $imdbid ) . '" data-target="#theModal' . sanitize_title( $title ) . '" title="' . esc_attr( wp_sprintf( __( 'Open a new window with IMDb informations for %1s', 'lumiere-movies' ), ucfirst( $title ) ) ) . '">' . esc_html( $title ) . '</a>' . $this->output_linkmaker_class->bootstrap_modal( $imdbid, '', $this->imdb_admin_values );

			// No Link modal.
		} elseif ( $window_type === self::LINK_OPTIONS['nolinks'] ) {
			return esc_html( $title );

			// AMP modal.
		} elseif ( $window_type === self::LINK_OPTIONS['amp'] ) {
			return '<a class="add_cursor lum_link_make_popup ' . esc_attr( $a_class ) . '" href="' . wp_nonce_url( Get_Options::get_popup_url( 'film', site_url() ) . '?film=' . esc_html( $title ) ) . '" title="' . esc_html__( 'No Links', 'lumiere-movies' ) . '">' . esc_html( $title ) . '</a>';

		}

		throw new Exception( 'No window_type found' );
	}

	/**
	 * Exernal URL
	 *
	 * @param string $website_title The URL's title
	 * @param string $url The external URL
	 * @param 0|1|2|3|4 $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @param string $a_class A class to be added in popup building link, none by default
	 * @return string
	 */
	protected function get_external_url_details( string $website_title, string $url, int $window_type, string $a_class = '' ): string {

		// No Links class, do not display any link.
		if ( $window_type === self::LINK_OPTIONS['nolinks'] ) {
			return "\n\t\t\t" . esc_html( $website_title );
		}
		/* Translators: %1s is a website name, ie "New 70mm Trailer" */
		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_html( wp_sprintf( __( 'External URL %1s', 'lumiere-movies' ), $website_title ) ) . "'>" . esc_html( $website_title ) . '</a>';

	}

	/**
	 * Trailer data details
	 *
	 * @param string $url Url to the trailer
	 * @param string $website_title website name
	 * @param 0|1|2|3|4 $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @return string
	 */
	protected function get_trailer_details( string $url, string $website_title, int $window_type ): string {

		// No Links class, do not display any link.
		if ( $window_type === self::LINK_OPTIONS['nolinks'] ) {
			return "\n\t\t\t" . esc_html( $website_title );
		}
		/* Translators: %1s is a website name, ie "New 70mm Trailer" */
		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_html( wp_sprintf( __( 'Watch on IMBb website the trailer for %1s', 'lumiere-movies' ), $website_title ) ) . "'>" . esc_html( $website_title ) . '</a>';

	}

	/**
	 * Production company data details
	 *
	 * @param string $name prod company name
	 * @param string $comp_id ID of the prod company
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @return string
	 */
	protected function get_prodcompany_details( string $name, string $comp_id = '', string $notes = '', int $window_type = 0 ): string {

		// No Links class or AMP, do not display any link.
		if ( $window_type === self::LINK_OPTIONS['nolinks'] || $window_type === self::LINK_OPTIONS['amp'] ) {
			return esc_attr( $name ) . '<br />';
		}

		$output = "\n\t\t\t" . '<div align="center" class="lumiere_container">'
			. "\n\t\t\t\t" . '<div class="lumiere_align_left lumiere_flex_auto">'
			. "\n\t\t\t\t\t<a href='" . esc_url( 'https://www.imdb.com/search/title/?companies=co' . $comp_id ) . "' title='" . esc_html( $name ) . "'>"
			. esc_html( $name )
			. '</a>'
			. "\n\t\t\t\t</div>"
			. "\n\t\t\t\t" . '<div class="lumiere_align_right lumiere_flex_auto">';

		if ( strlen( $notes ) !== 0 ) {
			$output .= esc_html( $notes );
		} else {
			$output .= '&nbsp;';
		}

		$output .= '</div>' . "\n\t\t\t</div>";

		return $output;
	}

	/**
	 * Official websites data details
	 *
	 * @param string $url Url to the offical website
	 * @param string $name Offical website name
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @return string
	 */
	protected function get_officialsites_details( string $url, string $name, int $window_type ): string {
		// No Links class, do not display any link.
		if ( $window_type === self::LINK_OPTIONS['nolinks'] ) {
			return "\n\t\t\t" . sanitize_text_field( $name );
		}
		return "\n\t\t\t<a href='" . esc_url( $url ) . "' title='" . esc_attr( $name ) . "'>" . esc_html( $name ) . '</a>';
	}

	/**
	 * Source data details
	 *
	 * @param string $mid IMDb ID of the movie
	 * @param int $window_type Define the window_type: 0 for highslide, 1 classic links, 2 bootstrap popups, 3 for no links, 4 for AMP
	 * @param null|string $class extra class to add, only AMP does not use it
	 * @return string
	 */
	protected function get_source_details( string $mid, int $window_type, ?string $class = null ): string {

		// No Links class, do not return links.
		if ( $window_type === self::LINK_OPTIONS['nolinks'] ) {
			return "\n\t\t\t"
				. '<img class="imdbelementSOURCE-picture" alt="link to imdb" width="33" height="15" src="'
				. esc_url(
					Get_Options::LUM_PICS_URL
					. '/imdb-link.png'
				) . '" />'
				. ' https://www.imdb.com/title/tt' . $mid;
		}

		$output = "\n\t\t\t" . '<img';

		// Add a class if requested, should be imdbelementSOURCE-picture, which breaks AMP
		if ( $class !== null ) {
			$output .= ' class="' . $class . '"';
		}

		$output .= ' alt="link to imdb" width="33" height="15" src="'
			. esc_url( Get_Options::LUM_PICS_URL . '/imdb-link.png' ) . '" />'
			. '<a class="lum_link_sourceimdb" title="'
			. esc_html__( 'Go to IMDb website for this movie', 'lumiere-movies' ) . '" href="'
			. esc_url( 'https://www.imdb.com/title/tt' . $mid ) . '" >'
			. '&nbsp;&nbsp;'
			. esc_html__( "IMDb's page for this movie", 'lumiere-movies' ) . '</a>';

		return $output;
	}
}
